<?php

namespace App\Console\Commands;

use App\Mail\AppointmentReminder;
use App\Models\Appointment;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Herinneringen voor geboekte afspraken. Cadans: 24 uur vooraf en 1 uur vooraf.
 *
 * Waarom twee momenten: het gesprek is gratis en vrijblijvend en staat dagen
 * vooruit, dus no-show is het grootste lek. De 24u-mail komt op tijd om nog te
 * kunnen verzetten (dat is de gewenste uitkomst, een verzette afspraak is geen
 * verloren lead). De 1u-mail vangt de mensen die het gewoon vergeten en zet de
 * Meet-link binnen handbereik op het moment dat ze hem nodig hebben.
 *
 * Waarom niet alleen 24u: wie 's ochtends de mail leest en om 16:00 moet, is dat
 * om 16:00 kwijt. Waarom niet alleen 1u: dan is verzetten geen optie meer en is
 * afzeggen het enige alternatief voor niet komen opdagen.
 */
class SendAppointmentReminders extends Command
{
    protected $signature = 'appointments:send-reminders';

    protected $description = 'Stuurt herinneringen voor geboekte afspraken (24 uur en 1 uur vooraf).';

    public function handle(): int
    {
        $now = Carbon::now();
        $sent = 0;
        $skipped = 0;

        // Alleen 'booked'. Dat sluit in één keer cancelled/completed/no_show uit én
        // 'held' (een reservering tijdens het invullen is geen afspraak om aan te
        // herinneren). starts_at > now houdt alles in het verleden buiten de deur.
        $appointments = Appointment::query()
            ->where('status', 'booked')
            ->where('starts_at', '>', $now)
            ->where('starts_at', '<=', $now->copy()->addDay())
            ->where(function ($q) {
                $q->whereNull('reminder_24h_sent_at')->orWhereNull('reminder_1h_sent_at');
            })
            ->orderBy('starts_at')
            ->get();

        foreach ($appointments as $appt) {
            $kind = $this->dueKind($appt, $now);

            if ($kind === null) {
                $skipped++;
                continue;
            }

            $column = $kind === '1h' ? 'reminder_1h_sent_at' : 'reminder_24h_sent_at';

            try {
                Mail::to($appt->email)->send(new AppointmentReminder($appt, $kind));
            } catch (\Throwable $e) {
                // Eén kapot mailadres mag de rest van de batch niet blokkeren; de
                // kolom blijft leeg, dus de volgende run probeert het opnieuw.
                Log::warning("appointment_reminder_mail (#{$appt->id}, {$kind}): " . $e->getMessage());
                $skipped++;
                continue;
            }

            // Pas ná een geslaagde verzending stempelen: dit is wat dubbel sturen
            // voorkomt bij de volgende run.
            $appt->forceFill([$column => $now])->save();
            $sent++;
        }

        $this->info("Afspraak-herinneringen verstuurd: {$sent}, overgeslagen: {$skipped}.");

        return self::SUCCESS;
    }

    /**
     * Welke herinnering is deze afspraak nu toe? Null = geen.
     * De 1u-mail gaat vóór op de 24u-mail: staat de afspraak nog maar een uur weg
     * en is er nooit een 24u-mail gegaan (bijv. laat geboekt), dan is "over een uur"
     * het enige eerlijke bericht.
     */
    private function dueKind(Appointment $appt, Carbon $now): ?string
    {
        $start = Carbon::parse($appt->starts_at);

        if ($start->lte($now->copy()->addHour())) {
            return $this->isDue($appt, 'reminder_1h_sent_at', $start, 1, $now) ? '1h' : null;
        }

        return $this->isDue($appt, 'reminder_24h_sent_at', $start, 24, $now) ? '24h' : null;
    }

    /**
     * Een herinnering is toe als hij nog niet ging én de afspraak al geboekt was
     * toen het venster openging. Die tweede eis voorkomt de rare dubbelslag bij
     * een late boeking: wie vandaag om 10:00 boekt voor 15:00 zit al binnen het
     * 24u-venster en zou binnen een kwartier na de bevestigingsmail een
     * "herinnering" krijgen aan iets dat hij net zelf inplande.
     */
    private function isDue(Appointment $appt, string $column, Carbon $start, int $hoursBefore, Carbon $now): bool
    {
        if ($appt->{$column} !== null) {
            return false;
        }

        $windowOpensAt = $start->copy()->subHours($hoursBefore);

        if ($now->lt($windowOpensAt)) {
            return false;
        }

        return $appt->created_at === null || Carbon::parse($appt->created_at)->lt($windowOpensAt);
    }
}
