<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Throwable;

/**
 * Verstuurt één echte testmail via exact dezelfde mailer als de afspraak-keten,
 * en laat zien wélke transport actief is.
 *
 * Waarom dit bestaat: zowel de klant-bevestiging als de interne leadmelding zitten
 * in een try/catch die een fout alleen als Log::warning wegschrijft. Faalt de mail
 * (MAIL_MAILER=log, verkeerde SMTP-gegevens, of een SocketLabs-suppressie), dan boekt
 * de afspraak gewoon door — mét Meet-link — maar komt er niets binnen en zie je nergens
 * een fout. Dit command vangt de fout NIET: een echte SMTP-fout verschijnt hier voluit.
 *
 *   php artisan channels:mail-test --to=jij@voorbeeld.nl
 *
 * Verstuurt niets naar klanten; alleen naar het opgegeven adres (of scheduling.notify_email).
 */
class ChannelsMailTest extends Command
{
    protected $signature = 'channels:mail-test
        {--to= : ontvanger van de testmail; standaard scheduling.notify_email}';

    protected $description = 'Toont de actieve mailer en verstuurt één echte testmail (fout wordt NIET weggevangen).';

    public function handle(): int
    {
        $mailer = (string) config('mail.default');
        $from   = (string) config('mail.from.address');
        $fromNm = (string) config('mail.from.name');
        $notify = (string) config('scheduling.notify_email');
        $to     = (string) ($this->option('to') ?: $notify);

        $this->line('');
        $this->line('  <fg=cyan>Mail-configuratie</> (zoals de afspraak-keten hem ziet)');
        $this->line('  ────────────────────────────────────────────');
        $this->line("  MAIL_MAILER              : <options=bold>{$mailer}</>");
        if ($mailer === 'smtp') {
            $this->line('  MAIL_HOST / PORT         : ' . config('mail.mailers.smtp.host') . ':' . config('mail.mailers.smtp.port'));
            $this->line('  MAIL_USERNAME            : ' . (config('mail.mailers.smtp.username') ? '(ingevuld)' : '(LEEG)'));
            $this->line('  MAIL_ENCRYPTION          : ' . (config('mail.mailers.smtp.encryption') ?: '(geen)'));
        }
        $this->line("  MAIL_FROM                : {$from} ({$fromNm})");
        $this->line("  scheduling.notify_email  : " . ($notify ?: '(LEEG)'));
        $this->line('');

        if (in_array($mailer, ['log', 'array'], true)) {
            $this->warn("  LET OP: MAIL_MAILER={$mailer} — er vertrekt niets naar buiten.");
            $this->warn('  De mail belandt in storage/logs/laravel.log (log) of nergens (array).');
            $this->warn('  Zet MAIL_MAILER=smtp in de .env op de server en draai daarna:');
            $this->warn('    php artisan config:clear   (of config:cache met de juiste .env)');
            $this->line('');
        }

        if ($to === '' || ! filter_var($to, FILTER_VALIDATE_EMAIL)) {
            $this->error('  Geen geldig ontvangeradres. Geef --to=jij@voorbeeld.nl mee.');
            return self::FAILURE;
        }

        $this->line("  Testmail versturen naar <options=bold>{$to}</> via <options=bold>{$mailer}</> …");

        try {
            Mail::raw(
                "Dit is een testmail van Betergeregeld (channels:mail-test).\n\n"
                . "Actieve mailer: {$mailer}\n"
                . 'Afzender: ' . $from . "\n"
                . 'Verstuurd op: ' . now()->format('d-m-Y H:i:s') . "\n\n"
                . "Komt deze binnen, dan werkt de mailroute die ook de afspraak-bevestiging\n"
                . 'en de interne leadmelding gebruiken.',
                fn ($m) => $m->to($to)->subject('Betergeregeld testmail · ' . now()->format('H:i:s'))
            );
        } catch (Throwable $e) {
            // Bewust NIET wegvangen zoals de keten dat doet: dit is precies de fout die
            // daar stilletjes in de log verdween.
            $this->line('');
            $this->error('  VERSTUREN MISLUKT — dit is de fout die de afspraak-keten wegslikt:');
            $this->error('  ' . $e->getMessage());
            return self::FAILURE;
        }

        $this->line('');
        $this->info("  Verstuurd zonder fout via {$mailer}.");
        if (in_array($mailer, ['log', 'array'], true)) {
            $this->line('  (Maar met deze mailer komt hij dus niet in een postvak — zie de waarschuwing hierboven.)');
        } else {
            $this->line("  Controleer de inbox van {$to} (ook spam). Komt er niets aan terwijl dit 'zonder fout' meldt,");
            $this->line('  dan accepteert de SMTP-server hem maar bezorgt hij niet — check dan een suppressie/blokkade bij de provider.');
        }

        return self::SUCCESS;
    }
}
