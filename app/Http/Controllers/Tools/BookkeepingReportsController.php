<?php

namespace App\Http\Controllers\Tools;

use App\Http\Controllers\Controller;
use App\Models\BookkeepingTransaction;
use App\Services\Features\FeatureResolver;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BookkeepingReportsController extends Controller
{
	public function __construct(private readonly FeatureResolver $features) {}

	/** Winst & verlies — income − expense per period, optional breakdown per category. */
	public function profitLoss(Request $request, string $locale): View
	{
		$this->mustHaveAccess($request);

		$data = $request->validate([
			'year' => ['nullable', 'integer', 'min:2000', 'max:2100'],
			'from' => ['nullable', 'date'],
			'to' => ['nullable', 'date'],
		]);

		$today = CarbonImmutable::now();
		$year = $data['year'] ?? (int) $today->year;
		$from = $data['from'] ?? $today->setDate($year, 1, 1)->toDateString();
		$to = $data['to'] ?? $today->setDate($year, 12, 31)->toDateString();

		$q = BookkeepingTransaction::query()
			->where('tenant_id', $request->user()->tenant_id)
			->whereBetween('transaction_date', [$from, $to])
			->with(['category', 'vatRate']);

		$txs = $q->get();

		[$incomeRows, $incomeTotal] = $this->groupByCategory($txs->where('type', 'income'));
		[$expenseRows, $expenseTotal] = $this->groupByCategory($txs->where('type', 'expense'));
		$result = $incomeTotal - $expenseTotal;

		$availableYears = BookkeepingTransaction::query()
			->where('tenant_id', $request->user()->tenant_id)
			->selectRaw('YEAR(transaction_date) as y')
			->distinct()
			->orderByDesc('y')
			->pluck('y')
			->all();
		if (empty($availableYears)) {
			$availableYears = [(int) $today->year];
		}

		return view('tools.bookkeeping.reports.profit-loss', [
			'year' => $year,
			'from' => $from,
			'to' => $to,
			'availableYears' => $availableYears,
			'incomeRows' => $incomeRows,
			'incomeTotal' => $incomeTotal,
			'expenseRows' => $expenseRows,
			'expenseTotal' => $expenseTotal,
			'result' => $result,
		]);
	}

	/** BTW-aangifte per kwartaal (NL). */
	public function vatReturn(Request $request, string $locale): View
	{
		$this->mustHaveAccess($request);

		$data = $request->validate([
			'year' => ['nullable', 'integer', 'min:2000', 'max:2100'],
			'quarter' => ['nullable', 'integer', 'min:1', 'max:4'],
		]);

		$today = CarbonImmutable::now();
		$year = $data['year'] ?? (int) $today->year;
		$quarter = $data['quarter'] ?? (int) ceil($today->month / 3);

		$monthStart = (($quarter - 1) * 3) + 1;
		$from = CarbonImmutable::create($year, $monthStart, 1)->toDateString();
		$to = CarbonImmutable::create($year, $monthStart, 1)->addMonths(3)->subDay()->toDateString();

		$txs = BookkeepingTransaction::query()
			->where('tenant_id', $request->user()->tenant_id)
			->whereBetween('transaction_date', [$from, $to])
			->with('vatRate')
			->get();

		$incomeByRate = [];  // [rate => ['net' => , 'vat' => , 'gross' => , 'count' => ]]
		foreach ($txs->where('type', 'income') as $tx) {
			$rate = $tx->vatRate ? (float) $tx->vatRate->rate : 0.0;
			$key = (string) $rate;
			$incomeByRate[$key] ??= ['rate' => $rate, 'net' => 0, 'vat' => 0, 'gross' => 0, 'count' => 0];
			$incomeByRate[$key]['net'] += $tx->netAmount();
			$incomeByRate[$key]['vat'] += $tx->vatAmount();
			$incomeByRate[$key]['gross'] += $tx->grossAmount();
			$incomeByRate[$key]['count']++;
		}

		$deductibleVat = 0.0;
		$nonDeductibleVat = 0.0;
		$expenseNet = 0.0;
		$expenseCount = 0;
		foreach ($txs->where('type', 'expense') as $tx) {
			$vat = $tx->vatAmount();
			if ($tx->vat_deductible) {
				$deductibleVat += $vat;
			} else {
				$nonDeductibleVat += $vat;
			}
			$expenseNet += $tx->netAmount();
			$expenseCount++;
		}

		uksort($incomeByRate, fn ($a, $b) => (float) $b <=> (float) $a);

		$totalVatDue = array_sum(array_column($incomeByRate, 'vat'));
		$netPayable = $totalVatDue - $deductibleVat;

		return view('tools.bookkeeping.reports.vat', [
			'year' => $year,
			'quarter' => $quarter,
			'from' => $from,
			'to' => $to,
			'incomeByRate' => array_values($incomeByRate),
			'totalVatDue' => $totalVatDue,
			'deductibleVat' => $deductibleVat,
			'nonDeductibleVat' => $nonDeductibleVat,
			'expenseNet' => $expenseNet,
			'expenseCount' => $expenseCount,
			'netPayable' => $netPayable,
		]);
	}

	// ----- helpers -----

	private function mustHaveAccess(Request $request): void
	{
		abort_unless($request->user(), 401);
		$bag = $this->features->forUser($request->user());
		abort_unless($bag->bool('tool.bookkeeping.enabled'), 402);
	}

	/** @return array{0:array<array{category:string,total:float,count:int}>,1:float} */
	private function groupByCategory($txs): array
	{
		$rows = [];
		$total = 0.0;
		foreach ($txs as $tx) {
			$label = $tx->category?->name ?? __('Overig');
			$rows[$label] ??= ['category' => $label, 'total' => 0.0, 'count' => 0];
			$rows[$label]['total'] += (float) $tx->amount;
			$rows[$label]['count']++;
			$total += (float) $tx->amount;
		}
		usort($rows, fn ($a, $b) => $b['total'] <=> $a['total']);
		return [array_values($rows), $total];
	}
}
