<?php

namespace App\Mail;

use App\Models\AccessGuard\Radar\Asset;
use App\Models\AccessGuard\Radar\Scan;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Status email after a Radar scan finishes — sent on scheduled scans
 * (cron / radar:scan CLI). Manually-triggered scans from the admin UI
 * skip this since the user already gets a Filament notification.
 *
 * Body summarises:
 *   - asset name + url
 *   - detection count
 *   - findings broken down by severity
 *   - the top 3 critical/high findings with CVE-id + short description
 *   - link back to the asset detail page
 */
class AccessGuardRadarScanComplete extends Mailable
{
	use Queueable, SerializesModels;

	public function __construct(
		public readonly Asset $asset,
		public readonly Scan $scan,
	) {}

	public function envelope(): Envelope
	{
		$badge = match (true) {
			$this->scan->status === 'error'                   => '⚠️',
			($this->scan->findings_count ?? 0) > 0            => '🚨',
			default                                            => '✅',
		};
		return new Envelope(
			subject: "{$badge} Radar scan: {$this->asset->name}",
		);
	}

	public function content(): Content
	{
		// Top critical/high findings, capped at 3 — full list is in the
		// admin panel; this is a heads-up, not a report.
		$topFindings = $this->asset->findings()
			->with('vulnerability:id,cve_id,description,cisa_kev')
			->whereIn('status', ['new', 'reopened', 'confirmed', 'in_progress'])
			->whereIn('severity', ['critical', 'high'])
			->orderByDesc('risk_score')
			->limit(3)
			->get();

		return new Content(
			markdown: 'emails.accessguard.radar-scan-complete',
			with: [
				'asset' => $this->asset,
				'scan' => $this->scan,
				'topFindings' => $topFindings,
			],
		);
	}
}
