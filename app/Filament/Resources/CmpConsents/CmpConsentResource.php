<?php

namespace App\Filament\Resources\CmpConsents;

use App\Filament\Resources\CmpConsents\Pages\ManageCmpConsents;
use App\Models\CmpConsent;
use BackedEnum;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class CmpConsentResource extends Resource
{
    protected static ?string $model = CmpConsent::class;
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentCheck;
    protected static string|\UnitEnum|null $navigationGroup = 'CMP (Cookies)';
    protected static ?int $navigationSort = 60;
    protected static ?string $recordTitleAttribute = 'consent_id';
    protected static ?string $modelLabel = 'Consent (audit)';
    protected static ?string $pluralModelLabel = 'Consents (audit)';

    /** Read-only — audit-trail mag niet worden bewerkt of verwijderd. */
    public static function canCreate(): bool { return false; }
    public static function canEdit($record): bool { return false; }
    public static function canDelete($record): bool { return false; }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('consent_id')->disabled(),
            TextInput::make('tenant_key')->disabled(),
            TextInput::make('status')->disabled(),
            TextInput::make('policy_version')->disabled(),
            Textarea::make('choices_json')->disabled()->rows(4),
            TextInput::make('first_seen_at')->disabled(),
            TextInput::make('last_updated_at')->disabled(),
            TextInput::make('expires_at')->disabled(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('consent_id')->label('Consent-ID')->searchable()->copyable()->limit(12),
                TextColumn::make('tenant_key')->label('Tenant')->searchable()->sortable()->toggleable(),
                TextColumn::make('domain.domain')->label('Domein')->toggleable(),
                TextColumn::make('status')->label('Status')->badge()
                    ->color(fn (string $state) => $state === 'accepted' ? 'success' : ($state === 'rejected' ? 'gray' : 'warning')),
                TextColumn::make('policy_version')->label('Policy v')->sortable(),
                TextColumn::make('choices_json')->label('Keuzes')->limit(40),
                TextColumn::make('last_updated_at')->label('Laatst bijgewerkt')->dateTime('d-m H:i')->sortable(),
                TextColumn::make('expires_at')->label('Vervalt')->dateTime('d-m-Y')->toggleable(),
            ])
            ->defaultSort('last_updated_at', 'desc')
            ->filters([
                SelectFilter::make('status')->options(['accepted' => 'Accepted', 'rejected' => 'Rejected', 'custom' => 'Custom']),
                SelectFilter::make('tenant_key')->options(fn () => CmpConsent::query()->distinct()->pluck('tenant_key', 'tenant_key')->all()),
            ])
            ->recordActions([ViewAction::make()])
            ->toolbarActions([]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageCmpConsents::route('/')];
    }
}
