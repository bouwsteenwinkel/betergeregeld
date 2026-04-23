<?php

namespace App\Services;

use App\Models\BookkeepingInvoice;
use App\Models\BookkeepingTenantSettings;
use FPDF;
use RuntimeException;

/**
 * Renders a BookkeepingInvoice to a PDF file via FPDF.
 *
 * FPDF uses cp1252 — all strings are transliterated from UTF-8 first so
 * € and accented characters render correctly.
 */
class InvoicePdfRenderer
{
	public function render(BookkeepingInvoice $invoice, string $outputPath): string
	{
		if (! class_exists(FPDF::class)) {
			throw new RuntimeException('FPDF library is not installed');
		}

		$settings = BookkeepingTenantSettings::find($invoice->tenant_id);
		$invoice->loadMissing(['lines.vatRate', 'relation']);

		$pdf = new FPDF('P', 'mm', 'A4');
		$pdf->SetMargins(18, 18, 18);
		$pdf->SetAutoPageBreak(true, 18);
		$pdf->AddPage();

		$this->header($pdf, $invoice, $settings);
		$this->billTo($pdf, $invoice);
		$this->lines($pdf, $invoice);
		$this->totals($pdf, $invoice);
		$this->footer($pdf, $invoice, $settings);

		$dir = dirname($outputPath);
		if (! is_dir($dir)) {
			mkdir($dir, 0755, true);
		}
		$pdf->Output('F', $outputPath);

		if (! is_file($outputPath) || filesize($outputPath) < 500) {
			throw new RuntimeException('PDF was not written correctly');
		}
		return $outputPath;
	}

	// ---------- building blocks ----------

	private function header(FPDF $pdf, BookkeepingInvoice $inv, ?BookkeepingTenantSettings $s): void
	{
		// Optional logo: top-left, max 40 × 20 mm, preserves aspect
		if ($s?->hasLogo()) {
			try {
				$info = @getimagesize($s->logo_path);
				if ($info) {
					$maxW = 40;
					$maxH = 20;
					$ratio = $info[0] / max(1, $info[1]);
					$w = $ratio > ($maxW / $maxH) ? $maxW : $maxH * $ratio;
					$h = $w / $ratio;
					$pdf->Image($s->logo_path, 18, 18, $w, $h);
					$pdf->SetY(18 + $h + 3);
				}
			} catch (\Throwable) {
				// logo unreadable — fall through to plain header
			}
		}

		// Left: sender
		$pdf->SetFont('Arial', 'B', 14);
		$pdf->Cell(100, 6, $this->t($s?->company_name ?: '(bedrijfsnaam niet ingevuld)'), 0, 2);
		$pdf->SetFont('Arial', '', 9);
		$addrLines = array_filter([
			$s?->address,
			trim(($s?->postal_code ?? '') . ' ' . ($s?->city ?? '')),
			$s?->country,
		]);
		foreach ($addrLines as $line) {
			$pdf->Cell(100, 4, $this->t($line), 0, 2);
		}
		$pdf->Ln(2);
		if ($s?->email) {
			$pdf->Cell(100, 4, $this->t($s->email), 0, 2);
		}
		if ($s?->phone) {
			$pdf->Cell(100, 4, $this->t($s->phone), 0, 2);
		}

		// Right: invoice meta (fixed position at top-right, independent of logo)
		$savedY = $pdf->GetY();
		$pdf->SetXY(120, 18);
		$pdf->SetFont('Arial', 'B', 22);
		$pdf->Cell(72, 10, 'FACTUUR', 0, 2, 'R');
		$pdf->SetFont('Arial', '', 9);
		$pdf->Cell(72, 6, $this->t('Nummer: ' . $inv->invoice_number), 0, 2, 'R');
		$pdf->Cell(72, 4, $this->t('Datum: ' . $inv->issue_date->format('d-m-Y')), 0, 2, 'R');
		if ($inv->due_date) {
			$pdf->Cell(72, 4, $this->t('Vervaldatum: ' . $inv->due_date->format('d-m-Y')), 0, 2, 'R');
		}
		if ($inv->reference) {
			$pdf->Cell(72, 4, $this->t('Ref.: ' . $inv->reference), 0, 2, 'R');
		}

		// Move cursor below both columns — pick the larger Y
		$bottomLeft = max($savedY, $pdf->GetY());
		$pdf->SetY(max(60, $bottomLeft + 3));
	}

	private function billTo(FPDF $pdf, BookkeepingInvoice $inv): void
	{
		$rel = $inv->relation;
		if (! $rel) {
			return;
		}
		$pdf->SetFont('Arial', 'B', 10);
		$pdf->Cell(0, 5, 'Aan:', 0, 1);
		$pdf->SetFont('Arial', '', 10);
		$pdf->Cell(0, 5, $this->t($rel->name), 0, 1);
		if ($rel->address) {
			$pdf->Cell(0, 5, $this->t($rel->address), 0, 1);
		}
		$cityLine = trim(($rel->postal_code ?? '') . ' ' . ($rel->city ?? ''));
		if ($cityLine !== '') {
			$pdf->Cell(0, 5, $this->t($cityLine), 0, 1);
		}
		if ($rel->country) {
			$pdf->Cell(0, 5, $this->t($rel->country), 0, 1);
		}
		if ($rel->vat_number) {
			$pdf->Cell(0, 5, $this->t('BTW-nr: ' . $rel->vat_number), 0, 1);
		}
		$pdf->Ln(4);
	}

	private function lines(FPDF $pdf, BookkeepingInvoice $inv): void
	{
		$pdf->SetFont('Arial', 'B', 9);
		$pdf->SetFillColor(241, 241, 241);
		$pdf->Cell(90, 7, 'Omschrijving', 0, 0, 'L', true);
		$pdf->Cell(16, 7, 'Aantal', 0, 0, 'R', true);
		$pdf->Cell(28, 7, 'Prijs', 0, 0, 'R', true);
		$pdf->Cell(14, 7, 'BTW%', 0, 0, 'R', true);
		$pdf->Cell(26, 7, 'Totaal', 0, 1, 'R', true);

		$pdf->SetFont('Arial', '', 9);
		foreach ($inv->lines as $line) {
			$pdf->Cell(90, 6, $this->t($this->truncate($line->description, 60)), 0, 0);
			$pdf->Cell(16, 6, rtrim(rtrim(number_format((float) $line->quantity, 2, ',', ''), '0'), ','), 0, 0, 'R');
			$pdf->Cell(28, 6, $this->money((float) $line->unit_price), 0, 0, 'R');
			$pdf->Cell(14, 6, $line->vatRate ? rtrim(rtrim(number_format((float) $line->vatRate->rate, 2, ',', ''), '0'), ',') . '%' : '—', 0, 0, 'R');
			$pdf->Cell(26, 6, $this->money($line->lineNet()), 0, 1, 'R');
		}
		$pdf->Ln(2);
		$pdf->Cell(0, 0, '', 'T', 1);
	}

	private function totals(FPDF $pdf, BookkeepingInvoice $inv): void
	{
		$pdf->Ln(2);
		$labelX = 120;
		$pdf->SetX($labelX);
		$pdf->SetFont('Arial', '', 9);
		$pdf->Cell(40, 6, 'Subtotaal (excl. BTW):', 0, 0, 'R');
		$pdf->Cell(34, 6, $this->money((float) $inv->subtotal), 0, 1, 'R');

		$pdf->SetX($labelX);
		$pdf->Cell(40, 6, 'BTW:', 0, 0, 'R');
		$pdf->Cell(34, 6, $this->money((float) $inv->vat_amount), 0, 1, 'R');

		$pdf->SetX($labelX);
		$pdf->SetFont('Arial', 'B', 11);
		$pdf->Cell(40, 8, 'Totaal:', 'T', 0, 'R');
		$pdf->Cell(34, 8, $this->money((float) $inv->total), 'T', 1, 'R');
	}

	private function footer(FPDF $pdf, BookkeepingInvoice $inv, ?BookkeepingTenantSettings $s): void
	{
		if ($inv->notes) {
			$pdf->Ln(6);
			$pdf->SetFont('Arial', 'B', 9);
			$pdf->Cell(0, 5, 'Opmerkingen:', 0, 1);
			$pdf->SetFont('Arial', '', 9);
			$pdf->MultiCell(0, 4, $this->t($inv->notes));
		}

		$pdf->SetY(-30);
		$pdf->SetFont('Arial', '', 8);
		$footerParts = array_filter([
			$s?->company_name,
			$s?->iban ? 'IBAN ' . $s->iban : null,
			$s?->kvk_number ? 'KvK ' . $s->kvk_number : null,
			$s?->vat_number ? 'BTW ' . $s->vat_number : null,
		]);
		if (! empty($footerParts)) {
			$pdf->SetTextColor(120, 120, 120);
			$pdf->Cell(0, 4, $this->t(implode('  ·  ', $footerParts)), 0, 1, 'C');
		}
		if ($s?->invoice_footer) {
			$pdf->Cell(0, 4, $this->t($this->truncate($s->invoice_footer, 120)), 0, 1, 'C');
		}
	}

	// ---------- helpers ----------

	private function t(string $utf8): string
	{
		// FPDF uses cp1252 / latin-1
		$converted = @iconv('UTF-8', 'windows-1252//TRANSLIT//IGNORE', $utf8);
		return $converted !== false ? $converted : $utf8;
	}

	private function money(float $amount): string
	{
		return '€ ' . number_format($amount, 2, ',', '.');
	}

	private function truncate(string $s, int $n): string
	{
		return mb_strlen($s) > $n ? mb_substr($s, 0, $n - 1) . '…' : $s;
	}
}
