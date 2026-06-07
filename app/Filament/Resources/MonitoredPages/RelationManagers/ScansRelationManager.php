<?php

namespace App\Filament\Resources\MonitoredPages\RelationManagers;

use App\Jobs\RunQualityScanJob;
use App\Models\Quality\QualityScan;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\View;

/**
 * Scan-historie van een pagina: per run de score + aantal fails/warns, met een
 * detailweergave (bevindingen gegroepeerd per ernst + verschil met de vorige scan).
 */
class ScansRelationManager extends RelationManager
{
	protected static string $relationship = 'scans';

	protected static ?string $title = 'Scans';

	protected static string|\BackedEnum|null $icon = 'heroicon-m-clipboard-document-check';

	public function table(Table $table): Table
	{
		return $table
			->modifyQueryUsing(fn ($query) => $query->with('findings'))
			->defaultSort('id', 'desc')
			->columns([
				TextColumn::make('created_at')->label('Datum')->dateTime('d-m-Y H:i'),
				TextColumn::make('status')->label('Status')->badge()
					->formatStateUsing(fn (string $s) => match ($s) {
						'completed' => 'Voltooid', 'failed' => 'Mislukt', 'running' => 'Bezig', default => 'Wachtend',
					})
					->color(fn (string $s) => match ($s) {
						'completed' => 'success', 'failed' => 'danger', 'running' => 'info', default => 'gray',
					}),
				TextColumn::make('score')->label('Score')->badge()->placeholder('—')
					->color(fn (QualityScan $r) => $r->scoreColor()),
				TextColumn::make('fails')->label('Fails')->state(fn (QualityScan $r) => $r->failCount())
					->color(fn (QualityScan $r) => $r->failCount() > 0 ? 'danger' : 'gray'),
				TextColumn::make('warns')->label('Warns')->state(fn (QualityScan $r) => $r->warnCount())
					->color(fn (QualityScan $r) => $r->warnCount() > 0 ? 'warning' : 'gray'),
				TextColumn::make('ai_tokens')->label('AI-tokens')
					->state(fn (QualityScan $r) => $r->ai_input_tokens !== null ? $r->ai_input_tokens . '/' . $r->ai_output_tokens : '—')
					->toggleable(),
			])
			->headerActions([
				Action::make('scan_now')->label('Nu scannen')->icon('heroicon-m-bolt')->color('primary')
					->action(function (): void {
						RunQualityScanJob::dispatch($this->getOwnerRecord());
						Notification::make()->title('Scan gestart')->success()->send();
					}),
			])
			->recordActions([
				Action::make('view')->label('Bekijk')->icon('heroicon-m-eye')
					->modalHeading(fn (QualityScan $record) => 'Scan ' . $record->created_at->format('d-m-Y H:i') . ' — score ' . ($record->score ?? '—'))
					->modalWidth('5xl')
					->modalSubmitAction(false)
					->modalCancelActionLabel('Sluiten')
					->modalContent(fn (QualityScan $record) => View::make('filament.quality-scan-detail', [
						'scan' => $record->load('findings'),
						'diff' => self::computeDiff($record),
					])),
			]);
	}

	/** Verschil met de vorige voltooide scan: nieuwe en opgeloste problemen (op check_id + element). */
	public static function computeDiff(QualityScan $scan): array
	{
		$prev = QualityScan::where('monitored_page_id', $scan->monitored_page_id)
			->where('status', 'completed')->where('id', '<', $scan->id)
			->latest('id')->with('findings')->first();

		$key = fn ($f) => $f->check_id . '|' . $f->element;
		$cur = $scan->findings->whereIn('status', ['warn', 'fail']);
		$old = $prev ? $prev->findings->whereIn('status', ['warn', 'fail']) : collect();
		$curKeys = $cur->map($key);
		$oldKeys = $old->map($key);

		return [
			'has_prev'  => $prev !== null,
			'new'       => $cur->reject(fn ($f) => $oldKeys->contains($key($f)))->values(),
			'resolved'  => $old->reject(fn ($f) => $curKeys->contains($key($f)))->values(),
		];
	}
}
