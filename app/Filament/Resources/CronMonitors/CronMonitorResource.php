<?php

namespace App\Filament\Resources\CronMonitors;

use App\Filament\Resources\CronMonitors\Pages\CreateCronMonitor;
use App\Filament\Resources\CronMonitors\Pages\EditCronMonitor;
use App\Filament\Resources\CronMonitors\Pages\ListCronMonitors;
use App\Filament\Resources\CronMonitors\RelationManagers\PingsRelationManager;
use App\Filament\Resources\CronMonitors\Schemas\CronMonitorForm;
use App\Filament\Resources\CronMonitors\Tables\CronMonitorsTable;
use App\Models\Monitor\CronMonitor;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class CronMonitorResource extends Resource
{
	protected static ?string $model = CronMonitor::class;

	protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClock;

	protected static string|\UnitEnum|null $navigationGroup = 'Monitoring';

	protected static ?string $navigationLabel = 'Cron-jobs';

	protected static ?int $navigationSort = 20;

	protected static ?string $modelLabel = 'cron-monitor';

	protected static ?string $pluralModelLabel = 'cron-monitors';

	protected static ?string $recordTitleAttribute = 'name';

	/**
	 * Beheer is super-admin only — net als de VPS-servers.
	 */
	public static function canAccess(): bool
	{
		return auth()->user()?->isSuperAdmin() ?? false;
	}

	public static function form(Schema $schema): Schema
	{
		return CronMonitorForm::configure($schema);
	}

	public static function table(Table $table): Table
	{
		return CronMonitorsTable::configure($table);
	}

	public static function getRelations(): array
	{
		return [
			PingsRelationManager::class,
		];
	}

	public static function getPages(): array
	{
		return [
			'index'  => ListCronMonitors::route('/'),
			'create' => CreateCronMonitor::route('/create'),
			'edit'   => EditCronMonitor::route('/{record}/edit'),
		];
	}
}
