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

    /**
     * Strengere spamcheck voor het kiosk-scherm dan de live-drempel (die veel
     * oude rommel als 'new' liet staan). Combineert de bestaande scorer met een
     * paar signalen uit de echte spamstroom: nep-bedrijf, lange cijferreeksen in
     * naam/onderwerp, en wartaal-namen (willekeurige hoofd/kleine letters).
     */
    private function lijktSpam($r): bool
    {
        $naam    = (string) ($r->name ?? '');
        $subject = (string) ($r->subject ?? '');
        $bedrijf = strtolower(trim((string) ($r->company ?? '')));

        // 1. Bestaande scorer, maar al vanaf 2 punten (bot-namen e.d.).
        $data = array(
            'name' => $naam, 'email' => (string) ($r->email ?? ''),
            'message' => (string) ($r->message ?? ''), 'subject' => $subject,
            'website' => (string) ($r->website ?? ''),
        );
        if (class_exists(\App\Support\ContactSpam::class)
            && \App\Support\ContactSpam::beoordeel($data)['score'] >= 2) {
            return true;
        }
        // 2. Nep-bedrijf dat in de echte aanvragen nooit voorkomt.
        if (in_array($bedrijf, array('google', 'n/a', 'na', 'test', '-'), true)) {
            return true;
        }
        // 3. Lange cijferreeks in naam of onderwerp (bv. "TORTYT1315683", "916988581").
        if (preg_match('/\d{4,}/', $naam . ' ' . $subject)) {
            return true;
        }
        // 4. Wartaal-naam: geen spatie, lang, met meerdere klein→HOOFDletter-sprongen.
        if (! str_contains($naam, ' ') && mb_strlen($naam) >= 12
            && preg_match_all('/[a-z][A-Z]/', $naam) >= 2) {
            return true;
        }
        // 5. Onderwerp dat alleen uit aanhef/titels bestaat ("Ms", "Prof.", "Dr. Prof.", "Mme").
        if (preg_match('/^((mr|mrs|ms|miss|mx|prof|dr|sir|madam|dhr|mevr|mw|mme|mlle)\.?[\s,.-]*)+$/i', trim($subject))) {
            return true;
        }
        // 6. Naam én bedrijf allebei willekeurige kleine-letter-wartaal.
        if (preg_match('/^[a-z]{8,}$/', $naam) && preg_match('/^[a-z]{8,}$/', $bedrijf)) {
            return true;
        }
        // 7. Een domein in het naamveld ("hello google.com") — nooit een echte naam.
        if (preg_match('/\b[a-z0-9-]+\.(com|nl|net|org|ru|xyz|top|io|co|info|shop)\b/i', $naam)) {
            return true;
        }
        return false;
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

        // --- Contactformulieren (status=new, én spam eruit gefilterd) ---
        // De spamdetectie ging pas 12-09-2026 live, dus oudere rommel staat nog
        // als 'new'. We her-scoren daarom bij het tonen en weren spam ook uit de
        // tellingen, zodat het scherm echte aanvragen laat zien.
        $schoon = $this->poging(function () use ($weekAgo) {
            return DB::table('contact_messages')->where('status', 'new')
                ->where('created_at', '>=', $weekAgo)
                ->orderByDesc('created_at')->limit(400)
                ->get(['created_at', 'name', 'company', 'email', 'subject', 'topic', 'message', 'website'])
                ->reject(fn ($r) => $this->lijktSpam($r))
                ->values();
        }, collect());
        $out['contact_week'] = $schoon->count();
        $out['contact_vandaag'] = $schoon->filter(fn ($r) => Carbon::parse($r->created_at) >= $today)->count();
        $out['contact_recent'] = $schoon->take(6)->map(fn ($r) => array(
            'wanneer'   => Carbon::parse($r->created_at)->format('d-m H:i'),
            'naam'      => $r->name,
            'bedrijf'   => $r->company,
            'onderwerp' => $r->subject ?: ($r->topic ?: '—'),
        ))->all();

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
