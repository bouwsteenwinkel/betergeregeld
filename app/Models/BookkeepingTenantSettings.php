<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BookkeepingTenantSettings extends Model
{
	protected $table = 'bookkeeping_tenant_settings';
	protected $primaryKey = 'tenant_id';
	public $incrementing = false;
	protected $keyType = 'string';

	protected $casts = [
		'default_payment_terms_days' => 'int',
		'auto_reminders_enabled' => 'bool',
	];

	protected $fillable = [
		'tenant_id',
		'company_name', 'address', 'postal_code', 'city', 'country',
		'kvk_number', 'vat_number', 'iban',
		'email', 'phone',
		'default_payment_terms_days', 'invoice_footer', 'auto_reminders_enabled',
		'logo_path',
	];

	public function hasLogo(): bool
	{
		return $this->logo_path && is_file($this->logo_path);
	}
}
