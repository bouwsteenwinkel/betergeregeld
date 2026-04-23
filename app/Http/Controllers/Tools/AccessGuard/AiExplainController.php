<?php

namespace App\Http\Controllers\Tools\AccessGuard;

use App\Http\Controllers\Controller;
use App\Models\AccessGuard\RiskFlag;
use App\Services\AccessGuard\Ai\AiExplainService;
use App\Services\Features\FeatureResolver;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AiExplainController extends Controller
{
	public function __construct(
		private readonly FeatureResolver $features,
		private readonly AiExplainService $ai,
	) {}

	public function explain(Request $request, string $locale): JsonResponse
	{
		$this->mustHaveAccess($request);

		$data = $request->validate([
			'subject_type' => ['required', 'in:risk_flag'],
			'subject_id' => ['required', 'integer'],
		]);

		$tenantId = $request->user()->tenant_id;
		$userId = (string) $request->user()->getAuthIdentifier();

		// Only risk_flag subjects in this phase.
		$flag = RiskFlag::query()
			->where('tenant_id', $tenantId)
			->findOrFail($data['subject_id']);

		try {
			$result = $this->ai->explainRiskFlag($tenantId, $userId, $flag, $locale === 'en' ? 'en' : 'nl', $request);
		} catch (\RuntimeException $e) {
			return response()->json(['ok' => false, 'error' => $e->getMessage()], 429);
		}

		return response()->json([
			'ok' => true,
			'cached' => $result['cached'],
			'explanation' => $result['response'],
			'tokens' => ['in' => $result['tokens_in'], 'out' => $result['tokens_out']],
		]);
	}

	protected function mustHaveAccess(Request $request): void
	{
		abort_unless($request->user(), 401);
		$bag = $this->features->forUser($request->user());
		abort_unless($bag->bool('tool.accessguard.enabled'), 402);
		abort_unless($bag->bool('tool.accessguard.ai_explain'), 402);
		abort_unless(config('accessguard.ai.enabled', true), 503);
	}
}
