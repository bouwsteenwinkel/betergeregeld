<?php

namespace App\Filament\Resources\AccessGuardFeedback\Pages;

use App\Filament\Resources\AccessGuardFeedback\AccessGuardFeedbackResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditAccessGuardFeedback extends EditRecord
{
	protected static string $resource = AccessGuardFeedbackResource::class;

	protected function getHeaderActions(): array
	{
		return [
			ViewAction::make(),
			DeleteAction::make(),
		];
	}
}
