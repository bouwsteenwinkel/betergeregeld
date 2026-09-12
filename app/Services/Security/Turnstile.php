<?php

namespace App\Services\Security;

use Illuminate\Support\Facades\Http;

/**
 * Cloudflare Turnstile: de mensencheck op de publieke formulieren.
 *
 * Overgenomen uit Studloop en Bouwsteenwinkel v3 (includes/turnstile.php), zodat de
 * drie projecten hetzelfde patroon houden.
 *
 * - `enabled()` is false zolang de sleutels ontbreken; dan verandert er niets. Zo
 *   draaien lokaal en de tests door zonder Cloudflare.
 * - `verify()` controleert het token server-side bij Cloudflare. Fail-closed: geen
 *   token, een ongeldig token of een mislukte call betekent afkeuren. Een bot die
 *   het formulier rechtstreeks POST heeft geen token en komt er dus niet langs.
 *
 * HOST-BEWUST. Een widget geldt maar voor maximaal 10 hostnames, en deze app bedient
 * betergeregeld.com plus 17 kanaaldomeinen. Daarom kiest siteKey()/secret() het
 * sleutelpaar op basis van de aangevraagde host (zie config/turnstile.php). Dat de
 * widget en de verificatie hetzelfde paar pakken gaat automatisch goed: beide gebeuren
 * binnen hetzelfde verzoek, dus op dezelfde host. Zet ze NOOIT los van elkaar vast —
 * een widget van host A met het secret van host B keurt elk token af.
 */
class Turnstile
{
    public function enabled(): bool
    {
        if ($this->skipLocal()) {
            return false;
        }

        return $this->siteKey() !== '' && $this->secret() !== '';
    }

    public function siteKey(): string
    {
        return (string) ($this->pair()['site_key'] ?? '');
    }

    public function verify(?string $token, ?string $ip = null): bool
    {
        if (! $this->enabled()) {
            return true;
        }

        if (! is_string($token) || $token === '') {
            return false;
        }

        try {
            $response = Http::asForm()
                ->timeout(5)
                ->post((string) config('turnstile.verify_url'), array_filter([
                    'secret' => $this->secret(),
                    'response' => $token,
                    'remoteip' => $ip,
                ]));

            return $response->successful() && ($response->json('success') === true);
        } catch (\Throwable $e) {
            report($e);

            return false;
        }
    }

    private function secret(): string
    {
        return (string) ($this->pair()['secret'] ?? '');
    }

    /**
     * Het sleutelpaar voor de host van dit verzoek: de eerste widgetgroep waarin de
     * host voorkomt, anders de losse site_key/secret uit de .env.
     *
     * @return array{site_key:string,secret:string}
     */
    private function pair(): array
    {
        $host = $this->host();

        if ($host !== '') {
            $kaal = str_starts_with($host, 'www.') ? substr($host, 4) : $host;

            foreach ((array) config('turnstile.widgets', []) as $widget) {
                $hosts = array_map('strtolower', (array) ($widget['hosts'] ?? []));

                if (in_array($host, $hosts, true) || in_array($kaal, $hosts, true)) {
                    return [
                        'site_key' => (string) ($widget['site_key'] ?? ''),
                        'secret' => (string) ($widget['secret'] ?? ''),
                    ];
                }
            }
        }

        return [
            'site_key' => (string) config('turnstile.site_key'),
            'secret' => (string) config('turnstile.secret'),
        ];
    }

    /**
     * De aangevraagde host, zonder poort en in kleine letters.
     *
     * Bewust GEEN uitzondering voor console-runs: die krijgen host 'localhost' en
     * vallen dus al onder skipLocal(), met hetzelfde eindresultaat (check uit). Een
     * extra runningInConsole()-tak maakt de host-resolutie alleen ontestbaar, en
     * juist dat stuk breekt bij een fout stil op 17 sites tegelijk.
     */
    private function host(): string
    {
        $host = strtolower((string) request()->getHost());

        return ($pos = strpos($host, ':')) !== false ? substr($host, 0, $pos) : $host;
    }

    /**
     * Lokaal draaien: Cloudflare staat localhost/.test niet op een widget toe, dus
     * de check zou daar altijd falen en elk formulier onbruikbaar maken.
     */
    private function skipLocal(): bool
    {
        if (! config('turnstile.skip_local', true)) {
            return false;
        }

        $host = $this->host();
        if ($host === '') {
            return false;
        }

        return in_array($host, ['localhost', '127.0.0.1', '::1', '[::1]'], true)
            || str_ends_with($host, '.local')
            || str_ends_with($host, '.test');
    }
}
