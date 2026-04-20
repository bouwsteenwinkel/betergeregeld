<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\EmailVerifyToken;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VerifyEmailController extends Controller
{
	public function verify(Request $request, string $locale, string $user, string $token): RedirectResponse
	{
		$u = User::find($user);
		$row = EmailVerifyToken::where('user_id', $user)->first();

		if (! $u || ! $row || hash('sha256', $token) !== $row->token_hash || $row->expires_at->isPast()) {
			abort(403, __('Deze verificatielink is ongeldig of verlopen.'));
		}

		$u->forceFill([
			'email_verified_at' => now(),
			'status' => 'active',
		])->save();

		$row->delete();

		Auth::login($u);
		$request->session()->regenerate();

		return redirect(route('home'))->with('verified', true);
	}
}
