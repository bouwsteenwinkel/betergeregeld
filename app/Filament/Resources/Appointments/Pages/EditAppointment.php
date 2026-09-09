<?php

namespace App\Filament\Resources\Appointments\Pages;

use App\Filament\Resources\Appointments\AppointmentResource;
use Carbon\CarbonImmutable;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditAppointment extends EditRecord
{
    protected static string $resource = AppointmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    /**
     * De eindtijd laten meeschuiven met de begintijd.
     *
     * Het formulier vraagt alleen naar het begin; de duur staat vast in de
     * configuratie. Zonder dit blijft ends_at op het oude moment staan zodra
     * iemand hier verzet, en klopt de duur van de afspraak niet meer met wat
     * er in de agenda staat.
     *
     * LET OP: dit verzet alleen de administratie. Het agenda-item bij Google
     * wordt vanuit dit scherm niet bijgewerkt.
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (! empty($data['starts_at'])) {
            $data['ends_at'] = CarbonImmutable::parse($data['starts_at'])
                ->addMinutes((int) config('scheduling.meeting_minutes', 60));
        }

        return $data;
    }
}
