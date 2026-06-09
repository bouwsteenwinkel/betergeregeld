<?php

namespace App\Services\Rankdata;

use App\Models\Seo\SeoProperty;
use App\Services\Seo\GoogleApiAuth;
use FPDF;
use Illuminate\Support\Str;

/**
 * Programmatisch (FPDF) klant-instructieblad per site: de twee acties die de
 * klant moet doen om volledige monitoring mogelijk te maken — Search
 * Console-toegang geven en de beveiligingsplugin installeren (met de site-token).
 */
class OnboardingPdf extends FPDF
{
	private string $domain = '';

	public function build(SeoProperty $site): string
	{
		$this->domain = preg_replace('/^sc-domain:/', '', (string) $site->site_url);

		if (! $site->security_ingest_token) {
			$site->forceFill(['security_ingest_token' => Str::random(48)])->save();
		}
		$serviceAccount = app(GoogleApiAuth::class)->serviceAccountEmail() ?: 'service-account ontbreekt';

		$this->SetMargins(18, 18, 18);
		$this->SetAutoPageBreak(true, 20);
		$this->SetTitle($this->t('Aansluiten op monitoring - ' . $this->domain));
		$this->AddPage();

		// Kop
		$this->SetFillColor(17, 17, 17);
		$this->Rect(18, 18, 174, 24, 'F');
		$this->SetTextColor(255);
		$this->SetFont('Helvetica', 'B', 18);
		$this->SetXY(23, 23);
		$this->Cell(0, 8, $this->t('Aansluiten op monitoring'), 0, 2, 'L');
		$this->SetFont('Helvetica', '', 11);
		$this->Cell(0, 6, $this->t($site->label . '  -  ' . $this->domain), 0, 2, 'L');
		$this->SetTextColor(17);
		$this->SetY(50);

		$this->SetFont('Helvetica', '', 10);
		$this->MultiCell(0, 5.5, $this->t(
			'Om uw website volledig te kunnen monitoren (vindbaarheid, beschikbaarheid, snelheid, '
			. 'kwaliteit en beveiliging) vragen we twee korte, eenmalige handelingen. De rest meten wij zelf.'
		));
		$this->Ln(4);

		// Stap 1
		$this->step('1', 'Geef toegang tot Google Search Console');
		$this->body(
			"Hiermee kunnen we uw vindbaarheid in Google (zoekwoorden, posities, klikken) tonen.\n"
			. "1. Log in op Google Search Console (search.google.com/search-console).\n"
			. "2. Kies bovenaan de juiste website.\n"
			. "3. Ga naar Instellingen -> Gebruikers en machtigingen -> Gebruiker toevoegen.\n"
			. "4. Voeg onderstaand e-mailadres toe met rol 'Beperkt' (alleen lezen):"
		);
		$this->highlight($serviceAccount);

		// Stap 2
		$this->step('2', 'Installeer de beveiligingsplugin (WordPress)');
		$this->body(
			"Hiermee monitoren we updates, bekende kwetsbaarheden en gewijzigde kernbestanden.\n"
			. "1. Installeer de meegeleverde plugin 'Beter Geregeld - Security Monitor' in WordPress.\n"
			. "2. Vul bij de instelling BG_MONITOR_TOKEN onderstaande code in en activeer de plugin:"
		);
		$this->highlight($site->security_ingest_token);

		$this->Ln(4);
		$this->SetFont('Helvetica', 'I', 9);
		$this->SetTextColor(110);
		$this->MultiCell(0, 5, $this->t(
			'Geen toegang of hulp nodig? Neem contact op, dan regelen we het samen. '
			. 'Beschikbaarheid, snelheid (PageSpeed), kwaliteit en malware/blacklist-controle werken '
			. 'ook zonder deze twee stappen.'
		));

		return (string) $this->Output('S');
	}

	public function Footer(): void
	{
		$this->SetY(-15);
		$this->SetDrawColor(225);
		$this->Line(18, $this->GetY(), 192, $this->GetY());
		$this->Ln(2);
		$this->SetFont('Helvetica', '', 8);
		$this->SetTextColor(140);
		$this->Cell(0, 6, $this->t('Monitoring door Beter Geregeld'), 0, 0, 'L');
		$this->SetTextColor(17);
	}

	private function step(string $n, string $title): void
	{
		$this->Ln(3);
		$y = $this->GetY();
		$this->SetFillColor(17, 17, 17);
		$this->SetTextColor(255);
		$this->SetFont('Helvetica', 'B', 11);
		$this->Cell(8, 8, $n, 0, 0, 'C', true);
		$this->SetTextColor(17);
		$this->SetXY(30, $y);
		$this->Cell(0, 8, $this->t($title), 0, 1, 'L');
		$this->Ln(1);
	}

	private function body(string $text): void
	{
		$this->SetFont('Helvetica', '', 10);
		$this->SetTextColor(40);
		$this->MultiCell(0, 5.5, $this->t($text));
		$this->SetTextColor(17);
	}

	private function highlight(string $value): void
	{
		$this->Ln(1);
		$this->SetFillColor(245, 245, 245);
		$this->SetFont('Courier', 'B', 11);
		$this->SetTextColor(17);
		$this->Cell(0, 9, '  ' . $this->t($value), 0, 1, 'L', true);
		$this->Ln(2);
	}

	private function t(string $s): string
	{
		$r = @iconv('UTF-8', 'windows-1252//TRANSLIT', $s);

		return $r === false ? $s : $r;
	}
}
