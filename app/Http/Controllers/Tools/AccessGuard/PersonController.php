<?php

namespace App\Http\Controllers\Tools\AccessGuard;

use App\Http\Controllers\Controller;
use App\Models\AccessGuard\Person;
use App\Services\Features\FeatureResolver;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PersonController extends Controller
{
	public function __construct(private readonly FeatureResolver $features) {}

	public function index(Request $request, string $locale): View
	{
		$this->mustHaveAccess($request);

		$q = trim((string) $request->query('q', ''));
		$status = $request->query('status', '');

		$people = Person::query()
			->where('tenant_id', $request->user()->tenant_id)
			->when($q !== '', fn ($qb) => $qb->where(function ($w) use ($q) {
				$w->where('first_name', 'like', "%{$q}%")
					->orWhere('last_name', 'like', "%{$q}%")
					->orWhere('email', 'like', "%{$q}%")
					->orWhere('job_title', 'like', "%{$q}%");
			}))
			->when($status !== '', fn ($qb) => $qb->where('status', $status))
			->orderBy('last_name')
			->orderBy('first_name')
			->paginate(50)
			->withQueryString();

		return view('tools.accessguard.people.index', compact('people', 'q', 'status'));
	}

	public function create(Request $request, string $locale): View
	{
		$this->mustHaveAccess($request);
		return view('tools.accessguard.people.form', ['person' => new Person(['status' => 'active', 'type' => 'employee'])]);
	}

	public function store(Request $request, string $locale): RedirectResponse
	{
		$this->mustHaveAccess($request);
		$data = $this->validateData($request);
		$data['tenant_id'] = $request->user()->tenant_id;

		Person::create($data);

		return redirect()
			->route('tools.accessguard.people.index', ['locale' => $locale])
			->with('status', __('Persoon toegevoegd.'));
	}

	public function edit(Request $request, string $locale, int $id): View
	{
		$this->mustHaveAccess($request);
		$person = $this->findOwned($request, $id);
		return view('tools.accessguard.people.form', ['person' => $person]);
	}

	public function update(Request $request, string $locale, int $id): RedirectResponse
	{
		$this->mustHaveAccess($request);
		$person = $this->findOwned($request, $id);

		$person->update($this->validateData($request));

		return redirect()
			->route('tools.accessguard.people.index', ['locale' => $locale])
			->with('status', __('Persoon bijgewerkt.'));
	}

	public function destroy(Request $request, string $locale, int $id): RedirectResponse
	{
		$this->mustHaveAccess($request);
		$person = $this->findOwned($request, $id);
		$person->delete();

		return redirect()
			->route('tools.accessguard.people.index', ['locale' => $locale])
			->with('status', __('Persoon gearchiveerd.'));
	}

	private function validateData(Request $request): array
	{
		return $request->validate([
			'type' => ['required', 'in:employee,contractor,external'],
			'first_name' => ['required', 'string', 'max:100'],
			'middle_name' => ['nullable', 'string', 'max:50'],
			'last_name' => ['required', 'string', 'max:100'],
			'email' => ['nullable', 'email', 'max:190'],
			'phone' => ['nullable', 'string', 'max:40'],
			'job_title' => ['nullable', 'string', 'max:120'],
			'department' => ['nullable', 'string', 'max:120'],
			'status' => ['required', 'in:active,scheduled_in,scheduled_out,inactive'],
			'start_date' => ['nullable', 'date'],
			'end_date' => ['nullable', 'date'],
			'notes' => ['nullable', 'string', 'max:2000'],
		]);
	}

	private function findOwned(Request $request, int $id): Person
	{
		return Person::query()
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
