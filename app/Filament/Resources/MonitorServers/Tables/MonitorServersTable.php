<?php

namespace App\Filament\Resources\MonitorServers\Tables;

use App\Filament\Resources\MonitorServers\Actions\InstallAgentAction;
use App\Filament\Resources\MonitorServers\Actions\RegenerateTokenAction;
use App\Models\Monitor\Server;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class MonitorServersTable
{
	public static function configure(Table $table): Table
	{
		return $table
			->modifyQueryUsing(fn ($query) => $query->withCount('tenants'))
			->defaultSort('name')
			->poll('30s')
			->columns([
				TextColumn::make('name')
					->searchable()
					->weight('bold')
					->description(fn (Server $r) => $r->hostname ?: $r->ip_address),

				TextColumn::make('status')
					->label('Status')
					->state(fn (Server $r) => $r->status())
					->badge()
					->formatStateUsing(fn (string $state) => match ($state) {
						'online' => 'Online',
						'stale' => 'Vertraagd',
						'offline' => 'Offline',
						default => 'Onbekend',
					})
					->color(fn (string $state) => match ($state) {
						'online' => 'success',
						'stale' => 'warning',
						'offline' => 'danger',
						default => 'gray',
					}),

				// CPU / RAM / Disk gebundeld in één compacte kolom — scheelt twee
				// kolombreedtes zodat de tabel binnen ~1900px past. Wordt rood zodra
				// één van de drie zijn waarschuwingsdrempel overschrijdt.
				TextColumn::make('resources')
					->label('CPU / RAM / Disk')
					->state(function (Server $r) {
						$fmt = fn (?float $v) => $v === null ? '—' : rtrim(rtrim(number_format($v, 1), '0'), '.') . '%';

						return $fmt($r->last_cpu_percent) . '  ·  ' . $fmt($r->last_mem_percent) . '  ·  ' . $fmt($r->last_disk_percent);
					})
					->color(fn (Server $r) => (
						($r->last_cpu_percent !== null && $r->last_cpu_percent >= (int) config('monitor.cpu_warn'))
						|| ($r->last_mem_percent !== null && $r->last_mem_percent >= (int) config('monitor.mem_warn'))
						|| ($r->last_disk_percent !== null && $r->last_disk_percent >= (int) config('monitor.disk_warn'))
					) ? 'danger' : null)
					->tooltip('Drempels: CPU ' . config('monitor.cpu_warn') . '% · RAM ' . config('monitor.mem_warn') . '% · Disk ' . config('monitor.disk_warn') . '%'),

				TextColumn::make('agent_last_seen_at')
					->label('Laatste contact')
					->since()
					->placeholder('Nooit')
					->sortable(),

				TextColumn::make('tenants_count')
					->label('Tenants')
					->badge()
					->color('gray'),

				IconColumn::make('is_active')
					->label('Actief')
					->boolean()
					->toggleable(),

				TextColumn::make('ingest_token')
					->label('Token')
					->copyable()
					->formatStateUsing(fn (string $state) => substr($state, 0, 8) . '…')
					->toggleable(isToggledHiddenByDefault: true),
			])
			->recordActions([
				InstallAgentAction::make(),
				RegenerateTokenAction::make(),
				EditAction::make(),
			])
			->toolbarActions([
				BulkActionGroup::make([
					DeleteBulkAction::make(),
				]),
			]);
	}
}
