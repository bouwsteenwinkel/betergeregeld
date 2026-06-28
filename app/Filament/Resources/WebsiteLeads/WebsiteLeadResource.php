<?php

namespace App\Filament\Resources\WebsiteLeads;

use App\Filament\Resources\WebsiteLeads\Pages\CreateWebsiteLead;
use App\Filament\Resources\WebsiteLeads\Pages\EditWebsiteLead;
use App\Filament\Resources\WebsiteLeads\Pages\ListWebsiteLeads;
use App\Filament\Resources\WebsiteLeads\Schemas\WebsiteLeadForm;
use App\Filament\Resources\WebsiteLeads\Tables\WebsiteLeadsTable;
use App\Models\WebsiteLead;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class WebsiteLeadResource extends Resource
{
    protected static ?string $model = WebsiteLead::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static string|\UnitEnum|null $navigationGroup = 'Sales';

    protected static ?int $navigationSort = 1;

    protected static ?string $modelLabel = 'Website-lead';

    protected static ?string $pluralModelLabel = 'Website-leads';

    protected static ?string $recordTitleAttribute = 'company';

    public static function getNavigationBadge(): ?string
    {
        // Aantal openstaande leads (nog geen klant/verloren) — opvolg-signaal.
        $n = WebsiteLead::query()->whereNotIn('status', ['won', 'lost'])->count();
        return $n > 0 ? (string) $n : null;
    }

    public static function form(Schema $schema): Schema
    {
        return WebsiteLeadForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return WebsiteLeadsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListWebsiteLeads::route('/'),
            'create' => CreateWebsiteLead::route('/create'),
            'edit' => EditWebsiteLead::route('/{record}/edit'),
        ];
    }
}
