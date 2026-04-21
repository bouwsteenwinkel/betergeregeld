<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Models\Tenant;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Hash;

class UserForm
{
	public static function configure(Schema $schema): Schema
	{
		return $schema->components([
			Section::make('Account')
				->columns(2)
				->schema([
					TextInput::make('email')
						->label('E-mail')
						->email()
						->required()
						->maxLength(190)
						->unique(ignoreRecord: true),
					Select::make('tenant_id')
						->label('Tenant')
						->options(fn () => Tenant::orderBy('name')->pluck('name', 'id'))
						->searchable()
						->required(),
					Select::make('role')
						->options([
							'admin' => 'Admin',
							'user' => 'User',
						])
						->default('user')
						->required(),
					Select::make('status')
						->options([
							'pending' => 'Pending',
							'active' => 'Active',
							'suspended' => 'Suspended',
						])
						->default('pending')
						->required(),
					Toggle::make('is_active')
						->label('Actief')
						->default(true),
					DateTimePicker::make('email_verified_at')
						->label('E-mail geverifieerd op'),
				]),

			Section::make('Wachtwoord')
				->description('Leeg laten behoudt het huidige wachtwoord.')
				->schema([
					TextInput::make('password_hash')
						->label('Nieuw wachtwoord')
						->password()
						->revealable()
						->minLength(10)
						->dehydrateStateUsing(fn (?string $state): ?string => filled($state) ? Hash::make($state) : null)
						->dehydrated(fn (?string $state): bool => filled($state))
						->required(fn (string $operation): bool => $operation === 'create'),
				]),

			Section::make('Activiteit')
				->columns(2)
				->schema([
					DateTimePicker::make('last_login_at')->label('Laatste login')->disabled(),
				])
				->hiddenOn('create'),
		]);
	}
}
