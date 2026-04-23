<?php

namespace App\Services\AccessGuard\Ai;

use App\Models\AccessGuard\Cycle;
use App\Models\AccessGuard\CycleItem;
use RuntimeException;

class ReviewRecommendationService
{
	private const RESPONSE_SCHEMA = [
		'type' => 'object',
		'additionalProperties' => false,
		'required' => ['suggestions'],
		'properties' => [
			'suggestions' => [
				'type' => 'array',
				'items' => [
					'type' => 'object',
					'additionalProperties' => false,
					'required' => ['review_item_id', 'decision', 'rationale'],
					'properties' => [
						'review_item_id' => ['type' => 'integer'],
						'decision' => ['type' => 'string', 'enum' => ['keep', 'revoke', 'change']],
						'rationale' => ['type' => 'string'],
					],
				],
			],
		],
	];

	public function __construct(private readonly OpenAiClient $client) {}

	/**
	 * Ask the LLM to suggest a decision for every undecided item in a
	 * cycle. Context is built from the snapshot alone — no secrets,
	 * no full emails (user only).
	 */
	public function suggestForCycle(Cycle $cycle): array
	{
		if ($cycle->tenant_id === null) throw new RuntimeException('Cycle without tenant');

		$undecided = CycleItem::query()
			->where('cycle_id', $cycle->id)
			->whereNull('decision')
			->orderBy('id')
			->limit(80) // keep prompt size sane
			->get();

		if ($undecided->isEmpty()) {
			return ['suggestions' => [], 'skipped' => 0];
		}

		$items = $undecided->map(fn ($it) => [
			'id' => $it->id,
			'person' => $it->person_label,
			'system' => $it->system_label,
			'item_name' => $it->snapshot_item_name,
			'state' => $it->snapshot_state,
			'level' => $it->snapshot_level,
		])->all();

		$system = 'You are an IAM review advisor. For each access review item, recommend one of: '
			. 'keep (access still appropriate), revoke (access should be removed), change (level/scope should change). '
			. 'Use conservative defaults: if the current state is has_access and the item is clearly core to the role, keep. '
			. 'If it is admin-like and under-verified, prefer revoke or change. '
			. 'If snapshot_state is no_access, keep (no action needed). '
			. 'Return ONE suggestion per item. Max 40 words for each rationale.';

		$user = 'Cycle: ' . $cycle->title . "\n\n"
			. 'Review items:' . "\n"
			. json_encode($items, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

		$result = $this->client->chat($system, $user, self::RESPONSE_SCHEMA);
		$suggestions = $result['json']['suggestions'] ?? [];

		// Index the suggestions by review_item_id and only keep those
		// that are actually undecided on this cycle.
		$validIds = $undecided->pluck('id')->flip();
		$out = [];
		foreach ($suggestions as $s) {
			if (! isset($validIds[$s['review_item_id'] ?? -1])) continue;
			if (! in_array($s['decision'] ?? '', ['keep', 'revoke', 'change'], true)) continue;
			$out[(int) $s['review_item_id']] = [
				'decision' => $s['decision'],
				'rationale' => (string) ($s['rationale'] ?? ''),
			];
		}

		return [
			'suggestions' => $out,
			'items_count' => $undecided->count(),
			'skipped' => $undecided->count() - count($out),
			'tokens_in' => $result['tokens_in'],
			'tokens_out' => $result['tokens_out'],
		];
	}
}
