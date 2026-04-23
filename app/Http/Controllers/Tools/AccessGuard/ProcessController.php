<?php

namespace App\Http\Controllers\Tools\AccessGuard;

use App\Http\Controllers\Controller;
use App\Models\AccessGuard\ChecklistTemplate;
use App\Models\AccessGuard\Person;
use App\Models\AccessGuard\Process;
use App\Models\AccessGuard\ProcessEvidence;
use App\Models\AccessGuard\ProcessItem;
use App\Services\AccessGuard\ProcessService;
use App\Services\Features\FeatureResolver;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProcessController extends Controller
{
	public function __construct(
		private readonly FeatureResolver $features,
		private readonly ProcessService $service,
	) {}

	public function index(Request $request, string $locale): View
	{
		$this->mustHaveAccess($request);

		$kind = $request->query('kind', '');
		$status = $request->query('status', '');

		$processes = Process::query()
			->where('tenant_id', $request->user()->tenant_id)
			->when(in_array($kind, Process::KINDS, true), fn ($q) => $q->where('kind', $kind))
			->when(in_array($status, Process::STATUSES, true), fn ($q) => $q->where('status', $status))
			->with('person:id,first_name,middle_name,last_name')
			->withCount([
				'items',
				'items as done_count' => fn ($q) => $q->whereIn('status', ['done', 'na']),
			])
			->orderByDesc('started_at')
			->paginate(25);

		return view('tools.accessguard.processes.index', compact('processes', 'kind', 'status'));
	}

	public function create(Request $request, string $locale): View
	{
		$this->mustHaveAccess($request);

		$tenantId = $request->user()->tenant_id;
		$preselectPerson = $request->integer('person_id');
		$kind = $request->query('kind', 'onboarding');

		$people = Person::query()
			->where('tenant_id', $tenantId)
			->orderBy('last_name')
			->orderBy('first_name')
			->get(['id', 'first_name', 'middle_name', 'last_name', 'status']);

		$templates = ChecklistTemplate::query()
			->where('tenant_id', $tenantId)
			->orderBy('kind')
			->orderBy('name')
			->get();

		return view('tools.accessguard.processes.start', compact('people', 'templates', 'kind', 'preselectPerson'));
	}

	public function store(Request $request, string $locale): RedirectResponse
	{
		$this->mustHaveAccess($request);
		$tenantId = $request->user()->tenant_id;

		$data = $request->validate([
			'person_id' => ['required', 'integer'],
			'kind' => ['required', 'in:onboarding,offboarding'],
			'template_id' => ['nullable', 'integer'],
			'due_at' => ['nullable', 'date'],
			'notes' => ['nullable', 'string', 'max:2000'],
		]);

		$person = Person::query()->where('tenant_id', $tenantId)->findOrFail($data['person_id']);

		$process = $this->service->start(
			$tenantId,
			(string) $request->user()->getAuthIdentifier(),
			$person,
			$data['kind'],
			$data['template_id'] ?? null,
			$data['due_at'] ?? null,
			$data['notes'] ?? null,
		);

		return redirect()
			->route('tools.accessguard.processes.show', ['locale' => $locale, 'id' => $process->id])
			->with('status', __('Proces gestart met :n checklist-items.', ['n' => $process->items->count()]));
	}

	public function show(Request $request, string $locale, int $id): View
	{
		$this->mustHaveAccess($request);
		$tenantId = $request->user()->tenant_id;

		$process = Process::query()
			->with(['person', 'items.evidence'])
			->where('tenant_id', $tenantId)
			->findOrFail($id);

		$counts = [
			'todo' => $process->items->where('status', 'todo')->count(),
			'in_progress' => $process->items->where('status', 'in_progress')->count(),
			'done' => $process->items->where('status', 'done')->count(),
			'blocked' => $process->items->where('status', 'blocked')->count(),
			'na' => $process->items->where('status', 'na')->count(),
		];

		return view('tools.accessguard.processes.show', compact('process', 'counts'));
	}

	public function updateItem(Request $request, string $locale, int $id, int $itemId): RedirectResponse
	{
		$this->mustHaveAccess($request);
		$tenantId = $request->user()->tenant_id;

		$data = $request->validate([
			'status' => ['required', 'in:todo,in_progress,done,blocked,na'],
			'reason' => ['nullable', 'string', 'max:500'],
		]);

		$item = ProcessItem::query()
			->where('tenant_id', $tenantId)
			->where('process_id', $id)
			->findOrFail($itemId);

		try {
			$this->service->updateItem(
				$tenantId,
				(string) $request->user()->getAuthIdentifier(),
				$item,
				$data['status'],
				$data['reason'] ?? null,
			);
		} catch (\RuntimeException $e) {
			return back()->with('error', $e->getMessage())->withInput();
		}

		return back()->with('status', __('Item bijgewerkt.'));
	}

	public function uploadEvidence(Request $request, string $locale, int $id, int $itemId): RedirectResponse
	{
		$this->mustHaveAccess($request);
		$tenantId = $request->user()->tenant_id;

		$request->validate([
			'file' => ['required', 'file', 'mimes:pdf,png,jpg,jpeg', 'max:15360'],
		]);

		$item = ProcessItem::query()
			->where('tenant_id', $tenantId)
			->where('process_id', $id)
			->findOrFail($itemId);

		$this->service->uploadEvidence(
			$tenantId,
			(string) $request->user()->getAuthIdentifier(),
			$item,
			$request->file('file'),
		);

		return back()->with('status', __('Bewijs toegevoegd.'));
	}

	public function downloadEvidence(Request $request, string $locale, int $id, int $evidenceId): StreamedResponse
	{
		$this->mustHaveAccess($request);
		$tenantId = $request->user()->tenant_id;

		$evidence = ProcessEvidence::query()
			->where('tenant_id', $tenantId)
			->whereHas('processItem', fn ($q) => $q->where('process_id', $id))
			->findOrFail($evidenceId);

		return Storage::disk('local')->download(
			$evidence->stored_path,
			$evidence->original_name,
			['Content-Type' => $evidence->mime],
		);
	}

	public function deleteEvidence(Request $request, string $locale, int $id, int $evidenceId): RedirectResponse
	{
		$this->mustHaveAccess($request);
		$tenantId = $request->user()->tenant_id;

		$evidence = ProcessEvidence::query()
			->where('tenant_id', $tenantId)
			->whereHas('processItem', fn ($q) => $q->where('process_id', $id))
			->findOrFail($evidenceId);

		$this->service->deleteEvidence($tenantId, $evidence);

		return back()->with('status', __('Bewijs verwijderd.'));
	}

	public function complete(Request $request, string $locale, int $id): RedirectResponse
	{
		$this->mustHaveAccess($request);
		$tenantId = $request->user()->tenant_id;

		$process = Process::query()->where('tenant_id', $tenantId)->findOrFail($id);

		try {
			$this->service->complete(
				$tenantId,
				(string) $request->user()->getAuthIdentifier(),
				$process,
			);
		} catch (\RuntimeException $e) {
			return back()->with('error', $e->getMessage());
		}

		return redirect()
			->route('tools.accessguard.processes.show', ['locale' => $locale, 'id' => $process->id])
			->with('status', __('Proces afgerond.'));
	}

	public function cancel(Request $request, string $locale, int $id): RedirectResponse
	{
		$this->mustHaveAccess($request);
		$tenantId = $request->user()->tenant_id;

		$process = Process::query()->where('tenant_id', $tenantId)->findOrFail($id);
		$this->service->cancel(
			$tenantId,
			(string) $request->user()->getAuthIdentifier(),
			$process,
			$request->input('reason'),
		);

		return redirect()
			->route('tools.accessguard.processes.index', ['locale' => $locale])
			->with('status', __('Proces geannuleerd.'));
	}

	protected function mustHaveAccess(Request $request): void
	{
		abort_unless($request->user(), 401);
		$bag = $this->features->forUser($request->user());
		abort_unless($bag->bool('tool.accessguard.enabled'), 402);
	}
}
