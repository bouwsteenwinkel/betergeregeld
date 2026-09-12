<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * Alleen-lezen kiosk-schermen voor een permanent scherm (Raspberry Pi) zonder
 * login. Toegang via een geheime sleutel in de URL (?key=<sleutel>), vergeleken
 * met de hash in config/kiosk.php. Alles lezend; elke bron in een eigen try/catch
 * zodat één missende tabel niet het hele scherm sloopt.
 */
class KioskController extends Controller
{
    /** Betergeregeld: bezoekers, contactformulieren en AI-telefonie testcalls. */
    public function betergeregeld(Request $request)
    {
        $this->guard($request);

        if ($request->boolean('json')) {
            return response()->json($this->betergeregeldData());
        }

        return view('kiosk.betergeregeld', [
            'd'   => $this->betergeregeldData(),
            'key' => (string) $request->query('key', ''),
        ]);
    }

    /** 404 tenzij de sleutel klopt — geen hint dat de pagina bestaat. */
    private function guard(Request $request): void
    {
        $hash = (string) config('kiosk.token_hash', '');
        $key  = (string) $request->query('key', '');
        if ($hash === '' || $key === '' || ! hash_equals($hash, hash('sha256', $key))) {
            abort(404);
        }
    }

    private function poging(callable $fn, $terugval = null)
    {
        try {
            return $fn();
        } catch (\Throwable $e) {
            report($e);
            return $terugval;
        }
    }

    private function betergeregeldData(): array
    {
        $today   = Carbon::today();
        $weekAgo = Carbon::today()->subDays(6);

        $out = ['tijd' => Carbon::now()->format('H:i:s')];

        // --- Bezoekers (channel_events, event=page_view; visit_ref = bezoek) ---
        $out['bezoek_vandaag'] = $this->poging(fn () =>
            (int) DB::table('channel_events')
                ->where('event', 'page_view')
                ->where('created_at', '>=', $today)
                ->distinct()->count('visit_ref'), 0);

        $out['pageviews_vandaag'] = $this->poging(fn () =>
            (int) DB::table('channel_events')
                ->where('event', 'page_view')
                ->where('created_at', '>=', $today)->count(), 0);

        $out['bezoek_per_site'] = $this->poging(fn () =>
            DB::table('channel_events')
                ->selectRaw('COALESCE(site_key, "(onbekend)") AS site, COUNT(DISTINCT visit_ref) AS bezoekers, COUNT(*) AS pv')
                ->where('event', 'page_view')
                ->where('created_at', '>=', $today)
                ->groupBy('site_key')
                ->orderByDesc('bezoekers')
                ->limit(6)->get()
                ->map(fn ($r) => [
                    'site'      => $r->site,
                    'bezoekers' => (int) $r->bezoekers,
                    'pv'        => (int) $r->pv,
                ])->all(), []);

        // --- Contactformulieren (status=new; spam telt niet) ---
        $out['contact_vandaag'] = $this->poging(fn () =>
            (int) DB::table('contact_messages')->where('status', 'new')
                ->where('created_at', '>=', $today)->count(), 0);
        $out['contact_week'] = $this->poging(fn () =>
            (int) DB::table('contact_messages')->where('status', 'new')
                ->where('created_at', '>=', $weekAgo)->count(), 0);
        $out['contact_recent'] = $this->poging(fn () =>
            DB::table('contact_messages')->where('status', 'new')
                ->orderByDesc('created_at')->limit(6)
                ->get(['created_at', 'name', 'company', 'email', 'subject', 'topic'])
                ->map(fn ($r) => [
                    'wanneer' => Carbon::parse($r->created_at)->format('d-m H:i'),
                    'naam'    => $r->name,
                    'bedrijf' => $r->company,
                    'onderwerp' => $r->subject ?: ($r->topic ?: '—'),
                ])->all(), []);

        // --- AI-telefonie (channel_type=voice) — nu vooral testcalls ---
        $out['calls_vandaag'] = $this->poging(fn () =>
            (int) DB::table('ai_conversations')->where('channel_type', 'voice')
                ->where('created_at', '>=', $today)->count(), 0);
        $out['calls_week'] = $this->poging(fn () =>
            (int) DB::table('ai_conversations')->where('channel_type', 'voice')
                ->where('created_at', '>=', $weekAgo)->count(), 0);
        // Gemiddelde duur (sec) van vandaag afgeronde calls.
        $out['calls_gem_duur'] = $this->poging(fn () =>
            (int) round((float) DB::table('ai_conversations')
                ->where('channel_type', 'voice')
                ->whereNotNull('ended_at')
                ->where('created_at', '>=', $today)
                ->avg(DB::raw('TIMESTAMPDIFF(SECOND, started_at, ended_at)'))), 0);
        $out['calls_recent'] = $this->poging(fn () =>
            DB::table('ai_conversations')->where('channel_type', 'voice')
                ->orderByDesc('created_at')->limit(6)
                ->get(['created_at', 'started_at', 'ended_at', 'status', 'sentiment', 'message_count', 'cost_eur', 'summary'])
                ->map(function ($r) {
                    $duur = ($r->started_at && $r->ended_at)
                        ? Carbon::parse($r->started_at)->diffInSeconds(Carbon::parse($r->ended_at))
                        : null;
                    return [
                        'wanneer'   => Carbon::parse($r->created_at)->format('d-m H:i'),
                        'status'    => $r->status,
                        'sentiment' => $r->sentiment,
                        'duur'      => $duur !== null ? sprintf('%d:%02d', intdiv($duur, 60), $duur % 60) : '—',
                        'beurten'   => (int) $r->message_count,
                        'kosten'    => (float) $r->cost_eur,
                        'samenvatting' => $r->summary ? mb_substr($r->summary, 0, 90) : '',
                    ];
                })->all(), []);

        return $out;
    }
}
