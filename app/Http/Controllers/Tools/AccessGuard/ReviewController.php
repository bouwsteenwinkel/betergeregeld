<?php

namespace App\Http\Controllers\Tools\AccessGuard;

use App\Http\Controllers\Controller;
use App\Models\AccessGuard\Cycle;
use App\Models\AccessGuard\CycleItem;
use App\Models\AccessGuard\ReviewLogEntry;
use App\Services\AccessGuard\Ai\ExecutiveSummaryService;
use App\Services\AccessGuard\Ai\ReviewRecommendationService;
use App\Services\AccessGuard\ReviewService;
use App\Services\Features\FeatureResolver;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class ReviewController extends Controller
{
	public function __construct(
		private readonly FeatureResolver $features,
		private readonly ReviewService $service,
		private readonly ReviewRecommendationService $recommender,
		private readonly ExecutiveSummaryService $summariser,
	) {}

	public function index(Request $request, string $locale): View
	{
		$this->mustHaveAccess($request);

		$cycles = Cycle::query()
			->where('tenant_id', $request->user()->tenant_id)
			->withCount([
				'items',
				'items as decided_count' => fn ($q) => $q->whereNotNull('decision'),
				'actions',
				'actions as open_actions_count' => fn ($q) => $q->where('status', 'open'),
			])
			->orderByDesc('created_at')
			->paginate(25);

		return view('tools.accessguard.reviews.index', compact('cycles'));
	}

	public function create(Request $request, string $locale): View
	{
		$this->mustHaveAccess($request);
		return view('tools.accessguard.reviews.create', [
			'defaultTitle' => now()->format('Y') . ' Q' . ceil(now()->month / 3) . ' — ' . __('Access Review'),
		]);
	}

	public function store(Request $request, string $locale): RedirectResponse
	{
		$this->mustHaveAccess($request);

		$data = $request->validate([
			'title' => ['required', 'string', 'max:200'],
			'scope' => ['required', 'in:all,active_people'],
			'due_at' => ['nullable', 'date'],
			'notes' => ['nullable', 'string', 'max:2000'],
		]);

		$cycle = $this->service->createCycle(
			tenantId: $request->user()->tenant_id,
			actorUserId: (string) $request->user()->getAuthIdentifier(),
			title: $data['title'],
			scope: $data['scope'],
			dueAt: $data['due_at'] ?? null,
			notes: $data['notes'] ?? null,
		);

		if ($cycle->items()->count() === 0) {
			$cycle->delete();
			return redirect()
				->route('tools.accessguard.reviews.create', ['locale' => $locale])
				->with('error', __('Geen personen of systemen om te reviewen. Voeg die eerst toe.'));
		}

		return redirect()
			->route('tools.accessguard.reviews.show', ['locale' => $locale, 'id' => $cycle->id])
			->with('status', __('Review-cyclus gestart met :n items.', ['n' => $cycle->items()->count()]));
	}

	public function show(Request $request, string $locale, int $id): View
	{
		$this->mustHaveAccess($request);
		$tenantId = $request->user()->tenant_id;

		$cycle = Cycle::query()
			->where('tenant_id', $tenantId)
			->findOrFail($id);

		$items = CycleItem::query()
			->where('cycle_id', $cycle->id)
			->orderBy('person_label')
			->orderBy('system_label')
			->get();

		$counts = [
			'total' => $items->count(),
			'keep' => $items->where('decision', 'keep')->count(),
			'revoke' => $items->where('decision', 'revoke')->count(),
			'change' => $items->where('decision', 'change')->count(),
			'undecided' => $items->whereNull('decision')->count(),
		];

		$logs = ReviewLogEntry::query()
			->where('tenant_id', $tenantId)
			->where('cycle_id', $cycle->id)
			->orderByDesc('created_at')
			->limit(200)
			->get();

		return view('tools.accessguard.reviews.show', compact('cycle', 'items', 'counts', 'logs'));
	}

	public function decide(Request $request, string $locale, int $id, int $itemId): RedirectResponse
	{
		$this->mustHaveAccess($request);

		$data = $request->validate([
			'decision' => ['required', 'in:keep,revoke,change'],
			'decision_note' => ['nullable', 'string', 'max:500'],
		]);

		$tenantId = $request->user()->tenant_id;
		$item = CycleItem::query()
			->where('tenant_id', $tenantId)
			->where('cycle_id', $id)
			->findOrFail($itemId);

		$this->service->recordDecision(
			$tenantId,
			(string) $request->user()->getAuthIdentifier(),
			$item,
			$data['decision'],
			$data['decision_note'] ?? null,
		);

		return back()->with('status', __('Beslissing opgeslagen.'));
	}

	public function bulkDecide(Request $request, string $locale, int $id): RedirectResponse
	{
		$this->mustHaveAccess($request);

		$data = $request->validate([
			'decision' => ['required', 'in:keep,revoke,change'],
			'item_ids' => ['required', 'array', 'min:1'],
			'item_ids.*' => ['integer'],
		]);

		$tenantId = $request->user()->tenant_id;
		$items = CycleItem::query()
			->where('tenant_id', $tenantId)
			->where('cycle_id', $id)
			->whereIn('id', $data['item_ids'])
			->get();

		foreach ($items as $item) {
			$this->service->recordDecision(
				$tenantId,
				(string) $request->user()->getAuthIdentifier(),
				$item,
				$data['decision'],
			);
		}

		return back()->with('status', __(':n beslissingen opgeslagen.', ['n' => $items->count()]));
	}

	public function complete(Request $request, string $locale, int $id): RedirectResponse
	{
		$this->mustHaveAccess($request);
		$tenantId = $request->user()->tenant_id;

		$data = $request->validate([
			'undecided_default' => ['required', 'in:keep,revoke,change'],
		]);

		$cycle = Cycle::query()->where('tenant_id', $tenantId)->findOrFail($id);
		$this->service->completeCycle(
			$tenantId,
			(string) $request->user()->getAuthIdentifier(),
			$cycle,
			$data['undecided_default'],
		);

		$openActions = $cycle->actions()->where('status', 'open')->count();

		return redirect()
			->route('tools.accessguard.reviews.show', ['locale' => $locale, 'id' => $cycle->id])
			->with('status', __('Cyclus afgerond. :n openstaande acties aangemaakt.', ['n' => $openActions]));
	}

	public function cancel(Request $request, string $locale, int $id): RedirectResponse
	{
		$this->mustHaveAccess($request);
		$tenantId = $request->user()->tenant_id;

		$cycle = Cycle::query()->where('tenant_id', $tenantId)->findOrFail($id);
		$this->service->cancelCycle(
			$tenantId,
			(string) $request->user()->getAuthIdentifier(),
			$cycle,
			$request->input('reason'),
		);

		return redirect()
			->route('tools.accessguard.reviews.index', ['locale' => $locale])
			->with('status', __('Cyclus geannuleerd.'));
	}

	public function aiSuggest(Request $request, string $locale, int $id): JsonResponse
	{
		$this->mustHaveAccess($request);
		$this->mustHaveAi($request);

		$cycle = Cycle::query()->where('tenant_id', $request->user()->tenant_id)->findOrFail($id);
		if (! $cycle->isOpen()) {
			return response()->json(['ok' => false, 'error' => __('Cyclus is niet open.')], 422);
		}

		try {
			$result = $this->recommender->suggestForCycle($cycle);
		} catch (\Throwable $e) {
			return response()->json(['ok' => false, 'error' => $e->getMessage()], 502);
		}

		return response()->json([
			'ok' => true,
			'suggestions' => $result['suggestions'],
			'items_count' => $result['items_count'] ?? 0,
			'skipped' => $result['skipped'] ?? 0,
		]);
	}

	public function aiSummary(Request $request, string $locale, int $id): Response
	{
		$this->mustHaveAccess($request);
		$this->mustHaveAi($request);

		$cycle = Cycle::query()->where('tenant_id', $request->user()->tenant_id)->findOrFail($id);

		try {
			$result = $this->summariser->summarise($cycle);
		} catch (\Throwable $e) {
			return back()->with('error', $e->getMessage());
		}

		$pdf = Pdf::loadView('pages.accessguard-exec-summary', [
			'cycle' => $cycle,
			'summary' => $result['summary'],
			'counts' => $result['counts'],
			'bySystem' => $result['by_system'],
			'actionCounts' => $result['action_counts'],
			'generatedAt' => now()->format('d-m-Y H:i'),
		])
			->setPaper('a4')
			->setOption('defaultFont', 'DejaVu Sans');

		return $pdf->stream('accessguard-cycle-' . $cycle->id . '-summary.pdf');
	}

	protected function mustHaveAccess(Request $request): void
	{
		abort_unless($request->user(), 401);
		$bag = $this->features->forUser($request->user());
		abort_unless($bag->bool('tool.accessguard.enabled'), 402);
	}

	protected function mustHaveAi(Request $request): void
	{
		$bag = $this->features->forUser($request->user());
		abort_unless($bag->bool('tool.accessguard.ai_explain'), 402);
		abort_unless(config('accessguard.ai.enabled', true), 503);
	}
}
