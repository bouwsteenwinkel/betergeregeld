<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\EmailVerifyToken;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class RegisterController extends Controller
{
	public function show(): View
	{
		return view('auth.register');
	}

	public function store(Request $request): RedirectResponse
	{
		// Honeypot: bots vullen dit verborgen veld in. Stil weigeren (doen alsof
		// het gelukt is) zodat de bot geen feedback krijgt en er niets ontstaat.
		if (filled($request->input('website'))) {
			return redirect(route('register.sent'))->with('email', (string) $request->input('email'));
		}

		// Mensencheck (Cloudflare Turnstile). Een account aanmaken kost ons weinig,
		// maar het stuurt wel een verificatiemail naar een adres dat de bot kiest,
		// en dat is precies waar zo'n formulier als mailkanon voor misbruikt wordt.
		// Fail-closed; zijn de sleutels leeg, dan staat de check uit en verandert
		// er niets. De volledige klassenaam staat hier inline, zoals in de blades.
		if (! app(\App\Services\Security\Turnstile::class)->verify($request->input('cf-turnstile-response'), $request->ip())) {
			return back()
				->withInput()
				->withErrors(['register' => __('Bevestig even dat je geen robot bent en verstuur het opnieuw.')]);
		}

		$data = $request->validate([
			'name' => ['required', 'string', 'max:190'],
			'email' => ['required', 'email', 'max:190', 'unique:users,email'],
			'password' => ['required', 'confirmed', Password::min(10)],
		]);

		[$user, $rawToken] = DB::transaction(function () use ($data) {
			$tenant = Tenant::create([
				'name' => $data['name'],
				'plan' => 'free',
				'is_active' => true,
			]);

			// Nieuwe self-signups krijgen NOOIT automatisch het admin-panel:
			// rol 'client' (front-end dashboard) en geblokkeerd (is_active=false)
			// tot de super-admin de aanvraag goedkeurt via de Users-resource.
			$user = User::create([
				'tenant_id' => $tenant->id,
				'email' => $data['email'],
				'password_hash' => Hash::make($data['password']),
				'role' => 'client',
				'is_active' => false,
				'status' => 'pending',
			]);

			$rawToken = Str::random(64);
			EmailVerifyToken::updateOrCreate(
				['user_id' => $user->id],
				[
					'token_hash' => hash('sha256', $rawToken),
					'sent_at' => now(),
					'expires_at' => now()->addHours(24),
				]
			);

			return [$user, $rawToken];
		});

		$verifyUrl = route('verify.email', ['locale' => app()->getLocale(), 'user' => $user->id, 'token' => $rawToken]);

		Mail::raw(
			"Welkom bij " . config('app.name') . ".\n\n" .
			"Bevestig je e-mailadres via deze link:\n" . $verifyUrl . "\n\n" .
			"De link is 24 uur geldig.\n\n" .
			"Na bevestiging beoordelen wij je aanvraag; je krijgt bericht zodra je account is geactiveerd.",
			function ($msg) use ($user) {
				$msg->to($user->email)
					->subject(__('Bevestig je e-mailadres'));
			}
		);

		return redirect(route('register.sent'))->with('email', $user->email);
	}

	public function sent(): View
	{
		return view('auth.register-sent');
	}
}
