<?php

namespace App\Filament\Bureau\Resources\Pages;

use App\Filament\Bureau\Resources\ClientResource;
use App\Models\Seo\SeoProperty;
use App\Models\Tenant;
use App\Models\User;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CreateClient extends CreateRecord
{
	protected static string $resource = ClientResource::class;

	/**
	 * Maakt in één keer de klant (tenant), z'n statistieken-property en een
	 * klant-login aan, gekoppeld aan het bureau van de ingelogde beheerder.
	 */
	protected function handleRecordCreation(array $data): Model
	{
		$agencyId = auth()->user()->agency_id;
		$password = filled($data['password'] ?? null) ? $data['password'] : Str::random(10);
		$domain = preg_replace('#^https?://#', '', rtrim(trim($data['domain']), '/'));

		$tenant = Tenant::create([
			'agency_id' => $agencyId,
			'name' => $data['name'],
			'plan' => 'pro',
			'is_active' => $data['is_active'] ?? true,
		]);

		SeoProperty::create([
			'tenant_id' => $tenant->id,
			'site_url' => 'sc-domain:' . $domain,
			'label' => $data['name'],
			'is_active' => true,
			'freshness_alert_state' => 'ok',
		]);

		User::create([
			'tenant_id' => $tenant->id,
			'email' => $data['email'],
			'password_hash' => Hash::make($password),
			'role' => 'client',
			'is_active' => true,
			'status' => 'active',
			'email_verified_at' => now(),
		]);

		Notification::make()
			->title('Klant aangemaakt')
			->body("Login: {$data['email']}\nWachtwoord: {$password}\n(noteer dit — het wachtwoord wordt niet opnieuw getoond)")
			->success()
			->persistent()
			->send();

		return $tenant;
	}
}
