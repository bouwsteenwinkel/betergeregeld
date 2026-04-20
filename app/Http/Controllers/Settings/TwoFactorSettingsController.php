<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\UserTwofa;
use App\Services\TwoFactorService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class TwoFactorSettingsController extends Controller
{
	public function __construct(private readonly TwoFactorService $tfa) {}

	public function show(Request $request, string $locale): View
	{
		$user = Auth::user();
		$enabled = $this->tfa->isEnabled($user);

		$secret = null;
		$qr = null;
		if (! $enabled) {
			$secret = $request->session()->get('2fa_setup_secret');
			if (! $secret) {
				$secret = $this->tfa->generateSecret();
				$request->session()->put('2fa_setup_secret', $secret);
			}
			$qr = $this->tfa->qrDataUri($user, $secret);
		}

		return view('settings.2fa', [
			'enabled' => $enabled,
			'secret' => $secret,
			'qr' => $qr,
			'backupCodes' => $request->session()->pull('2fa_backup_codes'),
		]);
	}

	public function enable(Request $request, string $locale): RedirectResponse
	{
		$data = $request->validate(['code' => ['required', 'string', 'digits_between:6,8']]);

		$secret = $request->session()->get('2fa_setup_secret');
		if (! $secret) {
			return redirect(route('settings.2fa'))
				->withErrors(['code' => __('2FA-setup is verlopen, probeer opnieuw.')]);
		}

		if (! $this->tfa->verifyTotp($secret, $data['code'])) {
			return back()->withErrors(['code' => __('Code klopt niet. Probeer opnieuw.')]);
		}

		$backupCodes = $this->tfa->enable(Auth::user(), $secret);
		$request->session()->forget('2fa_setup_secret');
		$request->session()->flash('2fa_backup_codes', $backupCodes);

		return redirect(route('settings.2fa'));
	}

	public function disable(Request $request, string $locale): RedirectResponse
	{
		$data = $request->validate(['password' => ['required', 'string']]);

		$user = Auth::user();
		if (! Hash::check($data['password'], $user->password_hash)) {
			return back()->withErrors(['password' => __('Wachtwoord klopt niet.')]);
		}

		$this->tfa->disable($user);

		return redirect(route('settings.2fa'));
	}
}
