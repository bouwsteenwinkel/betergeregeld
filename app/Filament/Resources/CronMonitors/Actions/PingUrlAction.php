<?php

namespace App\Filament\Resources\CronMonitors\Actions;

use App\Models\Monitor\CronMonitor;
use Filament\Actions\Action;
use Illuminate\Support\HtmlString;

/**
 * Read-only helper: toont de ping-URL's (success/start/fail) plus kant-en-klare
 * curl- en PowerShell-voorbeelden om aan het einde van een cron-job te plakken.
 */
class PingUrlAction
{
	public static function make(?string $name = 'ping_url'): Action
	{
		return Action::make($name)
			->label('Ping-URL')
			->icon('heroicon-m-link')
			->color('gray')
			->modalHeading('Cron-job laten pingen')
			->modalSubmitAction(false)
			->modalCancelActionLabel('Sluiten')
			->modalContent(fn (CronMonitor $record) => new HtmlString(self::snippet($record)));
	}

	private static function snippet(CronMonitor $monitor): string
	{
		$success = route('cron.ping', $monitor->ping_token);
		$start   = route('cron.ping', [$monitor->ping_token, 'start']);
		$fail    = route('cron.ping', [$monitor->ping_token, 'fail']);

		$bash = <<<SH
# Linux/macOS — meld succes aan het einde van je script:
curl -fsS -m 10 "{$success}" > /dev/null

# Volledig (start + automatische success/fail op basis van exit-code):
curl -fsS -m 10 "{$start}" > /dev/null
./mijn-job.sh
curl -fsS -m 10 "{$success}?code=\$?" > /dev/null
SH;

		$ps = <<<PS
# Windows PowerShell — aan het einde van de geplande taak:
try { Invoke-RestMethod -Uri "{$success}" -TimeoutSec 10 } catch {}

# Met fout-afhandeling:
try {
    & "C:\\pad\\naar\\job.ps1"
    Invoke-RestMethod -Uri "{$success}?code=\$LASTEXITCODE" -TimeoutSec 10
} catch {
    Invoke-RestMethod -Uri "{$fail}?msg=\$($_.Exception.Message)" -TimeoutSec 10
}
PS;

		$rows = [
			['Success (heartbeat)', $success],
			['Start (optioneel)', $start],
			['Fout', $fail],
		];

		$urlList = '';
		foreach ($rows as [$label, $url]) {
			$urlList .= "<div class='flex flex-col'><span class='text-xs text-gray-500'>" . e($label)
				. "</span><code class='text-xs break-all'>" . e($url) . "</code></div>";
		}

		return "<div class='space-y-4 text-sm'>"
			. "<div class='space-y-2'>{$urlList}</div>"
			. "<p class='text-xs text-gray-500'>Extra parameters: <code>?code=&lt;exit&gt;</code>, <code>?ms=&lt;duur&gt;</code>, <code>?msg=&lt;tekst&gt;</code>. "
			. "Een success-ping met <code>code</code> ≠ 0 telt automatisch als fout.</p>"
			. "<pre class='overflow-x-auto rounded-lg bg-gray-950 p-3 text-xs text-gray-100'>" . e($bash) . "</pre>"
			. "<pre class='overflow-x-auto rounded-lg bg-gray-950 p-3 text-xs text-gray-100'>" . e($ps) . "</pre>"
			. "</div>";
	}
}
