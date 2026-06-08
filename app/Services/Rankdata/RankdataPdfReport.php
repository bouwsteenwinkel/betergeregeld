<?php

namespace App\Services\Rankdata;

use FPDF;

/**
 * Programmatisch (FPDF) getekend Rankdata-klantrapport — geen HTML→PDF.
 * Monochrome Betergeregeld-stijl. Tekst wordt naar Windows-1252 geconverteerd
 * omdat FPDF-core geen UTF-8 kan (€, é, … e.d.).
 */
class RankdataPdfReport extends FPDF
{
	private string $reportTenant = '';

	private string $generatedLabel = '';

	/** Bouwt het PDF en geeft de bytes terug. */
	public function build(array $data): string
	{
		$this->reportTenant = $data['tenant'];
		$this->generatedLabel = $data['generated_at']->format('d-m-Y H:i');

		$this->SetMargins(15, 18, 15);
		$this->SetAutoPageBreak(true, 20);
		$this->SetTitle($this->t('Rankdata-rapport - ' . $data['tenant']));
		$this->AddPage();
		$this->cover($data);

		if (empty($data['sites'])) {
			$this->SetFont('Helvetica', 'I', 11);
			$this->SetTextColor(150);
			$this->MultiCell(0, 6, $this->t('Geen actieve sites voor deze klant.'));
		}

		foreach ($data['sites'] as $site) {
			$this->siteSection($site);
		}

		return (string) $this->Output('S');
	}

	// --- FPDF hooks -----------------------------------------------------

	public function Header(): void
	{
		if ($this->PageNo() === 1) {
			return; // de cover heeft een eigen kop
		}
		$this->SetFont('Helvetica', 'B', 9);
		$this->SetTextColor(130);
		$this->Cell(0, 6, $this->t('Rankdata-rapport - ' . $this->reportTenant), 0, 1, 'R');
		$this->SetDrawColor(225);
		$this->Line(15, $this->GetY(), 195, $this->GetY());
		$this->Ln(4);
		$this->SetTextColor(17);
	}

	public function Footer(): void
	{
		$this->SetY(-15);
		$this->SetDrawColor(225);
		$this->Line(15, $this->GetY(), 195, $this->GetY());
		$this->Ln(2);
		$this->SetFont('Helvetica', '', 8);
		$this->SetTextColor(140);
		$this->Cell(90, 6, $this->t('Gegenereerd ' . $this->generatedLabel), 0, 0, 'L');
		$this->Cell(0, 6, $this->t('Pagina ' . $this->PageNo()), 0, 0, 'R');
		$this->SetTextColor(17);
	}

	// --- Secties --------------------------------------------------------

	private function cover(array $data): void
	{
		$this->SetFillColor(17, 17, 17);
		$this->Rect(15, 18, 180, 26, 'F');
		$this->SetTextColor(255);
		$this->SetFont('Helvetica', 'B', 20);
		$this->SetXY(20, 22);
		$this->Cell(0, 9, $this->t('Rankdata-rapport'), 0, 2, 'L');
		$this->SetFont('Helvetica', '', 11);
		$this->Cell(0, 6, $this->t($data['tenant'] . ($data['agency'] ? '  -  ' . $data['agency'] : '')), 0, 2, 'L');

		$this->SetTextColor(110);
		$this->SetY(50);
		$this->SetFont('Helvetica', '', 10);
		$this->Cell(0, 6, $this->t('Periode: laatste ' . $data['days'] . ' dagen (SEO) en 7 dagen (uptime) - gegenereerd ' . $this->generatedLabel), 0, 1, 'L');
		$this->SetTextColor(17);
		$this->Ln(4);
	}

	private function siteSection(array $s): void
	{
		if ($this->GetY() > 225) {
			$this->AddPage();
		}

		$this->SetFont('Helvetica', 'B', 13);
		$this->Cell(0, 8, $this->t($s['label']), 0, 1, 'L');
		$this->SetFont('Helvetica', '', 9);
		$this->SetTextColor(125);
		$this->Cell(0, 5, $this->t($s['site_url']), 0, 1, 'L');
		$this->SetTextColor(17);
		$this->Ln(2);

		$seo = $s['seo'];
		$up = $s['uptime'];
		$trend = $seo['trend'] === null ? '' : ($seo['trend'] >= 0 ? ' (+' . $seo['trend'] . '%)' : ' (' . $seo['trend'] . '%)');

		$this->kpiRow([
			['Clicks (' . $this->reportDays($s) . 'd)', $this->num($seo['clicks']) . $trend],
			['Impressies', $this->num($seo['impressions'])],
			['CTR', $this->dec($seo['ctr'], 1) . '%'],
			['Gem. positie', $seo['position'] ? $this->dec($seo['position'], 1) : '-'],
			['Uptime (7d)', $up ? $this->dec($up['percent'], 1) . '%' : '-'],
		]);
		$this->Ln(4);

		$this->psiBlock($s['psi']);
		$this->qualityBlock($s['quality'] ?? null);

		if (! empty($seo['topQueries'])) {
			$this->subHeading('Top zoekwoorden');
			$this->table(
				[['Zoekwoord', 90], ['Clicks', 26], ['Impressies', 32], ['Positie', 32]],
				array_map(fn ($q) => [$q['query'], $this->num($q['clicks']), $this->num($q['impr']), $this->dec($q['pos'], 1)], $seo['topQueries']),
				['L', 'R', 'R', 'R'],
			);
		}

		if (! empty($seo['topPages'])) {
			$this->subHeading("Top pagina's");
			$this->table(
				[['Pagina', 122], ['Clicks', 26], ['Impressies', 32]],
				array_map(fn ($p) => [$this->shortUrl($p['page']), $this->num($p['clicks']), $this->num($p['impr'])], $seo['topPages']),
				['L', 'R', 'R'],
			);
		}

		if (! $seo['has_data']) {
			$this->Ln(1);
			$this->SetFont('Helvetica', 'I', 9);
			$this->SetTextColor(150);
			$this->MultiCell(0, 5, $this->t('Nog geen Search Console-data voor deze site (toegang of import in afwachting).'));
			$this->SetTextColor(17);
		}

		$this->Ln(6);
	}

	private function qualityBlock(?array $q): void
	{
		$this->subHeading('Paginakwaliteit (AI-scan)');

		if (! $q) {
			$this->SetFont('Helvetica', 'I', 9);
			$this->SetTextColor(150);
			$this->Cell(0, 6, $this->t('Nog geen kwaliteitsscan voor deze site.'), 0, 1, 'L');
			$this->SetTextColor(17);
			$this->Ln(2);

			return;
		}

		$this->SetFont('Helvetica', 'B', 16);
		$this->Cell(16, 8, (string) $q['score'], 0, 0, 'L');
		$this->SetFont('Helvetica', '', 9);
		$this->SetTextColor(110);
		$this->Cell(0, 8, $this->t('/ 100   -   ' . $q['fail'] . ' kritiek, ' . $q['warn'] . ' aandachtspunten, ' . $q['pass'] . ' ok'), 0, 1, 'L');
		$this->SetTextColor(17);

		foreach ($q['top'] as $f) {
			$tag = $f['status'] === 'fail' ? '[Kritiek] ' : '[Let op] ';
			$this->SetFont('Helvetica', '', 8.5);
			$this->MultiCell(0, 4.5, $this->t($tag . $f['finding']), 0, 'L');
			$this->Ln(0.5);
		}
		$this->Ln(2);
	}

	private function psiBlock(array $psi): void
	{
		$this->subHeading('PageSpeed');
		foreach (['mobile' => 'Mobiel', 'desktop' => 'Desktop'] as $key => $label) {
			$p = $psi[$key] ?? null;
			$this->SetFont('Helvetica', 'B', 9);
			$this->Cell(22, 6, $this->t($label), 0, 0, 'L');
			$this->SetFont('Helvetica', '', 9);

			if ($p) {
				$line = 'Performance ' . $p['performance'] . ' - SEO ' . $p['seo'] . ' - Toegankelijkheid ' . $p['accessibility'];
				if ($p['lcp'] !== null) {
					$line .= ' - LCP ' . $this->dec($p['lcp'], 1) . ' s';
				}
				if ($p['cls'] !== null) {
					$line .= ' - CLS ' . $this->dec($p['cls'], 2);
				}
				$this->Cell(0, 6, $this->t($line), 0, 1, 'L');
			} else {
				$this->SetTextColor(150);
				$this->Cell(0, 6, $this->t('geen meting (quota of nog niet gedraaid)'), 0, 1, 'L');
				$this->SetTextColor(17);
			}
		}
		$this->Ln(2);
	}

	// --- Bouwstenen -----------------------------------------------------

	private function kpiRow(array $kpis): void
	{
		$n = max(1, count($kpis));
		$w = 180 / $n;
		$x = 15;
		$y = $this->GetY();

		foreach ($kpis as $k) {
			$this->SetFillColor(245, 245, 245);
			$this->Rect($x, $y, $w - 2, 16, 'F');
			$this->SetXY($x + 2, $y + 2.5);
			$this->SetFont('Helvetica', '', 7.5);
			$this->SetTextColor(120);
			$this->Cell($w - 4, 4, $this->t($k[0]), 0, 2, 'L');
			$this->SetFont('Helvetica', 'B', 11);
			$this->SetTextColor(17);
			$this->Cell($w - 4, 6, $this->t($k[1]), 0, 0, 'L');
			$x += $w;
		}
		$this->SetY($y + 16);
	}

	private function subHeading(string $title): void
	{
		if ($this->GetY() > 252) {
			$this->AddPage();
		}
		$this->Ln(2);
		$this->SetFont('Helvetica', 'B', 10);
		$this->SetTextColor(17);
		$this->Cell(0, 6, $this->t($title), 0, 1, 'L');
		$this->SetDrawColor(225);
		$this->Line(15, $this->GetY(), 195, $this->GetY());
		$this->Ln(1);
	}

	/**
	 * @param array<int,array{0:string,1:float}> $cols  [label, breedte]
	 * @param array<int,array<int,string>>       $rows
	 * @param array<int,string>                  $align
	 */
	private function table(array $cols, array $rows, array $align): void
	{
		$this->drawTableHead($cols, $align);

		$this->SetFont('Helvetica', '', 8);
		$fill = false;
		foreach ($rows as $r) {
			if ($this->GetY() > 272) {
				$this->AddPage();
				$this->drawTableHead($cols, $align);
				$this->SetFont('Helvetica', '', 8);
			}
			$this->SetFillColor(247, 247, 247);
			$this->SetTextColor(17);
			foreach ($cols as $i => $c) {
				$this->Cell($c[1], 5.5, $this->clip((string) $r[$i], $c[1]), 0, 0, $align[$i] ?? 'L', $fill);
			}
			$this->Ln();
			$fill = ! $fill;
		}
	}

	private function drawTableHead(array $cols, array $align): void
	{
		$this->SetFont('Helvetica', 'B', 8);
		$this->SetFillColor(17, 17, 17);
		$this->SetTextColor(255);
		foreach ($cols as $i => $c) {
			$this->Cell($c[1], 6, $this->t($c[0]), 0, 0, $align[$i] ?? 'L', true);
		}
		$this->Ln();
		$this->SetTextColor(17);
	}

	// --- Helpers --------------------------------------------------------

	/** UTF-8 → Windows-1252 (FPDF-core kan geen UTF-8). */
	private function t(string $s): string
	{
		$r = @iconv('UTF-8', 'windows-1252//TRANSLIT', $s);

		return $r === false ? $s : $r;
	}

	/** Encodeert én kapt af op cel-breedte (FPDF Cell breekt niet af). */
	private function clip(string $s, float $w): string
	{
		$enc = $this->t($s);
		$max = $w - 2;
		if ($this->GetStringWidth($enc) <= $max) {
			return $enc;
		}
		while (strlen($enc) > 1 && $this->GetStringWidth($enc . '...') > $max) {
			$enc = substr($enc, 0, -1);
		}

		return $enc . '...';
	}

	private function num(int $n): string
	{
		return number_format($n, 0, ',', '.');
	}

	private function dec(float $n, int $d): string
	{
		return number_format($n, $d, ',', '.');
	}

	private function shortUrl(string $u): string
	{
		$path = preg_replace('#^https?://[^/]+#', '', $u);

		return ($path === '' || $path === null) ? '/' : $path;
	}

	private function reportDays(array $s): int
	{
		return 30;
	}
}
