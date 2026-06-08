<?php

namespace App\Filament\Resources\SeoProperties\Schemas;

use App\Models\Tenant;
use App\Services\Seo\GoogleApiAuth;
use Filament\Forms\Components\Placeholder;
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
				Section::make('Uitleg & klant-instructies')
					->icon('heroicon-m-question-mark-circle')
					->description('Hoe je een website aan Search Console koppelt — en wat de klant daarvoor moet doen.')
					->collapsible()
					->collapsed()
					->columnSpanFull()
					->components([
						Placeholder::make('gsc_help')
							->hiddenLabel()
							->content(fn () => view('filament.seo.gsc-help', [
								'serviceAccount' => app(GoogleApiAuth::class)->serviceAccountEmail(),
							])),
					]),
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
							->helperText('Exact zoals in Search Console (domein-property = sc-domain:..., anders de volledige https-URL). Na opslaan: gebruik "Toegang testen" om te verifiëren.'),
						Placeholder::make('service_account_hint')
							->label('Service-account (door klant toe te voegen in Search Console)')
							->columnSpanFull()
							->content(fn (): string => app(GoogleApiAuth::class)->serviceAccountEmail()
								?? 'Service-account JSON ontbreekt op deze omgeving (storage/app/google-api.json).'),
						Toggle::make('is_active')
							->label('Actief')
							->default(true)
							->helperText('Alleen actieve properties worden dagelijks geïmporteerd.'),
					]),
			]);
	}
}
