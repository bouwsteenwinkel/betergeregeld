<?php

namespace App\Console\Commands;

use App\Models\Channel\Site;
use App\Services\Ai\AnthropicClient;
use App\Services\ChannelSites\ChannelImageGenerator;
use App\Services\Scheduling\Contracts\CalendarGateway;
use App\Services\Scheduling\StubCalendarGateway;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Throwable;

/**
 * Doorlicht een channel-site vóór go-live / vóór je er advertentiegeld op zet.
 *
 * Waarom dit bestaat: bijna alles wat hier misgaat, faalt STIL. De interne
 * leadmeldingen zitten in een try/catch met alleen een Log::warning, een
 * ontbrekende CMP-categorie laat ad_storage eeuwig op denied staan, en een
 * niet-gekoppelde agenda valt terug op de stub. Zonder deze check merk je pas
 * weken later dat er niets binnenkwam.
 *
 *   php artisan channels:preflight
 *   php artisan channels:preflight --channel=elektricien --strict
 *
 * Read-only: dit command wijzigt niets.
 */
class ChannelsPreflight extends Command
{
    protected $signature = 'channels:preflight
        {--channel=bedrijfswebsite : site-key of domein van het kanaal}
        {--strict : ook falen (exitcode 1) op waarschuwingen, voor in een deploy-script}';

    protected $description = 'Doorlicht een channel-site: mail, CMP, meting, agenda, AI-keys, migraties. Zegt in één keer wat go-live nog blokkeert.';

    private const GOED = 'GOED';
    private const WAARSCHUWING = 'WAARSCHUWING';
    private const FOUT = 'FOUT';
    private const INFO = 'INFO';

    /** @var list<array{status:string,naam:string,tekst:string}> */
    private array $resultaten = [];

    /** Tabellen die de funnel (voorbeeld bewaren + afspraak plannen) nodig heeft. */
    private const VEREISTE_TABELLEN = [
        'website_leads'          => 'de leads uit /voorbeeld-maken',
        'saved_previews'         => 'de "Bewaar dit voorbeeld"-knop',
        'appointments'           => 'de afsprakenplanner',
        'availability_rules'     => 'de beschikbaarheid van de planner',
        'availability_exceptions' => 'de uitzonderingen op de beschikbaarheid',
        'preview_intakes'        => 'het archief van niet-geconverteerde voorbeelden',
    ];

    /**
     * Kolommen op website_leads die de bewaar- en herinnerflow gebruikt. De
     * attributie-kolommen staan er bewust bij: SavePreviewController::save() vangt
     * geen QueryException, dus zonder die kolommen geeft de eerste bezoeker met
     * ?gclid= een 500 op precies het advertentieverkeer dat geld kost.
     */
    private const LEAD_KOLOMMEN = [
        'saved_at', 'preview_url', 'preview_status',
        'token_hash', 'token_expires_at',
        'reminder_stage', 'next_reminder_at', 'unsubscribed_at',
        'gclid', 'gbraid', 'wbraid',
        'utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content',
    ];

    /** Kolommen op appointments die de herinneringsjob elk kwartier aanraakt. */
    private const AFSPRAAK_KOLOMMEN = [
        'reminder_24h_sent_at', 'reminder_1h_sent_at',
    ];

    public function handle(): int
    {
        $arg  = trim((string) $this->option('channel'));
        $site = Site::query()->where('key', $arg)->orWhere('domain', $arg)->first();

        if (! $site) {
            $this->error("Site niet gevonden: {$arg}");
            $this->line('Bekende sites: ' . Site::query()
                ->where('key', 'not like', 'preview-%')
                ->pluck('key')->implode(', '));

            return self::FAILURE;
        }

        $this->info("Preflight: {$site->key} (" . ($site->domain ?: 'geen domein') . ")");
        $this->newLine();

        $this->checkMailFrom();
        $this->checkMailMailer();
        $this->checkAnthropic();
        $this->checkOpenAi();
        $this->checkTabellen();
        $this->checkLeadKolommen();
        $this->checkAfspraakKolommen();
        $this->checkSite($site);
        $this->checkCmp($site);
        $this->checkAnalytics();
        $this->checkBeschikbaarheid();
        $this->checkAgenda();

        return $this->rapporteer();
    }

    // ---------------------------------------------------------------- checks

    private function checkMailFrom(): void
    {
        $from = trim((string) config('mail.from.address'));

        // Dit adres is in dit project zowel afzender ALS ontvanger van alle
        // interne meldingen (nieuwe lead, nieuwe boeking, voorbeeld bewaard).
        if ($from === '') {
            $this->fout('mail-afzender', 'MAIL_FROM_ADDRESS is leeg. Interne leadmeldingen komen nergens aan en het faalt stil (alleen een Log::warning). Zet MAIL_FROM_ADDRESS in .env.');

            return;
        }

        if (! filter_var($from, FILTER_VALIDATE_EMAIL)) {
            $this->fout('mail-afzender', "MAIL_FROM_ADDRESS ({$from}) is geen geldig e-mailadres. Interne leadmeldingen falen stil.");

            return;
        }

        $domein = strtolower(substr(strrchr($from, '@') ?: '', 1));
        if ($from === 'hello@example.com' || $domein === 'example.com') {
            $this->fout('mail-afzender', "MAIL_FROM_ADDRESS staat nog op de voorbeeldwaarde ({$from}). Interne leadmeldingen komen nergens aan en het faalt stil. Zet MAIL_FROM_ADDRESS in .env op een echt adres.");

            return;
        }

        $this->goed('mail-afzender', "Meldingen gaan naar {$from}.");
    }

    private function checkMailMailer(): void
    {
        $mailer  = (string) config('mail.default');
        $mailers = (array) config('mail.mailers', []);
        $prod    = ! app()->environment('local', 'testing');

        if (! array_key_exists($mailer, $mailers)) {
            $this->fout('mail-transport', "MAIL_MAILER '{$mailer}' bestaat niet in config/mail.php. Verzenden gooit pas een exception op het moment zelf, en die wordt gevangen. Kies uit: " . implode(', ', array_keys($mailers)) . '.');

            return;
        }

        if (in_array($mailer, ['log', 'array'], true)) {
            $tekst = "MAIL_MAILER={$mailer}: mail wordt niet verstuurd (log schrijft naar storage/logs/laravel.log). Zet MAIL_MAILER op smtp (of ses/postmark/resend).";
            $prod ? $this->fout('mail-transport', $tekst) : $this->info_('mail-transport', "MAIL_MAILER={$mailer}: er vertrekt niets, mail gaat naar storage/logs/laravel.log. Prima lokaal, op productie een blokker.");

            return;
        }

        if ($mailer === 'smtp' && (string) config('mail.mailers.smtp.host') === '127.0.0.1') {
            $this->waarschuwing('mail-transport', 'MAIL_HOST staat nog op de default 127.0.0.1; dat is zelden een echte mailserver.');

            return;
        }

        $this->goed('mail-transport', "MAIL_MAILER={$mailer}.");
    }

    private function checkAnthropic(): void
    {
        try {
            // apiKey() gooit als de key nergens te vinden is; dat is precies de melding.
            $key = (new AnthropicClient())->apiKey();
        } catch (Throwable $e) {
            $this->fout('ai-tekst', 'Anthropic-key ontbreekt; /voorbeeld-maken gooit een RuntimeException en de funnel is stuk. Zet ANTHROPIC_API_KEY in .env of ANTHROPIC_KEY_PATH naar een config-file.');

            return;
        }

        if (str_starts_with($key, 'fake') || str_starts_with($key, 'xxxx')) {
            $this->fout('ai-tekst', 'Anthropic-key is een testwaarde (begint met fake/xxxx); de voorbeeldgenerator werkt niet.');

            return;
        }

        if (! str_starts_with($key, 'sk-ant-')) {
            $this->waarschuwing('ai-tekst', 'Anthropic-key heeft niet het gebruikelijke sk-ant--voorvoegsel; controleer of dit de juiste key is.');

            return;
        }

        $this->goed('ai-tekst', 'Anthropic-key gevonden.');
    }

    private function checkOpenAi(): void
    {
        if ((new ChannelImageGenerator())->isFake()) {
            $this->fout('ai-beeld', 'Geen echte OpenAI-key (leeg, of begint met fake/xxxx); beeldgeneratie draait in fake-modus en schrijft SVG-placeholders in plaats van beelden. Zet CHANNEL_IMAGES_OPENAI_KEY of OPENAI_API_KEY in .env.');

            return;
        }

        $this->goed('ai-beeld', 'OpenAI-key gevonden, beeldgeneratie draait echt.');
    }

    private function checkTabellen(): void
    {
        $missend = [];
        foreach (self::VEREISTE_TABELLEN as $tabel => $waarvoor) {
            if (! Schema::hasTable($tabel)) {
                $missend[] = "{$tabel} ({$waarvoor})";
            }
        }

        if ($missend !== []) {
            // `php artisan migrate` faalt in dit project (schema-dump + geen mysql-CLI),
            // dus de handmatige route hoort in de melding.
            $this->fout('migraties', 'Ontbrekende tabellen: ' . implode(', ', $missend) . '. Pas de migratie in database/migrations toe met php artisan tinker --execute=\'$m = require database_path("migrations/<bestand>.php"); $m->up();\'.');

            return;
        }

        $this->goed('migraties', count(self::VEREISTE_TABELLEN) . ' vereiste tabellen aanwezig.');
    }

    private function checkLeadKolommen(): void
    {
        if (! Schema::hasTable('website_leads')) {
            $this->fout('lead-kolommen', 'Tabel website_leads ontbreekt; zie de melding bij migraties.');

            return;
        }

        $missend = array_values(array_filter(
            self::LEAD_KOLOMMEN,
            fn (string $kolom) => ! Schema::hasColumn('website_leads', $kolom),
        ));

        if ($missend !== []) {
            $this->fout('lead-kolommen', 'website_leads mist: ' . implode(', ', $missend) . '. De bewaar-flow (Bewaar dit voorbeeld) crasht met een 500 en de herinneringsmails vallen stil. Draai 2026_07_14_140000_extend_website_leads_account.php en 2026_07_15_120000_add_attribution_to_website_leads.php.');

            return;
        }

        $this->goed('lead-kolommen', 'website_leads heeft alle kolommen voor de bewaar-flow en de klik-herkomst.');
    }

    /**
     * De herinneringsjob draait elk kwartier en stempelt op deze kolommen. Ontbreken
     * ze, dan gooit hij bij elke run en krijgt niemand een herinnering.
     */
    private function checkAfspraakKolommen(): void
    {
        if (! Schema::hasTable('appointments')) {
            $this->fout('afspraak-kolommen', 'Tabel appointments ontbreekt; zie de melding bij migraties.');

            return;
        }

        $missend = array_values(array_filter(
            self::AFSPRAAK_KOLOMMEN,
            fn (string $kolom) => ! Schema::hasColumn('appointments', $kolom),
        ));

        if ($missend !== []) {
            $this->fout('afspraak-kolommen', 'appointments mist: ' . implode(', ', $missend) . '. De job appointments:send-reminders crasht elk kwartier en er gaat geen enkele herinnering uit. Draai 2026_07_15_120000_add_reminder_columns_to_appointments.php.');

            return;
        }

        $this->goed('afspraak-kolommen', 'appointments heeft de kolommen voor de herinneringsjob.');
    }

    private function checkSite(Site $site): void
    {
        $domein = trim((string) $site->domain);
        $status = (string) ($site->status ?: 'draft');

        if ($domein === '') {
            $this->fout('site-domein', "Site '{$site->key}' heeft geen domein; alle URL's vallen terug op /_site/{$site->key} (ook canonicals en sitemap). Vul het domein in de admin.");
        } else {
            $this->goed('site-domein', $domein);
        }

        if ($status !== 'live') {
            $this->fout('site-status', "Site staat op status '{$status}'; die is noindex en niet bereikbaar op het eigen domein. Zet op live (of draai php artisan channel:go-live {$site->key}).");

            return;
        }

        $this->goed('site-status', 'live.');
    }

    private function checkCmp(Site $site): void
    {
        // De channel-sites laden de CMP hardcoded met tenant 'channels'
        // (resources/views/channels/layout.blade.php).
        $tenant = 'channels';

        if (! Schema::hasTable('cmp_policy') || ! Schema::hasTable('cmp_categories') || ! Schema::hasTable('cmp_domains')) {
            $this->fout('cmp-tenant', 'CMP-tabellen ontbreken. Draai: php artisan cmp:seed-channels');

            return;
        }

        $heeftPolicy = DB::table('cmp_policy')->where('tenant_key', $tenant)->exists();
        if (! $heeftPolicy) {
            $this->fout('cmp-tenant', "CMP-tenant '{$tenant}' ontbreekt; de cookiebanner laadt een lege config. Draai: php artisan cmp:seed-channels");

            return;
        }

        $this->goed('cmp-tenant', "Tenant '{$tenant}' bestaat.");

        $marketing = DB::table('cmp_categories')
            ->where('tenant_key', $tenant)->where('key', 'marketing')->first();

        if (! $marketing) {
            $this->fout('cmp-marketing', "CMP-categorie 'marketing' ontbreekt voor tenant '{$tenant}'; de bezoeker kan geen toestemming geven voor advertentiecookies, ad_storage blijft altijd denied en Ads meet geen enkele conversie. Draai: php artisan cmp:seed-channels");
        } elseif (! (int) $marketing->is_enabled) {
            $this->fout('cmp-marketing', "CMP-categorie 'marketing' staat uit (is_enabled=0); de bezoeker kan geen toestemming geven voor advertentiecookies, ad_storage blijft altijd denied. Zet de categorie aan in de CMP-admin.");
        } else {
            $this->goed('cmp-marketing', 'Bezoeker kan toestemmen voor advertentiecookies.');
        }

        $analytics = DB::table('cmp_categories')
            ->where('tenant_key', $tenant)->where('key', 'analytics')
            ->where('is_enabled', 1)->exists();

        if (! $analytics && trim((string) config('analytics.gtm_id')) !== '') {
            $this->fout('cmp-analytics', "GTM is geconfigureerd maar CMP-categorie 'analytics' ontbreekt of staat uit; analytics_storage blijft altijd denied en GA4 vuurt nooit.");
        } elseif (! $analytics) {
            $this->waarschuwing('cmp-analytics', "CMP-categorie 'analytics' ontbreekt of staat uit; zodra je GTM aanzet, meet je niets.");
        } else {
            $this->goed('cmp-analytics', 'Bezoeker kan toestemmen voor statistiek.');
        }

        $domein = trim((string) $site->domain);
        if ($domein === '') {
            return; // Al als FOUT gemeld bij site-domein.
        }

        $inCmp = DB::table('cmp_domains')
            ->where('tenant_key', $tenant)
            ->where('domain', $domein)
            ->where('status', 'active')
            ->exists();

        if (! $inCmp) {
            // saveConsent() laat domain_id dan gewoon null; de toestemming wordt
            // wel bewaard, maar zonder domein-koppeling (lastig te verantwoorden).
            $this->waarschuwing('cmp-domein', "Domein {$domein} staat niet actief in cmp_domains voor tenant '{$tenant}'; toestemming wordt zonder domein-koppeling opgeslagen. Voeg het domein toe in de CMP-admin.");

            return;
        }

        $this->goed('cmp-domein', "{$domein} staat in cmp_domains.");
    }

    private function checkAnalytics(): void
    {
        $gtm = trim((string) config('analytics.gtm_id'));
        $ga4 = trim((string) config('analytics.ga4_id'));

        if ($gtm === '' && $ga4 === '') {
            $this->waarschuwing('meting', 'Geen ANALYTICS_GTM_ID: er wordt niets gemeten en Google Ads kan niet optimaliseren. Zet ANALYTICS_GTM_ID in .env.');

            return;
        }

        if ($gtm !== '' && ! str_starts_with($gtm, 'GTM-')) {
            $this->waarschuwing('meting', "ANALYTICS_GTM_ID ({$gtm}) begint niet met GTM-; de container laadt niet en dat faalt stil in de browser.");

            return;
        }

        if ($ga4 !== '' && ! str_starts_with($ga4, 'G-')) {
            $this->waarschuwing('meting', "ANALYTICS_GA4_ID ({$ga4}) begint niet met G-.");

            return;
        }

        if ($gtm !== '' && $ga4 !== '') {
            $this->waarschuwing('meting', 'Zowel GTM als GA4 direct geconfigureerd; dat telt pageviews dubbel. Zet GA4 in de GTM-container en maak ANALYTICS_GA4_ID leeg.');

            return;
        }

        $this->goed('meting', $gtm !== '' ? "GTM {$gtm}." : "GA4 {$ga4} (zonder GTM).");
    }

    private function checkBeschikbaarheid(): void
    {
        if (! Schema::hasTable('availability_rules')) {
            $this->fout('beschikbaarheid', 'Tabel availability_rules ontbreekt; zie de melding bij migraties.');

            return;
        }

        $totaal = DB::table('availability_rules')->count();
        $actief = DB::table('availability_rules')->where('active', true)->count();

        if ($totaal === 0) {
            $this->waarschuwing('beschikbaarheid', "Geen availability_rules; de planner valt terug op config('scheduling.default_hours'), ma-vr 09:00-17:00. Is dat echt jouw beschikbaarheid? Zo nee, vul de regels.");

            return;
        }

        if ($actief === 0) {
            // SlotEngine kijkt naar where('active', true); is die set leeg, dan
            // gebruikt hij default_hours. Alles uitvinken zet de agenda dus
            // juist OPEN in plaats van dicht.
            $this->waarschuwing('beschikbaarheid', "Alle {$totaal} availability_rules staan op inactief; de planner valt daardoor terug op default_hours (ma-vr 09:00-17:00) in plaats van de agenda te sluiten.");

            return;
        }

        $this->goed('beschikbaarheid', "{$actief} actieve regel(s).");
    }

    private function checkAgenda(): void
    {
        $aan = (bool) config('scheduling.google.enabled');
        $gateway = app(CalendarGateway::class);
        $stub = $gateway instanceof StubCalendarGateway;

        if (! $aan) {
            $this->info_('agenda', 'GOOGLE_CALENDAR_ENABLED staat uit; boekingen gebruiken de stub. De klant krijgt een bevestiging, maar de afspraak staat in geen enkele agenda.');

            return;
        }

        if ($stub) {
            $this->fout('agenda', 'GOOGLE_CALENDAR_ENABLED=true maar de agenda is niet gekoppeld (geen refresh-token); boekingen vallen stil terug op de stub en landen in geen enkele agenda. Koppel de Google-agenda opnieuw.');

            return;
        }

        $this->info_('agenda', 'Google Calendar gekoppeld; boekingen landen in ' . config('scheduling.google.calendar_id') . '.');
    }

    // ------------------------------------------------------------ rapportage

    private function goed(string $naam, string $tekst): void
    {
        $this->resultaten[] = ['status' => self::GOED, 'naam' => $naam, 'tekst' => $tekst];
    }

    private function waarschuwing(string $naam, string $tekst): void
    {
        $this->resultaten[] = ['status' => self::WAARSCHUWING, 'naam' => $naam, 'tekst' => $tekst];
    }

    private function fout(string $naam, string $tekst): void
    {
        $this->resultaten[] = ['status' => self::FOUT, 'naam' => $naam, 'tekst' => $tekst];
    }

    /** info() is al bezet door Command zelf, vandaar de underscore. */
    private function info_(string $naam, string $tekst): void
    {
        $this->resultaten[] = ['status' => self::INFO, 'naam' => $naam, 'tekst' => $tekst];
    }

    private function rapporteer(): int
    {
        $fouten = $waarschuwingen = 0;

        foreach ($this->resultaten as $r) {
            $regel = sprintf('  %-14s %-16s %s', $r['status'], $r['naam'], $r['tekst']);

            if ($r['status'] === self::FOUT) {
                $fouten++;
                $this->line("<error>{$regel}</error>");
            } elseif ($r['status'] === self::WAARSCHUWING) {
                $waarschuwingen++;
                $this->line("<comment>{$regel}</comment>");
            } elseif ($r['status'] === self::GOED) {
                $this->line("<info>{$regel}</info>");
            } else {
                $this->line($regel);
            }
        }

        $this->newLine();
        $this->line(sprintf(
            'Klaar, %d check(s): %d fout, %d waarschuwing(en).',
            count($this->resultaten),
            $fouten,
            $waarschuwingen,
        ));

        if ($fouten > 0) {
            $this->line('<error>Niet klaar voor go-live: los de FOUT-regels eerst op.</error>');

            return self::FAILURE;
        }

        if ($waarschuwingen > 0 && $this->option('strict')) {
            $this->line('<comment>--strict: waarschuwingen tellen als blokker.</comment>');

            return self::FAILURE;
        }

        $this->line($waarschuwingen > 0
            ? '<comment>Geen blokkers, maar loop de waarschuwingen na voor je gaat adverteren.</comment>'
            : '<info>Klaar voor go-live.</info>');

        return self::SUCCESS;
    }
}
