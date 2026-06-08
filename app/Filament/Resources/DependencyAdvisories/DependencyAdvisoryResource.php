<?php

namespace App\Filament\Resources\DependencyAdvisories;

use App\Filament\Resources\DependencyAdvisories\Pages\ListDependencyAdvisories;
use App\Models\Security\DependencyAdvisory;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

/**
 * Read-only overzicht van composer/npm-advisories (security:audit-deps) op de
 * eigen code-projecten. Alleen super-admin.
 */
class DependencyAdvisoryResource extends Resource
{
	protected static ?string $model = DependencyAdvisory::class;

	protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBugAnt;

	protected static string|\UnitEnum|null $navigationGroup = 'Monitoring';

	protected static ?string $navigationLabel = 'Dependency-advisories';

	protected static ?int $navigationSort = 40;

	protected static ?string $modelLabel = 'advisory';

	protected static ?string $pluralModelLabel = 'dependency-advisories';

	public static function canAccess(): bool
	{
		return auth()->user()?->isSuperAdmin() ?? false;
	}

	public static function getNavigationBadge(): ?string
	{
		$n = DependencyAdvisory::query()->count();

		return $n > 0 ? (string) $n : null;
	}

	public static function getNavigationBadgeColor(): ?string
	{
		return DependencyAdvisory::query()->count() > 0 ? 'danger' : null;
	}

	public static function table(Table $table): Table
	{
		return $table
			->defaultSort('severity')
			->columns([
				TextColumn::make('project')->label('Project')->badge()->color('gray')->searchable(),
				TextColumn::make('ecosystem')->label('Type')->badge()
					->color(fn (string $s) => $s === 'composer' ? 'warning' : 'info'),
				TextColumn::make('package')->label('Package')->weight('bold')->searchable(),
				TextColumn::make('severity')->label('Ernst')->badge()
					->color(fn (?string $s) => match (strtolower((string) $s)) {
						'critical', 'high' => 'danger',
						'medium', 'moderate' => 'warning',
						'low' => 'gray',
						default => 'gray',
					})
					->formatStateUsing(fn (?string $s) => $s ? ucfirst($s) : '—'),
				TextColumn::make('title')->label('Kwetsbaarheid')->wrap()->limit(90)->tooltip(fn (DependencyAdvisory $r) => $r->title),
				TextColumn::make('cve')->label('CVE')->placeholder('—')->toggleable(),
				TextColumn::make('fixed_in')->label('Opgelost in')->placeholder('—')->toggleable(),
				TextColumn::make('link')->label('Bron')->url(fn (DependencyAdvisory $r) => $r->link, true)
					->formatStateUsing(fn (?string $s) => $s ? 'openen' : '—')->color('primary'),
				TextColumn::make('imported_at')->label('Gescand')->since()->toggleable(),
			])
			->filters([])
			->recordActions([])
			->toolbarActions([]);
	}

	public static function getPages(): array
	{
		return [
			'index' => ListDependencyAdvisories::route('/'),
		];
	}
}
