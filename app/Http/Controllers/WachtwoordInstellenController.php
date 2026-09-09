<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * Eenmalige link om zelf een wachtwoord te zetten.
 *
 * WAAROM DIT ER IS. Het admin-paneel heeft wel een inlog maar geen
 * "wachtwoord vergeten": AdminPanelProvider roept ->login() aan en niet
 * ->passwordReset(). Wie zijn wachtwoord kwijt is, komt er dus niet meer in.
 *
 * WAAROM NIET DE INGEBOUWDE RESET VAN FILAMENT. Die hangt aan mail, en of er
 * vanaf deze server daadwerkelijk post verstuurd wordt is niet vastgesteld.
 * Een resetknop die stilletjes niets doet is erger dan geen resetknop. Deze
 * weg heeft geen mail nodig: de beheerder krijgt de link rechtstreeks.
 *
 * WAAROM EEN TOKEN IN DE DATABASE EN GEEN ONDERTEKENDE URL. Een signed URL
 * hangt aan APP_KEY, en die kan lokaal anders zijn dan op de server -- dan
 * lijkt de link kapot terwijl er niets mis is. Een token in de tabel werkt
 * ongeacht waar hij gemaakt is.
 *
 * De token staat gehasht in `password_reset_tokens`, verloopt na 48 uur en
 * wordt bij gebruik meteen weggegooid. Eenmalig dus, ook als de link ergens
 * blijft rondslingeren.
 */
class WachtwoordInstellenController extends Controller
{
    private const GELDIG_UREN = 48;

    public function show(string $token)
    {
        $rij = $this->zoekRij($token);
        if (! $rij) {
            return response()->view('auth.wachtwoord-instellen', [
                'fout'  => 'Deze link is verlopen of al gebruikt. Vraag om een nieuwe.',
                'token' => null,
                'email' => null,
            ], 410);
        }

        return view('auth.wachtwoord-instellen', [
            'fout'  => null,
            'token' => $token,
            'email' => $rij->email,
        ]);
    }

    public function store(Request $request, string $token)
    {
        $rij = $this->zoekRij($token);
        if (! $rij) {
            return response()->view('auth.wachtwoord-instellen', [
                'fout'  => 'Deze link is verlopen of al gebruikt. Vraag om een nieuwe.',
                'token' => null,
                'email' => null,
            ], 410);
        }

        $request->validate([
            // Acht is de ondergrens van Laravel; confirmed dwingt de tweede keer af.
            'password' => ['required', 'string', 'min:12', 'confirmed'],
        ], [
            'password.min'       => 'Kies een wachtwoord van minstens 12 tekens.',
            'password.confirmed' => 'De twee wachtwoorden zijn niet gelijk.',
        ]);

        $user = User::where('email', $rij->email)->first();
        if (! $user) {
            return response()->view('auth.wachtwoord-instellen', [
                'fout'  => 'Bij deze link hoort geen gebruiker meer.',
                'token' => null,
                'email' => null,
            ], 410);
        }

        $user->forceFill([
            'password_hash'     => Hash::make($request->input('password')),
            'email_verified_at' => $user->email_verified_at ?: now(),
            'is_active'         => 1,
            'status'            => 'active',
        ])->save();

        // Meteen weg: de link is eenmalig.
        DB::table('password_reset_tokens')->where('email', $rij->email)->delete();

        return redirect('/admin/login')->with('status', 'Je wachtwoord staat klaar. Log in met ' . $rij->email . '.');
    }

    /** De rij die bij deze token hoort, mits nog niet verlopen. */
    private function zoekRij(string $token)
    {
        foreach (DB::table('password_reset_tokens')->get() as $rij) {
            if (! Hash::check($token, $rij->token)) {
                continue;
            }
            if (strtotime((string) $rij->created_at) < strtotime('-' . self::GELDIG_UREN . ' hours')) {
                return null;
            }
            return $rij;
        }
        return null;
    }
}
