<?php

namespace App\Http\Controllers;

use App\Http\Middleware\CaptureAdAttribution;
use App\Models\Appointment;
use App\Models\WebsiteLead;
use App\Services\Scheduling\BookingService;
use App\Services\Scheduling\CalendarUnavailableException;
use App\Services\Scheduling\SlotEngine;
use App\Services\Scheduling\SlotTakenException;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Publieke afspraken-API, gedeeld door alle trigger-sites (same-origin per domein).
 */
class AppointmentController extends Controller
{
    public function availability(Request $request, SlotEngine $engine): JsonResponse
    {
        // Zonder validatie gooide Carbon::parse() op elke onzin-invoer (?from=x) een
        // ongevangen exception: een 500 op een publieke, ongeknepen route.
        $request->validate([
            'from' => 'nullable|date',
            'to'   => 'nullable|date',
        ]);

        $tz   = (string) config('scheduling.timezone', 'Europe/Amsterdam');
        $from = $request->filled('from') ? CarbonImmutable::parse($request->query('from'), $tz) : null;
        $to   = $request->filled('to') ? CarbonImmutable::parse($request->query('to'), $tz) : null;

        try {
            $days = $engine->slots($from, $to);
        } catch (CalendarUnavailableException $e) {
            // Liever geen sloten tonen dan sloten die misschien al bezet zijn: de agenda
            // is nu onleesbaar, dus elk "vrij" zou een gok zijn.
            Log::error('appointment_availability_calendar: ' . $e->getMessage());

            return response()->json([
                'days'    => [],
                'error'   => 'calendar_unavailable',
                'message' => 'We kunnen de agenda nu even niet uitlezen. Probeer het zo opnieuw.',
            ], 503);
        }

        return response()->json(['days' => $days]);
    }

    public function book(Request $request, BookingService $service): JsonResponse
    {
        $data = $request->validate([
            'name'        => 'required|string|max:120',
            'email'       => 'required|email|max:190',
            'phone'       => 'nullable|string|max:40',
            'starts_at'   => 'required|date',
            'note'        => 'nullable|string|max:1000',
            'source_site' => 'nullable|string|max:80',
            'website'     => 'nullable|max:0', // honeypot: moet leeg zijn (geen autofill)
        ]);

        try {
            $appt = $service->book($data);
        } catch (SlotTakenException) {
            return response()->json([
                'ok'      => false,
                'message' => 'Dit tijdstip is net bezet. Kies een ander moment.',
            ], 409);
        } catch (CalendarUnavailableException $e) {
            // Niet doorboeken op een agenda die we niet kunnen lezen: dan zetten we
            // iemand mogelijk boven op een bestaande afspraak.
            Log::error('appointment_book_calendar: ' . $e->getMessage());

            return response()->json([
                'ok'      => false,
                'message' => 'We kunnen de agenda nu even niet bereiken. Probeer het over een paar minuten opnieuw.',
            ], 503);
        }

        // Een boeking is een lead. Zonder dit levert de widget alleen een Appointment
        // op: niet zichtbaar tussen de website-leads, en niemand krijgt bericht.
        // Bewust hier en niet in BookingService: die wordt ook aangeroepen vanuit
        // ChannelSiteController::leadStore(), waar de lead al bestaat (dubbele rij).
        // De herkomst komt uit de cookie: dit is een POST, dus de klik-parameters van
        // de landingspagina staan niet in deze URL.
        $this->recordLead($appt, $data, CaptureAdAttribution::fromRequest($request));

        $tz = (string) config('scheduling.timezone', 'Europe/Amsterdam');

        return response()->json([
            'ok'        => true,
            'meet_url'  => $appt->meet_url,
            'starts_at' => $appt->starts_at->setTimezone($tz)->format('Y-m-d H:i'),
            'message'   => $appt->meet_url
                ? 'Je afspraak staat! De Google Meet-link staat in je bevestigingsmail.'
                : 'Je afspraak staat! Je ontvangt de bevestiging en de Meet-link per e-mail.',
        ]);
    }

    /**
     * Legt de boeking vast als WebsiteLead en stuurt de interne melding. Bestaat er
     * al een lead met dit e-mailadres (bv. iemand die eerder zijn voorbeeld bewaarde),
     * dan wordt die bijgewerkt i.p.v. gedupliceerd — zelfde dedupe-regel als de
     * bewaar-flow. Best-effort: een fout hier mag de bevestigde afspraak niet omver
     * halen, dus alleen loggen.
     *
     * @param  array<string,mixed>  $data
     * @param  array<string,string>  $attribution  klik-herkomst (gclid/utm_*)
     */
    private function recordLead(Appointment $appt, array $data, array $attribution = []): void
    {
        try {
            $email = (string) $data['email'];
            $lead  = WebsiteLead::firstOrNew(['email' => $email]);
            $isNew = ! $lead->exists;

            $lead->contact_name = $lead->contact_name ?: (string) $data['name'];
            if (! empty($data['phone'])) {
                $lead->phone = $data['phone'];
            }
            if (! empty($data['note'])) {
                $lead->message = $data['note'];
            }
            if ($isNew) {
                $lead->source  = 'afspraak';
                $lead->channel = (string) ($data['source_site'] ?? '') ?: null;
            }

            // First touch wint, ook hier: boekt iemand een afspraak nadat hij eerder
            // zijn voorbeeld bewaarde, dan blijft de klik uit de bewaar-flow de bron.
            $lead->fillAttributionOnce($attribution);

            // De afspraak zelf wint altijd: dit is het verste punt in de funnel.
            $lead->status             = 'appointment';
            $lead->appointment_at     = $appt->starts_at;
            $lead->appointment_type   = $appt->type;
            $lead->appointment_status = $appt->leadAppointmentStatus();
            $lead->meet_link          = $appt->meet_url;
            $lead->google_event_id    = $appt->google_event_id;
            $lead->save();

            $this->notifyInternal($lead, $appt, $isNew);
        } catch (\Throwable $e) {
            Log::warning('appointment_lead: ' . $e->getMessage());
        }
    }

    /**
     * Interne ping bij een nieuwe boeking.
     *
     * Bewust scheduling.notify_email en niet mail.from.address: dat laatste is een
     * afzender, geen postbus. Op de voorbeeldwaarde (hello@example.com) verdween deze
     * melding geruisloos, en een gemiste afspraakmelding merk je pas als de klant voor
     * een lege Meet-kamer zit.
     */
    private function notifyInternal(WebsiteLead $lead, Appointment $appt, bool $isNew): void
    {
        try {
            $to = (string) config('scheduling.notify_email');
            if ($to === '') {
                return;
            }
            $tz   = (string) config('scheduling.timezone', 'Europe/Amsterdam');
            $when = $appt->starts_at->setTimezone($tz)->format('d-m-Y H:i');

            Mail::raw(
                "Nieuwe afspraak ingepland.\n\n"
                . "Wanneer: {$when}\n"
                . "Naam: {$lead->contact_name}\n"
                . "Contact: {$lead->email} · " . ($lead->phone ?: '—') . "\n"
                . 'Via: ' . ($lead->channel ?: 'onbekend') . "\n"
                . 'Bericht: ' . ($lead->message ?: '—') . "\n"
                . 'Meet: ' . ($appt->meet_url ?: '—') . "\n\n"
                . ($isNew ? "Dit is een nieuwe lead.\n\n" : "Bestaande lead bijgewerkt.\n\n")
                . 'Opvolgen in de admin → Website-leads.',
                fn ($m) => $m->to($to)->subject("Nieuwe afspraak {$when}: {$lead->contact_name}")
            );
        } catch (\Throwable $e) {
            Log::warning('appointment_lead_mail: ' . $e->getMessage());
        }
    }
}
