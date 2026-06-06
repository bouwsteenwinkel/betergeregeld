<?php

namespace App\Http\Controllers\Tools\AccessGuard;

use App\Http\Controllers\Controller;
use App\Models\AccessGuard\RiskFlag;
use App\Services\AccessGuard\RiskScannerService;
use App\Services\Features\FeatureResolver;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RiskFlagController extends Controller
{
	public function __construct(
		private readonly FeatureResolver $features,
		private readonly RiskScannerService $scanner,
	) {}

	public function index(Request $request, string $locale): View
	{
		$this->mustHaveAccess($request);

		$status = $request->query('status', 'open');
		$flags = RiskFlag::query()
			->where('tenant_id', $request->user()->tenant_id)
			->when(in_array($status, RiskFlag::STATUSES, true), fn ($q) => $q->where('status', $status))
			->orderByDesc('severity')
			->orderByDesc('detected_at')
			->paginate(50);

		return view('tools.accessguard.risks.index', compact('flags', 'status'));
	}

	public function scanNow(Request $request, string $locale): RedirectResponse
	{
		$this->mustHaveAccess($request);
		$counts = $this->scanner->scan($request->user()->tenant_id);
		$total = array_sum($counts);
		return back()->with('status', __('Scan klaar, :n risico\'s gedetecteerd.', ['n' => $total]));
	}

	public function acknowledge(Request $request, string $locale, int $id): RedirectResponse
	{
		$this->mustHaveAccess($request);
		$flag = $this->findOwned($request, $id);
		$flag->update([
			'status' => 'acknowledged',
			'acknowledged_at' => CarbonImmutable::now(),
			'acknowledged_by_user_id' => (string) $request->user()->getAuthIdentifier(),
		]);
		return back()->with('status', __('Risico bevestigd.'));
	}

	public function resolve(Request $request, string $locale, int $id): RedirectResponse
	{
		$this->mustHaveAccess($request);
		$flag = $this->findOwned($request, $id);
		$flag->update([
			'status' => 'resolved',
			'resolved_at' => CarbonImmutable::now(),
			'resolved_by_user_id' => (string) $request->user()->getAuthIdentifier(),
		]);
		return back()->with('status', __('Risico opgelost.'));
	}

	public function reopen(Request $request, string $locale, int $id): RedirectResponse
	{
		$this->mustHaveAccess($request);
		$flag = $this->findOwned($request, $id);
		$flag->update([
			'status' => 'open',
			'acknowledged_at' => null,
			'acknowledged_by_user_id' => null,
			'resolved_at' => null,
			'resolved_by_user_id' => null,
		]);
		return back()->with('status', __('Risico heropend.'));
	}

	private function findOwned(Request $request, int $id): RiskFlag
	{
		return RiskFlag::query()
			->where('tenant_id', $request->user()->tenant_id)
			->findOrFail($id);
	}

	protected function mustHaveAccess(Request $request): void
	{
		abort_unless($request->user(), 401);
		$bag = $this->features->forUser($request->user());
		abort_unless($bag->bool('tool.accessguard.enabled'), 402);
	}
}
