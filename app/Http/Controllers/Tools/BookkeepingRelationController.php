<?php

namespace App\Http\Controllers\Tools;

use App\Http\Controllers\Controller;
use App\Models\BookkeepingRelation;
use App\Services\Features\FeatureResolver;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BookkeepingRelationController extends Controller
{
	public function __construct(private readonly FeatureResolver $features) {}

	public function index(Request $request, string $locale): View
	{
		$this->mustHaveAccess($request);

		$filters = $request->validate([
			'type' => ['nullable', 'in:client,supplier,both'],
			'q' => ['nullable', 'string', 'max:190'],
			'active' => ['nullable', 'in:all,active,inactive'],
		]);

		$q = BookkeepingRelation::query()
			->where('tenant_id', $request->user()->tenant_id)
			->orderBy('name');

		if (! empty($filters['type'])) {
			$q->where('type', $filters['type']);
		}
		if (! empty($filters['q'])) {
			$search = '%' . $filters['q'] . '%';
			$q->where(function ($sub) use ($search) {
				$sub->where('name', 'like', $search)
					->orWhere('email', 'like', $search)
					->orWhere('kvk_number', 'like', $search)
					->orWhere('vat_number', 'like', $search)
					->orWhere('city', 'like', $search);
			});
		}
		$activeFilter = $filters['active'] ?? 'active';
		if ($activeFilter === 'active') {
			$q->where('is_active', true);
		} elseif ($activeFilter === 'inactive') {
			$q->where('is_active', false);
		}

		$relations = $q->paginate(50)->withQueryString();

		return view('tools.bookkeeping.relations.index', [
			'relations' => $relations,
			'filters' => $filters + ['active' => $activeFilter],
		]);
	}

	public function create(Request $request, string $locale): View
	{
		$this->mustHaveAccess($request);
		return view('tools.bookkeeping.relations.form', [
			'relation' => null,
		]);
	}

	public function store(Request $request, string $locale): RedirectResponse
	{
		$this->mustHaveAccess($request);
		$data = $this->validated($request);
		$data['tenant_id'] = $request->user()->tenant_id;

		BookkeepingRelation::create($data);

		return redirect()->route('tools.bookkeeping.relations.index', ['locale' => $locale])
			->with('bookkeeping_message', __('Relatie toegevoegd.'));
	}

	public function edit(Request $request, string $locale, int $id): View
	{
		$this->mustHaveAccess($request);
		$relation = $this->mustOwn($request, $id);
		return view('tools.bookkeeping.relations.form', [
			'relation' => $relation,
		]);
	}

	public function update(Request $request, string $locale, int $id): RedirectResponse
	{
		$this->mustHaveAccess($request);
		$relation = $this->mustOwn($request, $id);
		$relation->update($this->validated($request));

		return redirect()->route('tools.bookkeeping.relations.index', ['locale' => $locale])
			->with('bookkeeping_message', __('Relatie bijgewerkt.'));
	}

	public function destroy(Request $request, string $locale, int $id): RedirectResponse
	{
		$this->mustHaveAccess($request);
		$relation = $this->mustOwn($request, $id);
		$relation->delete();

		return redirect()->route('tools.bookkeeping.relations.index', ['locale' => $locale])
			->with('bookkeeping_message', __('Relatie verwijderd.'));
	}

	private function mustHaveAccess(Request $request): void
	{
		abort_unless($request->user(), 401);
		abort_unless(
			$this->features->forUser($request->user())->bool('tool.bookkeeping.enabled'),
			402,
		);
	}

	private function mustOwn(Request $request, int $id): BookkeepingRelation
	{
		return BookkeepingRelation::query()
			->where('id', $id)
			->where('tenant_id', $request->user()->tenant_id)
			->firstOrFail();
	}

	private function validated(Request $request): array
	{
		return $request->validate([
			'name' => ['required', 'string', 'max:190'],
			'type' => ['required', 'in:client,supplier,both'],
			'email' => ['nullable', 'email', 'max:190'],
			'phone' => ['nullable', 'string', 'max:50'],
			'kvk_number' => ['nullable', 'string', 'max:20'],
			'vat_number' => ['nullable', 'string', 'max:20'],
			'iban' => ['nullable', 'string', 'max:34'],
			'address' => ['nullable', 'string', 'max:190'],
			'postal_code' => ['nullable', 'string', 'max:16'],
			'city' => ['nullable', 'string', 'max:120'],
			'country' => ['nullable', 'string', 'size:2'],
			'notes' => ['nullable', 'string', 'max:2000'],
			'is_active' => ['nullable', 'boolean'],
		]) + [
			'is_active' => (bool) $request->boolean('is_active', true),
		];
	}
}
