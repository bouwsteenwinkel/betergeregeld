<?php

namespace App\Http\Controllers\Tools;

use App\Http\Controllers\Controller;
use App\Models\BookkeepingTransaction;
use App\Services\BankCsvImporter;
use App\Services\Features\FeatureResolver;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\View\View;

class BookkeepingImportController extends Controller
{
	public function __construct(
		private readonly FeatureResolver $features,
		private readonly BankCsvImporter $importer,
	) {}

	public function show(Request $request, string $locale): View
	{
		$this->mustHaveAccess($request);
		return view('tools.bookkeeping.import.upload', []);
	}

	public function upload(Request $request, string $locale): RedirectResponse
	{
		$this->mustHaveAccess($request);

		$request->validate([
			'file' => ['required', 'file', 'max:5120'],
			'delimiter' => ['nullable', \Illuminate\Validation\Rule::in(['comma', 'semicolon', 'tab'])],
		]);

		$delim = match ($request->input('delimiter', 'comma')) {
			'semicolon' => ';',
			'tab' => "\t",
			default => ',',
		};

		try {
			$result = $this->importer->parse($request->file('file')->getRealPath(), $delim);
		} catch (\Throwable $e) {
			return back()->withErrors(['file' => __('CSV-parser fout: :msg', ['msg' => $e->getMessage()])]);
		}

		$rows = $this->importer->matchAgainstExisting($result['rows'], $request->user()->tenant_id);

		$key = Str::uuid()->toString();
		$request->session()->put('bk_import_' . $key, [
			'filename' => $request->file('file')->getClientOriginalName(),
			'delimiter' => $delim,
			'columns_used' => $result['columns_used'],
			'rows' => $rows,
			'created_at' => now()->toIso8601String(),
		]);

		return redirect()->route('tools.bookkeeping.import.preview', ['locale' => $locale, 'key' => $key]);
	}

	public function preview(Request $request, string $locale, string $key): View|RedirectResponse
	{
		$this->mustHaveAccess($request);
		$state = $request->session()->get('bk_import_' . $key);
		if (! $state) {
			return redirect()->route('tools.bookkeeping.import.show', ['locale' => $locale])
				->withErrors(['file' => __('Importsessie verlopen, upload opnieuw.')]);
		}

		$stats = [
			'total' => count($state['rows']),
			'with_date' => count(array_filter($state['rows'], fn ($r) => (bool) $r['date'])),
			'matched' => count(array_filter($state['rows'], fn ($r) => ! empty($r['match_tx_id']))),
			'income' => count(array_filter($state['rows'], fn ($r) => $r['amount'] > 0)),
			'expense' => count(array_filter($state['rows'], fn ($r) => $r['amount'] < 0)),
		];

		// Preload matched transactions for display
		$matchIds = array_filter(array_column($state['rows'], 'match_tx_id'));
		$matches = BookkeepingTransaction::query()
			->whereIn('id', $matchIds)
			->where('tenant_id', $request->user()->tenant_id)
			->get()
			->keyBy('id');

		return view('tools.bookkeeping.import.preview', [
			'key' => $key,
			'state' => $state,
			'stats' => $stats,
			'matches' => $matches,
		]);
	}

	public function commit(Request $request, string $locale, string $key): RedirectResponse
	{
		$this->mustHaveAccess($request);
		$state = $request->session()->get('bk_import_' . $key);
		abort_unless($state, 404);

		$data = $request->validate([
			'action' => ['required', 'array'],
			'action.*' => ['in:skip,create,link'],
		]);

		$created = 0;
		$linked = 0;
		$skipped = 0;

		foreach ($state['rows'] as $idx => $row) {
			$choice = $data['action'][$idx] ?? 'skip';
			if ($choice === 'skip' || ! $row['date'] || $row['amount'] === null) {
				$skipped++;
				continue;
			}

			if ($choice === 'link' && $row['match_tx_id']) {
				$tx = BookkeepingTransaction::query()
					->where('id', $row['match_tx_id'])
					->where('tenant_id', $request->user()->tenant_id)
					->first();
				if ($tx && ! $tx->bank_reference && ! $tx->import_source) {
					$tx->update([
						'bank_reference' => $row['reference'] ?: null,
						'import_source' => 'csv:' . $state['filename'],
					]);
					$linked++;
				}
				continue;
			}

			if ($choice === 'create') {
				BookkeepingTransaction::create([
					'id' => (string) Str::uuid(),
					'tenant_id' => $request->user()->tenant_id,
					'created_by_user_id' => $request->user()->getAuthIdentifier(),
					'type' => $row['type'],
					'transaction_date' => $row['date'],
					'amount' => abs((float) $row['amount']),
					'vat_included' => true,
					'vat_deductible' => $row['type'] === 'expense',
					'description' => Str::limit((string) ($row['description'] ?: '(via CSV-import)'), 480),
					'counterparty' => $row['counterparty'] ?: null,
					'bank_reference' => $row['reference'] ?: null,
					'import_source' => 'csv:' . $state['filename'],
				]);
				$created++;
			}
		}

		$request->session()->forget('bk_import_' . $key);

		Log::info('bk csv import done', [
			'tenant' => $request->user()->tenant_id,
			'filename' => $state['filename'],
			'created' => $created, 'linked' => $linked, 'skipped' => $skipped,
		]);

		return redirect()->route('tools.bookkeeping.index', ['locale' => $locale])
			->with('bookkeeping_message', __(':c nieuw, :l gelinkt, :s overgeslagen.', [
				'c' => $created, 'l' => $linked, 's' => $skipped,
			]));
	}

	private function mustHaveAccess(Request $request): void
	{
		abort_unless($request->user(), 401);
		abort_unless(
			$this->features->forUser($request->user())->bool('tool.bookkeeping.enabled'),
			402,
		);
	}
}
