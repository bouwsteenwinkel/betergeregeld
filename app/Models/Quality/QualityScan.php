<?php

namespace App\Models\Quality;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Eén scan-run van een pagina. Bevat status/timing, de input-hash (om
 * ongewijzigde pagina's over te slaan), AI-kosten (tokens) en de totaalscore.
 *
 * @property int $id
 * @property int $monitored_page_id
 * @property string $status  pending|running|completed|failed
 * @property int|null $http_status
 * @property int|null $fetch_duration_ms
 * @property string|null $raw_input_hash
 * @property string|null $ai_model
 * @property int|null $ai_input_tokens
 * @property int|null $ai_output_tokens
 * @property int|null $score
 * @property string|null $error_message
 */
class QualityScan extends Model
{
	protected $table = 'quality_scans';

	protected $fillable = [
		'monitored_page_id', 'status', 'started_at', 'completed_at',
		'http_status', 'fetch_duration_ms', 'raw_input_hash',
		'ai_model', 'ai_input_tokens', 'ai_output_tokens', 'score', 'error_message',
	];

	protected $casts = [
		'started_at'        => 'datetime',
		'completed_at'      => 'datetime',
		'http_status'       => 'integer',
		'fetch_duration_ms' => 'integer',
		'ai_input_tokens'   => 'integer',
		'ai_output_tokens'  => 'integer',
		'score'             => 'integer',
	];

	public function page(): BelongsTo
	{
		return $this->belongsTo(MonitoredPage::class, 'monitored_page_id');
	}

	public function findings(): HasMany
	{
		return $this->hasMany(QualityFinding::class);
	}

	public function failCount(): int
	{
		return $this->findings->where('status', 'fail')->count();
	}

	public function warnCount(): int
	{
		return $this->findings->where('status', 'warn')->count();
	}

	/** Badge-kleur voor de score (groen ≥85, oranje 60-84, rood <60). */
	public function scoreColor(): string
	{
		return match (true) {
			$this->score === null => 'gray',
			$this->score >= 85    => 'success',
			$this->score >= 60    => 'warning',
			default               => 'danger',
		};
	}
}
