<?php

namespace App\Filament\Resources\AccessGuardFeedback\Pages;

use App\Filament\Resources\AccessGuardFeedback\AccessGuardFeedbackResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewAccessGuardFeedback extends ViewRecord
{
	protected static string $resource = AccessGuardFeedbackResource::class;

	protected function getHeaderActions(): array
	{
		return [EditAction::make()];
	}
}
