<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class PdfMergeJob
 * 
 * @property int $id
 * @property int $session_id
 * @property Carbon $created_at
 * @property Carbon|null $started_at
 * @property Carbon|null $finished_at
 * @property string $status
 * @property int $input_files_count
 * @property int $input_total_bytes
 * @property int $input_pdf_count
 * @property int $input_image_count
 * @property int|null $input_pages_total
 * @property int $largest_input_file_bytes
 * @property bool $has_heic
 * @property bool $has_tiff
 * @property bool $has_webp
 * @property bool $has_encrypted_pdf
 * @property string $preset
 * @property int $dpi
 * @property bool $autorotate
 * @property bool $page_numbers
 * @property string|null $page_numbers_pos
 * @property string|null $page_number_fmt
 * @property bool $watermark
 * @property int $watermark_len
 * @property int|null $watermark_angle
 * @property float|null $watermark_opacity
 * @property bool $optimize_pdf
 * @property string|null $gs_pdfsettings
 * @property string $convert_tool_used
 * @property int $output_bytes
 * @property float|null $output_ratio
 * @property int|null $output_pages
 * @property int|null $upload_ms
 * @property int|null $convert_ms
 * @property int|null $merge_ms
 * @property int|null $total_ms
 * @property bool $downloaded
 * @property Carbon|null $downloaded_at
 * @property bool $is_large_event
 * @property string|null $large_reason
 * @property string|null $error_code
 * @property string|null $error_stage
 * @property string|null $error_tool
 * @property string|null $error_message
 * @property array|null $meta_json
 * 
 * @property PdfMergeSession $pdf_merge_session
 * @property Collection|PdfMergeEvent[] $pdf_merge_events
 * @property Collection|PdfMergeJobFile[] $pdf_merge_job_files
 *
 * @package App\Models
 */
class PdfMergeJob extends Model
{
	protected $table = 'pdf_merge_jobs';
	public $timestamps = false;

	protected $casts = [
		'session_id' => 'int',
		'started_at' => 'datetime',
		'finished_at' => 'datetime',
		'input_files_count' => 'int',
		'input_total_bytes' => 'int',
		'input_pdf_count' => 'int',
		'input_image_count' => 'int',
		'input_pages_total' => 'int',
		'largest_input_file_bytes' => 'int',
		'has_heic' => 'bool',
		'has_tiff' => 'bool',
		'has_webp' => 'bool',
		'has_encrypted_pdf' => 'bool',
		'dpi' => 'int',
		'autorotate' => 'bool',
		'page_numbers' => 'bool',
		'watermark' => 'bool',
		'watermark_len' => 'int',
		'watermark_angle' => 'int',
		'watermark_opacity' => 'float',
		'optimize_pdf' => 'bool',
		'output_bytes' => 'int',
		'output_ratio' => 'float',
		'output_pages' => 'int',
		'upload_ms' => 'int',
		'convert_ms' => 'int',
		'merge_ms' => 'int',
		'total_ms' => 'int',
		'downloaded' => 'bool',
		'downloaded_at' => 'datetime',
		'is_large_event' => 'bool',
		'meta_json' => 'json'
	];

	protected $fillable = [
		'session_id',
		'started_at',
		'finished_at',
		'status',
		'input_files_count',
		'input_total_bytes',
		'input_pdf_count',
		'input_image_count',
		'input_pages_total',
		'largest_input_file_bytes',
		'has_heic',
		'has_tiff',
		'has_webp',
		'has_encrypted_pdf',
		'preset',
		'dpi',
		'autorotate',
		'page_numbers',
		'page_numbers_pos',
		'page_number_fmt',
		'watermark',
		'watermark_len',
		'watermark_angle',
		'watermark_opacity',
		'optimize_pdf',
		'gs_pdfsettings',
		'convert_tool_used',
		'output_bytes',
		'output_ratio',
		'output_pages',
		'upload_ms',
		'convert_ms',
		'merge_ms',
		'total_ms',
		'downloaded',
		'downloaded_at',
		'is_large_event',
		'large_reason',
		'error_code',
		'error_stage',
		'error_tool',
		'error_message',
		'meta_json'
	];

	public function pdf_merge_session()
	{
		return $this->belongsTo(PdfMergeSession::class, 'session_id');
	}

	public function pdf_merge_events()
	{
		return $this->hasMany(PdfMergeEvent::class, 'job_id');
	}

	public function pdf_merge_job_files()
	{
		return $this->hasMany(PdfMergeJobFile::class, 'job_id');
	}
}
