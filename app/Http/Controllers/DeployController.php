<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

/**
 * Token-beveiligde ops-endpoints, zodat een deploy niet per se een RDP-sessie op de VPS vereist.
 *
 *   POST webhooks/deploy   → git pull + optimize:clear + config:cache + filament:optimize
 *                            + OPcache-flush (php-cgi recyclen)
 *   POST webhooks/artisan  → één geregistreerd artisan-commando draaien
 *
 * Waarom in het webproces en niet via een cron/CLI? Alleen dit proces zit in dezelfde PHP-omgeving
 * (php-cgi, open_basedir, OPcache) als de site zelf. De Plesk-CLI-binary heeft z'n eigen caches, dus
 * een clear daar raakt de site niet.
 *
 * Beveiliging: geheim uit config('deploy.token') (env DEPLOY_TOKEN). Niet gezet = endpoints bestaan
 * niet (404). Optioneel een IP-allowlist erbovenop. De artisan-runner draait ALLEEN geregistreerde
 * artisan-commando's — geen vrije shell — en weigert config('deploy.blocked_commands').
 */
class DeployController extends Controller
{
    /** git pull + cache-rebuild + OPcache-flush: de volledige deploy uit DEPLOY.md in één call. */
    public function handle(Request $request): JsonResponse
    {
        $this->guard($request);

        return $this->withLock(function () use ($request) {
            @set_time_limit((int) config('deploy.command_timeout', 280));
            $steps = [];
            $ok = true;

            // 1) Code ophalen. Fast-forward only: nooit een merge-commit of conflict op de server.
            if ($request->boolean('pull', true)) {
                $pull = $this->git('pull --ff-only origin ' . escapeshellarg((string) config('deploy.branch')));
                $steps['git_pull'] = $pull;
                $ok = $ok && $pull['exit'] === 0;
            }
            // --oneline i.p.v. een --pretty-format: PHP's escapeshellarg vervangt op Windows '%'
            // door een spatie, dus een format-string met %h/%s komt verminkt aan.
            $head = $this->git('log -1 --oneline --no-decorate');
            $steps['head'] = $head['output'];

            // 2) Caches herbouwen in de volgorde uit config/deploy.php. Een commando dat hier niet
            //    geregistreerd is (bv. filament:optimize op een kale install) slaan we over i.p.v.
            //    de hele deploy te laten klappen.
            foreach ((array) config('deploy.cache_commands', []) as $command) {
                if (! array_key_exists($command, Artisan::all())) {
                    $steps[$command] = 'overgeslagen (commando bestaat niet)';
                    continue;
                }
                try {
                    $exit = Artisan::call($command);
                    $steps[$command] = ['exit' => $exit, 'output' => trim(Artisan::output())];
                    $ok = $ok && $exit === 0;
                } catch (\Throwable $e) {
                    $steps[$command] = ['exit' => 1, 'error' => $e->getMessage()];
                    $ok = false;
                }
            }

            // 3) OPcache. Eerst dit proces, daarna de andere php-cgi-workers (die hebben elk hun
            //    eigen cache en zouden anders oude code + oude config-cache blijven serveren).
            $steps['opcache_reset'] = $this->resetOpcache();
            if ($request->boolean('recycle', true)) {
                $steps['recycle'] = $this->recycleWorkers();
            }

            Log::info('Deploy uitgevoerd', ['ip' => $request->ip(), 'head' => $steps['head'], 'ok' => $ok]);

            return response()->json(['ok' => $ok, 'steps' => $steps]);
        });
    }

    /**
     * Draai één artisan-commando en geef output + exit-code terug.
     *
     * Body (JSON):
     *   command    (verplicht) — de signatuur, bv. "migrate" of "seo:import-gsc"
     *   parameters (optioneel) — object zoals Artisan::call() het wil: {"--date": "2026-07-01", "--force": true}
     *   args       (optioneel) — losse tokens als gemak: ["--force", "--date=2026-07-01"]
     *   recycle    (optioneel) — php-cgi-workers herstarten na afloop (default: alleen bij
     *                            commando's die code/caches raken)
     */
    public function command(Request $request): JsonResponse
    {
        $this->guard($request);

        $command = trim((string) $request->input('command', ''));
        if ($command === '') {
            return response()->json(['ok' => false, 'error' => 'Geen commando opgegeven'], 422);
        }

        // Alleen geregistreerde artisan-commando's — geen vrije shell.
        $name = strtok($command, ' '); // losse args in de string tellen niet mee voor de check
        if (! array_key_exists($name, Artisan::all())) {
            return response()->json(['ok' => false, 'error' => "Onbekend artisan-commando: {$name}"], 422);
        }

        $blocked = array_map('strval', (array) config('deploy.blocked_commands', []));
        if (in_array($name, $blocked, true)) {
            Log::warning('Artisan-webhook geweigerd: geblokkeerd commando', ['ip' => $request->ip(), 'command' => $name]);

            return response()->json(['ok' => false, 'error' => "Commando geblokkeerd: {$name}"], 403);
        }

        $parameters = $this->buildParameters($request);

        return $this->withLock(function () use ($request, $name, $parameters, $command) {
            @set_time_limit((int) config('deploy.command_timeout', 280));

            try {
                $exit = Artisan::call($name, $parameters);
            } catch (\Throwable $e) {
                Log::warning('Artisan-webhook faalde', ['ip' => $request->ip(), 'command' => $name, 'error' => $e->getMessage()]);

                return response()->json(['ok' => false, 'command' => $name, 'error' => $e->getMessage()], 500);
            }

            $output = Artisan::output();

            // Raakte het commando de code/caches, dan is de OPcache van de workers nu stale.
            $touchesCache = in_array($name, ['optimize', 'optimize:clear', 'config:cache', 'config:clear', 'filament:optimize', 'migrate'], true);
            $recycle = $request->boolean('recycle', $touchesCache);

            Log::info('Artisan-webhook uitgevoerd', ['ip' => $request->ip(), 'command' => $name, 'exit' => $exit]);

            return response()->json(array_filter([
                'ok' => $exit === 0,
                'command' => $command,
                'exit' => $exit,
                'output' => $output,
                'opcache_reset' => $touchesCache ? $this->resetOpcache() : null,
                'recycle' => $recycle ? $this->recycleWorkers() : null,
            ], fn ($v) => $v !== null));
        });
    }

    /** Token- + IP-controle; abort()'t bij een probleem. Gedeeld door alle endpoints. */
    private function guard(Request $request): void
    {
        $secret = (string) config('deploy.token', '');
        if ($secret === '') {
            abort(404); // geen geheim gezet → endpoint bestaat niet
        }

        $given = (string) ($request->header('X-Deploy-Token') ?? $request->input('token', ''));
        if ($given === '' || ! hash_equals($secret, $given)) {
            Log::warning('Ops-webhook geweigerd: verkeerde/ontbrekende token', ['ip' => $request->ip(), 'path' => $request->path()]);
            abort(403);
        }

        $allowed = (array) config('deploy.allowed_ips');
        if ($allowed !== [] && ! in_array($request->ip(), $allowed, true)) {
            Log::warning('Ops-webhook geweigerd: IP niet toegestaan', ['ip' => $request->ip(), 'path' => $request->path()]);
            abort(403);
        }
    }

    /**
     * Voer $work uit onder één gedeeld slot: nooit twee ops-acties tegelijk (een halve pull naast
     * een config:cache is vragen om ellende). 409 als er al iets loopt.
     *
     * Bewust flock() en géén Cache::lock(): (1) `optimize:clear` gooit de cache leeg en daarmee het
     * slot dat we op dat moment zelf vasthouden, en (2) een cache-slot blijft na een hard afgekapt
     * verzoek (IIS-timeout) staan tot de TTL verloopt — zie [[feedback_laravel_schedule_stuck_locks]].
     * Een flock verdwijnt zodra het proces sterft.
     *
     * @param  callable():JsonResponse  $work
     */
    private function withLock(callable $work): JsonResponse
    {
        $path = storage_path('framework/deploy.lock');
        $handle = @fopen($path, 'c');
        if ($handle === false) {
            return response()->json(['ok' => false, 'error' => "Kan slot-bestand niet openen: {$path}"], 500);
        }

        if (! flock($handle, LOCK_EX | LOCK_NB)) {
            fclose($handle);

            return response()->json(['ok' => false, 'error' => 'Er loopt al een ops-actie'], 409);
        }

        try {
            return $work();
        } finally {
            flock($handle, LOCK_UN);
            fclose($handle);
        }
    }

    /** Eén git-commando in de app-root; nette array met exit-code en output terug. */
    private function git(string $arguments): array
    {
        if (! function_exists('exec')) {
            return ['exit' => -1, 'output' => 'exec() is uitgeschakeld op deze host'];
        }

        $git = escapeshellarg((string) config('deploy.git_bin'));
        $base = escapeshellarg(base_path());
        $out = [];
        $exit = -1;
        @exec("{$git} -C {$base} {$arguments} 2>&1", $out, $exit);

        return ['exit' => $exit, 'output' => trim(implode("\n", $out))];
    }

    /** OPcache van dít proces legen; nette status-string terug. */
    private function resetOpcache(): string
    {
        return function_exists('opcache_reset')
            ? (opcache_reset() ? 'ok' : 'geen effect (uit/CLI)')
            : 'niet beschikbaar';
    }

    /**
     * De andere php-cgi-workers afschieten zodat ze met een verse OPcache respawnen. Ons eigen
     * proces blijft staan (filter op PID), anders sterft dit verzoek voordat het antwoord verstuurd
     * is. IIS start nieuwe workers vanzelf bij het volgende bezoek — de eerste hit is dus koud.
     */
    private function recycleWorkers(): array
    {
        $template = (string) config('deploy.recycle_command', '');
        if ($template === '') {
            return ['exit' => -1, 'output' => 'geen recycle_command ingesteld'];
        }
        if (! function_exists('exec')) {
            return ['exit' => -1, 'output' => 'exec() is uitgeschakeld op deze host'];
        }

        $command = str_replace('%PID%', (string) getmypid(), $template);
        $out = [];
        $exit = -1;
        @exec($command . ' 2>&1', $out, $exit);

        return ['exit' => $exit, 'output' => trim(implode("\n", $out))];
    }

    /**
     * Request-input omzetten naar de parameter-array die Artisan::call() verwacht.
     * `parameters` (object) is leidend; losse `args`-tokens worden erbovenop gemerged.
     */
    private function buildParameters(Request $request): array
    {
        $parameters = (array) $request->input('parameters', []);

        foreach ((array) $request->input('args', []) as $token) {
            $token = (string) $token;
            if ($token === '') {
                continue;
            }
            if (str_contains($token, '=')) {
                [$key, $value] = explode('=', $token, 2);
                $parameters[$key] = $value;
            } else {
                $parameters[$token] = true;
            }
        }

        return $parameters;
    }
}
