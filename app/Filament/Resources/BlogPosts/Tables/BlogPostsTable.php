<?php

namespace App\Filament\Resources\BlogPosts\Tables;

use App\Models\Blog\BlogCategory;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class BlogPostsTable
{
	public static function configure(Table $table): Table
	{
		return $table
			->columns([
				TextColumn::make('title')
					->label('Titel')
					->searchable()
					->sortable()
					->limit(60)
					->wrap(),

				TextColumn::make('category.name')
					->label('Categorie')
					->badge()
					->sortable()
					->toggleable(),

				TextColumn::make('status')
					->label('Status')
					->badge()
					->state(function ($record): string {
						if (! $record->published_at) return 'concept';
						return $record->published_at->isFuture() ? 'ingepland' : 'gepubliceerd';
					})
					->color(fn (string $state) => match ($state) {
						'gepubliceerd' => 'success',
						'ingepland'    => 'warning',
						default         => 'gray',
					}),

				IconColumn::make('is_pillar')
					->label('Pillar')
					->boolean()
					->toggleable(),

				IconColumn::make('featured')
					->label('Uitgelicht')
					->boolean()
					->toggleable(),

				TextColumn::make('tags_count')
					->label('Tags')
					->counts('tags')
					->sortable()
					->toggleable(),

				TextColumn::make('reading_time_min')
					->label('Leestijd')
					->suffix(' min')
					->sortable()
					->toggleable(isToggledHiddenByDefault: true),

				TextColumn::make('published_at')
					->label('Gepubliceerd')
					->dateTime('d-m-Y H:i')
					->sortable()
					->placeholder('—')
					->since()
					->tooltip(fn ($record) => $record->published_at?->format('d-m-Y H:i')),

				TextColumn::make('updated_at')
					->label('Bijgewerkt')
					->dateTime('d-m-Y')
					->sortable()
					->toggleable(isToggledHiddenByDefault: true),
			])
			->defaultSort('published_at', 'desc')
			->filters([
				SelectFilter::make('category_id')
					->label('Categorie')
					->options(BlogCategory::query()->orderBy('name')->pluck('name', 'id')),

				SelectFilter::make('status')
					->label('Status')
					->options([
						'concept'       => 'Concept',
						'ingepland'     => 'Ingepland',
						'gepubliceerd'  => 'Gepubliceerd',
					])
					->query(function (Builder $query, array $data): Builder {
						return match ($data['value'] ?? null) {
							'concept'      => $query->whereNull('published_at'),
							'ingepland'    => $query->whereNotNull('published_at')->where('published_at', '>', Carbon::now()),
							'gepubliceerd' => $query->whereNotNull('published_at')->where('published_at', '<=', Carbon::now()),
							default        => $query,
						};
					}),

				TernaryFilter::make('is_pillar')->label('Pillar (cornerstone)'),
				TernaryFilter::make('featured')->label('Uitgelicht'),
			])
			->recordActions([
				EditAction::make(),
				DeleteAction::make(),
			])
			->toolbarActions([
				BulkActionGroup::make([DeleteBulkAction::make()]),
			]);
	}
}
