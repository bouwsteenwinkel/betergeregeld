<?php

namespace App\Filament\Resources\RadarAssets\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class RadarAssetInfolist
{
	public static function configure(Schema $schema): Schema
	{
		return $schema
			->components([
				Section::make('Asset')
					->columns(3)
					->components([
						TextEntry::make('name')->weight('bold'),
						TextEntry::make('kind')->badge(),
						TextEntry::make('environment')->badge()->color(fn (string $state) => match ($state) {
							'production' => 'danger',
							'staging' => 'warning',
							'test' => 'gray',
							default => 'gray',
						}),
						TextEntry::make('url')
							->label('URL')
							->placeholder('—')
							->url(fn (?string $state) => $state, true),
						TextEntry::make('hostname')->placeholder('—'),
						TextEntry::make('tenant_id')->label('Tenant')
							->formatStateUsing(fn (?string $state) => $state ? substr($state, 0, 8) . '…' : '—'),
					]),

				Section::make('Detected stack')
					->columns(3)
					->components([
						TextEntry::make('vendor')->placeholder('—'),
						TextEntry::make('product')->placeholder('—'),
						TextEntry::make('current_version')->label('Version')->placeholder('—'),
					]),

				Section::make('Exposure & risk posture')
					->columns(4)
					->components([
						TextEntry::make('is_public')->label('Public')->badge()
							->color(fn (string $state) => match ($state) { 'yes' => 'danger', 'no' => 'success', default => 'gray' }),
						TextEntry::make('auth_required')->badge()
							->color(fn (string $state) => match ($state) { 'yes' => 'success', 'no' => 'danger', default => 'gray' }),
						TextEntry::make('criticality')->badge()
							->color(fn (string $state) => match ($state) {
								'critical' => 'danger', 'high' => 'warning',
								'medium' => 'gray', 'low' => 'success', default => 'gray',
							}),
						TextEntry::make('status')->badge()
							->color(fn (string $state) => match ($state) {
								'active' => 'success', 'ignored' => 'gray',
								'deleted' => 'danger', default => 'warning',
							}),
					]),

				Section::make('Timeline')
					->columns(3)
					->components([
						TextEntry::make('discovered_at')->dateTime('d-m-Y H:i')->placeholder('—'),
						TextEntry::make('last_seen_at')->dateTime('d-m-Y H:i')->placeholder('—'),
						TextEntry::make('last_scanned_at')->dateTime('d-m-Y H:i')->placeholder('Never scanned'),
					]),

				Section::make('Notes')
					->visible(fn ($record) => filled($record?->notes))
					->components([
						TextEntry::make('notes')->columnSpanFull()->markdown(),
					]),
			]);
	}
}
