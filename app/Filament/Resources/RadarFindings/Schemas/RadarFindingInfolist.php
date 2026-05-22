<?php

namespace App\Filament\Resources\RadarFindings\Schemas;

use App\Models\AccessGuard\Radar\Finding;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class RadarFindingInfolist
{
	public static function configure(Schema $schema): Schema
	{
		return $schema->components([
			Section::make('Bevinding')->columns(3)->components([
				TextEntry::make('check_type')
					->label('Type')
					->badge()
					->formatStateUsing(fn (?string $state) => $state ?: 'cve')
					->color(fn (?string $state) => match ($state) {
						'security_headers' => 'warning',
						'tls'              => 'info',
						'cookies', 'cmp'   => 'gray',
						default            => 'danger',
					}),
				TextEntry::make('severity')->badge()->color(fn (string $state) => match ($state) {
					'critical' => 'danger', 'high' => 'warning',
					'medium' => 'gray', 'low' => 'success', default => 'gray',
				}),
				TextEntry::make('risk_score')->label('Score'),
				TextEntry::make('title')
					->label('Titel')
					->placeholder('—')
					->weight('bold')
					->columnSpanFull(),
				TextEntry::make('detail')
					->label('Detail')
					->placeholder('—')
					->columnSpanFull(),
				TextEntry::make('finding_key')
					->label('Finding key')
					->placeholder('—')
					->copyable()
					->columnSpanFull(),
				TextEntry::make('status')->badge(),
			]),

			Section::make('CVE-informatie')
				->visible(fn (Finding $r) => ($r->check_type ?? 'cve') === 'cve')
				->columns(2)
				->components([
					TextEntry::make('vulnerability.cve_id')->label('CVE')->placeholder('—')->weight('bold'),
					TextEntry::make('vulnerability.cvss_score')->label('CVSS')->placeholder('—'),
					TextEntry::make('vulnerability.cisa_kev')
						->label('Actively exploited')
						->formatStateUsing(fn ($state) => $state ? 'Yes (CISA KEV)' : 'No')
						->badge()
						->color(fn ($state) => $state ? 'danger' : 'gray'),
					TextEntry::make('vulnerability.fixed_version')->label('Fixed in')->placeholder('—'),
					TextEntry::make('vulnerability.affected_range')->label('Affected range')->placeholder('—'),
					TextEntry::make('vulnerability.description')
						->label('Beschrijving')
						->placeholder('—')
						->columnSpanFull(),
					TextEntry::make('vulnerability.references_json')
						->label('References')
						->placeholder('—')
						->formatStateUsing(fn ($state) => is_array($state) ? implode("\n", $state) : (string) $state)
						->columnSpanFull(),
					TextEntry::make('softwareInstance.vendor')->label('Vendor')->placeholder('—'),
					TextEntry::make('softwareInstance.product')->label('Product')->placeholder('—'),
					TextEntry::make('softwareInstance.version')->label('Detected version')->placeholder('—'),
				]),

			Section::make('Risk factors')->components([
				TextEntry::make('risk_factors')
					->placeholder('—')
					->formatStateUsing(fn ($state) => is_array($state) ? implode("\n", $state) : (string) $state)
					->columnSpanFull(),
			]),

			Section::make('Asset')->columns(2)->components([
				TextEntry::make('asset.name')->label('Asset')->weight('bold'),
				TextEntry::make('asset.url')->label('URL')->placeholder('—')->url(fn (?string $state) => $state, true),
			]),

			Section::make('Workflow')->columns(3)->components([
				TextEntry::make('first_detected_at')->dateTime('d-m-Y H:i'),
				TextEntry::make('last_detected_at')->dateTime('d-m-Y H:i'),
				TextEntry::make('resolved_at')->dateTime('d-m-Y H:i')->placeholder('—'),
				TextEntry::make('assigned_to_user_id')->label('Assigned to')->placeholder('—'),
				TextEntry::make('due_date')->date('d-m-Y')->placeholder('—'),
				TextEntry::make('linked_ticket_id')->label('Ticket')->placeholder('—'),
			]),
		]);
	}
}
