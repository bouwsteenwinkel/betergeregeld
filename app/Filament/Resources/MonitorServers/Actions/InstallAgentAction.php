<?php

namespace App\Filament\Resources\MonitorServers\Actions;

use App\Models\Monitor\Server;
use Filament\Actions\Action;
use Illuminate\Support\HtmlString;

/**
 * Read-only helper: shows the exact PowerShell to install the collector on the
 * host as a scheduled task that pushes a sample every minute. The token is
 * baked into the snippet so it is copy-paste ready.
 */
class InstallAgentAction
{
	public static function make(?string $name = 'install'): Action
	{
		return Action::make($name)
			->label('Install')
			->icon('heroicon-m-command-line')
			->color('gray')
			->modalHeading('Collector installeren op de VPS')
			->modalSubmitAction(false)
			->modalCancelActionLabel('Sluiten')
			->modalContent(fn (Server $record) => new HtmlString(self::snippet($record)));
	}

	private static function snippet(Server $server): string
	{
		$endpoint = route('monitor.ingest');
		$token = e($server->ingest_token);
		$script = 'C:\\Inetpub\\vhosts\\betergeregeld.com\\httpdocs\\tools\\monitor-agent\\collect.ps1';

		$ps = <<<PS
# 1) Token + endpoint als machine-omgevingsvariabelen (eenmalig, als Administrator):
[Environment]::SetEnvironmentVariable('MONITOR_TOKEN', '{$token}', 'Machine')
[Environment]::SetEnvironmentVariable('MONITOR_ENDPOINT', '{$endpoint}', 'Machine')

# 2) Geplande taak die elke minuut een sample pusht:
\$a = New-ScheduledTaskAction -Execute 'powershell.exe' -Argument '-NoProfile -ExecutionPolicy Bypass -File "{$script}"'
\$t = New-ScheduledTaskTrigger -Once -At (Get-Date) -RepetitionInterval (New-TimeSpan -Minutes 1)
\$p = New-ScheduledTaskPrincipal -UserId 'SYSTEM' -LogonType ServiceAccount -RunLevel Highest
Register-ScheduledTask -TaskName 'BG-Monitor-Agent' -Action \$a -Trigger \$t -Principal \$p -Force

# 3) Direct testen (eenmalige push):
powershell -NoProfile -ExecutionPolicy Bypass -File "{$script}"
PS;

		$pre = e($ps);

		return "<div class='space-y-3 text-sm'>"
			. "<p>Endpoint: <code>{$endpoint}</code></p>"
			. "<p>Plak dit op de VPS (PowerShell als Administrator). De <code>collect.ps1</code> staat na een <code>git pull</code> klaar onder <code>tools\\monitor-agent\\</code>.</p>"
			. "<pre class='overflow-x-auto rounded-lg bg-gray-950 p-3 text-xs text-gray-100'>{$pre}</pre>"
			. "</div>";
	}
}
