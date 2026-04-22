<?php

namespace App\Http\Controllers\Tools;

use App\Http\Controllers\Controller;
use App\Models\BookkeepingInvoice;
use App\Models\BookkeepingInvoiceLine;
use App\Models\BookkeepingRelation;
use App\Models\BookkeepingTenantSettings;
use App\Models\BookkeepingVatRate;
use App\Services\Features\FeatureResolver;
use App\Services\InvoiceNumberer;
use App\Services\InvoicePdfRenderer;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class BookkeepingInvoiceController extends Controller
{
	public function __construct(
		private readonly FeatureResolver $features,
		private readonly InvoiceNumberer $numberer,
		private readonly InvoicePdfRenderer $pdfRenderer,
	) {}

	public function index(Request $request, string $locale): View
	{
		$this->mustHaveAccess($request);

		$filters = $request->validate([
			'status' => ['nullable', 'in:draft,sent,paid,cancelled'],
			'relation_id' => ['nullable', 'integer'],
		]);

		$q = BookkeepingInvoice::query()
			->where('tenant_id', $request->user()->tenant_id)
			->with('relation')
			->orderByDesc('issue_date')
			->orderByDesc('invoice_number');

		if (! empty($filters['status'])) {
			$q->where('status', $filters['status']);
		}
		if (! empty($filters['relation_id'])) {
			$q->where('relation_id', $filters['relation_id']);
		}

		return view('tools.bookkeeping.invoices.index', [
			'invoices' => $q->paginate(50)->withQueryString(),
			'filters' => $filters,
			'relations' => $this->clientRelations($request),
		]);
	}

	public function create(Request $request, string $locale): View
	{
		$this->mustHaveAccess($request);
		$settings = BookkeepingTenantSettings::find($request->user()->tenant_id);
		return view('tools.bookkeeping.invoices.form', [
			'invoice' => null,
			'lines' => [['description' => '', 'quantity' => 1, 'unit_price' => 0, 'vat_rate_id' => null]],
			'relations' => $this->clientRelations($request),
			'vatRates' => $this->vatRates($request),
			'defaultTerms' => $settings?->default_payment_terms_days ?? 30,
		]);
	}

	public function store(Request $request, string $locale): RedirectResponse
	{
		$this->mustHaveAccess($request);
		$data = $this->validated($request);
		$lines = $this->validatedLines($request);

		$invoice = DB::transaction(function () use ($request, $data, $lines) {
			$invoice = new BookkeepingInvoice($data);
			$invoice->id = (string) Str::uuid();
			$invoice->tenant_id = $request->user()->tenant_id;
			$invoice->created_by_user_id = $request->user()->getAuthIdentifier();
			$invoice->invoice_number = $this->numberer->next($invoice->tenant_id, (int) CarbonImmutable::parse($data['issue_date'])->year);
			$invoice->status = 'draft';
			$invoice->save();

			$this->syncLines($invoice, $lines);
			$invoice->recalculate();
			$invoice->save();
			return $invoice;
		});

		return redirect()->route('tools.bookkeeping.invoices.show', ['locale' => $locale, 'id' => $invoice->id])
			->with('bookkeeping_message', __('Factuur :n aangemaakt.', ['n' => $invoice->invoice_number]));
	}

	public function show(Request $request, string $locale, string $id): View
	{
		$this->mustHaveAccess($request);
		$invoice = $this->mustOwn($request, $id);
		$invoice->load(['lines.vatRate', 'relation']);
		$settings = BookkeepingTenantSettings::find($request->user()->tenant_id);

		return view('tools.bookkeeping.invoices.show', [
			'invoice' => $invoice,
			'settings' => $settings,
		]);
	}

	public function edit(Request $request, string $locale, string $id): View|RedirectResponse
	{
		$this->mustHaveAccess($request);
		$invoice = $this->mustOwn($request, $id);
		if (! $invoice->isEditable()) {
			return redirect()
				->route('tools.bookkeeping.invoices.show', ['locale' => $locale, 'id' => $id])
				->withErrors(['invoice' => __('Deze factuur is al verzonden of betaald en is niet meer bewerkbaar.')]);
		}
		$invoice->load(['lines.vatRate']);

		return view('tools.bookkeeping.invoices.form', [
			'invoice' => $invoice,
			'lines' => $invoice->lines->map(fn ($l) => [
				'id' => $l->id,
				'description' => $l->description,
				'quantity' => $l->quantity,
				'unit_price' => $l->unit_price,
				'vat_rate_id' => $l->vat_rate_id,
			])->all(),
			'relations' => $this->clientRelations($request),
			'vatRates' => $this->vatRates($request),
			'defaultTerms' => 30,
		]);
	}

	public function update(Request $request, string $locale, string $id): RedirectResponse
	{
		$this->mustHaveAccess($request);
		$invoice = $this->mustOwn($request, $id);
		if (! $invoice->isEditable()) {
			return back()->withErrors(['invoice' => __('Deze factuur is niet meer bewerkbaar.')]);
		}

		$data = $this->validated($request);
		$lines = $this->validatedLines($request);

		DB::transaction(function () use ($invoice, $data, $lines) {
			$invoice->fill($data)->save();
			$invoice->lines()->delete();
			$this->syncLines($invoice, $lines);
			$invoice->load('lines.vatRate');
			$invoice->recalculate();
			$invoice->save();
		});

		return redirect()->route('tools.bookkeeping.invoices.show', ['locale' => $locale, 'id' => $invoice->id])
			->with('bookkeeping_message', __('Factuur bijgewerkt.'));
	}

	public function destroy(Request $request, string $locale, string $id): RedirectResponse
	{
		$this->mustHaveAccess($request);
		$invoice = $this->mustOwn($request, $id);
		$invoice->delete();

		return redirect()->route('tools.bookkeeping.invoices.index', ['locale' => $locale])
			->with('bookkeeping_message', __('Factuur verwijderd.'));
	}

	public function markSent(Request $request, string $locale, string $id): RedirectResponse
	{
		$this->mustHaveAccess($request);
		$invoice = $this->mustOwn($request, $id);
		if ($invoice->status === 'draft') {
			$invoice->update(['status' => 'sent', 'sent_at' => now()]);
		}
		return back()->with('bookkeeping_message', __('Factuur :n gemarkeerd als verzonden.', ['n' => $invoice->invoice_number]));
	}

	public function markPaid(Request $request, string $locale, string $id): RedirectResponse
	{
		$this->mustHaveAccess($request);
		$invoice = $this->mustOwn($request, $id);
		if (in_array($invoice->status, ['draft', 'sent'], true)) {
			$invoice->update(['status' => 'paid', 'paid_at' => now(), 'sent_at' => $invoice->sent_at ?: now()]);
		}
		return back()->with('bookkeeping_message', __('Factuur :n gemarkeerd als betaald.', ['n' => $invoice->invoice_number]));
	}

	public function markCancelled(Request $request, string $locale, string $id): RedirectResponse
	{
		$this->mustHaveAccess($request);
		$invoice = $this->mustOwn($request, $id);
		if ($invoice->status !== 'paid') {
			$invoice->update(['status' => 'cancelled']);
		}
		return back()->with('bookkeeping_message', __('Factuur :n geannuleerd.', ['n' => $invoice->invoice_number]));
	}

	public function pdf(Request $request, string $locale, string $id): BinaryFileResponse
	{
		$this->mustHaveAccess($request);
		$invoice = $this->mustOwn($request, $id);

		$dir = storage_path('app/bookkeeping/invoices/' . $invoice->tenant_id);
		$path = $dir . '/factuur-' . $invoice->invoice_number . '.pdf';
		$this->pdfRenderer->render($invoice, $path);

		return response()->download($path, 'factuur-' . $invoice->invoice_number . '.pdf', [
			'Content-Type' => 'application/pdf',
		]);
	}

	// ---------- helpers ----------

	private function mustHaveAccess(Request $request): void
	{
		abort_unless($request->user(), 401);
		abort_unless(
			$this->features->forUser($request->user())->bool('tool.bookkeeping.enabled'),
			402,
		);
	}

	private function mustOwn(Request $request, string $id): BookkeepingInvoice
	{
		return BookkeepingInvoice::query()
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
			'relation_id' => ['required', 'integer', \Illuminate\Validation\Rule::exists('bookkeeping_relations', 'id')
				->where('tenant_id', $request->user()->tenant_id)],
			'issue_date' => ['required', 'date'],
			'due_date' => ['nullable', 'date', 'after_or_equal:issue_date'],
			'payment_terms_days' => ['nullable', 'integer', 'min:0', 'max:365'],
			'reference' => ['nullable', 'string', 'max:80'],
			'notes' => ['nullable', 'string', 'max:2000'],
		]);
	}

	private function validatedLines(Request $request): array
	{
		$lines = $request->validate([
			'lines' => ['required', 'array', 'min:1'],
			'lines.*.description' => ['required', 'string', 'max:500'],
			'lines.*.quantity' => ['required', 'numeric', 'min:0.01', 'max:999999'],
			'lines.*.unit_price' => ['required', 'numeric', 'min:0', 'max:9999999999'],
			'lines.*.vat_rate_id' => ['nullable', 'integer', 'exists:bookkeeping_vat_rates,id'],
		])['lines'];
		return $lines;
	}

	private function syncLines(BookkeepingInvoice $invoice, array $lines): void
	{
		foreach ($lines as $i => $line) {
			BookkeepingInvoiceLine::create([
				'invoice_id' => $invoice->id,
				'description' => $line['description'],
				'quantity' => $line['quantity'],
				'unit_price' => $line['unit_price'],
				'vat_rate_id' => $line['vat_rate_id'] ?? null,
				'sort_order' => $i,
			]);
		}
	}
}
