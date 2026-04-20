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

			$user = User::create([
				'tenant_id' => $tenant->id,
				'email' => $data['email'],
				'password_hash' => Hash::make($data['password']),
				'role' => 'admin',
				'is_active' => true,
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
			"De link is 24 uur geldig.",
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
