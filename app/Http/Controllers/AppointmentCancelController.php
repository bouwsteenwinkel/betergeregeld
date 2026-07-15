<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Services\Scheduling\BookingService;
use App\Services\Scheduling\SlotTakenException;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Wachtwoordloze annuleer-/verzetpagina, bereikbaar via de cancel_token-link uit de
 * bevestigingsmail. Zelfde low-stakes token-patroon als de revisit-link van de
 * voorbeelden ({@see SavedPreviewsController}): geen login, want een klant die wil
 * afzeggen gaat geen account aanmaken, en dan komt hij gewoon niet opdagen.
 *
 * Alle acties zijn post-redirect-get: de pagina die je na een annulering ziet is de
 * GET, zodat verversen niets opnieuw uitvoert.
 */
class AppointmentCancelController extends Controller
{
    public function show(string $token): View
    {
        $appt = $this->find($token);
        abort_unless($appt, 404);

        return $this->page($appt, $token);
    }

    public function cancel(string $token, BookingService $service): RedirectResponse
    {
        $appt = $this->find($token);
        abort_unless($appt, 404);

        // Al geannuleerd of allang geweest: geen actie, wel gewoon de pagina. Dat is
        // ook meteen de dubbelklik-beveiliging op deze publieke POST.
        if ($this->state($appt) === 'open') {
            $service->cancel($appt);

            return redirect($this->url($token))->with('appointment_cancelled', true);
        }

        return redirect($this->url($token));
    }

    public function reschedule(string $token, Request $request, BookingService $service): RedirectResponse
    {
        $appt = $this->find($token);
        abort_unless($appt, 404);

        if ($this->state($appt) !== 'open') {
            return redirect($this->url($token));
        }

        $data = $request->validate([
            // after:now vangt alleen het grove geval; of het moment écht vrij is
            // beslist de SlotEngine binnen book().
            'starts_at' => ['required', 'date', 'after:now'],
        ], [
            // Eigen teksten: app.locale staat op 'en', dus de standaardmelding zou in
            // het Engels op deze Nederlandse pagina landen.
            'starts_at.required' => 'Kies eerst een nieuw moment.',
            'starts_at.date'     => 'Dat is geen geldig moment. Kies een moment uit de lijst.',
            'starts_at.after'    => 'Dat moment is al geweest. Kies een moment uit de lijst.',
        ]);

        $tz    = (string) config('scheduling.timezone', 'Europe/Amsterdam');
        $start = CarbonImmutable::parse($data['starts_at'], $tz);

        if ($start->equalTo(CarbonImmutable::parse($appt->starts_at))) {
            return redirect($this->url($token))->withErrors(['starts_at' => 'Dat is het moment dat al gepland staat. Kies een ander moment.']);
        }

        try {
            $new = $service->reschedule($appt, $start->format('Y-m-d H:i'));
        } catch (SlotTakenException) {
            return redirect($this->url($token))->withErrors(['starts_at' => 'Dat moment is net vergeven. Kies een ander moment.']);
        }

        // De nieuwe afspraak heeft een eigen cancel_token (en een eigen mail), dus
        // doorsturen naar díe link: de oude is na het annuleren dood.
        return redirect($this->url($new->cancel_token))->with('appointment_rescheduled', true);
    }

    /** Rendert de pagina in de staat waar de afspraak in staat. */
    private function page(Appointment $appt, string $token): View
    {
        return view('appointments.cancel', [
            'appt'  => $appt,
            'token' => $token,
            'state' => $this->state($appt),
            'tz'    => (string) config('scheduling.timezone', 'Europe/Amsterdam'),
        ]);
    }

    /**
     * open = nog af te zeggen; cancelled = al afgezegd; past = het moment is geweest
     * (afzeggen heeft dan geen zin meer, en achteraf een agenda-event slopen ook niet).
     */
    private function state(Appointment $appt): string
    {
        if ($appt->status === 'cancelled') {
            return 'cancelled';
        }

        return $appt->starts_at->isPast() ? 'past' : 'open';
    }

    /**
     * Op cancel_token, niet op id: het token is het enige bewijs dat de bezoeker deze
     * afspraak mag aanraken. De lengte-ondergrens houdt een lege of afgeknotte token
     * uit de query (Str::random(48) bij het boeken); zonder dat zou een afspraak met
     * een lege cancel_token-kolom via /afspraak/annuleren/ te vinden zijn.
     */
    private function find(string $token): ?Appointment
    {
        return strlen($token) >= 20
            ? Appointment::where('cancel_token', $token)->first()
            : null;
    }

    /**
     * Bewust een PAD en geen absolute URL: de mail-link wijst naar het hoofddomein,
     * maar het pad valt (via de 'afspraak/'-uitzondering in de catch-all) ook op een
     * channel-domein door. Terugsturen naar config('app.url') zou de bezoeker dan
     * midden in de flow van domein wisselen.
     */
    private function url(string $token): string
    {
        return '/afspraak/annuleren/' . $token;
    }
}
