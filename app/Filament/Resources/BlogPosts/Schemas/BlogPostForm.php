<?php

namespace App\Filament\Resources\BlogPosts\Schemas;

use App\Models\Blog\BlogCategory;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class BlogPostForm
{
	public static function configure(Schema $schema): Schema
	{
		return $schema->components([
			Section::make('Inhoud')
				->columnSpanFull()
				->schema([
					TextInput::make('title')
						->label('Titel')
						->required()
						->maxLength(255)
						->live(onBlur: true)
						->afterStateUpdated(function (?string $state, callable $set, callable $get): void {
							if (! $get('slug') && $state) {
								$set('slug', Str::slug($state));
							}
						}),
					TextInput::make('slug')
						->label('Slug (URL-deel)')
						->required()
						->maxLength(255)
						->unique(ignoreRecord: true)
						->helperText('Wordt gebruikt in de URL — /blog/{slug}. Verander dit niet meer ná publicatie zonder een redirect.'),
					Textarea::make('excerpt')
						->label('Samenvatting (excerpt)')
						->rows(3)
						->maxLength(500)
						->helperText('Korte teaser onder de titel + in lijstweergaven. Houd onder 300 tekens voor goede meta-description.'),
					MarkdownEditor::make('body')
						->label('Inhoud (Markdown)')
						->required()
						->columnSpanFull()
						->disableToolbarButtons(['attachFiles'])
						->helperText('Markdown. H1 al uit titel; gebruik ## voor sub-headers.'),
				]),

			Section::make('Categorie & tags')
				->columnSpan(1)
				->schema([
					Select::make('category_id')
						->label('Categorie')
						->required()
						->options(BlogCategory::query()->orderBy('name')->pluck('name', 'id'))
						->searchable()
						->preload(),
					Select::make('tags')
						->label('Tags')
						->multiple()
						->relationship('tags', 'name')
						->preload()
						->searchable()
						->createOptionForm([
							TextInput::make('name')->label('Naam')->required()->maxLength(120),
							TextInput::make('slug')
								->label('Slug')
								->required()
								->maxLength(120)
								->unique(\App\Models\Blog\BlogTag::class, 'slug'),
						]),
				]),

			Section::make('Publicatie')
				->columnSpan(1)
				->schema([
					DateTimePicker::make('published_at')
						->label('Publicatiedatum')
						->seconds(false)
						->helperText('Leeg = concept. Datum in de toekomst = ingepland.')
						->native(false),
					Grid::make(2)->schema([
						Toggle::make('is_pillar')
							->label('Pillar (cornerstone)')
							->helperText('Hoofdartikel van een cluster.'),
						Toggle::make('featured')
							->label('Uitgelicht'),
					]),
					TextInput::make('reading_time_min')
						->label('Leestijd (minuten)')
						->numeric()
						->minValue(1)
						->maxValue(120)
						->helperText('Leeg laten = auto-bereken op basis van bodylengte (≈ 200 wpm).'),
				]),

			Section::make('SEO (optioneel)')
				->columnSpanFull()
				->collapsible()
				->collapsed()
				->schema([
					TextInput::make('meta_title')
						->label('Meta title')
						->maxLength(255)
						->helperText('Leeg = gebruik post-titel. Houd onder 60 tekens voor Google.'),
				]),
		])->columns(2);
	}
}
