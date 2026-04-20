<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\UserSession;
use App\Services\TwoFactorService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class LoginController extends Controller
{
	public function __construct(private readonly TwoFactorService $tfa) {}

	public function show(): View
	{
		return view('auth.login');
	}

	public function login(Request $request): RedirectResponse
	{
		$credentials = $request->validate([
			'email' => ['required', 'email'],
			'password' => ['required', 'string'],
		]);

		$remember = $request->boolean('remember');

		if (! Auth::attempt($credentials, $remember)) {
			return back()
				->withInput($request->only('email'))
				->withErrors(['email' => __('De combinatie van e-mail en wachtwoord klopt niet.')]);
		}

		$user = Auth::user();

		if (! $user->is_active) {
			Auth::logout();

			return back()
				->withInput($request->only('email'))
				->withErrors(['email' => __('Dit account is niet actief.')]);
		}

		if ($this->tfa->isEnabled($user)) {
			Auth::logout();
			$request->session()->put('2fa.user_id', $user->id);
			$request->session()->put('2fa.remember', $remember);
			return redirect(route('2fa.challenge'));
		}

		$request->session()->regenerate();

		DB::table('users')->where('id', $user->id)->update(['last_login_at' => now()]);

		UserSession::create([
			'user_id' => $user->id,
			'session_id' => $request->session()->getId(),
			'ip' => $request->ip() ?? '',
			'user_agent' => substr((string) $request->userAgent(), 0, 255),
			'created_at' => now(),
		]);

		return redirect()->intended(route('home'));
	}

	public function logout(Request $request): RedirectResponse
	{
		$sessionId = $request->session()->getId();
		$userId = Auth::id();

		Auth::logout();
		$request->session()->invalidate();
		$request->session()->regenerateToken();

		if ($userId) {
			UserSession::where('user_id', $userId)
				->where('session_id', $sessionId)
				->whereNull('revoked_at')
				->update(['revoked_at' => now()]);
		}

		return redirect(route('home'));
	}
}
