<?php

namespace App\Filament\Actions;

use App\Models\Tenant;
use App\Services\Rankdata\RankdataPdfReport;
use App\Services\Rankdata\RankdataReport;
use Filament\Actions\Action;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Download-actie die een FPDF-klantrapport (SEO/PageSpeed/uptime) genereert.
 * Gedeeld door super-admin- en bureau-ClientResource.
 */
class RankdataReportAction
{
	public static function make(): Action
	{
		return Action::make('pdfReport')
			->label('Rapport (PDF)')
			->icon('heroicon-m-document-arrow-down')
			->color('gray')
			->action(function (Tenant $record): StreamedResponse {
				$data = app(RankdataReport::class)->forTenant($record, 30);
				$pdf = (new RankdataPdfReport())->build($data);
				$name = 'rankdata-' . Str::slug($record->name) . '-' . now()->format('Y-m-d') . '.pdf';

				return response()->streamDownload(function () use ($pdf) {
					echo $pdf;
				}, $name, ['Content-Type' => 'application/pdf']);
			});
	}
}
