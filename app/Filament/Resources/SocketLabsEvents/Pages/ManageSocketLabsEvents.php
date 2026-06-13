<?php

namespace App\Filament\Resources\SocketLabsEvents\Pages;

use App\Filament\Resources\SocketLabsEvents\SocketLabsEventResource;
use App\Filament\Widgets\SocketLabsStatusWidget;
use Filament\Resources\Pages\ManageRecords;

class ManageSocketLabsEvents extends ManageRecords
{
	protected static string $resource = SocketLabsEventResource::class;

	/** Geen create — read-only audit van binnenkomende events. */
	protected function getHeaderActions(): array
	{
		return [];
	}

	protected function getHeaderWidgets(): array
	{
		return [SocketLabsStatusWidget::class];
	}
}
