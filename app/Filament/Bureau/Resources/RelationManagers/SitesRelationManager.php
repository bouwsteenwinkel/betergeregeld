<?php

namespace App\Filament\Bureau\Resources\RelationManagers;

use App\Filament\Actions\GscPropertyActions;
use App\Models\Seo\SeoProperty;
use App\Services\Rankdata\SiteProvisioner;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

/**
 * Websites/applicaties (sites) onder een klant. Een site = een seo_property;
 * aanmaken maakt ook meteen de uptime-check (monitor_checks.property_id) aan.
 */
class SitesRelationManager extends RelationManager
{
	protected static string $relationship = 'seoProperties';

	protected static ?string $title = 'Websites / applicaties';

	protected static string|\BackedEnum|null $icon = 'heroicon-m-globe-alt';

	public function form(Schema $schema): Schema
	{
		return $schema->columns(2)->components([
			TextInput::make('label')->label('Naam')->required()->maxLength(120)->placeholder('Hoofdsite / Webshop'),
			Select::make('type')->label('Type')->options(['website' => 'Website', 'app' => 'Applicatie'])
				->default('website')->required(),
			TextInput::make('domain')->label('Domein')->required()->maxLength(190)
				->placeholder('klant.nl')->helperText('Zonder https:// — bv. klant.nl')->columnSpanFull(),
			TextInput::make('notify_email')->label('Alert-e-mail (optioneel)')->email()->maxLength(190)
				->placeholder('leeg = naar bureau-contact')
				->helperText('Waarheen downtime-/SEO-alerts voor deze site gaan. Leeg = bureau-contact.')
				->columnSpanFull(),
			Toggle::make('is_active')->label('Actief')->default(true),
		]);
	}

	public function table(Table $table): Table
	{
		return $table
			->recordTitleAttribute('label')
			->columns([
				TextColumn::make('label')->label('Naam')->weight('bold')
					->description(fn (SeoProperty $r) => $r->domain()),
				TextColumn::make('type')->label('Type')->badge()
					->formatStateUsing(fn (?string $s) => $s === 'app' ? 'Applicatie' : 'Website')
					->color(fn (?string $s) => $s === 'app' ? 'info' : 'gray'),
				TextColumn::make('last_imported_date')->label('Laatst geïmporteerd')->date('d-m-Y')->placeholder('Nog niet'),
				IconColumn::make('is_active')->label('Actief')->boolean(),
			])
			->headerActions([
				CreateAction::make()->label('Site toevoegen')
					->mutateDataUsing(fn (array $data) => $this->withSiteUrl($data))
					->after(fn (Model $record) => app(SiteProvisioner::class)->provision($record)),
			])
			->recordActions([
				Action::make('dashboard')->label('Dashboard')->icon('heroicon-m-arrow-top-right-on-square')->color('primary')
					->url(fn (SeoProperty $r) => route('rankdata.client', ['locale' => 'nl', 'tenant' => $r->tenant_id]) . '?site=' . $r->id)
					->openUrlInNewTab(),
				GscPropertyActions::onboardingStatus(),
				GscPropertyActions::testAccess(),
				GscPropertyActions::importNow(),
				GscPropertyActions::securityAgent(),
				GscPropertyActions::onboardingPdf(),
				EditAction::make()
					->mutateRecordDataUsing(fn (array $data) => $data + ['domain' => preg_replace('/^sc-domain:/', '', $data['site_url'] ?? '')])
					->mutateDataUsing(fn (array $data) => $this->withSiteUrl($data))
					->after(fn (Model $record) => app(SiteProvisioner::class)->provision($record)),
				DeleteAction::make(),
			]);
	}

	/** Zet het ingevoerde domein om naar de site_url (sc-domain:) en haalt 'domain' eruit. */
	private function withSiteUrl(array $data): array
	{
		$domain = preg_replace('#^https?://#', '', rtrim(trim($data['domain'] ?? ''), '/'));
		$data['site_url'] = 'sc-domain:' . $domain;
		unset($data['domain']);

		return $data;
	}

}
