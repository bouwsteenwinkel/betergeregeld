<?php

namespace App\Filament\Resources\BlogCategories;

use App\Filament\Resources\BlogCategories\Pages\ManageBlogCategories;
use App\Models\Blog\BlogCategory;
use App\Models\Blog\BlogPost;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class BlogCategoryResource extends Resource
{
	protected static ?string $model = BlogCategory::class;

	protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedFolder;

	protected static string|\UnitEnum|null $navigationGroup = 'Blog';

	protected static ?string $navigationLabel = 'Categorieën';

	protected static ?int $navigationSort = 20;

	protected static ?string $modelLabel = 'categorie';

	protected static ?string $pluralModelLabel = 'categorieën';

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
				->label('Slug (URL-deel)')
				->required()
				->maxLength(120)
				->unique(ignoreRecord: true)
				->helperText('Gebruikt in /blog/categorie/{slug}. Niet wijzigen na publicatie.'),
			TextInput::make('pillar_title')
				->label('Pillar-paginatitel')
				->maxLength(255)
				->helperText('Optioneel: extra titel voor de cluster-overzichtspagina (bv. "Alles over Access Reviews").'),
			Textarea::make('intro')
				->label('Intro / lead-paragraaf')
				->rows(4)
				->helperText('Korte intro bovenaan de categoriepagina.'),
			TextInput::make('sort_order')
				->label('Sorteervolgorde')
				->numeric()
				->default(0)
				->helperText('Lager = eerder in navigatie.'),
		]);
	}

	public static function table(Table $table): Table
	{
		return $table
			->columns([
				TextColumn::make('sort_order')->label('#')->sortable()->width(60),
				TextColumn::make('name')->label('Naam')->searchable()->sortable(),
				TextColumn::make('slug')->label('Slug')->copyable()->toggleable(),
				TextColumn::make('posts_count')
					->label('Posts')
					->counts('posts')
					->badge()
					->sortable(),
				TextColumn::make('pillar_post')
					->label('Pillar')
					->state(fn (BlogCategory $r) => BlogPost::query()
						->where('category_id', $r->id)
						->where('is_pillar', true)
						->value('title'))
					->placeholder('— geen —')
					->limit(40),
			])
			->defaultSort('sort_order')
			->recordActions([EditAction::make(), DeleteAction::make()])
			->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
	}

	public static function getPages(): array
	{
		return ['index' => ManageBlogCategories::route('/')];
	}
}
