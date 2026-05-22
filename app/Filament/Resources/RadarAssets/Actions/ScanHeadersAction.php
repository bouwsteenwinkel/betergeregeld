<?php

namespace App\Filament\Resources\RadarAssets\Actions;

use App\Models\AccessGuard\Radar\Asset;
use App\Services\Radar\SecurityHeadersScanner;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Throwable;

/**
 * Synchrone security-headers scan op een RadarAsset (kind=website).
 * Uses App\Services\Radar\SecurityHeadersScanner — HTTP GET op asset.url
 * en analyseert response-headers tegen 6 verplichte + 3 verklikkers.
 *
 * Findings landen in radar_findings met check_type='security_headers'.
 * Bestaande findings die nu pass zijn worden auto-resolved.
 */
class ScanHeadersAction
{
	public static function make(?string $name = 'scan_headers'): Action
	{
		return Action::make($name)
			->label('Scan headers')
			->icon('heroicon-m-shield-check')
			->color('warning')
			->requiresConfirmation()
			->modalHeading('Security headers scannen?')
			->modalDescription(fn (Asset $r) =>
				"HTTP-probe op {$r->url} — checkt HSTS, CSP, X-Frame, COOP, "
				. 'Permissions-Policy en server-headers. Findings worden bijgewerkt '
				. 'in de Findings-tab; gefixte headers worden auto-resolved.'
			)
			->modalSubmitActionLabel('Scan nu')
			->visible(fn (Asset $r) => $r->status === 'active' && filled($r->url) && in_array($r->kind, ['website', 'webshop', 'cms', 'api'], true))
			->action(function (Asset $record): void {
				try {
					$result = (new SecurityHeadersScanner())->scan($record);
					$record->refresh();

					$msg = "Pass: {$result['pass']}, Fail: {$result['fail']}, Warn: {$result['warn']}";
					if ($result['findings_count'] > 0) {
						$msg .= " — {$result['findings_count']} actieve finding(s). Zie Findings-tab.";
					} else {
						$msg .= ' — alle headers in orde.';
					}

					$severity = $result['fail'] > 0 ? 'danger' : ($result['warn'] > 0 ? 'warning' : 'success');
					Notification::make()
						->title($result['fail'] > 0 ? 'Scan klaar — issues gevonden' : 'Scan klaar')
						->body($msg)
						->{$severity}()
						->send();
				} catch (Throwable $e) {
					Notification::make()
						->title('Scan mislukt')
						->body($e->getMessage())
						->danger()
						->send();
				}
			});
	}
}
