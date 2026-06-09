<?php

namespace App\Services\Rankdata;

use App\Models\Tenant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

/**
 * Genereert het PDF-klantrapport en mailt het (met bijlage) naar de
 * ontvanger(s). Gebruikt door de maandelijkse scheduler én de handmatige
 * "Rapport e-mailen"-knop.
 */
class RankdataReportSender
{
	private const NL_MONTHS = [1 => 'januari', 'februari', 'maart', 'april', 'mei', 'juni',
		'juli', 'augustus', 'september', 'oktober', 'november', 'december'];

	public function __construct(private readonly RankdataReport $report)
	{
	}

	/**
	 * Verstuur het rapport voor een klant naar de opgegeven adressen (of, leeg,
	 * naar de standaard-ontvangers van de klant).
	 *
	 * @param  string[]|null  $to
	 */
	public function send(Tenant $tenant, ?array $to = null): bool
	{
		$recipients = $to ?: $this->recipientsFor($tenant);
		if (empty($recipients)) {
			return false;
		}

		$data = $this->report->forTenant($tenant, 30);
		$pdf = (new RankdataPdfReport())->build($data);
		$period = self::NL_MONTHS[(int) now()->month] . ' ' . now()->year;
		$filename = 'rankdata-' . Str::slug($tenant->name) . '-' . now()->format('Y-m') . '.pdf';

		$body = "Beste,\n\nIn de bijlage vind je het maandrapport ({$period}) van {$tenant->name}: "
			. "vindbaarheid, beschikbaarheid, snelheid, kwaliteit en beveiliging van je website(s).\n\n"
			. ($data['sites'] ? 'Bekijk de details en aanbevolen acties in het dashboard.' : '')
			. "\n\nMet vriendelijke groet,\n" . ($data['agency'] ?: 'Rankdata');

		Mail::raw($body, function ($m) use ($recipients, $tenant, $period, $pdf, $filename) {
			$m->to($recipients)->subject("Maandrapport {$period} — {$tenant->name}")
				->attachData($pdf, $filename, ['mime' => 'application/pdf']);
		});

		DB::table('tenants')->where('id', $tenant->id)->update(['report_sent_at' => now()]);

		return true;
	}

	/** Standaard-ontvangers: de klant-logins; anders het bureau-contact. */
	private function recipientsFor(Tenant $tenant): array
	{
		$emails = DB::table('users')->where('tenant_id', $tenant->id)
			->where('is_active', true)->pluck('email')->filter()->all();
		if (! empty($emails)) {
			return array_values($emails);
		}
		$agencyEmail = $tenant->agency?->contact_email;

		return $agencyEmail ? [$agencyEmail] : [];
	}
}
