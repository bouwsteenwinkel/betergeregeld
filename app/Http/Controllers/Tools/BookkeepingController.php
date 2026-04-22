<?php

namespace App\Http\Controllers\Tools;

use App\Http\Controllers\Controller;
use App\Models\BookkeepingCategory;
use App\Models\BookkeepingTransaction;
use App\Models\BookkeepingVatRate;
use App\Services\Features\FeatureResolver;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BookkeepingController extends Controller
{
	public function __construct(private readonly FeatureResolver $features) {}

	public function index(Request $request, string $locale): View
	{
		$this->mustHaveAccess($request);
		$user = $request->user();

		$filters = $request->validate([
			'type' => ['nullable', 'in:expense,income'],
			'from' => ['nullable', 'date'],
			'to' => ['nullable', 'date'],
			'q' => ['nullable', 'string', 'max:190'],
		]);

		$q = BookkeepingTransaction::query()
			->where('tenant_id', $user->tenant_id)
			->with(['category', 'vatRate'])
			->orderByDesc('transaction_date')
			->orderByDesc('created_at');

		if (! empty($filters['type'])) {
			$q->where('type', $filters['type']);
		}
		if (! empty($filters['from'])) {
			$q->where('transaction_date', '>=', $filters['from']);
		}
		if (! empty($filters['to'])) {
			$q->where('transaction_date', '<=', $filters['to']);
		}
		if (! empty($filters['q'])) {
			$search = '%' . $filters['q'] . '%';
			$q->where(function ($sub) use ($search) {
				$sub->where('description', 'like', $search)
					->orWhere('counterparty', 'like', $search)
					->orWhere('invoice_number', 'like', $search);
			});
		}

		$transactions = $q->paginate(50)->withQueryString();

		// Running totals for the filtered set
		$totalsQuery = clone $q;
		$totalsQuery->getQuery()->orders = null;
		$totals = [
			'expense_count' => (clone $totalsQuery)->where('type', 'expense')->count(),
			'expense_total' => (float) (clone $totalsQuery)->where('type', 'expense')->sum('amount'),
			'income_count' => (clone $totalsQuery)->where('type', 'income')->count(),
			'income_total' => (float) (clone $totalsQuery)->where('type', 'income')->sum('amount'),
		];

		return view('tools.bookkeeping.index', [
			'transactions' => $transactions,
			'totals' => $totals,
			'filters' => $filters,
		]);
	}

	public function create(Request $request, string $locale): View
	{
		$this->mustHaveAccess($request);
		return view('tools.bookkeeping.form', [
			'transaction' => null,
			'type' => $request->query('type') === 'income' ? 'income' : 'expense',
			'categories' => $this->categoriesFor($request->user()),
			'vatRates' => $this->vatRatesFor($request->user()),
		]);
	}

	public function store(Request $request, string $locale): RedirectResponse
	{
		$this->mustHaveAccess($request);
		$data = $this->validated($request);
		$data['id'] = (string) \Str::uuid();
		$data['tenant_id'] = $request->user()->tenant_id;
		$data['created_by_user_id'] = $request->user()->getAuthIdentifier();

		BookkeepingTransaction::create($data);

		return redirect()->route('tools.bookkeeping.index', ['locale' => $locale])
			->with('bookkeeping_message', __('Transactie toegevoegd.'));
	}

	public function edit(Request $request, string $locale, string $id): View
	{
		$this->mustHaveAccess($request);
		$transaction = $this->mustOwn($request, $id);

		return view('tools.bookkeeping.form', [
			'transaction' => $transaction,
			'type' => $transaction->type,
			'categories' => $this->categoriesFor($request->user()),
			'vatRates' => $this->vatRatesFor($request->user()),
		]);
	}

	public function update(Request $request, string $locale, string $id): RedirectResponse
	{
		$this->mustHaveAccess($request);
		$transaction = $this->mustOwn($request, $id);
		$transaction->update($this->validated($request));

		return redirect()->route('tools.bookkeeping.index', ['locale' => $locale])
			->with('bookkeeping_message', __('Transactie bijgewerkt.'));
	}

	public function destroy(Request $request, string $locale, string $id): RedirectResponse
	{
		$this->mustHaveAccess($request);
		$transaction = $this->mustOwn($request, $id);
		$transaction->delete();

		return redirect()->route('tools.bookkeeping.index', ['locale' => $locale])
			->with('bookkeeping_message', __('Transactie verwijderd.'));
	}

	// ---------- helpers ----------

	private function mustHaveAccess(Request $request): void
	{
		abort_unless($request->user(), 401);
		$bag = $this->features->forUser($request->user());
		abort_unless(
			$bag->bool('tool.bookkeeping.enabled'),
			402,
			'Boekhouden is onderdeel van Pro en Business.',
		);
	}

	private function mustOwn(Request $request, string $id): BookkeepingTransaction
	{
		$t = BookkeepingTransaction::query()
			->where('id', $id)
			->where('tenant_id', $request->user()->tenant_id)
			->firstOrFail();
		return $t;
	}

	private function validated(Request $request): array
	{
		return $request->validate([
			'type' => ['required', 'in:expense,income'],
			'transaction_date' => ['required', 'date'],
			'amount' => ['required', 'numeric', 'min:0', 'max:9999999999'],
			'vat_rate_id' => ['nullable', 'integer', 'exists:bookkeeping_vat_rates,id'],
			'vat_included' => ['nullable', 'boolean'],
			'vat_deductible' => ['nullable', 'boolean'],
			'category_id' => ['nullable', 'integer', 'exists:bookkeeping_categories,id'],
			'description' => ['required', 'string', 'max:500'],
			'counterparty' => ['nullable', 'string', 'max:190'],
			'invoice_number' => ['nullable', 'string', 'max:64'],
		]) + [
			'vat_included' => (bool) $request->boolean('vat_included', true),
			'vat_deductible' => (bool) $request->boolean('vat_deductible', true),
		];
	}

	private function categoriesFor($user)
	{
		return BookkeepingCategory::query()
			->forTenant($user->tenant_id)
			->where('is_active', true)
			->orderBy('type')
			->orderBy('sort_order')
			->orderBy('name')
			->get();
	}

	private function vatRatesFor($user)
	{
		return BookkeepingVatRate::query()
			->forTenant($user->tenant_id)
			->orderBy('is_default', 'desc')
			->orderByDesc('rate')
			->get();
	}
}
