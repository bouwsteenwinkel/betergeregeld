<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserSession;
use App\Models\UserTwofa;
use App\Services\TwoFactorService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class TwoFactorChallengeController extends Controller
{
	public function __construct(private readonly TwoFactorService $tfa) {}

	public function show(Request $request, string $locale): View|RedirectResponse
	{
		if (! $request->session()->has('2fa.user_id')) {
			return redirect(route('login'));
		}
		return view('auth.2fa-challenge');
	}

	public function verify(Request $request, string $locale): RedirectResponse
	{
		$userId = $request->session()->get('2fa.user_id');
		if (! $userId) {
			return redirect(route('login'));
		}

		$data = $request->validate([
			'code' => ['required_without:backup_code', 'nullable', 'string'],
			'backup_code' => ['required_without:code', 'nullable', 'string'],
		]);

		$user = User::find($userId);
		if (! $user) {
			$request->session()->forget(['2fa.user_id', '2fa.remember']);
			return redirect(route('login'));
		}

		$ok = false;
		if (! empty($data['code'])) {
			$row = UserTwofa::where('user_id', $user->id)->where('enabled', true)->first();
			if ($row) {
				$secret = $this->tfa->decryptSecret($row);
				$ok = $this->tfa->verifyTotp($secret, $data['code']);
			}
		} elseif (! empty($data['backup_code'])) {
			$ok = $this->tfa->consumeBackupCode($user, $data['backup_code']);
		}

		if (! $ok) {
			return back()->withErrors(['code' => __('Code klopt niet. Probeer opnieuw.')]);
		}

		$remember = (bool) $request->session()->pull('2fa.remember', false);
		$request->session()->forget('2fa.user_id');

		Auth::login($user, $remember);
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
}
