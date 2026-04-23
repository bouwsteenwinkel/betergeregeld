<?php

namespace App\Filament\Resources\AccessGuardFeedback;

use App\Filament\Resources\AccessGuardFeedback\Pages\EditAccessGuardFeedback;
use App\Filament\Resources\AccessGuardFeedback\Pages\ListAccessGuardFeedback;
use App\Filament\Resources\AccessGuardFeedback\Pages\ViewAccessGuardFeedback;
use App\Filament\Resources\AccessGuardFeedback\Schemas\AccessGuardFeedbackForm;
use App\Filament\Resources\AccessGuardFeedback\Schemas\AccessGuardFeedbackInfolist;
use App\Filament\Resources\AccessGuardFeedback\Tables\AccessGuardFeedbackTable;
use App\Models\AccessGuard\Feedback;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class AccessGuardFeedbackResource extends Resource
{
	protected static ?string $model = Feedback::class;

	protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChatBubbleLeftRight;

	protected static string|\UnitEnum|null $navigationGroup = 'AccessGuard';

	protected static ?int $navigationSort = 10;

	protected static ?string $modelLabel = 'Feedback';

	protected static ?string $pluralModelLabel = 'Feedback';

	protected static ?string $recordTitleAttribute = 'message';

	public static function form(Schema $schema): Schema
	{
		return AccessGuardFeedbackForm::configure($schema);
	}

	public static function infolist(Schema $schema): Schema
	{
		return AccessGuardFeedbackInfolist::configure($schema);
	}

	public static function table(Table $table): Table
	{
		return AccessGuardFeedbackTable::configure($table);
	}

	public static function getPages(): array
	{
		return [
			'index' => ListAccessGuardFeedback::route('/'),
			'view' => ViewAccessGuardFeedback::route('/{record}'),
			'edit' => EditAccessGuardFeedback::route('/{record}/edit'),
		];
	}

	public static function getNavigationBadge(): ?string
	{
		$count = static::$model::query()->where('status', 'new')->count();
		return $count > 0 ? (string) $count : null;
	}

	public static function getNavigationBadgeColor(): ?string
	{
		return 'danger';
	}
}
