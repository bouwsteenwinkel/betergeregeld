<?php

namespace App\Http\Controllers\Tools\AccessGuard;

use App\Http\Controllers\Controller;
use App\Models\AccessGuard\NotificationPref;
use App\Services\Features\FeatureResolver;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationSettingsController extends Controller
{
	public function __construct(private readonly FeatureResolver $features) {}

	public function edit(Request $request, string $locale): View
	{
		$this->mustHaveAccess($request);
		$pref = NotificationPref::forUser(
			$request->user()->tenant_id,
			(string) $request->user()->getAuthIdentifier(),
		);
		return view('tools.accessguard.notifications.edit', compact('pref'));
	}

	public function update(Request $request, string $locale): RedirectResponse
	{
		$this->mustHaveAccess($request);
		$pref = NotificationPref::forUser(
			$request->user()->tenant_id,
			(string) $request->user()->getAuthIdentifier(),
		);
		$pref->update([
			'digest_enabled' => $request->boolean('digest_enabled'),
			'instant_critical_enabled' => $request->boolean('instant_critical_enabled'),
		]);
		return back()->with('status', __('Notificatie-voorkeuren opgeslagen.'));
	}

	public function unsubscribe(Request $request, string $locale, string $token): View
	{
		$pref = NotificationPref::query()->where('unsubscribe_token', $token)->firstOrFail();
		$pref->update([
			'digest_enabled' => false,
			'instant_critical_enabled' => false,
		]);
		return view('tools.accessguard.notifications.unsubscribed');
	}

	protected function mustHaveAccess(Request $request): void
	{
		abort_unless($request->user(), 401);
		$bag = $this->features->forUser($request->user());
		abort_unless($bag->bool('tool.accessguard.enabled'), 402);
	}
}
