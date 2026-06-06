<?php

namespace App\Models\Monitor;

use App\Models\Tenant;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

/**
 * Eén bewaakte cron-job (dead-man's-switch). De job pingt ping_token bij
 * succes (heartbeat), en optioneel bij start/fout. Loopt de heartbeat te ver
 * achter op de verwachte cadans (expected_period_minutes + grace_minutes), of
 * meldt de job een fout, dan is currentCondition() 'late' resp. 'failed' en
 * mailt cron:check-monitors bij de overgang.
 *
 * @property string $id
 * @property string|null $tenant_id   null = platform / gedeeld over tenants
 * @property string|null $website     null = gedeeld binnen de tenant
 * @property string $name
 * @property string $ping_token
 * @property int $expected_period_minutes
 * @property int $grace_minutes
 * @property bool $is_active
 * @property bool $alerts_enabled
 * @property string|null $notify_email
 * @property Carbon|null $last_ping_at
 * @property Carbon|null $last_started_at
 * @property string|null $last_status
 * @property int|null $last_duration_ms
 * @property int|null $last_exit_code
 * @property string|null $last_message
 * @property string $alert_state
 * @property Carbon|null $alerted_at
 */
class CronMonitor extends Model
{
	use HasUuids;

	protected $table = 'cron_monitors';

	protected $keyType = 'string';

	public $incrementing = false;

	protected $fillable = [
		'tenant_id',
		'website',
		'name',
		'ping_token',
		'source_key',
		'description',
		'expected_period_minutes',
		'grace_minutes',
		'is_active',
		'alerts_enabled',
		'is_source',
		'notify_email',
	];

	protected $casts = [
		'is_active'               => 'bool',
		'alerts_enabled'          => 'bool',
		'is_source'               => 'bool',
		'expected_period_minutes' => 'int',
		'grace_minutes'           => 'int',
		'last_duration_ms'        => 'int',
		'last_exit_code'          => 'int',
		'last_ping_at'            => 'datetime',
		'last_started_at'         => 'datetime',
		'alerted_at'              => 'datetime',
	];

	protected static function booted(): void
	{
		static::creating(function (CronMonitor $monitor): void {
			if (empty($monitor->ping_token)) {
				$monitor->ping_token = Str::random(40);
			}
		});
	}

	public function tenant(): BelongsTo
	{
		return $this->belongsTo(Tenant::class);
	}

	public function pings(): HasMany
	{
		return $this->hasMany(CronPing::class);
	}

	/**
	 * Verwerkt één binnenkomende ping: legt de ruwe regel vast (cron_pings) én
	 * werkt de gedenormaliseerde laatste-stand bij. Gedeeld door de HTTP-endpoint
	 * (externe jobs) en de in-process scheduler-hooks (eigen Laravel-jobs).
	 *
	 * Een success met een exit-code != 0 telt automatisch als fout. Bij success
	 * verschuift de heartbeat (last_ping_at) en wordt een foutstand gewist; bij
	 * fail blijft last_ping_at staan zodat dezelfde run niet óók 'late' wordt.
	 *
	 * @return string Het uiteindelijk toegepaste signaal (start|success|fail).
	 */
	public function applyPing(
		string $signal = 'success',
		?int $exitCode = null,
		?int $durationMs = null,
		?string $message = null,
		?string $sourceIp = null,
	): string {
		$signal = strtolower($signal);
		if (! in_array($signal, ['success', 'start', 'fail'], true)) {
			$signal = 'success';
		}
		if ($signal === 'success' && $exitCode !== null && $exitCode !== 0) {
			$signal = 'fail';
		}

		$now = now();
		$msg = $message !== null ? mb_substr($message, 0, 500) : null;

		$this->pings()->create([
			'status'      => $signal,
			'exit_code'   => $exitCode,
			'duration_ms' => $durationMs,
			'message'     => $msg,
			'source_ip'   => $sourceIp,
			'received_at' => $now,
		]);

		$update = ['last_status' => $signal];

		if ($signal === 'start') {
			$update['last_started_at'] = $now;
			if ($msg !== null) {
				$update['last_message'] = $msg;
			}
		} elseif ($signal === 'success') {
			$update['last_ping_at']     = $now;
			$update['last_duration_ms'] = $durationMs;
			$update['last_exit_code']   = null;
			$update['last_message']     = $msg;
		} else { // fail
			$update['last_exit_code'] = $exitCode;
			if ($durationMs !== null) {
				$update['last_duration_ms'] = $durationMs;
			}
			if ($msg !== null) {
				$update['last_message'] = $msg;
			}
		}

		$this->forceFill($update)->save();

		return $signal;
	}

	/**
	 * Vindt (of maakt idempotent aan) de onder-monitor voor een job onder deze
	 * bron. De job-naam wordt geslugd en geprefixt met de bron-sleutel, zodat
	 * jobs van verschillende bronnen niet botsen. period/grace komen van de ping
	 * (de bron kent het interval het best) met de bron-defaults als terugval.
	 */
	public function childForJob(string $job, ?int $period = null, ?int $grace = null): CronMonitor
	{
		$slug = trim(preg_replace('/[^a-z0-9]+/', '-', strtolower($job)), '-');
		$prefix = $this->source_key ?: substr($this->ping_token, 0, 8);

		return static::firstOrCreate(
			['source_key' => $prefix . ':' . $slug],
			[
				'name'                    => mb_substr($job, 0, 120),
				'website'                 => $this->website,
				'tenant_id'               => $this->tenant_id,
				'expected_period_minutes' => ($period && $period > 0) ? $period : $this->expected_period_minutes,
				'grace_minutes'           => ($grace !== null && $grace >= 0) ? $grace : $this->grace_minutes,
				'description'             => 'Auto-geprovisioned via bron "' . $this->name . '".',
				'alerts_enabled'          => true,
				'is_active'               => true,
			]
		);
	}

	/**
	 * Uiterste tijdstip waarop de volgende succes-ping binnen had moeten zijn.
	 * Gerekend vanaf de laatste heartbeat (of, als die er nog niet is, vanaf het
	 * aanmaken — zo krijgt een net-toegevoegde monitor eerst zijn eigen periode
	 * de tijd voordat hij als 'late' geldt).
	 */
	public function dueAt(): Carbon
	{
		$base = $this->last_ping_at ?? $this->created_at ?? now();

		return $base->copy()->addMinutes($this->expected_period_minutes + $this->grace_minutes);
	}

	/**
	 * ok | late | failed.
	 *   failed — de job draaide maar meldde een fout (last_status = fail).
	 *   late   — geen succesvolle ping binnen periode + grace (job draaide niet).
	 *   ok     — recent succesvol gepingd.
	 */
	public function currentCondition(): string
	{
		// Een bron is een token-houder/container; zijn eigen heartbeat zegt niets
		// (alleen de onder-monitors worden gepingd). Nooit alarmeren.
		if ($this->is_source) {
			return 'ok';
		}

		if ($this->last_status === 'fail') {
			return 'failed';
		}

		if (now()->greaterThan($this->dueAt())) {
			return 'late';
		}

		return 'ok';
	}
}
