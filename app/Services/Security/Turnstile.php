<?php

namespace App\Services\Security;

use Illuminate\Support\Facades\Http;

/**
 * Cloudflare Turnstile: de mensencheck op het contactformulier.
 *
 * Overgenomen uit Studloop (app/Services/Security/Turnstile.php), zodat beide projecten
 * hetzelfde patroon houden.
 *
 * - `enabled()` is false zolang de sleutels ontbreken; dan verandert er niets. Zo draait
 *   lokaal en in tests alles door zonder Cloudflare.
 * - `verify()` controleert het token van de widget server-side bij Cloudflare. Fail-closed:
 *   geen token, ongeldig token of een mislukte call betekent afkeuren. Een bot die het
 *   formulier rechtstreeks POST heeft geen token en komt er dus niet langs.
 */
class Turnstile
{
    public function enabled(): bool
    {
        return (string) config('turnstile.site_key') !== '' && (string) config('turnstile.secret') !== '';
    }

    public function siteKey(): string
    {
        return (string) config('turnstile.site_key');
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
                    'secret' => (string) config('turnstile.secret'),
                    'response' => $token,
                    'remoteip' => $ip,
                ]));

            return $response->successful() && ($response->json('success') === true);
        } catch (\Throwable $e) {
            report($e);

            return false;
        }
    }
}
