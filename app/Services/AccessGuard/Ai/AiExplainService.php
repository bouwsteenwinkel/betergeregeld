<?php

namespace App\Services\AccessGuard\Ai;

use App\Models\AccessGuard\RiskFlag;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Orchestrates a single "explain this" round-trip:
 *   - gate checks (feature + rate limit)
 *   - context building (kind-specific)
 *   - prompt templating (NL/EN)
 *   - cache lookup by (tenant, prompt_hash) within TTL
 *   - OpenAI call (or fake canned response)
 *   - log row with tokens + duration
 */
class AiExplainService
{
	private const RESPONSE_SCHEMA = [
		'type' => 'object',
		'additionalProperties' => false,
		'required' => ['title', 'summary', 'why_it_matters', 'recommended_steps', 'warnings'],
		'properties' => [
			'title' => ['type' => 'string'],
			'summary' => ['type' => 'string'],
			'why_it_matters' => ['type' => 'string'],
			'recommended_steps' => ['type' => 'array', 'items' => ['type' => 'string']],
			'warnings' => ['type' => 'array', 'items' => ['type' => 'string']],
		],
	];

	public function __construct(
		private readonly OpenAiClient $client,
		private readonly RiskFlagContextBuilder $riskBuilder,
	) {}

	/**
	 * @return array{cached:bool, status:string, response:array, tokens_in:int, tokens_out:int}
	 */
	public function explainRiskFlag(
		string $tenantId,
		string $userId,
		RiskFlag $flag,
		string $lang = 'nl',
		?Request $request = null,
	): array {
		if ($flag->tenant_id !== $tenantId) {
			throw new RuntimeException('Tenant mismatch on risk flag');
		}

		// Rate limit per tenant per rolling 60 minutes.
		$limit = (int) config('accessguard.ai.rate_limit_per_hour', 10);
		$since = CarbonImmutable::now()->subHour();
		$recent = DB::table('accessguard_ai_logs')
			->where('tenant_id', $tenantId)
			->where('created_at', '>=', $since)
			->whereIn('status', ['ok', 'api_error'])
			->count();
		if ($recent >= $limit) {
			$this->log($tenantId, $userId, 'risk_flag', (string) $flag->id, '', 'gpt-?', null, 0, 0, 0, 'rate_limited', "over {$limit}/h", $request);
			throw new RuntimeException(__('AI-limiet bereikt (:n/uur). Probeer het later opnieuw.', ['n' => $limit]));
		}

		$context = $this->riskBuilder->build($flag);
		[$system, $user] = $this->buildPrompts($context, $lang);
		$promptHash = hash('sha256', $system . "\n\n" . $user);

		// Check 24h cache for identical prompt.
		$ttl = (int) config('accessguard.ai.cache_ttl_seconds', 86400);
		$cacheCutoff = CarbonImmutable::now()->subSeconds($ttl);
		$cached = DB::table('accessguard_ai_logs')
			->where('tenant_id', $tenantId)
			->where('prompt_hash', $promptHash)
			->where('status', 'ok')
			->where('created_at', '>=', $cacheCutoff)
			->orderByDesc('created_at')
			->first();

		if ($cached) {
			$this->log($tenantId, $userId, 'risk_flag', (string) $flag->id, $promptHash, (string) $cached->model, json_decode($cached->response_json, true), 0, 0, 0, 'cached', null, $request);
			return [
				'cached' => true,
				'status' => 'cached',
				'response' => json_decode($cached->response_json, true),
				'tokens_in' => 0,
				'tokens_out' => 0,
			];
		}

		$start = microtime(true);
		try {
			$result = $this->client->chat($system, $user, self::RESPONSE_SCHEMA);
		} catch (\Throwable $e) {
			$this->log($tenantId, $userId, 'risk_flag', (string) $flag->id, $promptHash, (string) config('accessguard.ai.model'), null, 0, 0, (int) ((microtime(true) - $start) * 1000), 'api_error', substr($e->getMessage(), 0, 500), $request);
			throw new RuntimeException(__('AI-service niet beschikbaar: :msg', ['msg' => $e->getMessage()]));
		}
		$duration = (int) ((microtime(true) - $start) * 1000);

		$this->log($tenantId, $userId, 'risk_flag', (string) $flag->id, $promptHash, $result['model'], $result['json'], $result['tokens_in'], $result['tokens_out'], $duration, 'ok', null, $request);

		return [
			'cached' => false,
			'status' => 'ok',
			'response' => $result['json'],
			'tokens_in' => $result['tokens_in'],
			'tokens_out' => $result['tokens_out'],
		];
	}

	private function buildPrompts(array $context, string $lang): array
	{
		$isEn = $lang === 'en';

		$system = $isEn
			? "You are an Identity & Access Management (IAM) advisor embedded in the AccessGuard product. "
				. "Given a single detected risk, explain it clearly to a non-security business user. "
				. "Be specific to the provided context — do not invent data. "
				. "Never ask for or output passwords, secrets, tokens or personal data. "
				. "Respond ONLY as valid JSON matching the provided schema."
			: "Je bent een IAM-adviseur binnen AccessGuard. "
				. "Leg één gedetecteerd risico helder uit aan een business-gebruiker (niet-security). "
				. "Wees specifiek op basis van de meegegeven context — verzin geen data. "
				. "Vraag nooit om of toon nooit wachtwoorden, secrets, tokens of persoonsgegevens. "
				. "Antwoord ALLEEN in geldig JSON volgens het schema.";

		$user = ($isEn ? "Explain this risk in " . ($isEn ? "English" : "Dutch") . ".\n\nContext:\n" : "Leg dit risico uit in het Nederlands.\n\nContext:\n")
			. json_encode($context, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)
			. "\n\n"
			. ($isEn
				? "Give a 2-3 sentence summary, a 'why it matters' paragraph, 3-4 concrete recommended steps the user can take in AccessGuard, and any warnings."
				: "Geef een 2-3 zin samenvatting, een 'waarom dit ertoe doet' alinea, 3-4 concrete aanbevolen stappen die de gebruiker kan nemen in AccessGuard, en eventuele waarschuwingen.");

		return [$system, $user];
	}

	private function log(
		string $tenantId,
		?string $userId,
		string $subjectType,
		string $subjectId,
		string $promptHash,
		string $model,
		?array $responseJson,
		int $tokensIn,
		int $tokensOut,
		int $durationMs,
		string $status,
		?string $error,
		?Request $request,
	): void {
		DB::table('accessguard_ai_logs')->insert([
			'tenant_id' => $tenantId,
			'user_id' => $userId,
			'subject_type' => $subjectType,
			'subject_id' => $subjectId,
			'prompt_hash' => $promptHash,
			'model' => $model,
			'response_json' => $responseJson ? json_encode($responseJson, JSON_UNESCAPED_UNICODE) : null,
			'tokens_in' => $tokensIn,
			'tokens_out' => $tokensOut,
			'duration_ms' => $durationMs,
			'status' => $status,
			'error_message' => $error,
			'ip_hash' => $request ? hash('sha256', ($request->ip() ?? '') . '|' . config('app.key')) : null,
			'created_at' => now(),
		]);
	}
}
