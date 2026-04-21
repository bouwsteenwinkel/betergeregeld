<?php

namespace App\Models;

use Carbon\Carbon;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * @property string $id
 * @property string $tenant_id
 * @property string $email
 * @property string $password_hash
 * @property string $role
 * @property bool $is_active
 * @property Carbon|null $last_login_at
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property string $status
 * @property Carbon|null $email_verified_at
 *
 * @property Tenant $tenant
 * @property Collection|SupportCase[] $cases
 * @property Collection|ContactMessage[] $contact_messages
 * @property Collection|UserSession[] $user_sessions
 * @property UserTwofa|null $user_twofa
 * @property Collection|UserTwofaBackupCode[] $user_twofa_backup_codes
 * @property Collection|UserTwofaTrustedDevice[] $user_twofa_trusted_devices
 */
class User extends Authenticatable implements FilamentUser
{
	use HasUuids, Notifiable;

	public function canAccessPanel(Panel $panel): bool
	{
		return $panel->getId() === 'admin'
			&& $this->role === 'admin'
			&& $this->is_active;
	}

	protected $table = 'users';
	protected $keyType = 'string';
	public $incrementing = false;

	protected $casts = [
		'is_active' => 'bool',
		'last_login_at' => 'datetime',
		'email_verified_at' => 'datetime',
	];

	protected $fillable = [
		'tenant_id',
		'email',
		'password_hash',
		'role',
		'is_active',
		'last_login_at',
		'status',
		'email_verified_at',
	];

	protected $hidden = [
		'password_hash',
	];

	public function getAuthPasswordName(): string
	{
		return 'password_hash';
	}

	public function tenant()
	{
		return $this->belongsTo(Tenant::class);
	}

	public function cases()
	{
		return $this->hasMany(SupportCase::class, 'assigned_to');
	}

	public function contact_messages()
	{
		return $this->hasMany(ContactMessage::class);
	}

	public function user_sessions()
	{
		return $this->hasMany(UserSession::class);
	}

	public function user_twofa()
	{
		return $this->hasOne(UserTwofa::class);
	}

	public function user_twofa_backup_codes()
	{
		return $this->hasMany(UserTwofaBackupCode::class);
	}

	public function user_twofa_trusted_devices()
	{
		return $this->hasMany(UserTwofaTrustedDevice::class);
	}
}
