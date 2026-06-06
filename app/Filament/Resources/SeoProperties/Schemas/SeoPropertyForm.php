<?php

namespace App\Filament\Resources\SeoProperties\Schemas;

use App\Models\Tenant;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SeoPropertyForm
{
	public static function configure(Schema $schema): Schema
	{
		return $schema
			->components([
				Section::make('Property')
					->columns(2)
					->components([
						TextInput::make('label')
							->required()
							->maxLength(120)
							->placeholder('Klantnaam — website'),
						Select::make('tenant_id')
							->label('Tenant (leeg = platform)')
							->options(fn () => Tenant::query()->orderBy('name')->pluck('name', 'id'))
							->searchable()
							->preload()
							->placeholder('Geen tenant'),
						TextInput::make('site_url')
							->label('GSC site-URL')
							->required()
							->maxLength(255)
							->columnSpanFull()
							->placeholder('sc-domain:klant.nl  of  https://klant.nl/')
							->helperText('Exact zoals in Search Console. Het service-account-mailadres moet als gebruiker zijn toegevoegd aan deze property.'),
						Toggle::make('is_active')
							->label('Actief')
							->default(true)
							->helperText('Alleen actieve properties worden dagelijks geïmporteerd.'),
					]),
			]);
	}
}
