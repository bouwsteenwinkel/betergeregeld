<?php

namespace App\Http\Controllers;

use App\Services\Scheduling\GoogleCalendarGateway;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\View\View;

/**
 * Eenmalige koppeling van de centrale Google-agenda (afsprakenplanner, fase 1b).
 */
class GoogleAgendaController extends Controller
{
    public function __construct(private GoogleCalendarGateway $gateway) {}

    public function status(): View
    {
        return view('admin.google-agenda', [
            'connected'  => $this->gateway->isConnected(),
            'configured' => (bool) config('scheduling.google.client_id'),
        ]);
    }

    public function connect(): RedirectResponse
    {
        if (! config('scheduling.google.client_id')) {
            return redirect()->route('google-agenda.status')->with('error', 'GOOGLE_CLIENT_ID ontbreekt in de configuratie.');
        }
        $state = Str::random(40);
        Session::put('google_agenda_state', $state);
        return redirect()->away($this->gateway->authUrl($this->redirectUri(), $state));
    }

    public function callback(Request $request): RedirectResponse
    {
        if ($request->query('error')) {
            return redirect()->route('google-agenda.status')->with('error', 'Google gaf een fout: ' . $request->query('error'));
        }
        if (! $request->filled('code') || $request->query('state') !== Session::pull('google_agenda_state')) {
            return redirect()->route('google-agenda.status')->with('error', 'Ongeldige of verlopen koppel-poging. Probeer opnieuw.');
        }
        $ok = $this->gateway->exchangeCode((string) $request->query('code'), $this->redirectUri());

        return redirect()->route('google-agenda.status')
            ->with($ok ? 'success' : 'error', $ok
                ? 'Google-agenda gekoppeld. Afspraken maken nu automatisch een agenda-event met Meet-link.'
                : 'Koppelen mislukt. Kreeg geen refresh-token terug (probeer opnieuw met "toestemming opnieuw geven").');
    }

    public function disconnect(): RedirectResponse
    {
        $this->gateway->disconnect();
        return redirect()->route('google-agenda.status')->with('success', 'Google-agenda ontkoppeld.');
    }

    private function redirectUri(): string
    {
        return url('/admin/google-agenda/callback');
    }
}
