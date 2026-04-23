<?php

namespace App\Filament\Resources\AccessGuardFeedback\Pages;

use App\Filament\Resources\AccessGuardFeedback\AccessGuardFeedbackResource;
use Filament\Resources\Pages\ListRecords;

class ListAccessGuardFeedback extends ListRecords
{
	protected static string $resource = AccessGuardFeedbackResource::class;

	protected function getHeaderActions(): array
	{
		return [];
	}
}
