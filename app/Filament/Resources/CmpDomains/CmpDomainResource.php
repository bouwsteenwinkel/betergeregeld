<?php

namespace App\Filament\Resources\CmpDomains;

use App\Filament\Resources\CmpDomains\Pages\ManageCmpDomains;
use App\Models\CmpDomain;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class CmpDomainResource extends Resource
{
    protected static ?string $model = CmpDomain::class;
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedGlobeAlt;
    protected static string|\UnitEnum|null $navigationGroup = 'CMP (Cookies)';
    protected static ?int $navigationSort = 10;
    protected static ?string $recordTitleAttribute = 'domain';
    protected static ?string $modelLabel = 'Domein';
    protected static ?string $pluralModelLabel = 'Domeinen';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('tenant_key')
                ->label('Tenant')
                ->default('bouwsteenwinkel')
                ->required()
                ->maxLength(64)
                ->helperText('Short-name string, bv. "bouwsteenwinkel" of "betergeregeld".'),
            TextInput::make('domain')
                ->label('Domein')
                ->required()
                ->maxLength(255)
                ->placeholder('bouwsteenwinkel.nl')
                ->rule('regex:/^[a-z0-9.\-]+\.[a-z]{2,}$/i')
                ->helperText('Zonder protocol of trailing slash.'),
            Toggle::make('is_primary')
                ->label('Primaire host')
                ->helperText('Slechts één primaire host per tenant.'),
            Select::make('status')
                ->label('Status')
                ->options(['active' => 'Actief', 'disabled' => 'Uitgeschakeld'])
                ->default('active')
                ->required(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('tenant_key')->label('Tenant')->searchable()->sortable(),
                TextColumn::make('domain')->label('Domein')->searchable()->sortable()->copyable(),
                IconColumn::make('is_primary')->label('Primary')->boolean(),
                TextColumn::make('status')->label('Status')->badge()
                    ->color(fn (string $state): string => $state === 'active' ? 'success' : 'gray'),
                TextColumn::make('created_at')->label('Aangemaakt')->dateTime('d-m-Y')->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('tenant_key')
            ->filters([
                SelectFilter::make('tenant_key')->options(fn () => CmpDomain::query()->distinct()->pluck('tenant_key', 'tenant_key')->all()),
                SelectFilter::make('status')->options(['active' => 'Actief', 'disabled' => 'Uitgeschakeld']),
            ])
            ->recordActions([EditAction::make(), DeleteAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageCmpDomains::route('/')];
    }
}
