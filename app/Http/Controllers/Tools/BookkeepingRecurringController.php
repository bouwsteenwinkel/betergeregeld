<?php

namespace App\Http\Controllers\Tools;

use App\Http\Controllers\Controller;
use App\Models\BookkeepingRecurringInvoice;
use App\Models\BookkeepingRecurringInvoiceLine;
use App\Models\BookkeepingRelation;
use App\Models\BookkeepingVatRate;
use App\Services\Features\FeatureResolver;
use App\Services\RecurringInvoiceGenerator;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class BookkeepingRecurringController extends Controller
{
	public function __construct(
		private readonly FeatureResolver $features,
		private readonly RecurringInvoiceGenerator $generator,
	) {}

	public function index(Request $request, string $locale): View
	{
		$this->mustHaveAccess($request);
		$templates = BookkeepingRecurringInvoice::query()
			->where('tenant_id', $request->user()->tenant_id)
			->with(['relation', 'lines'])
			->orderBy('is_active', 'desc')
			->orderBy('next_run_at')
			->get();

		return view('tools.bookkeeping.recurring.index', [
			'templates' => $templates,
		]);
	}

	public function create(Request $request, string $locale): View
	{
		$this->mustHaveAccess($request);
		return view('tools.bookkeeping.recurring.form', [
			'template' => null,
			'lines' => [['description' => '', 'quantity' => 1, 'unit_price' => 0, 'vat_rate_id' => null]],
			'relations' => $this->clientRelations($request),
			'vatRates' => $this->vatRates($request),
		]);
	}

	public function store(Request $request, string $locale): RedirectResponse
	{
		$this->mustHaveAccess($request);
		$data = $this->validated($request);
		$lines = $this->validatedLines($request);

		DB::transaction(function () use ($request, $data, $lines) {
			$template = new BookkeepingRecurringInvoice($data);
			$template->id = (string) Str::uuid();
			$template->tenant_id = $request->user()->tenant_id;
			$template->created_by_user_id = $request->user()->getAuthIdentifier();
			if (! isset($data['next_run_at'])) {
				$template->next_run_at = $this->pickNextRun($data);
			}
			$template->save();
			$this->syncLines($template, $lines);
		});

		return redirect()->route('tools.bookkeeping.recurring.index', ['locale' => $locale])
			->with('bookkeeping_message', __('Terugkerende factuur aangemaakt.'));
	}

	public function edit(Request $request, string $locale, string $id): View
	{
		$this->mustHaveAccess($request);
		$template = $this->mustOwn($request, $id);
		$template->load('lines.vatRate');

		return view('tools.bookkeeping.recurring.form', [
			'template' => $template,
			'lines' => $template->lines->map(fn ($l) => [
				'id' => $l->id,
				'description' => $l->description,
				'quantity' => $l->quantity,
				'unit_price' => $l->unit_price,
				'vat_rate_id' => $l->vat_rate_id,
			])->all(),
			'relations' => $this->clientRelations($request),
			'vatRates' => $this->vatRates($request),
		]);
	}

	public function update(Request $request, string $locale, string $id): RedirectResponse
	{
		$this->mustHaveAccess($request);
		$template = $this->mustOwn($request, $id);
		$data = $this->validated($request);
		$lines = $this->validatedLines($request);

		DB::transaction(function () use ($template, $data, $lines) {
			$template->fill($data)->save();
			$template->lines()->delete();
			$this->syncLines($template, $lines);
		});

		return redirect()->route('tools.bookkeeping.recurring.index', ['locale' => app()->getLocale()])
			->with('bookkeeping_message', __('Terugkerende factuur bijgewerkt.'));
	}

	public function destroy(Request $request, string $locale, string $id): RedirectResponse
	{
		$this->mustHaveAccess($request);
		$this->mustOwn($request, $id)->delete();
		return redirect()->route('tools.bookkeeping.recurring.index', ['locale' => $locale])
			->with('bookkeeping_message', __('Terugkerende factuur verwijderd.'));
	}

	public function runNow(Request $request, string $locale, string $id): RedirectResponse
	{
		$this->mustHaveAccess($request);
		$template = $this->mustOwn($request, $id);

		try {
			[$invoice, $emailed] = $this->generator->generateOne($template, CarbonImmutable::now()->startOfDay());
			$msg = __('Factuur :n aangemaakt uit template.', ['n' => $invoice->invoice_number]);
			if ($emailed) {
				$msg .= ' ' . __('E-mail verstuurd naar :e.', ['e' => $invoice->relation?->email]);
			}
		} catch (\Throwable $e) {
			return back()->withErrors(['recurring' => __('Genereren mislukt: :msg', ['msg' => $e->getMessage()])]);
		}

		return redirect()->route('tools.bookkeeping.invoices.show', ['locale' => $locale, 'id' => $invoice->id])
			->with('bookkeeping_message', $msg);
	}

	// ---- helpers ----

	private function mustHaveAccess(Request $request): void
	{
		abort_unless($request->user(), 401);
		abort_unless(
			$this->features->forUser($request->user())->bool('tool.bookkeeping.enabled'),
			402,
		);
	}

	private function mustOwn(Request $request, string $id): BookkeepingRecurringInvoice
	{
		return BookkeepingRecurringInvoice::query()
			->where('id', $id)
			->where('tenant_id', $request->user()->tenant_id)
			->firstOrFail();
	}

	private function clientRelations(Request $request)
	{
		return BookkeepingRelation::query()
			->where('tenant_id', $request->user()->tenant_id)
			->whereIn('type', ['client', 'both'])
			->where('is_active', true)
			->orderBy('name')
			->get();
	}

	private function vatRates(Request $request)
	{
		return BookkeepingVatRate::query()
			->forTenant($request->user()->tenant_id)
			->orderByDesc('rate')
			->get();
	}

	private function validated(Request $request): array
	{
		return $request->validate([
			'title' => ['required', 'string', 'max:190'],
			'relation_id' => ['required', 'integer', \Illuminate\Validation\Rule::exists('bookkeeping_relations', 'id')
				->where('tenant_id', $request->user()->tenant_id)],
			'frequency' => ['required', 'in:monthly'],
			'day_of_month' => ['required', 'integer', 'min:1', 'max:28'],
			'start_date' => ['required', 'date'],
			'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
			'next_run_at' => ['nullable', 'date'],
			'payment_terms_days' => ['required', 'integer', 'min:0', 'max:365'],
			'notes' => ['nullable', 'string', 'max:2000'],
			'auto_send_email' => ['nullable', 'boolean'],
			'is_active' => ['nullable', 'boolean'],
		]) + [
			'auto_send_email' => (bool) $request->boolean('auto_send_email', false),
			'is_active' => (bool) $request->boolean('is_active', true),
		];
	}

	private function validatedLines(Request $request): array
	{
		return $request->validate([
			'lines' => ['required', 'array', 'min:1'],
			'lines.*.description' => ['required', 'string', 'max:500'],
			'lines.*.quantity' => ['required', 'numeric', 'min:0.01', 'max:999999'],
			'lines.*.unit_price' => ['required', 'numeric', 'min:0', 'max:9999999999'],
			'lines.*.vat_rate_id' => ['nullable', 'integer', 'exists:bookkeeping_vat_rates,id'],
		])['lines'];
	}

	private function syncLines(BookkeepingRecurringInvoice $template, array $lines): void
	{
		foreach ($lines as $i => $line) {
			BookkeepingRecurringInvoiceLine::create([
				'recurring_invoice_id' => $template->id,
				'description' => $line['description'],
				'quantity' => $line['quantity'],
				'unit_price' => $line['unit_price'],
				'vat_rate_id' => $line['vat_rate_id'] ?? null,
				'sort_order' => $i,
			]);
		}
	}

	private function pickNextRun(array $data): string
	{
		$start = CarbonImmutable::parse($data['start_date']);
		$dom = max(1, min(28, (int) $data['day_of_month']));
		// First occurrence: this-month on dom if not past, else next month
		$thisMonth = $start->setDay(min($dom, $start->daysInMonth));
		if ($thisMonth->toDateString() >= $start->toDateString()) {
			return $thisMonth->toDateString();
		}
		$next = $start->addMonthNoOverflow();
		return $next->setDay(min($dom, $next->daysInMonth))->toDateString();
	}
}
