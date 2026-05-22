<?php

namespace App\Mail;

use App\Models\AccessGuard\Radar\Asset;
use App\Models\AccessGuard\Radar\Scan;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

/**
 * Geaggregeerde mail na een RunWebChecksScanJob (TLS + cookies + CMP).
 * Wordt alleen verzonden als minstens één van de 3 scans een new/resolved
 * finding-key heeft opgeleverd. Bundelt de 3 scans tot 1 mail i.p.v. spam.
 *
 * @param  array<string,array{scan:Scan,diff:array{new:string[],resolved:string[]}}>  $perCheck
 */
class AccessGuardRadarWebChecksComplete extends Mailable
{
	use Queueable, SerializesModels;

	public function __construct(
		public readonly Asset $asset,
		public readonly array $perCheck,
	) {}

	public function envelope(): Envelope
	{
		$totalNew = 0;
		$totalResolved = 0;
		foreach ($this->perCheck as $c) {
			$totalNew      += count($c['diff']['new'] ?? []);
			$totalResolved += count($c['diff']['resolved'] ?? []);
		}
		$badge = $totalNew > 0 ? '🚨' : ($totalResolved > 0 ? '✅' : 'ℹ️');

		return new Envelope(
			subject: "{$badge} Radar web-checks: {$this->asset->name}",
		);
	}

	public function content(): Content
	{
		$openFindings = $this->asset->findings()
			->whereIn('check_type', ['tls', 'cookies', 'cmp'])
			->whereIn('status', ['new', 'reopened', 'confirmed', 'in_progress'])
			->orderByDesc('risk_score')
			->limit(5)
			->get();

		return new Content(
			markdown: 'emails.accessguard.radar-web-checks-complete',
			with: [
				'asset'        => $this->asset,
				'perCheck'     => $this->perCheck,
				'openFindings' => $openFindings,
			],
		);
	}
}
