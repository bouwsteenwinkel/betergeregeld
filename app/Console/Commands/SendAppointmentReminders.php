<?php

namespace App\Console\Commands;

use App\Mail\AppointmentReminder;
use App\Models\Appointment;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Herinneringen voor geboekte afspraken. Cadans: 2 dagen vooraf en de ochtend van de
 * afspraak zelf (beide instelbaar via config/scheduling.php).
 *
 * Waarom twee momenten: het gesprek is gratis en vrijblijvend en staat dagen vooruit,
 * dus no-show is het grootste lek. De mail van 2 dagen vooraf komt ruim op tijd om nog
 * te kunnen verzetten (een verzette afspraak is geen verloren lead). De mail op de dag
 * zelf vangt de mensen die het simpelweg vergeten waren.
 *
 * "De dag van de afspraak" is een lokaal klokuur, geen vast aantal uren vooraf: wie om
 * 16:00 moet, heeft niets aan een mail die om 15:00 kwam als hij zijn ochtend al
 * volgepland had. Daarom een vast ochtenduur, met één uitzondering — zie sendAtFor().
 */
class SendAppointmentReminders extends Command
{
    protected $signature = 'appointments:send-reminders';

    protected $description = 'Stuurt herinneringen voor geboekte afspraken (2 dagen vooraf en op de dag zelf).';

    private const KIND_LEAD   = '2d';
    private const KIND_DAY_OF = 'day_of';

    private const COLUMNS = [
        self::KIND_LEAD   => 'reminder_2d_sent_at',
        self::KIND_DAY_OF => 'reminder_day_of_sent_at',
    ];

    public function handle(): int
    {
        $tz  = (string) config('scheduling.timezone', 'Europe/Amsterdam');
        $now = Carbon::now($tz);

        $leadDays = (int) config('scheduling.reminders.lead_days', 2);
        $sent     = 0;
        $skipped  = 0;

        // Alleen 'booked'. Dat sluit in één keer cancelled/completed/no_show uit én
        // 'held' (een reservering tijdens het invullen is geen afspraak om aan te
        // herinneren). Het venster loopt tot lead_days vooruit: verder weg dan dat kan
        // geen enkele herinnering toe zijn, en alles in het verleden valt af.
        $appointments = Appointment::query()
            ->where('status', 'booked')
            ->where('starts_at', '>', $now)
            ->where('starts_at', '<=', $now->copy()->addDays($leadDays))
            ->where(function ($q) {
                $q->whereNull('reminder_2d_sent_at')->orWhereNull('reminder_day_of_sent_at');
            })
            ->orderBy('starts_at')
            ->get();

        foreach ($appointments as $appt) {
            // Staat de afspraak vandaag, dan is de mail van 2 dagen vooraf niet meer waar:
            // hij zou "we spreken elkaar [datum]" zeggen over iets van vandaag, en de
            // dag-van-mail doet dat werk al. Afstempelen zonder te sturen, anders pikt een
            // volgende run hem alsnog op.
            //
            // Dit móét vóór dueKind(): na scheduler-uitval staan beide vensters open, en
            // om 06:00 is de dag-van-mail nog niet toe (die wacht op 08:00) terwijl de
            // 2d-mail dat wél is. Zonder deze voorrang vertrok die om 06:00 alsnog, en
            // kreeg de klant twee uur later óók nog de dag-van-mail.
            if ($appt->reminder_2d_sent_at === null && $this->isVandaag($appt, $now, $tz)) {
                $appt->forceFill(['reminder_2d_sent_at' => $now])->save();
            }

            $kind = $this->dueKind($appt, $now, $tz);

            if ($kind === null) {
                $skipped++;
                continue;
            }

            if (! $this->claim($appt, $kind, $now)) {
                // Een parallelle run was ons voor. Verzenden en stempelen waren eerder
                // twee losse stappen, waardoor twee runs dezelfde mail konden sturen.
                $skipped++;
                continue;
            }

            try {
                Mail::to($appt->email)->send(new AppointmentReminder($appt, $kind));
            } catch (\Throwable $e) {
                // Claim terugdraaien zodat de volgende run het opnieuw probeert; één
                // kapot mailadres mag de rest van de batch niet blokkeren.
                //
                // Via de query en niet via $appt->save(): claim() schreef de stempel om
                // dit model heen, dus in het geheugen staat de kolom nog op null. Een
                // forceFill(null) is dan "geen wijziging" en Eloquent schrijft niets weg —
                // waarna de claim blijft staan en deze klant nooit meer een herinnering
                // krijgt.
                Appointment::query()->where('id', $appt->id)->update([self::COLUMNS[$kind] => null]);

                Log::warning("appointment_reminder_mail (#{$appt->id}, {$kind}): " . $e->getMessage());
                $skipped++;
                continue;
            }

            $sent++;
        }

        $this->info("Afspraak-herinneringen verstuurd: {$sent}, overgeslagen: {$skipped}.");

        return self::SUCCESS;
    }

    /**
     * Claim de herinnering vóór het versturen, met een voorwaardelijke UPDATE. Slaagt
     * die bij precies één rij, dan is deze run de enige die deze mail verstuurt.
     */
    private function claim(Appointment $appt, string $kind, Carbon $now): bool
    {
        $column = self::COLUMNS[$kind];

        $claimed = Appointment::query()
            ->where('id', $appt->id)
            ->whereNull($column)
            ->update([$column => $now]);

        return $claimed === 1;
    }

    /**
     * Welke herinnering is deze afspraak nu toe? Null = geen.
     *
     * De dag-van-mail gaat vóór op die van 2 dagen vooraf: staat de afspraak vandaag,
     * dan is "vandaag" het enige eerlijke bericht.
     */
    private function dueKind(Appointment $appt, Carbon $now, string $tz): ?string
    {
        foreach ([self::KIND_DAY_OF, self::KIND_LEAD] as $kind) {
            if ($this->isDue($appt, $kind, $now, $tz)) {
                return $kind;
            }
        }

        return null;
    }

    /**
     * Een herinnering is toe als hij nog niet ging, zijn verzendmoment is aangebroken,
     * én de afspraak al geboekt was toen dat moment aanbrak.
     *
     * Die laatste eis voorkomt de rare dubbelslag bij een late boeking: wie vanochtend
     * om 10:00 boekt voor vanmiddag 15:00 zit al voorbij beide verzendmomenten en zou
     * anders binnen een kwartier na zijn bevestigingsmail een "herinnering" krijgen aan
     * iets dat hij net zelf inplande. Zo iemand krijgt geen herinnering meer, en dat is
     * de bedoeling: hij boekte een paar uur geleden, de bevestiging is nog vers.
     */
    private function isDue(Appointment $appt, string $kind, Carbon $now, string $tz): bool
    {
        if ($appt->{self::COLUMNS[$kind]} !== null) {
            return false;
        }

        $sendAt = $this->sendAtFor($appt, $kind, $tz);

        if ($now->lt($sendAt)) {
            return false;
        }

        return $appt->created_at === null || Carbon::parse($appt->created_at)->setTimezone($tz)->lt($sendAt);
    }

    /** Valt de afspraak op de kalenderdag die nu bezig is? (lokale tijdzone) */
    private function isVandaag(Appointment $appt, Carbon $now, string $tz): bool
    {
        return Carbon::parse($appt->starts_at)->setTimezone($tz)->isSameDay($now);
    }

    /** Het moment waarop deze herinnering hoort te vertrekken, in de lokale tijdzone. */
    private function sendAtFor(Appointment $appt, string $kind, string $tz): Carbon
    {
        $start = Carbon::parse($appt->starts_at)->setTimezone($tz);

        if ($kind === self::KIND_LEAD) {
            // subDays() en niet subHours(48): over de zomertijdgrens is een dag geen
            // vaste 24 uur, en "2 dagen vooraf" hoort op hetzelfde klokuur te vallen.
            return $start->copy()->subDays((int) config('scheduling.reminders.lead_days', 2));
        }

        $hour   = (int) config('scheduling.reminders.day_of_hour', 8);
        $sendAt = $start->copy()->startOfDay()->setTime($hour, 0);

        // Een afspraak om 08:00 zou anders zijn "herinnering" precies bij aanvang
        // krijgen, en eentje om 07:00 pas erna. Zit het ochtenduur te dicht op de
        // afspraak, dan schuift de mail naar voren tot minimaal min_lead_minutes ervoor.
        $latest = $start->copy()->subMinutes((int) config('scheduling.reminders.min_lead_minutes', 60));

        return $sendAt->gt($latest) ? $latest : $sendAt;
    }
}
