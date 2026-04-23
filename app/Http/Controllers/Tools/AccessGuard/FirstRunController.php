<?php

namespace App\Http\Controllers\Tools\AccessGuard;

use App\Http\Controllers\Controller;
use App\Services\AccessGuard\DemoSeeder;
use App\Services\Features\FeatureResolver;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class FirstRunController extends Controller
{
	public function __construct(
		private readonly FeatureResolver $features,
		private readonly DemoSeeder $seeder,
	) {}

	public function seedDemo(Request $request, string $locale): RedirectResponse
	{
		$this->mustHaveAccess($request);
		$tenantId = $request->user()->tenant_id;
		$userId = (string) $request->user()->getAuthIdentifier();

		$counts = $this->seeder->seed($tenantId, $userId);

		return redirect()
			->route('tools.accessguard.index', ['locale' => $locale])
			->with('status', __('Demo-data geladen: :people personen, :systems systemen, :cells matrix-cellen, 1 review-cyclus en :risks risk flags.', [
				'people' => $counts['people'],
				'systems' => $counts['systems'],
				'cells' => $counts['cells'],
				'risks' => $counts['risks'],
			]))
			->with('demo_just_seeded', true);
	}

	public function wipeDemo(Request $request, string $locale): RedirectResponse
	{
		$this->mustHaveAccess($request);
		$n = $this->seeder->wipe($request->user()->tenant_id);
		return redirect()
			->route('tools.accessguard.index', ['locale' => $locale])
			->with('status', __('Demo-data verwijderd (:n personen).', ['n' => $n]));
	}

	protected function mustHaveAccess(Request $request): void
	{
		abort_unless($request->user(), 401);
		$bag = $this->features->forUser($request->user());
		abort_unless($bag->bool('tool.accessguard.enabled'), 402);
	}
}
