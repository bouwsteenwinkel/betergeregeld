<?php

namespace App\Services\AccessGuard\Ai;

use App\Models\AccessGuard\Cycle;
use App\Models\AccessGuard\CycleAction;
use App\Models\AccessGuard\CycleItem;

class ExecutiveSummaryService
{
	private const RESPONSE_SCHEMA = [
		'type' => 'object',
		'additionalProperties' => false,
		'required' => ['title', 'lead', 'findings', 'next_actions'],
		'properties' => [
			'title' => ['type' => 'string'],
			'lead' => ['type' => 'string'],
			'findings' => [
				'type' => 'array',
				'items' => [
					'type' => 'object',
					'additionalProperties' => false,
					'required' => ['heading', 'body'],
					'properties' => [
						'heading' => ['type' => 'string'],
						'body' => ['type' => 'string'],
					],
				],
			],
			'next_actions' => [
				'type' => 'array',
				'items' => ['type' => 'string'],
			],
		],
	];

	public function __construct(private readonly OpenAiClient $client) {}

	public function summarise(Cycle $cycle): array
	{
		$items = CycleItem::query()->where('cycle_id', $cycle->id)->get();
		$actions = CycleAction::query()->where('cycle_id', $cycle->id)->get();

		$counts = [
			'total' => $items->count(),
			'keep' => $items->where('decision', 'keep')->count(),
			'revoke' => $items->where('decision', 'revoke')->count(),
			'change' => $items->where('decision', 'change')->count(),
			'undecided' => $items->whereNull('decision')->count(),
		];

		// Per-system revoke/change breakdown — keep top 10 most affected
		$bySystem = $items->whereIn('decision', ['revoke', 'change'])
			->groupBy('system_label')
			->map(fn ($g) => [
				'system' => $g->first()->system_label,
				'revokes' => $g->where('decision', 'revoke')->count(),
				'changes' => $g->where('decision', 'change')->count(),
			])
			->sortByDesc(fn ($r) => $r['revokes'] + $r['changes'])
			->take(10)
			->values()
			->all();

		$actionCounts = [
			'total' => $actions->count(),
			'open' => $actions->where('status', 'open')->count(),
			'done' => $actions->where('status', 'done')->count(),
		];

		$system = 'You are an executive-communication advisor. Write a one-pager for management '
			. 'summarising an access-review cycle. Tone: professional, factual, business-focused — '
			. 'not security jargon-heavy. Focus on what changed, what improved, and what needs attention. '
			. 'The "lead" is the 2-3 sentence opener. "findings" are 3-4 themed bullets. '
			. '"next_actions" are concrete things for management to note.';

		$user = 'Cycle: ' . $cycle->title . "\n"
			. 'Scope: ' . $cycle->scope . "\n"
			. 'Started: ' . ($cycle->starts_at?->format('Y-m-d') ?? '—') . "\n"
			. 'Completed: ' . ($cycle->completed_at?->format('Y-m-d H:i') ?? 'still open') . "\n\n"
			. 'Decision counts: ' . json_encode($counts) . "\n"
			. 'Actions materialised: ' . json_encode($actionCounts) . "\n\n"
			. 'Top affected systems: ' . json_encode($bySystem, JSON_UNESCAPED_UNICODE);

		$result = $this->client->chat($system, $user, self::RESPONSE_SCHEMA);

		return [
			'summary' => $result['json'],
			'counts' => $counts,
			'by_system' => $bySystem,
			'action_counts' => $actionCounts,
			'tokens_in' => $result['tokens_in'],
			'tokens_out' => $result['tokens_out'],
		];
	}
}
