<?php

namespace App\Filament\Resources\MonitoredPages;

use App\Filament\Resources\MonitoredPages\Pages\CreateMonitoredPage;
use App\Filament\Resources\MonitoredPages\Pages\EditMonitoredPage;
use App\Filament\Resources\MonitoredPages\Pages\ListMonitoredPages;
use App\Filament\Resources\MonitoredPages\RelationManagers\ScansRelationManager;
use App\Jobs\RunQualityScanJob;
use App\Models\Quality\MonitoredPage;
use App\Models\Seo\SeoProperty;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

/**
 * Pagina's van klantwebsites die periodiek op AI-/SEO-kwaliteit worden gescand.
 */
class MonitoredPageResource extends Resource
{
	protected static ?string $model = MonitoredPage::class;

	protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSparkles;

	protected static string|\UnitEnum|null $navigationGroup = 'Monitoring';

	protected static ?string $navigationLabel = 'Pagina-kwaliteit';

	protected static ?int $navigationSort = 30;

	protected static ?string $modelLabel = 'kwaliteitspagina';

	protected static ?string $pluralModelLabel = 'kwaliteitspagina\'s';

	protected static ?string $recordTitleAttribute = 'label';

	public static function canAccess(): bool
	{
		return auth()->user()?->isSuperAdmin() ?? false;
	}

	public static function form(Schema $schema): Schema
	{
		return $schema->components([
			Section::make('Pagina')->columns(2)->components([
				Select::make('site_id')->label('Site')->required()
					->relationship('site', 'label')
					->getOptionLabelFromRecordUsing(fn (SeoProperty $r) => $r->label . ' (' . $r->domain() . ')')
					->searchable()->preload(),
				TextInput::make('label')->label('Naam')->required()->maxLength(120)->placeholder('Homepage'),
				TextInput::make('url')->label('URL')->required()->url()->maxLength(500)->placeholder('https://klant.nl/')->columnSpanFull(),
				Select::make('scan_frequency')->label('Frequentie')
					->options(['daily' => 'Dagelijks', 'weekly' => 'Wekelijks', 'monthly' => 'Maandelijks'])
					->default('weekly')->required(),
				Toggle::make('is_active')->label('Actief')->default(true),
			]),
		]);
	}

	public static function table(Table $table): Table
	{
		return $table
			->modifyQueryUsing(fn ($query) => $query->with(['site', 'latestScan']))
			->defaultSort('label')
			->columns([
				TextColumn::make('label')->label('Pagina')->searchable()->weight('bold')
					->description(fn (MonitoredPage $r) => $r->url),
				TextColumn::make('site.label')->label('Site')->badge()->color('gray'),
				TextColumn::make('scan_frequency')->label('Frequentie')
					->formatStateUsing(fn (string $s) => match ($s) {
						'daily' => 'Dagelijks', 'monthly' => 'Maandelijks', default => 'Wekelijks',
					})->badge()->color('gray'),
				TextColumn::make('score')->label('Laatste score')
					->state(fn (MonitoredPage $r) => $r->latestScan?->score)
					->badge()->placeholder('—')
					->color(fn (MonitoredPage $r) => $r->latestScan?->scoreColor() ?? 'gray'),
				TextColumn::make('latest_scan_at')->label('Laatst gescand')
					->state(fn (MonitoredPage $r) => $r->latestScan?->completed_at)
					->since()->placeholder('Nooit'),
				IconColumn::make('is_active')->label('Actief')->boolean()->toggleable(),
			])
			->recordActions([
				Action::make('scan_now')->label('Nu scannen')->icon('heroicon-m-bolt')->color('primary')
					->action(function (MonitoredPage $record): void {
						RunQualityScanJob::dispatch($record);
						Notification::make()->title('Scan gestart')
							->body('De kwaliteitsscan voor "' . $record->label . '" is gedispatcht.')->success()->send();
					}),
				EditAction::make(),
			])
			->toolbarActions([
				BulkActionGroup::make([DeleteBulkAction::make()]),
			]);
	}

	public static function getRelations(): array
	{
		return [
			ScansRelationManager::class,
		];
	}

	public static function getPages(): array
	{
		return [
			'index'  => ListMonitoredPages::route('/'),
			'create' => CreateMonitoredPage::route('/create'),
			'edit'   => EditMonitoredPage::route('/{record}/edit'),
		];
	}
}
