<?php

namespace App\Filament\Resources\SeoProperties\Pages;

use App\Filament\Resources\SeoProperties\SeoPropertyResource;
use App\Services\Seo\GoogleApiAuth;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\View\View;

class ListSeoProperties extends ListRecords
{
	protected static string $resource = SeoPropertyResource::class;

	protected function getHeaderActions(): array
	{
		return [
			Action::make('help')
				->label('Uitleg & klant-instructies')
				->icon('heroicon-m-question-mark-circle')
				->color('gray')
				->modalHeading('GSC-property instellen')
				->modalDescription('Hoe je een website aan Search Console koppelt — en wat de klant daarvoor moet doen.')
				->modalContent(fn (): View => view('filament.seo.gsc-help', [
					'serviceAccount' => app(GoogleApiAuth::class)->serviceAccountEmail(),
				]))
				->modalSubmitAction(false)
				->modalCancelActionLabel('Sluiten'),
			CreateAction::make(),
		];
	}
}
