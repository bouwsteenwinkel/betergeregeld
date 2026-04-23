<?php

namespace App\Http\Controllers\Tools\AccessGuard;

use App\Http\Controllers\Controller;
use App\Models\AccessGuard\AccessItem;
use App\Models\AccessGuard\BusinessSystem;
use App\Services\Features\FeatureResolver;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AccessItemController extends Controller
{
	public function __construct(private readonly FeatureResolver $features) {}

	public function index(Request $request, string $locale, int $systemId): View
	{
		$this->mustHaveAccess($request);
		$system = $this->findSystem($request, $systemId);

		$items = AccessItem::query()
			->where('tenant_id', $request->user()->tenant_id)
			->where('system_id', $system->id)
			->orderByDesc('is_active')
			->orderBy('sort_order')
			->orderBy('name')
			->get();

		return view('tools.accessguard.systems.items.index', compact('system', 'items'));
	}

	public function create(Request $request, string $locale, int $systemId): View
	{
		$this->mustHaveAccess($request);
		$system = $this->findSystem($request, $systemId);
		return view('tools.accessguard.systems.items.form', [
			'system' => $system,
			'item' => new AccessItem(['type' => 'role', 'is_active' => true]),
		]);
	}

	public function store(Request $request, string $locale, int $systemId): RedirectResponse
	{
		$this->mustHaveAccess($request);
		$system = $this->findSystem($request, $systemId);
		$data = $this->validateData($request);
		$data['tenant_id'] = $request->user()->tenant_id;
		$data['system_id'] = $system->id;

		AccessItem::create($data);

		return redirect()
			->route('tools.accessguard.systems.items.index', ['locale' => $locale, 'systemId' => $system->id])
			->with('status', __('Item toegevoegd.'));
	}

	public function edit(Request $request, string $locale, int $systemId, int $id): View
	{
		$this->mustHaveAccess($request);
		$system = $this->findSystem($request, $systemId);
		$item = $this->findOwned($request, $system->id, $id);
		return view('tools.accessguard.systems.items.form', compact('system', 'item'));
	}

	public function update(Request $request, string $locale, int $systemId, int $id): RedirectResponse
	{
		$this->mustHaveAccess($request);
		$system = $this->findSystem($request, $systemId);
		$item = $this->findOwned($request, $system->id, $id);
		$item->update($this->validateData($request));

		return redirect()
			->route('tools.accessguard.systems.items.index', ['locale' => $locale, 'systemId' => $system->id])
			->with('status', __('Item bijgewerkt.'));
	}

	public function destroy(Request $request, string $locale, int $systemId, int $id): RedirectResponse
	{
		$this->mustHaveAccess($request);
		$system = $this->findSystem($request, $systemId);
		$item = $this->findOwned($request, $system->id, $id);
		$item->delete();

		return redirect()
			->route('tools.accessguard.systems.items.index', ['locale' => $locale, 'systemId' => $system->id])
			->with('status', __('Item verwijderd.'));
	}

	private function validateData(Request $request): array
	{
		return $request->validate([
			'name' => ['required', 'string', 'max:160'],
			'type' => ['required', 'in:role,licence,account,key,badge,group,other'],
			'description' => ['nullable', 'string', 'max:1000'],
			'is_active' => ['nullable', 'boolean'],
			'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
		]) + [
			'is_active' => $request->boolean('is_active'),
			'sort_order' => $request->integer('sort_order') ?: 100,
		];
	}

	private function findSystem(Request $request, int $systemId): BusinessSystem
	{
		return BusinessSystem::query()
			->where('tenant_id', $request->user()->tenant_id)
			->findOrFail($systemId);
	}

	private function findOwned(Request $request, int $systemId, int $id): AccessItem
	{
		return AccessItem::query()
			->where('tenant_id', $request->user()->tenant_id)
			->where('system_id', $systemId)
			->findOrFail($id);
	}

	protected function mustHaveAccess(Request $request): void
	{
		abort_unless($request->user(), 401);
		$bag = $this->features->forUser($request->user());
		abort_unless($bag->bool('tool.accessguard.enabled'), 402);
	}
}
