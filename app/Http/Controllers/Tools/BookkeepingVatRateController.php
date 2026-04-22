<?php

namespace App\Http\Controllers\Tools;

use App\Http\Controllers\Controller;
use App\Models\BookkeepingVatRate;
use App\Services\Features\FeatureResolver;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BookkeepingVatRateController extends Controller
{
	public function __construct(private readonly FeatureResolver $features) {}

	public function index(Request $request, string $locale): View
	{
		$this->mustHaveAccess($request);
		$user = $request->user();

		$defaults = BookkeepingVatRate::query()
			->whereNull('tenant_id')
			->orderByDesc('rate')
			->get();

		$own = BookkeepingVatRate::query()
			->where('tenant_id', $user->tenant_id)
			->orderByDesc('rate')
			->get();

		return view('tools.bookkeeping.vat-rates.index', [
			'defaults' => $defaults,
			'own' => $own,
		]);
	}

	public function create(Request $request, string $locale): View
	{
		$this->mustHaveAccess($request);
		return view('tools.bookkeeping.vat-rates.form', [
			'rate' => null,
		]);
	}

	public function store(Request $request, string $locale): RedirectResponse
	{
		$this->mustHaveAccess($request);
		$data = $this->validated($request);
		$data['tenant_id'] = $request->user()->tenant_id;

		BookkeepingVatRate::create($data);

		return redirect()->route('tools.bookkeeping.vat-rates.index', ['locale' => $locale])
			->with('bookkeeping_message', __('BTW-tarief toegevoegd.'));
	}

	public function edit(Request $request, string $locale, int $id): View
	{
		$this->mustHaveAccess($request);
		$rate = $this->mustOwn($request, $id);
		return view('tools.bookkeeping.vat-rates.form', [
			'rate' => $rate,
		]);
	}

	public function update(Request $request, string $locale, int $id): RedirectResponse
	{
		$this->mustHaveAccess($request);
		$rate = $this->mustOwn($request, $id);
		$rate->update($this->validated($request));

		return redirect()->route('tools.bookkeeping.vat-rates.index', ['locale' => $locale])
			->with('bookkeeping_message', __('BTW-tarief bijgewerkt.'));
	}

	public function destroy(Request $request, string $locale, int $id): RedirectResponse
	{
		$this->mustHaveAccess($request);
		$rate = $this->mustOwn($request, $id);
		$rate->delete();

		return redirect()->route('tools.bookkeeping.vat-rates.index', ['locale' => $locale])
			->with('bookkeeping_message', __('BTW-tarief verwijderd.'));
	}

	private function mustHaveAccess(Request $request): void
	{
		abort_unless($request->user(), 401);
		abort_unless(
			$this->features->forUser($request->user())->bool('tool.bookkeeping.enabled'),
			402,
		);
	}

	private function mustOwn(Request $request, int $id): BookkeepingVatRate
	{
		return BookkeepingVatRate::query()
			->where('id', $id)
			->where('tenant_id', $request->user()->tenant_id)
			->firstOrFail();
	}

	private function validated(Request $request): array
	{
		return $request->validate([
			'name' => ['required', 'string', 'max:32'],
			'rate' => ['required', 'numeric', 'min:0', 'max:100'],
			'effective_from' => ['required', 'date'],
			'effective_to' => ['nullable', 'date', 'after_or_equal:effective_from'],
			'is_default' => ['nullable', 'boolean'],
		]) + [
			'is_default' => (bool) $request->boolean('is_default', false),
		];
	}
}
