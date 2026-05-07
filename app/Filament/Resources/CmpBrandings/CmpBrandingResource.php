<?php

namespace App\Filament\Resources\CmpBrandings;

use App\Filament\Resources\CmpBrandings\Pages\ManageCmpBrandings;
use App\Models\CmpBranding;
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

class CmpBrandingResource extends Resource
{
    protected static ?string $model = CmpBranding::class;
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSwatch;
    protected static string|\UnitEnum|null $navigationGroup = 'CMP (Cookies)';
    protected static ?int $navigationSort = 50;
    protected static ?string $recordTitleAttribute = 'tenant_key';
    protected static ?string $modelLabel = 'Branding';
    protected static ?string $pluralModelLabel = 'Branding';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('tenant_key')
                ->label('Tenant')
                ->default('bouwsteenwinkel')
                ->required()
                ->maxLength(64)
                ->unique(ignoreRecord: true),
            Textarea::make('branding_json')
                ->label('Branding (JSON)')
                ->required()
                ->rows(20)
                ->helperText('JSON-config voor banner-styling. Velden: logo_url, banner_position (bottom|top|center-modal), banner_layout (inline|floating), colors {banner_bg, banner_text, btn_primary_bg, btn_primary_text, btn_secondary_bg, btn_secondary_text, link}, corner_radius_px, animate, show_close_x.')
                ->rule('json'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('tenant_key')->label('Tenant')->searchable()->sortable(),
                TextColumn::make('branding_json')->label('JSON-preview')->limit(80)->toggleable(),
                TextColumn::make('updated_at')->label('Bijgewerkt')->dateTime('d-m-Y H:i'),
            ])
            ->defaultSort('tenant_key')
            ->recordActions([EditAction::make(), DeleteAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageCmpBrandings::route('/')];
    }
}
