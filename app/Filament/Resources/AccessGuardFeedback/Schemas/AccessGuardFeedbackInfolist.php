<?php

namespace App\Filament\Resources\AccessGuardFeedback\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class AccessGuardFeedbackInfolist
{
	public static function configure(Schema $schema): Schema
	{
		return $schema
			->components([
				TextEntry::make('created_at')->dateTime('d-m-Y H:i'),
				TextEntry::make('category')->badge(),
				TextEntry::make('status')->badge(),
				TextEntry::make('message')->columnSpanFull(),
				TextEntry::make('page_url')->placeholder('-')->label('Pagina'),
				TextEntry::make('user_agent')->placeholder('-'),
				TextEntry::make('tenant_id')->label('Tenant'),
				TextEntry::make('user_id')->label('User'),
				TextEntry::make('admin_note')->label('Interne notitie')->placeholder('-')->columnSpanFull(),
				TextEntry::make('resolved_at')->dateTime('d-m-Y H:i')->placeholder('-'),
			]);
	}
}
