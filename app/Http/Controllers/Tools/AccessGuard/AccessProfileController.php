<?php

namespace App\Http\Controllers\Tools\AccessGuard;

use App\Http\Controllers\Controller;
use App\Models\AccessGuard\AccessItem;
use App\Models\AccessGuard\AccessProfile;
use App\Models\AccessGuard\BusinessSystem;
use App\Models\AccessGuard\Person;
use App\Models\AccessGuard\ProfileItem;
use App\Services\AccessGuard\ProfileService;
use App\Services\Features\FeatureResolver;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AccessProfileController extends Controller
{
	public function __construct(
		private readonly FeatureResolver $features,
		private readonly ProfileService $service,
	) {}

	public function index(Request $request, string $locale): View
	{
		$this->mustHaveAccess($request);
		$profiles = AccessProfile::query()
			->where('tenant_id', $request->user()->tenant_id)
			->withCount(['items', 'members'])
			->orderByDesc('is_active')
			->orderBy('name')
			->get();
		return view('tools.accessguard.profiles.index', compact('profiles'));
	}

	public function create(Request $request, string $locale): View
	{
		$this->mustHaveAccess($request);
		return view('tools.accessguard.profiles.form', [
			'profile' => new AccessProfile(['is_active' => true]),
			'profileItems' => collect(),
			'members' => collect(),
			'systems' => $this->systemsFor($request),
			'items' => $this->itemsFor($request),
		]);
	}

	public function store(Request $request, string $locale): RedirectResponse
	{
		$this->mustHaveAccess($request);
		$data = $this->validateProfile($request);
		$profile = AccessProfile::create($data + [
			'tenant_id' => $request->user()->tenant_id,
		]);
		$this->syncItems($profile, $request);
		return redirect()->route('tools.accessguard.profiles.index', ['locale' => $locale])
			->with('status', __('Profile aangemaakt.'));
	}

	public function edit(Request $request, string $locale, int $id): View
	{
		$this->mustHaveAccess($request);
		$profile = $this->findOwned($request, $id);
		return view('tools.accessguard.profiles.form', [
			'profile' => $profile,
			'profileItems' => $profile->items()->get(),
			'members' => $profile->members()->with('person:id,first_name,last_name,email,job_title,status,external_source')->get(),
			'systems' => $this->systemsFor($request),
			'items' => $this->itemsFor($request),
		]);
	}

	public function update(Request $request, string $locale, int $id): RedirectResponse
	{
		$this->mustHaveAccess($request);
		$profile = $this->findOwned($request, $id);
		$profile->update($this->validateProfile($request));
		$this->syncItems($profile, $request);
		return redirect()->route('tools.accessguard.profiles.index', ['locale' => $locale])
			->with('status', __('Profile bijgewerkt.'));
	}

	public function destroy(Request $request, string $locale, int $id): RedirectResponse
	{
		$this->mustHaveAccess($request);
		$profile = $this->findOwned($request, $id);
		$profile->delete();
		return redirect()->route('tools.accessguard.profiles.index', ['locale' => $locale])
			->with('status', __('Profile verwijderd.'));
	}

	public function applyForm(Request $request, string $locale, int $id): View
	{
		$this->mustHaveAccess($request);
		$profile = $this->findOwned($request, $id);
		$people = Person::query()
			->where('tenant_id', $request->user()->tenant_id)
			->whereIn('status', ['active', 'scheduled_in', 'scheduled_out'])
			->orderBy('last_name')->orderBy('first_name')
			->get(['id', 'first_name', 'middle_name', 'last_name', 'job_title']);
		return view('tools.accessguard.profiles.apply', compact('profile', 'people'));
	}

	public function apply(Request $request, string $locale, int $id): RedirectResponse
	{
		$this->mustHaveAccess($request);
		$data = $request->validate([
			'person_id' => ['required', 'integer'],
			'strategy' => ['required', 'in:add_only,overwrite,dry_run'],
		]);
		$profile = $this->findOwned($request, $id);
		$person = Person::query()
			->where('tenant_id', $request->user()->tenant_id)
			->findOrFail($data['person_id']);

		$result = $this->service->applyTo(
			$request->user()->tenant_id,
			(string) $request->user()->getAuthIdentifier(),
			$person,
			$profile,
			$data['strategy'],
		);

		$msg = $data['strategy'] === 'dry_run'
			? __(':n wijzigingen zouden worden toegepast.', ['n' => $result['would_change']])
			: __('Profile toegepast op :name: :a toegevoegd, :s overgeslagen.', [
				'name' => trim($person->first_name . ' ' . $person->last_name),
				'a' => $result['applied'],
				's' => $result['skipped'],
			]);

		return back()->with('status', $msg);
	}

	public function applyToMembers(Request $request, string $locale, int $id): RedirectResponse
	{
		$this->mustHaveAccess($request);
		$data = $request->validate([
			'strategy' => ['required', 'in:add_only,overwrite,dry_run'],
		]);
		$profile = $this->findOwned($request, $id);

		$result = $this->service->applyToAllMembers(
			$request->user()->tenant_id,
			(string) $request->user()->getAuthIdentifier(),
			$profile,
			$data['strategy'],
		);

		$msg = $data['strategy'] === 'dry_run'
			? __('Dry-run: :n wijzigingen zouden worden toegepast op :m leden.', [
				'n' => $result['total_would_change'], 'm' => $result['members'],
			])
			: __(':m leden verwerkt: :a toegepast, :s overgeslagen.', [
				'm' => $result['members'], 'a' => $result['total_applied'], 's' => $result['total_skipped'],
			]);

		return back()->with('status', $msg);
	}

	private function validateProfile(Request $request): array
	{
		return $request->validate([
			'name' => ['required', 'string', 'max:120'],
			'description' => ['nullable', 'string', 'max:1000'],
			'is_active' => ['nullable', 'boolean'],
		]) + ['is_active' => $request->boolean('is_active')];
	}

	private function syncItems(AccessProfile $profile, Request $request): void
	{
		$rows = $request->input('items', []);
		$profile->items()->delete();

		foreach ($rows as $row) {
			$systemId = (int) ($row['system_id'] ?? 0);
			if ($systemId === 0) continue;

			ProfileItem::create([
				'tenant_id' => $profile->tenant_id,
				'profile_id' => $profile->id,
				'system_id' => $systemId,
				'access_item_id' => ! empty($row['access_item_id']) ? (int) $row['access_item_id'] : null,
				'default_state' => in_array($row['default_state'] ?? 'has_access', ['has_access', 'no_access', 'needs_review'], true)
					? $row['default_state'] : 'has_access',
			]);
		}
	}

	private function findOwned(Request $request, int $id): AccessProfile
	{
		return AccessProfile::query()
			->where('tenant_id', $request->user()->tenant_id)
			->findOrFail($id);
	}

	private function systemsFor(Request $request)
	{
		return BusinessSystem::query()
			->where('tenant_id', $request->user()->tenant_id)
			->orderBy('name')
			->get(['id', 'name']);
	}

	private function itemsFor(Request $request)
	{
		return AccessItem::query()
			->where('tenant_id', $request->user()->tenant_id)
			->where('is_active', true)
			->orderBy('system_id')->orderBy('name')
			->get(['id', 'system_id', 'name', 'type']);
	}

	protected function mustHaveAccess(Request $request): void
	{
		abort_unless($request->user(), 401);
		$bag = $this->features->forUser($request->user());
		abort_unless($bag->bool('tool.accessguard.enabled'), 402);
	}
}
