<?php

namespace App\Filament\Resources\MonitorServers\Actions;

use App\Models\Monitor\Server;
use Filament\Actions\Action;
use Illuminate\Support\HtmlString;

/**
 * Read-only helper: shows the exact PowerShell to install the collector on the
 * host as a scheduled task that pushes a sample every minute. The token is
 * baked into the snippet so it is copy-paste ready.
 *
 * Sinds 23-08-2026 staat de dagelijkse schijfmeting in hetzelfde blok. Die
 * stond alleen in de README, en daar bleef hij ook staan: de meting is één keer
 * met de hand gedraaid en daarna niet meer, terwijl juist de HERHALING het
 * antwoord geeft — een losse stand vertelt niet welke map groeit.
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
		$diskEndpoint = route('monitor.ingest-disk');
		$token = e($server->ingest_token);
		$map = 'C:\\Inetpub\\vhosts\\betergeregeld.com\\httpdocs\\tools\\monitor-agent';
		$script = $map . '\\collect.ps1';
		$diskScript = $map . '\\schijfgebruik.ps1';

		$ps = <<<PS
# 1) Token + endpoint als machine-omgevingsvariabelen (eenmalig, als Administrator):
[Environment]::SetEnvironmentVariable('MONITOR_TOKEN', '{$token}', 'Machine')
[Environment]::SetEnvironmentVariable('MONITOR_ENDPOINT', '{$endpoint}', 'Machine')
[Environment]::SetEnvironmentVariable('MONITOR_DISK_ENDPOINT', '{$diskEndpoint}', 'Machine')

# 2) Geplande taak die elke minuut een sample pusht:
\$a = New-ScheduledTaskAction -Execute 'powershell.exe' -Argument '-NoProfile -ExecutionPolicy Bypass -File "{$script}"'
\$t = New-ScheduledTaskTrigger -Once -At (Get-Date) -RepetitionInterval (New-TimeSpan -Minutes 1)
\$p = New-ScheduledTaskPrincipal -UserId 'SYSTEM' -LogonType ServiceAccount -RunLevel Highest
Register-ScheduledTask -TaskName 'BG-Monitor-Agent' -Action \$a -Trigger \$t -Principal \$p -Force

# 3) Direct testen (eenmalige push):
powershell -NoProfile -ExecutionPolicy Bypass -File "{$script}"

# 4) Dagelijkse schijfmeting per map. Bewust 08:00 en niet 's nachts: een volle
#    schijf doorlopen kost een half uur schijf-IO, en tussen 03:00 en 05:00 ligt
#    deze machine toch al op zijn rug.
\$da = New-ScheduledTaskAction -Execute 'powershell.exe' -Argument '-NoProfile -ExecutionPolicy Bypass -File "{$diskScript}" -Push'
\$dt = New-ScheduledTaskTrigger -Daily -At 08:00
Register-ScheduledTask -TaskName 'BG-Monitor-Schijf' -Action \$da -Trigger \$dt -Principal \$p -Force
PS;

		$pre = e($ps);

		return "<div class='space-y-3 text-sm'>"
			. "<p>Endpoint: <code>{$endpoint}</code></p>"
			. "<p>Endpoint schijfmeting: <code>{$diskEndpoint}</code></p>"
			. "<p>Plak dit op de VPS (PowerShell als Administrator). <code>collect.ps1</code> en <code>schijfgebruik.ps1</code> staan na een <code>git pull</code> klaar onder <code>tools\\monitor-agent\\</code>.</p>"
			. "<pre class='overflow-x-auto rounded-lg bg-gray-950 p-3 text-xs text-gray-100'>{$pre}</pre>"
			. "</div>";
	}
}
