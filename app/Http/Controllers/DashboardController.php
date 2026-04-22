<?php

namespace App\Http\Controllers;

use App\Models\BookkeepingInvoice;
use App\Models\BookkeepingTransaction;
use App\Models\Plan;
use App\Models\TenantSubscription;
use App\Models\ToolUsageDaily;
use App\Services\Features\FeatureResolver;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
	private const TOOL_SLUGS = [
		'iban' => ['label_nl' => 'IBAN-check', 'label_en' => 'IBAN check', 'route' => 'tools.iban-check'],
		'vies' => ['label_nl' => 'VIES BTW-check', 'label_en' => 'VIES VAT check', 'route' => 'tools.vat-check'],
	];

	public function __construct(private readonly FeatureResolver $resolver) {}

	public function index(Request $request, string $locale): View
	{
		$user = $request->user();
		$bag = $this->resolver->forUser($user);
		$plan = $bag->plan;

		$subscription = TenantSubscription::query()
			->where('tenant_key', $user->tenant_id)
			->whereIn('status', ['active', 'trial', 'trialing'])
			->whereHas('plan', fn ($q) => $q->where('product', 'tools'))
			->with('plan')
			->orderByDesc('id')
			->first();

		$today = CarbonImmutable::now()->toDateString();
		$weekStart = CarbonImmutable::now()->subDays(6)->toDateString();
		$thirtyDaysAgo = CarbonImmutable::now()->subDays(29)->toDateString();

		$rows = ToolUsageDaily::query()
			->where('user_id', $user->getAuthIdentifier())
			->where('date', '>=', $thirtyDaysAgo)
			->select('tool', 'date', 'count')
			->get()
			->map(fn ($r) => (object) [
				'tool' => $r->tool,
				'date' => $r->date instanceof \DateTimeInterface ? $r->date->format('Y-m-d') : (string) $r->date,
				'count' => (int) $r->count,
			]);

		$tools = [];
		foreach (self::TOOL_SLUGS as $slug => $meta) {
			$forTool = $rows->where('tool', $slug);
			$todayCount = (int) $forTool->firstWhere('date', $today)?->count;
			$weekCount = (int) $forTool->filter(fn ($r) => $r->date >= $weekStart)->sum('count');
			$monthCount = (int) $forTool->sum('count');

			$limit = $bag->limit('tool.' . $slug . '.daily.user');
			$unlimited = $bag->isUnlimited('tool.' . $slug . '.daily.user');

			$tools[] = [
				'slug' => $slug,
				'label' => $meta['label_' . app()->getLocale()] ?? $meta['label_nl'],
				'route' => $meta['route'],
				'today' => $todayCount,
				'week' => $weekCount,
				'month' => $monthCount,
				'limit' => $limit,
				'unlimited' => $unlimited,
				'percent' => $limit ? min(100, (int) round($todayCount / $limit * 100)) : 0,
			];
		}

		$otherPlans = Plan::query()
			->product('tools')
			->where('is_active', true)
			->where('id', '!=', $plan->id)
			->orderBy('sort_order')
			->get(['id', 'plan_key', 'name', 'price_monthly', 'trial_days']);

		$bookkeeping = null;
		if ($bag->bool('tool.bookkeeping.enabled')) {
			$bookkeeping = $this->bookkeepingSnapshot($user->tenant_id);
		}

		return view('pages.dashboard', [
			'user' => $user,
			'plan' => $plan,
			'features' => $bag,
			'subscription' => $subscription,
			'tools' => $tools,
			'otherPlans' => $otherPlans,
			'bookkeeping' => $bookkeeping,
		]);
	}

	/** @return array{outstanding:array,overdue:array,upcoming:array,mtd:array,recent:array} */
	private function bookkeepingSnapshot(string $tenantId): array
	{
		$today = CarbonImmutable::now();
		$todayStr = $today->toDateString();
		$weekAheadStr = $today->addDays(7)->toDateString();
		$monthStartStr = $today->startOfMonth()->toDateString();

		$invoicesBase = BookkeepingInvoice::query()
			->where('tenant_id', $tenantId)
			->where('status', 'sent');

		$outstanding = [
			'count' => (clone $invoicesBase)->count(),
			'sum' => (float) (clone $invoicesBase)->sum('total'),
		];

		$overdue = [
			'count' => (clone $invoicesBase)->whereNotNull('due_date')->where('due_date', '<', $todayStr)->count(),
			'sum' => (float) (clone $invoicesBase)->whereNotNull('due_date')->where('due_date', '<', $todayStr)->sum('total'),
		];

		$upcoming = [
			'count' => (clone $invoicesBase)->whereBetween('due_date', [$todayStr, $weekAheadStr])->count(),
			'sum' => (float) (clone $invoicesBase)->whereBetween('due_date', [$todayStr, $weekAheadStr])->sum('total'),
		];

		$txBase = BookkeepingTransaction::query()
			->where('tenant_id', $tenantId)
			->where('transaction_date', '>=', $monthStartStr);

		$mtd = [
			'income' => (float) (clone $txBase)->where('type', 'income')->sum('amount'),
			'expense' => (float) (clone $txBase)->where('type', 'expense')->sum('amount'),
		];
		$mtd['result'] = $mtd['income'] - $mtd['expense'];

		$recent = BookkeepingInvoice::query()
			->where('tenant_id', $tenantId)
			->where('status', 'paid')
			->with('relation:id,name')
			->orderByDesc('paid_at')
			->limit(5)
			->get(['id', 'invoice_number', 'total', 'paid_at', 'relation_id']);

		return compact('outstanding', 'overdue', 'upcoming', 'mtd', 'recent');
	}
}
