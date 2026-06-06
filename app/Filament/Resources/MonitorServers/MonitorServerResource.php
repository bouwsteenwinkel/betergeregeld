<?php

namespace App\Filament\Resources\MonitorServers;

use App\Filament\Resources\MonitorServers\Pages\CreateMonitorServer;
use App\Filament\Resources\MonitorServers\Pages\EditMonitorServer;
use App\Filament\Resources\MonitorServers\Pages\ListMonitorServers;
use App\Filament\Resources\MonitorServers\Schemas\MonitorServerForm;
use App\Filament\Resources\MonitorServers\Tables\MonitorServersTable;
use App\Models\Monitor\Server;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class MonitorServerResource extends Resource
{
	protected static ?string $model = Server::class;

	protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedServerStack;

	protected static string|\UnitEnum|null $navigationGroup = 'Monitoring';

	protected static ?string $navigationLabel = 'VPS Servers';

	protected static ?int $navigationSort = 10;

	protected static ?string $modelLabel = 'VPS server';

	protected static ?string $pluralModelLabel = 'VPS servers';

	protected static ?string $recordTitleAttribute = 'name';

	/**
	 * Full server management is super-admin only. Tenants reach their limited
	 * SLA view through the ServerStatus page instead.
	 */
	public static function canAccess(): bool
	{
		return auth()->user()?->isSuperAdmin() ?? false;
	}

	public static function form(Schema $schema): Schema
	{
		return MonitorServerForm::configure($schema);
	}

	public static function table(Table $table): Table
	{
		return MonitorServersTable::configure($table);
	}

	public static function getPages(): array
	{
		return [
			'index'  => ListMonitorServers::route('/'),
			'create' => CreateMonitorServer::route('/create'),
			'edit'   => EditMonitorServer::route('/{record}/edit'),
		];
	}
}
