<?php

namespace App\Filament\Resources\AccessGuardFeedback\Tables;

use App\Models\AccessGuard\Feedback;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class AccessGuardFeedbackTable
{
	public static function configure(Table $table): Table
	{
		return $table
			->defaultSort('created_at', 'desc')
			->columns([
				TextColumn::make('created_at')
					->label('Binnen')
					->dateTime('d-m-Y H:i')
					->sortable()
					->description(fn ($record) => $record->created_at?->diffForHumans()),
				TextColumn::make('category')
					->badge()
					->color(fn (string $state): string => match ($state) {
						'bug' => 'danger',
						'feature' => 'primary',
						'question' => 'warning',
						'praise' => 'success',
						default => 'gray',
					})
					->icon(fn (string $state): string => match ($state) {
						'bug' => 'heroicon-m-bug-ant',
						'feature' => 'heroicon-m-light-bulb',
						'question' => 'heroicon-m-question-mark-circle',
						'praise' => 'heroicon-m-heart',
						default => 'heroicon-m-pencil-square',
					}),
				TextColumn::make('message')
					->label('Bericht')
					->limit(90)
					->tooltip(fn ($record) => $record->message)
					->wrap(),
				TextColumn::make('status')
					->badge()
					->color(fn (string $state): string => match ($state) {
						'new' => 'danger',
						'triaged' => 'warning',
						'resolved' => 'success',
						default => 'gray',
					}),
				TextColumn::make('page_url')
					->label('Pagina')
					->formatStateUsing(fn ($state) => $state ? preg_replace('#^.*?(/tools/accessguard)#', '$1', (string) $state) : '—')
					->toggleable(),
				TextColumn::make('tenant_id')
					->label('Tenant')
					->formatStateUsing(fn ($state) => substr((string) $state, 0, 8))
					->toggleable(isToggledHiddenByDefault: true),
				TextColumn::make('user_id')
					->label('User')
					->formatStateUsing(fn ($state) => substr((string) $state, 0, 8))
					->toggleable(isToggledHiddenByDefault: true),
			])
			->filters([
				SelectFilter::make('status')
					->options([
						'new' => 'New',
						'triaged' => 'Triaged',
						'resolved' => 'Resolved',
						'wontfix' => 'Won\'t fix',
					])
					->default('new'),
				SelectFilter::make('category')
					->options([
						'bug' => 'Bug',
						'feature' => 'Feature',
						'question' => 'Vraag',
						'praise' => 'Compliment',
						'other' => 'Anders',
					]),
			])
			->recordActions([
				ViewAction::make(),
				EditAction::make(),
				Action::make('triage')
					->label('Triage')
					->icon('heroicon-m-eye')
					->visible(fn (Feedback $r) => $r->status === 'new')
					->action(fn (Feedback $r) => $r->update(['status' => 'triaged'])),
				Action::make('resolve')
					->label('Resolve')
					->icon('heroicon-m-check-circle')
					->color('success')
					->visible(fn (Feedback $r) => in_array($r->status, ['new', 'triaged'], true))
					->action(fn (Feedback $r) => $r->update(['status' => 'resolved', 'resolved_at' => now()])),
			])
			->toolbarActions([
				BulkActionGroup::make([
					DeleteBulkAction::make(),
				]),
			]);
	}
}
