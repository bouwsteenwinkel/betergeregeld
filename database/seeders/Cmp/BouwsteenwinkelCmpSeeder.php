<?php

namespace Database\Seeders\Cmp;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

/**
 * Seed CMP-config voor de Bouwsteenwinkel-tenant. Idempotent: gebruikt
 * upsert (DELETE + INSERT) per record-set zodat herhaaldelijk runnen
 * hetzelfde resultaat geeft.
 *
 * tenant_key conventie voor CMP: short-name string (bouwsteenwinkel,
 * betergeregeld). Niet de tenants.id-UUID — dat is een aparte abstractie.
 */
class BouwsteenwinkelCmpSeeder extends Seeder
{
    private const TK = 'bouwsteenwinkel';

    public function run(): void
    {
        $now = Carbon::now();
        $this->seedDomains($now);
        $this->seedCategories($now);
        $this->seedTexts($now);
        $this->seedBranding($now);
        $this->seedPolicy($now);
        // Scripts blijven leeg by default — tenant zet ze via Filament-admin
        // (of we kunnen later GA4/Meta Pixel als seed-voorbeelden toevoegen)

        $this->command->info('Bouwsteenwinkel CMP-tenant geseed (tenant_key=' . self::TK . ').');
    }

    private function seedDomains(Carbon $now): void
    {
        $domains = [
            ['domain' => 'bouwsteenwinkel.nl',                'is_primary' => 1],
            ['domain' => 'brikl.nl',                          'is_primary' => 0],
            ['domain' => 'bouwersfeestje.nl',                 'is_primary' => 0],
            ['domain' => 'bulkbricks.shop',                   'is_primary' => 0],
            ['domain' => 'legoverhuurfotos.nl',               'is_primary' => 0],
            ['domain' => 'klantfotos.bouwsteenwinkel.nl',     'is_primary' => 0],
            ['domain' => 'brickmix.bouwsteenwinkel.nl',       'is_primary' => 0],
            ['domain' => 'brickflex.bouwsteenwinkel.nl',      'is_primary' => 0],
            ['domain' => 'xxlsoftbricks.bouwsteenwinkel.nl',  'is_primary' => 0],
            ['domain' => 'wijkopenlego.nl',                   'is_primary' => 0],
            ['domain' => 'bouwverrassing.nl',                 'is_primary' => 0],
            ['domain' => 'bsw-bricks.nl',                     'is_primary' => 0],
        ];
        DB::table('cmp_domains')->where('tenant_key', self::TK)->delete();
        foreach ($domains as $d) {
            DB::table('cmp_domains')->insert([
                'tenant_key' => self::TK,
                'domain'     => $d['domain'],
                'is_primary' => $d['is_primary'],
                'status'     => 'active',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    private function seedCategories(Carbon $now): void
    {
        $cats = [
            ['key' => 'necessary',  'is_required' => 1, 'is_enabled' => 1, 'sort_order' => 10],
            ['key' => 'functional', 'is_required' => 0, 'is_enabled' => 1, 'sort_order' => 20],
            ['key' => 'analytics',  'is_required' => 0, 'is_enabled' => 1, 'sort_order' => 30],
            ['key' => 'marketing',  'is_required' => 0, 'is_enabled' => 0, 'sort_order' => 40],
        ];
        DB::table('cmp_categories')->where('tenant_key', self::TK)->delete();
        foreach ($cats as $c) {
            DB::table('cmp_categories')->insert(array_merge(['tenant_key' => self::TK,
                'created_at' => $now, 'updated_at' => $now], $c));
        }
    }

    private function seedTexts(Carbon $now): void
    {
        $texts = [
            // Banner
            'banner.title'        => 'Wij gebruiken cookies',
            'banner.body'         => 'We gebruiken functionele cookies zodat je winkelmand en account werken. Met jouw toestemming gebruiken we ook analytische cookies om de site te verbeteren. Geen tracking of advertenties zonder opt-in.',
            'banner.btn_accept_all'    => 'Accepteer alles',
            'banner.btn_reject_all'    => 'Alleen functioneel',
            'banner.btn_customize'     => 'Voorkeuren aanpassen',
            'banner.link_more'         => 'Meer over cookies',
            // Preferences-modal
            'prefs.title'         => 'Cookie-voorkeuren',
            'prefs.intro'         => 'Hieronder kun je per categorie je voorkeur instellen. Functionele cookies zijn altijd nodig — zonder werkt de winkel niet.',
            'prefs.btn_save'      => 'Voorkeuren opslaan',
            'prefs.btn_cancel'    => 'Annuleren',
            // Categorie-namen
            'cat.necessary.name'  => 'Strikt noodzakelijk',
            'cat.necessary.desc'  => 'Sessies, winkelmand, login. Worden altijd gebruikt — zonder werkt de site niet.',
            'cat.functional.name' => 'Functioneel',
            'cat.functional.desc' => 'Onthouden van voorkeuren (taal, favorieten, recent bekeken).',
            'cat.analytics.name'  => 'Analytisch',
            'cat.analytics.desc'  => 'Anonieme statistieken om de site te verbeteren. Geen profielen.',
            'cat.marketing.name'  => 'Marketing',
            'cat.marketing.desc'  => 'Advertenties en remarketing op andere sites.',
            // Footer-link
            'footer.cookie_settings' => 'Cookievoorkeuren',
        ];
        DB::table('cmp_texts')->where('tenant_key', self::TK)->where('lang', 'nl')->delete();
        foreach ($texts as $key => $value) {
            DB::table('cmp_texts')->insert([
                'tenant_key' => self::TK,
                'lang'       => 'nl',
                'text_key'   => $key,
                'text_value' => $value,
                'updated_at' => $now,
            ]);
        }
    }

    private function seedBranding(Carbon $now): void
    {
        $branding = [
            'logo_url'         => 'https://bouwsteenwinkel.nl/images/brand/logo-full.svg',
            'banner_position'  => 'bottom',          // bottom | top | center-modal
            'banner_layout'    => 'inline',          // inline | floating
            'colors' => [
                'banner_bg'        => '#1F1F1D',
                'banner_text'      => '#F5F1E6',
                'btn_primary_bg'   => '#D85A30',
                'btn_primary_text' => '#FFFFFF',
                'btn_secondary_bg' => 'transparent',
                'btn_secondary_text' => '#F5F1E6',
                'link'             => '#F5B400',
            ],
            'corner_radius_px' => 12,
            'animate'          => true,
            'show_close_x'     => false,             // bewust geen 'X' — AVG-eis: keuze maken
        ];
        DB::table('cmp_branding')->where('tenant_key', self::TK)->delete();
        DB::table('cmp_branding')->insert([
            'tenant_key'    => self::TK,
            'branding_json' => json_encode($branding, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
            'updated_at'    => $now,
        ]);
    }

    private function seedPolicy(Carbon $now): void
    {
        // Bereken hash van de huidige config — wordt gebruikt om te
        // detecteren of de tenant z'n CMP-config heeft gewijzigd, in welk
        // geval een nieuwe policy_version moet worden uitgegeven en
        // visitors opnieuw consent moeten geven.
        $hash = $this->computeConfigHash(self::TK);
        DB::table('cmp_policy')->where('tenant_key', self::TK)->delete();
        DB::table('cmp_policy')->insert([
            'tenant_key'     => self::TK,
            'policy_version' => 1,
            'config_hash'    => $hash,
            'updated_at'     => $now,
        ]);
    }

    private function computeConfigHash(string $tenantKey): string
    {
        $cats = DB::table('cmp_categories')
            ->where('tenant_key', $tenantKey)
            ->orderBy('sort_order')
            ->get(['key', 'is_required', 'is_enabled'])
            ->toArray();
        $scripts = DB::table('cmp_scripts')
            ->where('tenant_key', $tenantKey)
            ->orderBy('sort_order')
            ->get(['category_key', 'name', 'script_type', 'src_url', 'is_enabled'])
            ->toArray();
        return hash('sha256', json_encode([
            'cats' => $cats, 'scripts' => $scripts,
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }
}
