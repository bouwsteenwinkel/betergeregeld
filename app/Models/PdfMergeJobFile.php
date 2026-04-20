<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class PdfMergeJobFile
 * 
 * @property int $id
 * @property int $job_id
 * @property Carbon $created_at
 * @property string $stored_name
 * @property string|null $original_name
 * @property string|null $ext
 * @property string $kind
 * @property int $bytes
 * @property bool $converted_to_pdf
 * @property string $convert_tool
 * 
 * @property PdfMergeJob $pdf_merge_job
 *
 * @package App\Models
 */
class PdfMergeJobFile extends Model
{
	protected $table = 'pdf_merge_job_files';
	public $timestamps = false;

	protected $casts = [
		'job_id' => 'int',
		'bytes' => 'int',
		'converted_to_pdf' => 'bool'
	];

	protected $fillable = [
		'job_id',
		'stored_name',
		'original_name',
		'ext',
		'kind',
		'bytes',
		'converted_to_pdf',
		'convert_tool'
	];

	public function pdf_merge_job()
	{
		return $this->belongsTo(PdfMergeJob::class, 'job_id');
	}
}
