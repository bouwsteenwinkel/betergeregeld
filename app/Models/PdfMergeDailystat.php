<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class PdfMergeDailystat
 * 
 * @property Carbon $stat_date
 * @property int $uploads_ok
 * @property int $uploads_bytes
 * @property int $uploads_files
 * @property int $merges_started
 * @property int $merges_success
 * @property int $merges_error
 * @property int $merges_blocked
 * @property int $merges_input_bytes
 * @property int $merges_output_bytes
 * @property int $downloads
 * @property int $downloads_bytes
 * @property int $jobs_watermark
 * @property int $jobs_page_numbers
 * @property int $inputs_encrypted
 * @property int $inputs_heic
 * @property int $inputs_tiff
 * @property int $inputs_webp
 * @property int $quality_small
 * @property int $quality_normal
 * @property int $quality_best
 * @property int $tool_imagick
 * @property int $tool_magick
 * @property int $tool_mixed
 * @property int $tool_none
 * @property int $large_events
 * @property float $sum_output_ratio
 * @property int $cnt_output_ratio
 * @property int $sum_total_ms
 * @property int $cnt_total_ms
 * @property Carbon $updated_at
 *
 * @package App\Models
 */
class PdfMergeDailystat extends Model
{
	protected $table = 'pdf_merge_dailystats';
	protected $primaryKey = 'stat_date';
	public $incrementing = false;
	public $timestamps = false;

	protected $casts = [
		'stat_date' => 'datetime',
		'uploads_ok' => 'int',
		'uploads_bytes' => 'int',
		'uploads_files' => 'int',
		'merges_started' => 'int',
		'merges_success' => 'int',
		'merges_error' => 'int',
		'merges_blocked' => 'int',
		'merges_input_bytes' => 'int',
		'merges_output_bytes' => 'int',
		'downloads' => 'int',
		'downloads_bytes' => 'int',
		'jobs_watermark' => 'int',
		'jobs_page_numbers' => 'int',
		'inputs_encrypted' => 'int',
		'inputs_heic' => 'int',
		'inputs_tiff' => 'int',
		'inputs_webp' => 'int',
		'quality_small' => 'int',
		'quality_normal' => 'int',
		'quality_best' => 'int',
		'tool_imagick' => 'int',
		'tool_magick' => 'int',
		'tool_mixed' => 'int',
		'tool_none' => 'int',
		'large_events' => 'int',
		'sum_output_ratio' => 'float',
		'cnt_output_ratio' => 'int',
		'sum_total_ms' => 'int',
		'cnt_total_ms' => 'int'
	];

	protected $fillable = [
		'uploads_ok',
		'uploads_bytes',
		'uploads_files',
		'merges_started',
		'merges_success',
		'merges_error',
		'merges_blocked',
		'merges_input_bytes',
		'merges_output_bytes',
		'downloads',
		'downloads_bytes',
		'jobs_watermark',
		'jobs_page_numbers',
		'inputs_encrypted',
		'inputs_heic',
		'inputs_tiff',
		'inputs_webp',
		'quality_small',
		'quality_normal',
		'quality_best',
		'tool_imagick',
		'tool_magick',
		'tool_mixed',
		'tool_none',
		'large_events',
		'sum_output_ratio',
		'cnt_output_ratio',
		'sum_total_ms',
		'cnt_total_ms'
	];
}
