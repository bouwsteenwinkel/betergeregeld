<?php

namespace App\Filament\Resources\BlogTags;

use App\Filament\Resources\BlogTags\Pages\ManageBlogTags;
use App\Models\Blog\BlogTag;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class BlogTagResource extends Resource
{
	protected static ?string $model = BlogTag::class;

	protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTag;

	protected static string|\UnitEnum|null $navigationGroup = 'Blog';

	protected static ?string $navigationLabel = 'Tags';

	protected static ?int $navigationSort = 30;

	protected static ?string $modelLabel = 'tag';

	protected static ?string $pluralModelLabel = 'tags';

	protected static ?string $recordTitleAttribute = 'name';

	public static function form(Schema $schema): Schema
	{
		return $schema->components([
			TextInput::make('name')
				->label('Naam')
				->required()
				->maxLength(120)
				->live(onBlur: true)
				->afterStateUpdated(function (?string $state, callable $set, callable $get): void {
					if (! $get('slug') && $state) {
						$set('slug', Str::slug($state));
					}
				}),
			TextInput::make('slug')
				->label('Slug')
				->required()
				->maxLength(120)
				->unique(ignoreRecord: true)
				->helperText('Gebruikt in /blog/tag/{slug}.'),
		]);
	}

	public static function table(Table $table): Table
	{
		return $table
			->columns([
				TextColumn::make('name')->label('Naam')->searchable()->sortable(),
				TextColumn::make('slug')->label('Slug')->copyable()->toggleable(),
				TextColumn::make('posts_count')
					->label('Posts')
					->counts('posts')
					->badge()
					->sortable(),
			])
			->defaultSort('name')
			->recordActions([EditAction::make(), DeleteAction::make()])
			->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
	}

	public static function getPages(): array
	{
		return ['index' => ManageBlogTags::route('/')];
	}
}
