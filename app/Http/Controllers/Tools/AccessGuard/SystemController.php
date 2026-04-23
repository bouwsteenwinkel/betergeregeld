<?php

namespace App\Http\Controllers\Tools\AccessGuard;

use App\Http\Controllers\Controller;
use App\Models\AccessGuard\BusinessSystem;
use App\Services\Features\FeatureResolver;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SystemController extends Controller
{
	public function __construct(private readonly FeatureResolver $features) {}

	public function index(Request $request, string $locale): View
	{
		$this->mustHaveAccess($request);

		$systems = BusinessSystem::query()
			->where('tenant_id', $request->user()->tenant_id)
			->orderByDesc('is_active')
			->orderBy('name')
			->paginate(50);

		return view('tools.accessguard.systems.index', compact('systems'));
	}

	public function create(Request $request, string $locale): View
	{
		$this->mustHaveAccess($request);
		return view('tools.accessguard.systems.form', [
			'system' => new BusinessSystem(['is_active' => true, 'category' => 'saas']),
		]);
	}

	public function store(Request $request, string $locale): RedirectResponse
	{
		$this->mustHaveAccess($request);
		$data = $this->validateData($request);
		$data['tenant_id'] = $request->user()->tenant_id;

		BusinessSystem::create($data);

		return redirect()
			->route('tools.accessguard.systems.index', ['locale' => $locale])
			->with('status', __('Systeem toegevoegd.'));
	}

	public function edit(Request $request, string $locale, int $id): View
	{
		$this->mustHaveAccess($request);
		$system = $this->findOwned($request, $id);
		return view('tools.accessguard.systems.form', ['system' => $system]);
	}

	public function update(Request $request, string $locale, int $id): RedirectResponse
	{
		$this->mustHaveAccess($request);
		$system = $this->findOwned($request, $id);
		$system->update($this->validateData($request));

		return redirect()
			->route('tools.accessguard.systems.index', ['locale' => $locale])
			->with('status', __('Systeem bijgewerkt.'));
	}

	public function destroy(Request $request, string $locale, int $id): RedirectResponse
	{
		$this->mustHaveAccess($request);
		$system = $this->findOwned($request, $id);
		$system->delete();

		return redirect()
			->route('tools.accessguard.systems.index', ['locale' => $locale])
			->with('status', __('Systeem verwijderd.'));
	}

	private function validateData(Request $request): array
	{
		return $request->validate([
			'name' => ['required', 'string', 'max:160'],
			'category' => ['required', 'in:saas,on_prem,infra,finance,security,comm,other'],
			'is_active' => ['nullable', 'boolean'],
			'notes' => ['nullable', 'string', 'max:2000'],
		]) + ['is_active' => $request->boolean('is_active')];
	}

	private function findOwned(Request $request, int $id): BusinessSystem
	{
		return BusinessSystem::query()
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
