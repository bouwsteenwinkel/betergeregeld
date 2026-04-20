<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class PdfMergeEvent
 * 
 * @property int $id
 * @property Carbon $created_at
 * @property int|null $session_id
 * @property int|null $job_id
 * @property string $event
 * @property string|null $stage
 * @property string|null $tool
 * @property bool $ok
 * @property int|null $bytes
 * @property int|null $files_count
 * @property int|null $ms
 * @property array|null $meta_json
 * 
 * @property PdfMergeJob|null $pdf_merge_job
 * @property PdfMergeSession|null $pdf_merge_session
 *
 * @package App\Models
 */
class PdfMergeEvent extends Model
{
	protected $table = 'pdf_merge_events';
	public $timestamps = false;

	protected $casts = [
		'session_id' => 'int',
		'job_id' => 'int',
		'ok' => 'bool',
		'bytes' => 'int',
		'files_count' => 'int',
		'ms' => 'int',
		'meta_json' => 'json'
	];

	protected $fillable = [
		'session_id',
		'job_id',
		'event',
		'stage',
		'tool',
		'ok',
		'bytes',
		'files_count',
		'ms',
		'meta_json'
	];

	public function pdf_merge_job()
	{
		return $this->belongsTo(PdfMergeJob::class, 'job_id');
	}

	public function pdf_merge_session()
	{
		return $this->belongsTo(PdfMergeSession::class, 'session_id');
	}
}
