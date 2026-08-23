<?php

namespace App\Filament\Resources\MonitorServers\RelationManagers;

use App\Models\Monitor\DiskUsage;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

/**
 * Schijfgebruik per map, met de groei erbij.
 *
 * Toont alleen de LAATSTE meting — een tijdreeks van tweehonderd mappen is
 * onleesbaar. De kolom 'Groei' haalt er de waarde van een week geleden bij, en
 * dat is de kolom waar het om begonnen was: welke map vreet die 1,45 GB per dag.
 */
class DiskUsageRelationManager extends RelationManager
{
	protected static string $relationship = 'diskUsage';

	protected static ?string $title = 'Schijfgebruik';

	protected static string|\BackedEnum|null $icon = 'heroicon-m-circle-stack';

	public function form(Schema $schema): Schema
	{
		return $schema->components([]);
	}

	public function table(Table $table): Table
	{
		return $table
			->modifyQueryUsing(function (Builder $query) {
				$serverId = $this->getOwnerRecord()->getKey();

				$laatste = DiskUsage::query()
					->where('server_id', $serverId)
					->max('measured_at');

				return $query
					->when($laatste, fn (Builder $q) => $q->where('measured_at', $laatste))
					->addSelect([
						// LET OP de alias. De subquery draait op dezelfde tabel, en
						// zonder 'as eerder' verwijst whereColumn('pad', ...) naar de
						// subquery zelf in plaats van naar de buitenste rij. Dan is
						// de subquery ongebonden, pakt hij voor iedere map dezelfde
						// willekeurige oude rij, en klopt elke groei behalve toeval.
						// Gemeten voor de fix: een map zonder historie kreeg een
						// groei van +1 GB toegedicht.
						'bytes_eerder' => DiskUsage::query()
							->from('monitor_disk_usage as eerder')
							->select('eerder.bytes')
							->whereColumn('eerder.pad', 'monitor_disk_usage.pad')
							->where('eerder.server_id', $serverId)
							->where('eerder.measured_at', '<=', now()->subDays(7))
							->orderByDesc('eerder.measured_at')
							->limit(1),
					]);
			})
			->defaultSort('bytes', 'desc')
			->columns([
				TextColumn::make('soort')
					->label('Soort')
					->badge()
					->color(fn (string $state): string => match ($state) {
						'logs'    => 'warning',
						'vhost'   => 'info',
						'bestand' => 'gray',
						default   => 'primary',
					}),

				TextColumn::make('pad')
					->label('Pad')
					->searchable()
					->wrap(),

				TextColumn::make('bytes')
					->label('Omvang')
					->alignEnd()
					->sortable()
					->formatStateUsing(fn (int $state): string => DiskUsage::formatteer($state)),

				TextColumn::make('bytes_eerder')
					->label('Groei (7 dagen)')
					->alignEnd()
					->state(function (DiskUsage $record): string {
						$eerder = $record->bytes_eerder;

						// Geen meting van een week terug = niets te vergelijken.
						// Bewust een streepje en geen 0: dat laatste zou "niet
						// gegroeid" suggereren terwijl we het simpelweg niet weten.
						if ($eerder === null) {
							return '—';
						}

						$verschil = $record->bytes - (int) $eerder;
						$teken = $verschil > 0 ? '+' : ($verschil < 0 ? '-' : '');

						return $teken . DiskUsage::formatteer(abs($verschil));
					})
					->color(function (DiskUsage $record): ?string {
						if ($record->bytes_eerder === null) {
							return 'gray';
						}

						$verschil = $record->bytes - (int) $record->bytes_eerder;

						// Een gigabyte erbij in een week is de moeite van het
						// opmerken waard; daaronder is het ruis.
						return $verschil >= 1073741824 ? 'danger' : null;
					}),

				TextColumn::make('measured_at')
					->label('Gemeten')
					->dateTime('d-m-Y H:i')
					->alignEnd()
					->toggleable(isToggledHiddenByDefault: true),
			])
			->emptyStateHeading('Nog geen schijfmeting')
			->emptyStateDescription('Draai schijfgebruik.ps1 met -Push op de server.');
	}
}
