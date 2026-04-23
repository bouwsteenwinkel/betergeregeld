<?php

namespace App\Http\Controllers\Tools\AccessGuard;

use App\Http\Controllers\Controller;
use App\Models\AccessGuard\CycleAction;
use App\Services\AccessGuard\ReviewService;
use App\Services\Features\FeatureResolver;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReviewActionController extends Controller
{
	public function __construct(
		private readonly FeatureResolver $features,
		private readonly ReviewService $service,
	) {}

	public function index(Request $request, string $locale): View
	{
		$this->mustHaveAccess($request);

		$status = $request->query('status', 'open');
		$actions = CycleAction::query()
			->where('tenant_id', $request->user()->tenant_id)
			->when(in_array($status, ['open', 'done', 'cancelled'], true), fn ($q) => $q->where('status', $status))
			->with('cycle:id,title')
			->orderBy('status')
			->orderByDesc('created_at')
			->paginate(50);

		return view('tools.accessguard.actions.index', compact('actions', 'status'));
	}

	public function markDone(Request $request, string $locale, int $id): RedirectResponse
	{
		$this->mustHaveAccess($request);

		$action = CycleAction::query()
			->where('tenant_id', $request->user()->tenant_id)
			->findOrFail($id);

		$this->service->markActionDone(
			$request->user()->tenant_id,
			(string) $request->user()->getAuthIdentifier(),
			$action,
			$request->input('note'),
		);

		return back()->with('status', __('Actie afgerond. Matrix bijgewerkt.'));
	}

	public function cancel(Request $request, string $locale, int $id): RedirectResponse
	{
		$this->mustHaveAccess($request);

		$action = CycleAction::query()
			->where('tenant_id', $request->user()->tenant_id)
			->findOrFail($id);

		$this->service->cancelAction(
			$request->user()->tenant_id,
			(string) $request->user()->getAuthIdentifier(),
			$action,
			$request->input('reason'),
		);

		return back()->with('status', __('Actie geannuleerd.'));
	}

	protected function mustHaveAccess(Request $request): void
	{
		abort_unless($request->user(), 401);
		$bag = $this->features->forUser($request->user());
		abort_unless($bag->bool('tool.accessguard.enabled'), 402);
	}
}
