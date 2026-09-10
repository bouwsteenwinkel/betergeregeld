<?php

namespace App\Console\Commands;

use App\Mail\AppointmentConfirmation;
use App\Mail\AppointmentReminder;
use App\Mail\PreviewReminderMail;
use App\Mail\PreviewSavedMail;
use App\Models\Appointment;
use App\Models\WebsiteLead;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

/**
 * Stuurt elke mail uit de "voorbeeld opslaan + afspraak maken"-flow één keer als test naar
 * een adres. Puur voor beoordeling van stijl + spam-risico: bouwt ALLE modellen in-memory
 * (niets wordt in de database geschreven, en er wordt niets in de agenda gezet).
 *
 * De voorbeeldgegevens zijn met opzet onmiskenbaar nep. Hier stonden ooit naam, bedrijf en
 * e-mailadres van een echte lead, met een afspraaktijd van "over drie dagen om 14:00". Mail 8
 * ("Nieuwe afspraak ...") was daardoor niet van een echte melding te onderscheiden: er is toen
 * naar een afspraak gezocht die nooit heeft bestaan. Houd deze gegevens dus nep, en gebruik
 * example.com — dat domein is gereserveerd en kan nooit iemand bereiken.
 */
class MailFlowTest extends Command
{
    /**
     * Voorbeeld-site-key. Bewust geen bestaande preview: de mails mogen nergens naar echte data wijzen.
     */
    private const SAMPLE_SITE_KEY = 'preview-voorbeeld0000';

    protected $signature = 'mail:flow-test {--to=dennis@bouwsteenwinkel.nl}';

    protected $description = 'Verstuur alle preview-/afspraak-flow-mails als test naar één adres (geen DB-writes)';

    public function handle(): int
    {
        $to = (string) $this->option('to');
        $this->info("Testmails versturen naar: {$to}");
        $this->newLine();

        // --- Sample-lead (unsaved; token_hash vooraf gezet zodat revisitUrl() niks opslaat) ---
        $lead = new WebsiteLead;
        $lead->forceFill([
            'contact_name' => 'Voorbeeld Klant',
            'company' => 'Voorbeeld Bedrijf BV',
            'email' => 'voorbeeld@example.com',
            'phone' => '06 12 34 56 78',
            'city' => 'Voorbeeldstad',
            'channel' => 'bedrijfswebsite',
            'message' => 'Dit is voorbeeldtekst uit mail:flow-test, geen echte aanvraag.',
            'preview_url' => rtrim((string) config('app.url'), '/').'/_site/'.self::SAMPLE_SITE_KEY,
            'token_hash' => 'testtoken'.str_repeat('x', 54),
        ]);

        // --- Sample-afspraak (unsaved; cancel_token + meet_url gezet) ---
        $appt = new Appointment;
        $appt->forceFill([
            'name' => 'Voorbeeld Klant',
            'email' => 'voorbeeld@example.com',
            'phone' => '06 12 34 56 78',
            'starts_at' => now()->addDays(3)->setTime(14, 0),
            'ends_at' => now()->addDays(3)->setTime(14, 30),
            'type' => 'online',
            'status' => 'confirmed',
            'meet_url' => 'https://meet.google.com/abc-defg-hij',
            'cancel_token' => 'testcanceltoken1234',
            'source_site' => 'bedrijfswebsite',
        ]);

        $tz = (string) config('scheduling.timezone', 'Europe/Amsterdam');
        $when = $appt->starts_at->copy()->setTimezone($tz)->format('d-m-Y H:i');
        $sourceChannel = 'bedrijfswebsite';
        $siteKey = self::SAMPLE_SITE_KEY;

        $sent = [];

        // === Klant-mails ========================================================
        $this->send($to, '1. Klant · voorbeeld opgeslagen', fn () => Mail::to($to)->send(new PreviewSavedMail($lead)), $sent);
        $this->send($to, '2. Klant · preview-herinnering (dag 1)', fn () => Mail::to($to)->send(new PreviewReminderMail($lead, 'dag1')), $sent);
        $this->send($to, '3. Klant · preview-herinnering (dag 4)', fn () => Mail::to($to)->send(new PreviewReminderMail($lead, 'dag4')), $sent);
        $this->send($to, '4. Klant · afspraak bevestigd', fn () => Mail::to($to)->send(new AppointmentConfirmation($appt)), $sent);
        $this->send($to, '5. Klant · afspraak-herinnering (ruim vooraf)', fn () => Mail::to($to)->send(new AppointmentReminder($appt, '2d')), $sent);
        $this->send($to, '6. Klant · afspraak-herinnering (dag zelf)', fn () => Mail::to($to)->send(new AppointmentReminder($appt, 'day_of')), $sent);

        // === Interne team-meldingen (zoals ze nu ZIJN: plain text) ==============
        $this->send($to, '7. Intern · voorbeeld opgeslagen', function () use ($to, $lead, $sourceChannel, $siteKey) {
            Mail::raw(
                "Voorbeeld opgeslagen door {$lead->contact_name} ({$lead->email}).\n"
                .'Bedrijf: '.($lead->company ?: '-')." | Kanaal: {$sourceChannel}\n"
                .'Preview: '.rtrim((string) config('app.url'), '/').'/_site/'.$siteKey,
                fn ($m) => $m->to($to)->subject('Voorbeeld opgeslagen: '.($lead->company ?: $lead->contact_name)),
            );
        }, $sent);

        $this->send($to, '8. Intern · nieuwe afspraak', function () use ($to, $lead, $appt, $when) {
            Mail::raw(
                "Nieuwe afspraak ingepland.\n\n"
                ."Wanneer: {$when}\n"
                ."Naam: {$lead->contact_name}\n"
                ."Contact: {$lead->email} · ".($lead->phone ?: '—')."\n"
                .'Via: '.($lead->channel ?: 'onbekend')."\n"
                .'Bericht: '.($lead->message ?: '—')."\n"
                .'Meet: '.($appt->meet_url ?: '—')."\n\n"
                ."Dit is een nieuwe lead.\n\n"
                .'Opvolgen in de admin → Website-leads.',
                fn ($m) => $m->to($to)->subject("Nieuwe afspraak {$when}: {$lead->contact_name}")
            );
        }, $sent);

        $this->send($to, '9. Intern · nieuwe lead via channel-site', function () use ($to, $lead, $appt, $when) {
            Mail::raw(
                "Nieuwe afspraak via channel-site.\n\n"
                ."AFSPRAAK: {$when}\n"
                .'Meet: '.($appt->meet_url ?: '— (nog geen link!)')."\n\n"
                ."Site: Jouw Bedrijfswebsite (bedrijfswebsite)\n"
                ."Branche: bedrijfswebsite\n"
                ."Naam: {$lead->contact_name}\n"
                .'Bedrijf: '.($lead->company ?: '—')."\n"
                ."Contact: {$lead->email} · {$lead->phone}\n"
                .'Plaats: '.($lead->city ?: '—')."\n"
                .'Bericht: '.($lead->message ?: '—')."\n\n"
                .'Opvolgen in de admin → Website-leads.',
                fn ($m) => $m->to($to)->subject('Nieuwe afspraak (bedrijfswebsite): '.($lead->company ?: $lead->contact_name))
            );
        }, $sent);

        $this->newLine();
        $this->info('Klaar. Verzonden: '.count($sent).' mails.');

        return self::SUCCESS;
    }

    private function send(string $to, string $label, callable $fn, array &$sent): void
    {
        try {
            $fn();
            $sent[] = $label;
            $this->line("  ✓ {$label}");
        } catch (\Throwable $e) {
            $this->error("  ✗ {$label} — {$e->getMessage()}");
        }
    }
}
