<?php

namespace App\Models;

use Carbon\Carbon;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasName;
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
class User extends Authenticatable implements FilamentUser, HasName
{
	use HasUuids, Notifiable;

	public function canAccessPanel(Panel $panel): bool
	{
		// Alleen het platform-team (role 'admin') mag het volledige Filament-
		// platform-admin in. Bureaus (role 'agency') en klanten (role 'client')
		// hebben hun eigen afgeschermde front-end portal — nooit het platform-admin.
		return $panel->getId() === 'admin'
			&& $this->role === 'admin'
			&& $this->is_active;
	}

	/**
	 * Bureau-beheerder (Rankdata-type): mag het admin-panel in, maar ziet er
	 * alleen de eigen klanten — niet de platform-brede super-admin-resources.
	 */
	public function isAgencyAdmin(): bool
	{
		return $this->role === 'agency' && $this->agency_id !== null;
	}

	/** Klantgebruiker: geen admin-panel, wel het eigen front-end dashboard. */
	public function isClient(): bool
	{
		return $this->role === 'client';
	}

	public function getFilamentName(): string
	{
		return $this->email ?? 'user';
	}

	/**
	 * Super-admins (the platform team) get full VPS monitoring; every other
	 * tenant only sees the limited SLA/status view for its own server.
	 */
	public function isSuperAdmin(): bool
	{
		return $this->tenant_id === config('monitor.platform_tenant_id');
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
		'agency_id',
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

	public function agency()
	{
		return $this->belongsTo(Agency::class);
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
