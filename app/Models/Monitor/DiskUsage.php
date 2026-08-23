<?php

namespace App\Models\Monitor;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Eén gemeten map (of los bestand) op een schijf, op één moment. Append-only,
 * net als Metric: alleen created_at, geen updated_at.
 *
 * @property int $id
 * @property string $server_id
 * @property \Carbon\Carbon $measured_at
 * @property string $soort
 * @property string $pad
 * @property int $bytes
 */
class DiskUsage extends Model
{
	protected $table = 'monitor_disk_usage';

	public $timestamps = false;

	protected $fillable = [
		'server_id',
		'measured_at',
		'soort',
		'pad',
		'bytes',
		'created_at',
	];

	protected $casts = [
		'measured_at' => 'datetime',
		'created_at' => 'datetime',
		'bytes' => 'integer',
	];

	public function server(): BelongsTo
	{
		return $this->belongsTo(Server::class, 'server_id');
	}

	/** Leesbare omvang, bijvoorbeeld "12,4 GB". */
	public function getOmvangAttribute(): string
	{
		return self::formatteer($this->bytes);
	}

	public static function formatteer(int $bytes): string
	{
		if ($bytes >= 1073741824) {
			return number_format($bytes / 1073741824, 1, ',', '.') . ' GB';
		}

		if ($bytes >= 1048576) {
			return number_format($bytes / 1048576, 0, ',', '.') . ' MB';
		}

		return number_format($bytes / 1024, 0, ',', '.') . ' KB';
	}
}
