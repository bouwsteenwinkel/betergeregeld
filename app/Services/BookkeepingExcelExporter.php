<?php

namespace App\Services;

use App\Models\BookkeepingTenantSettings;
use Carbon\CarbonImmutable;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

/**
 * Produces Excel (.xlsx) exports of bookkeeping reports for an
 * accountant / tax prep workflow. No formatting wizardry — just
 * clean tables with labels, amounts and totals.
 */
class BookkeepingExcelExporter
{
	public function profitLoss(string $tenantId, array $data): string
	{
		$settings = BookkeepingTenantSettings::find($tenantId);

		$spreadsheet = new Spreadsheet();
		$sheet = $spreadsheet->getActiveSheet();
		$sheet->setTitle('Winst & verlies');

		$row = 1;
		$sheet->setCellValue("A{$row}", 'Winst- en verliesrekening');
		$sheet->getStyle("A{$row}")->getFont()->setBold(true)->setSize(14);
		$row++;

		$sheet->setCellValue("A{$row}", 'Bedrijf:');
		$sheet->setCellValue("B{$row}", $settings?->company_name ?? '—');
		$row++;
		$sheet->setCellValue("A{$row}", 'Periode:');
		$sheet->setCellValue("B{$row}", $data['from'] . ' — ' . $data['to']);
		$row++;
		$sheet->setCellValue("A{$row}", 'Export gegenereerd:');
		$sheet->setCellValue("B{$row}", CarbonImmutable::now()->format('d-m-Y H:i'));
		$row += 2;

		// Revenue section
		$sheet->setCellValue("A{$row}", 'Omzet');
		$sheet->getStyle("A{$row}")->getFont()->setBold(true)->setSize(12);
		$row++;
		$sheet->setCellValue("A{$row}", 'Categorie');
		$sheet->setCellValue("B{$row}", 'Aantal');
		$sheet->setCellValue("C{$row}", 'Bedrag');
		$sheet->getStyle("A{$row}:C{$row}")->getFont()->setBold(true);
		$sheet->getStyle("A{$row}:C{$row}")->getBorders()->getBottom()->setBorderStyle(Border::BORDER_THIN);
		$row++;

		foreach ($data['incomeRows'] as $r) {
			$sheet->setCellValue("A{$row}", $r['category']);
			$sheet->setCellValue("B{$row}", $r['count']);
			$sheet->setCellValue("C{$row}", (float) $r['total']);
			$row++;
		}
		$sheet->setCellValue("A{$row}", 'Totaal omzet');
		$sheet->setCellValue("C{$row}", (float) $data['incomeTotal']);
		$sheet->getStyle("A{$row}:C{$row}")->getFont()->setBold(true);
		$sheet->getStyle("A{$row}:C{$row}")->getBorders()->getTop()->setBorderStyle(Border::BORDER_THIN);
		$row += 2;

		// Expense section
		$sheet->setCellValue("A{$row}", 'Kosten');
		$sheet->getStyle("A{$row}")->getFont()->setBold(true)->setSize(12);
		$row++;
		$sheet->setCellValue("A{$row}", 'Categorie');
		$sheet->setCellValue("B{$row}", 'Aantal');
		$sheet->setCellValue("C{$row}", 'Bedrag');
		$sheet->getStyle("A{$row}:C{$row}")->getFont()->setBold(true);
		$sheet->getStyle("A{$row}:C{$row}")->getBorders()->getBottom()->setBorderStyle(Border::BORDER_THIN);
		$row++;

		foreach ($data['expenseRows'] as $r) {
			$sheet->setCellValue("A{$row}", $r['category']);
			$sheet->setCellValue("B{$row}", $r['count']);
			$sheet->setCellValue("C{$row}", (float) $r['total']);
			$row++;
		}
		$sheet->setCellValue("A{$row}", 'Totaal kosten');
		$sheet->setCellValue("C{$row}", (float) $data['expenseTotal']);
		$sheet->getStyle("A{$row}:C{$row}")->getFont()->setBold(true);
		$sheet->getStyle("A{$row}:C{$row}")->getBorders()->getTop()->setBorderStyle(Border::BORDER_THIN);
		$row += 2;

		// Result
		$sheet->setCellValue("A{$row}", 'Resultaat');
		$sheet->setCellValue("C{$row}", (float) $data['result']);
		$sheet->getStyle("A{$row}:C{$row}")->getFont()->setBold(true)->setSize(12);
		$sheet->getStyle("A{$row}:C{$row}")->getBorders()->getTop()->setBorderStyle(Border::BORDER_DOUBLE);

		// Format currency column C
		$sheet->getStyle("C1:C{$row}")->getNumberFormat()
			->setFormatCode('"€ "#,##0.00;"€ -"#,##0.00');

		$sheet->getColumnDimension('A')->setWidth(40);
		$sheet->getColumnDimension('B')->setWidth(10);
		$sheet->getColumnDimension('C')->setWidth(18);

		return $this->writeToTemp($spreadsheet, 'winst-verlies-' . $data['from'] . '-' . $data['to']);
	}

	public function vatReturn(string $tenantId, array $data): string
	{
		$settings = BookkeepingTenantSettings::find($tenantId);

		$spreadsheet = new Spreadsheet();
		$sheet = $spreadsheet->getActiveSheet();
		$sheet->setTitle('BTW-aangifte Q' . $data['quarter'] . ' ' . $data['year']);

		$row = 1;
		$sheet->setCellValue("A{$row}", 'BTW-aangifte');
		$sheet->getStyle("A{$row}")->getFont()->setBold(true)->setSize(14);
		$row++;

		$sheet->setCellValue("A{$row}", 'Bedrijf:');
		$sheet->setCellValue("B{$row}", $settings?->company_name ?? '—');
		$row++;
		$sheet->setCellValue("A{$row}", 'BTW-nummer:');
		$sheet->setCellValue("B{$row}", $settings?->vat_number ?? '—');
		$row++;
		$sheet->setCellValue("A{$row}", 'Periode:');
		$sheet->setCellValue("B{$row}", 'Q' . $data['quarter'] . ' ' . $data['year'] . ' (' . $data['from'] . ' — ' . $data['to'] . ')');
		$row++;
		$sheet->setCellValue("A{$row}", 'Export gegenereerd:');
		$sheet->setCellValue("B{$row}", CarbonImmutable::now()->format('d-m-Y H:i'));
		$row += 2;

		// Rubriek 1 — omzet per tarief
		$sheet->setCellValue("A{$row}", '1. Prestaties binnenland — omzet per BTW-tarief');
		$sheet->getStyle("A{$row}")->getFont()->setBold(true)->setSize(12);
		$row++;
		$sheet->setCellValue("A{$row}", 'Tarief');
		$sheet->setCellValue("B{$row}", 'Omzet excl. BTW');
		$sheet->setCellValue("C{$row}", 'BTW-bedrag');
		$sheet->setCellValue("D{$row}", 'Omzet incl. BTW');
		$sheet->setCellValue("E{$row}", 'Aantal');
		$sheet->getStyle("A{$row}:E{$row}")->getFont()->setBold(true);
		$sheet->getStyle("A{$row}:E{$row}")->getBorders()->getBottom()->setBorderStyle(Border::BORDER_THIN);
		$row++;

		foreach ($data['incomeByRate'] as $r) {
			$sheet->setCellValue("A{$row}", rtrim(rtrim(number_format($r['rate'], 2, '.', ''), '0'), '.') . '%');
			$sheet->setCellValue("B{$row}", (float) $r['net']);
			$sheet->setCellValue("C{$row}", (float) $r['vat']);
			$sheet->setCellValue("D{$row}", (float) $r['gross']);
			$sheet->setCellValue("E{$row}", $r['count']);
			$row++;
		}
		$sheet->setCellValue("A{$row}", 'Totaal verschuldigd');
		$sheet->setCellValue("C{$row}", (float) $data['totalVatDue']);
		$sheet->getStyle("A{$row}:E{$row}")->getFont()->setBold(true);
		$sheet->getStyle("A{$row}:E{$row}")->getBorders()->getTop()->setBorderStyle(Border::BORDER_THIN);
		$row += 2;

		// Rubriek 5b — voorbelasting
		$sheet->setCellValue("A{$row}", '5b. Voorbelasting — BTW op inkoop');
		$sheet->getStyle("A{$row}")->getFont()->setBold(true)->setSize(12);
		$row++;
		$sheet->setCellValue("A{$row}", 'Aftrekbare BTW');
		$sheet->setCellValue("C{$row}", (float) $data['deductibleVat']);
		$row++;
		if ($data['nonDeductibleVat'] > 0) {
			$sheet->setCellValue("A{$row}", 'Niet-aftrekbare BTW (info)');
			$sheet->setCellValue("C{$row}", (float) $data['nonDeductibleVat']);
			$row++;
		}
		$sheet->setCellValue("A{$row}", 'Kosten excl. BTW');
		$sheet->setCellValue("C{$row}", (float) $data['expenseNet']);
		$row++;
		$sheet->setCellValue("A{$row}", 'Aantal kostenposten');
		$sheet->setCellValue("C{$row}", $data['expenseCount']);
		$row += 2;

		// Net payable
		$sheet->setCellValue("A{$row}", $data['netPayable'] >= 0
			? 'Te betalen aan Belastingdienst'
			: 'Te ontvangen van Belastingdienst');
		$sheet->setCellValue("C{$row}", abs((float) $data['netPayable']));
		$sheet->getStyle("A{$row}:C{$row}")->getFont()->setBold(true)->setSize(12);
		$sheet->getStyle("A{$row}:C{$row}")->getBorders()->getTop()->setBorderStyle(Border::BORDER_DOUBLE);

		// Format currency columns B, C, D
		$currency = '"€ "#,##0.00';
		$sheet->getStyle("B1:D{$row}")->getNumberFormat()->setFormatCode($currency);
		$sheet->getStyle("C{$row}")->getNumberFormat()->setFormatCode($currency);

		$sheet->getColumnDimension('A')->setWidth(42);
		$sheet->getColumnDimension('B')->setWidth(18);
		$sheet->getColumnDimension('C')->setWidth(18);
		$sheet->getColumnDimension('D')->setWidth(18);
		$sheet->getColumnDimension('E')->setWidth(10);

		return $this->writeToTemp($spreadsheet, 'btw-aangifte-Q' . $data['quarter'] . '-' . $data['year']);
	}

	private function writeToTemp(Spreadsheet $sheet, string $basename): string
	{
		$dir = storage_path('app/bookkeeping/exports');
		if (! is_dir($dir)) {
			mkdir($dir, 0755, true);
		}
		$path = $dir . '/' . $basename . '-' . bin2hex(random_bytes(4)) . '.xlsx';
		(new Xlsx($sheet))->save($path);
		return $path;
	}
}
