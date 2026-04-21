<?php

namespace App\Services\Features;

use App\Models\ToolUsageDaily;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Tracks per-day tool usage and enforces daily limits derived from plan features.
 *
 * Limit keys on the plan:
 *   tool.<slug>.daily.anon   → applies when there is no authenticated user
 *   tool.<slug>.daily.user   → applies when a user is logged in
 *
 * A missing key means "no limit" (same as unlimited). A limit of 0 blocks use entirely.
 */
class ToolUsageTracker
{
	public function __construct(private readonly FeatureResolver $resolver) {}

	/** @return array{allowed:bool, remaining:?int, limit:?int, count:int, scope:string} */
	public function check(Request $request, string $tool): array
	{
		[$scope, $identity] = $this->identity($request);
		$bag = $this->resolver->forRequest($request->user());
		$limit = $bag->limit('tool.' . $tool . '.daily.' . $scope);
		$unlimited = $bag->isUnlimited('tool.' . $tool . '.daily.' . $scope);

		$count = $this->count($identity, $tool);

		if ($unlimited || ($limit === null && ! $bag->has('tool.' . $tool . '.daily.' . $scope))) {
			return [
				'allowed' => true,
				'remaining' => null,
				'limit' => null,
				'count' => $count,
				'scope' => $scope,
			];
		}

		$allowed = $limit === null ? true : $count < $limit;

		return [
			'allowed' => $allowed,
			'remaining' => $limit === null ? null : max(0, $limit - $count),
			'limit' => $limit,
			'count' => $count,
			'scope' => $scope,
		];
	}

	public function record(Request $request, string $tool): void
	{
		[, $identity] = $this->identity($request);
		$this->increment($identity, $tool);
	}

	/** @return array{0:string,1:array<string,?string>} */
	private function identity(Request $request): array
	{
		$user = $request->user();
		if ($user) {
			$uid = (string) $user->getAuthIdentifier();
			return ['user', [
				'identity_key' => 'u:' . $uid,
				'user_id' => $uid,
				'tenant_id' => $user->tenant_id ?? null,
				'ip_hash' => null,
			]];
		}
		$ipHash = $this->hashIp($request->ip() ?? '');
		return ['anon', [
			'identity_key' => 'i:' . $ipHash,
			'user_id' => null,
			'tenant_id' => null,
			'ip_hash' => $ipHash,
		]];
	}

	private function hashIp(string $ip): string
	{
		return hash('sha256', $ip . '|' . config('app.key'));
	}

	private function count(array $identity, string $tool): int
	{
		$today = CarbonImmutable::now()->toDateString();
		return (int) (ToolUsageDaily::query()
			->where('identity_key', $identity['identity_key'])
			->where('tool', $tool)
			->where('date', $today)
			->value('count') ?? 0);
	}

	private function increment(array $identity, string $tool): void
	{
		$today = CarbonImmutable::now()->toDateString();
		$now = CarbonImmutable::now()->toDateTimeString();

		DB::statement(
			'INSERT INTO tool_usage_daily (identity_key, ip_hash, user_id, tenant_id, tool, date, count, updated_at)
			 VALUES (?, ?, ?, ?, ?, ?, 1, ?)
			 ON DUPLICATE KEY UPDATE count = count + 1, updated_at = VALUES(updated_at)',
			[
				$identity['identity_key'],
				$identity['ip_hash'],
				$identity['user_id'],
				$identity['tenant_id'],
				$tool,
				$today,
				$now,
			],
		);
	}
}
