<?php

namespace App\Filament\Resources\Agencies;

use App\Filament\Resources\Agencies\Pages\CreateAgency;
use App\Filament\Resources\Agencies\Pages\EditAgency;
use App\Filament\Resources\Agencies\Pages\ListAgencies;
use App\Models\Agency;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use App\Models\Plan;
use App\Services\Rankdata\RankdataBilling;
use App\Services\Rankdata\RankdataInvoiceService;
use Filament\Notifications\Notification;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

/**
 * Bureaus (white-label resellers zoals Rankdata) — alleen super-admin beheert ze.
 */
class AgencyResource extends Resource
{
	protected static ?string $model = Agency::class;

	protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingOffice2;

	protected static string|\UnitEnum|null $navigationGroup = 'Rankdata';

	protected static ?string $navigationLabel = 'Bureaus';

	protected static ?int $navigationSort = 10;

	protected static ?string $modelLabel = 'bureau';

	protected static ?string $pluralModelLabel = 'bureaus';

	protected static ?string $recordTitleAttribute = 'name';

	public static function canAccess(): bool
	{
		return auth()->user()?->isSuperAdmin() ?? false;
	}

	public static function form(Schema $schema): Schema
	{
		return $schema->components([
			Section::make('Bureau')->columns(2)->components([
				TextInput::make('name')->label('Naam')->required()->maxLength(120)->placeholder('Rankdata'),
				TextInput::make('slug')->label('Slug')->maxLength(64)
					->placeholder('rankdata')->helperText('Leeg = automatisch uit de naam.'),
				TextInput::make('contact_email')->label('Contact-e-mail')->email()->maxLength(190),
				ColorPicker::make('primary_color')->label('Merkkleur')
					->helperText('Voor het white-label klant-dashboard + bureau-panel.'),
				TextInput::make('subdomain')->label('Subdomein')->maxLength(80)
					->placeholder('rankdata')->helperText('Voor latere eigen-(sub)domein-routing.'),
				FileUpload::make('logo_path')->label('Logo')->image()
					->disk('public')->directory('agency-logos')->maxSize(1024)
					->helperText('Getoond in het bureau-panel.')->columnSpanFull(),
				Toggle::make('is_active')->label('Actief')->default(true),
			]),
			Section::make('Facturatie (Rankdata)')->columns(2)->components([
				Select::make('rankdata_plan_id')->label('Rankdata-plan')
					->options(fn () => Plan::query()->where('product', 'rankdata')->where('is_active', true)->orderBy('sort_order')->pluck('name', 'id'))
					->placeholder('Standaardplan')
					->helperText('Leeg = het standaard actieve Rankdata-plan.'),
				TextInput::make('discount_percent')->label('Korting % (override)')->numeric()->suffix('%')
					->placeholder('plan-standaard')
					->helperText('Leeg = de standaardkorting van het plan.'),
			]),
		]);
	}

	public static function table(Table $table): Table
	{
		return $table
			->modifyQueryUsing(fn ($query) => $query->withCount('tenants'))
			->defaultSort('name')
			->columns([
				TextColumn::make('name')->label('Bureau')->searchable()->weight('bold')
					->description(fn (Agency $r) => $r->contact_email),
				TextColumn::make('slug')->label('Slug')->color('gray'),
				TextColumn::make('primary_color')->label('Kleur')->badge()
					->color(fn () => 'gray')->formatStateUsing(fn (?string $s) => $s ?: '—'),
				TextColumn::make('tenants_count')->label('Klanten')->badge()->color('primary'),
				TextColumn::make('monthly_total')->label('Maandtotaal')->badge()->color('success')
					->state(fn (Agency $r) => '€ ' . number_format(app(RankdataBilling::class)->costForAgency($r)['total'], 2, ',', '.')),
				IconColumn::make('is_active')->label('Actief')->boolean(),
			])
			->recordActions([
				Action::make('invoice')
					->label('Maandfactuur')
					->icon('heroicon-m-document-text')
					->color('gray')
					->requiresConfirmation()
					->modalHeading('Concept-maandfactuur aanmaken')
					->modalDescription(fn (Agency $r) => 'Maakt een concept-factuur voor ' . $r->name . ' aan ('
						. $r->tenants()->where('is_active', true)->count() . ' actieve klanten). Je kijkt hem daarna na in Boekhouding voordat je verstuurt.')
					->action(function (Agency $record): void {
						if ($record->tenants()->where('is_active', true)->count() === 0) {
							Notification::make()->title('Geen actieve klanten')->body('Dit bureau heeft geen actieve klanten om te factureren.')->warning()->send();

							return;
						}

						try {
							$invoice = app(RankdataInvoiceService::class)->generateForAgency($record);
						} catch (\RuntimeException $e) {
							Notification::make()->title('Geen factuur aangemaakt')->body($e->getMessage())->warning()->send();

							return;
						}

						Notification::make()
							->title('Concept-factuur ' . $invoice->invoice_number)
							->body('Totaal € ' . number_format($invoice->total, 2, ',', '.') . ' incl. btw. Klaargezet als concept in Boekhouding.')
							->success()
							->persistent()
							->actions([
								\Filament\Notifications\Actions\Action::make('view')
									->label('Open factuur')
									->url(route('tools.bookkeeping.invoices.show', ['locale' => 'nl', 'id' => $invoice->id]), shouldOpenInNewTab: true),
							])
							->send();
					}),
				EditAction::make(),
			])
			->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
	}

	public static function getPages(): array
	{
		return [
			'index'  => ListAgencies::route('/'),
			'create' => CreateAgency::route('/create'),
			'edit'   => EditAgency::route('/{record}/edit'),
		];
	}
}
