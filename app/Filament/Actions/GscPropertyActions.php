<?php

namespace App\Filament\Actions;

use App\Models\Seo\SeoProperty;
use App\Services\Seo\GscAccessChecker;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;

/**
 * Gedeelde GSC-onboarding-acties, gebruikt door zowel de super-admin
 * (SeoPropertyResource) als het bureau-panel (SitesRelationManager), zodat de
 * twee identiek blijven.
 */
class GscPropertyActions
{
	/** Controleert via sites.list of ons service-account de property mag lezen. */
	public static function testAccess(): Action
	{
		return Action::make('testGscAccess')
			->label('Toegang testen')
			->icon('heroicon-m-key')
			->color('gray')
			->action(function (SeoProperty $record): void {
				$result = app(GscAccessChecker::class)->check($record);
				$sa = $result['service_account'] ?? 'service-account JSON ontbreekt';

				if ($result['status'] === 'granted') {
					Notification::make()->title('GSC-toegang in orde')->body($result['message'])->success()->send();

					return;
				}

				if ($result['status'] === 'pending') {
					$available = ! empty($result['sites'])
						? "\n\nWel toegankelijk voor ons account:\n• " . implode("\n• ", array_keys($result['sites']))
						: "\n\nOns account heeft op dit moment toegang tot geen enkele property.";

					Notification::make()
						->title('Nog geen toegang')
						->body($result['message'] . "\n\nVoeg dit service-account toe in Search Console:\n{$sa}" . $available)
						->warning()
						->persistent()
						->send();

					return;
				}

				Notification::make()->title('Fout bij toegangscontrole')->body($result['message'])->danger()->persistent()->send();
			});
	}

	/** Toont de installatie-instructie + per-site token voor de security-companion-plugin. */
	public static function securityAgent(): Action
	{
		return Action::make('securityAgent')
			->label('Beveiligingsagent')
			->icon('heroicon-m-shield-check')
			->color('gray')
			->modalHeading('Beveiligingsagent (CMS/plugin-monitoring)')
			->modalSubmitAction(false)
			->modalCancelActionLabel('Sluiten')
			->modalContent(function (SeoProperty $record): View {
				if (! $record->security_ingest_token) {
					$record->forceFill(['security_ingest_token' => Str::random(48)])->save();
				}

				return view('filament.security.agent-help', [
					'token'    => $record->security_ingest_token,
					'endpoint' => url('/security/ingest'),
					'download' => route('security.agent-plugin'),
				]);
			});
	}

	/** Draait een 30-daagse GSC-backfill voor deze property. */
	public static function importNow(): Action
	{
		return Action::make('importNow')
			->label('Nu importeren')
			->icon('heroicon-m-arrow-down-tray')
			->color('gray')
			->requiresConfirmation()
			->modalHeading('GSC-data nu importeren')
			->modalDescription('Haalt de laatste 30 dagen Search Console-data op voor deze property. Dit kan even duren.')
			->action(function (SeoProperty $record): void {
				$code = Artisan::call('seo:import-gsc', ['--property' => $record->id, '--days' => 30]);
				$tail = implode("\n", array_slice(explode("\n", trim(Artisan::output())), -6));

				$notification = Notification::make()
					->title($code === 0 ? 'Import voltooid' : 'Import met fouten')
					->body($tail !== '' ? $tail : 'Geen output.')
					->persistent();
				$code === 0 ? $notification->success() : $notification->danger();
				$notification->send();
			});
	}
}
