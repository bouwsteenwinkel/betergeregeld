<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class PdfMergeSession
 * 
 * @property int $id
 * @property string $guest_token
 * @property Carbon $created_at
 * @property Carbon $last_seen_at
 * @property string $ip_hash
 * @property string $ua_hash
 * @property string|null $country_code
 * @property string|null $region_code
 * @property int|null $asn
 * @property string|null $as_org
 * @property string|null $first_referrer
 * 
 * @property Collection|PdfMergeEvent[] $pdf_merge_events
 * @property Collection|PdfMergeJob[] $pdf_merge_jobs
 *
 * @package App\Models
 */
class PdfMergeSession extends Model
{
	protected $table = 'pdf_merge_sessions';
	public $timestamps = false;

	protected $casts = [
		'last_seen_at' => 'datetime',
		'asn' => 'int'
	];

	protected $hidden = [
		'guest_token'
	];

	protected $fillable = [
		'guest_token',
		'last_seen_at',
		'ip_hash',
		'ua_hash',
		'country_code',
		'region_code',
		'asn',
		'as_org',
		'first_referrer'
	];

	public function pdf_merge_events()
	{
		return $this->hasMany(PdfMergeEvent::class, 'session_id');
	}

	public function pdf_merge_jobs()
	{
		return $this->hasMany(PdfMergeJob::class, 'session_id');
	}
}
