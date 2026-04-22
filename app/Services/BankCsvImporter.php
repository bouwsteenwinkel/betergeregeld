<?php

namespace App\Services;

use App\Models\BookkeepingTransaction;
use Carbon\CarbonImmutable;
use RuntimeException;

/**
 * Parses a bank CSV into a normalised row set and matches each row
 * against existing transactions for the same tenant.
 *
 * Expected columns (header names matched case-insensitively, with aliases):
 *   date         — datum, transactiedatum, boekdatum
 *   amount       — bedrag, amount, value
 *   description  — omschrijving, description, mededelingen
 *   counterparty — tegenrekening naam, counterparty, naam, begunstigde
 *   iban         — tegenrekening iban, counterparty iban, iban (optional)
 *   reference    — referentie, reference, kenmerk (optional, used for dedupe)
 */
class BankCsvImporter
{
	private const ALIASES = [
		'date' => ['date', 'datum', 'transactiedatum', 'boekdatum', 'transaction date'],
		'amount' => ['amount', 'bedrag', 'value', 'transaction amount'],
		'description' => ['description', 'omschrijving', 'mededelingen', 'mededeling'],
		'counterparty' => ['counterparty', 'tegenrekening naam', 'naam', 'begunstigde', 'name'],
		'iban' => ['iban', 'tegenrekening iban', 'counterparty iban'],
		'reference' => ['reference', 'referentie', 'kenmerk', 'transaction id', 'mutatiesoort'],
	];

	private const DATE_MATCH_DAYS = 3;

	/**
	 * Parse a CSV file into a normalised array of rows.
	 * Returns ['rows' => [...], 'columns_used' => [...]].
	 */
	public function parse(string $csvPath, string $delimiter = ','): array
	{
		if (! is_file($csvPath)) {
			throw new RuntimeException('CSV file not found');
		}

		$fh = fopen($csvPath, 'r');
		if (! $fh) {
			throw new RuntimeException('Could not open CSV');
		}

		// Sniff BOM and skip
		$first3 = fread($fh, 3);
		if ($first3 !== "\xEF\xBB\xBF") {
			rewind($fh);
		}

		$header = fgetcsv($fh, 0, $delimiter);
		if (! $header) {
			fclose($fh);
			throw new RuntimeException('Lege CSV of geen header-regel gevonden');
		}

		$columnMap = $this->mapColumns($header);
		if (! isset($columnMap['date']) || ! isset($columnMap['amount'])) {
			fclose($fh);
			throw new RuntimeException('Kolommen "date" en "amount" verplicht — herkende kolommen: ' . implode(', ', array_keys($columnMap)));
		}

		$rows = [];
		while (($r = fgetcsv($fh, 0, $delimiter)) !== false) {
			if (count($r) === 1 && trim($r[0]) === '') {
				continue;
			}
			$rows[] = $this->normalizeRow($r, $columnMap);
		}
		fclose($fh);

		return [
			'rows' => $rows,
			'columns_used' => array_keys($columnMap),
		];
	}

	/**
	 * Add $tx_match to each parsed row: an existing transaction id if a
	 * plausible match is found, else null.
	 */
	public function matchAgainstExisting(array $rows, string $tenantId): array
	{
		if (empty($rows)) {
			return $rows;
		}

		$minDate = null;
		$maxDate = null;
		foreach ($rows as $r) {
			if (! $r['date']) {
				continue;
			}
			$minDate = $minDate === null || $r['date'] < $minDate ? $r['date'] : $minDate;
			$maxDate = $maxDate === null || $r['date'] > $maxDate ? $r['date'] : $maxDate;
		}
		if (! $minDate || ! $maxDate) {
			return $rows;
		}

		$from = CarbonImmutable::parse($minDate)->subDays(self::DATE_MATCH_DAYS)->toDateString();
		$to = CarbonImmutable::parse($maxDate)->addDays(self::DATE_MATCH_DAYS)->toDateString();

		$candidates = BookkeepingTransaction::query()
			->where('tenant_id', $tenantId)
			->whereBetween('transaction_date', [$from, $to])
			->get(['id', 'transaction_date', 'amount', 'type', 'counterparty', 'description'])
			->map(fn ($tx) => (object) [
				'id' => $tx->id,
				'date' => $tx->transaction_date->toDateString(),
				'amount' => abs((float) $tx->amount),
				'type' => $tx->type,
				'counterparty' => strtolower((string) $tx->counterparty),
				'description' => strtolower((string) $tx->description),
			]);

		foreach ($rows as $idx => $row) {
			$rows[$idx]['match_tx_id'] = null;
			$rows[$idx]['match_score'] = 0;
			if (! $row['date'] || $row['amount'] === null) {
				continue;
			}
			$bestScore = 0;
			$bestMatch = null;
			$rowAbsAmount = abs((float) $row['amount']);
			$rowDate = CarbonImmutable::parse($row['date']);

			foreach ($candidates as $cand) {
				if (abs($cand->amount - $rowAbsAmount) > 0.01) {
					continue; // amount must match within a cent
				}
				$daysDiff = abs(CarbonImmutable::parse($cand->date)->diffInDays($rowDate));
				if ($daysDiff > self::DATE_MATCH_DAYS) {
					continue;
				}
				$score = 60 - (int) ($daysDiff * 8); // 60 for same day, −8 per day off
				$cpLower = strtolower($row['counterparty'] ?? '');
				if ($cpLower !== '' && $cand->counterparty !== '') {
					if ($cand->counterparty === $cpLower) {
						$score += 30;
					} elseif (str_contains($cand->counterparty, $cpLower) || str_contains($cpLower, $cand->counterparty)) {
						$score += 20;
					}
				}
				$descLower = strtolower($row['description'] ?? '');
				if ($descLower !== '' && $cand->description !== '' && str_contains($descLower, $cand->description)) {
					$score += 10;
				}
				if ($score > $bestScore) {
					$bestScore = $score;
					$bestMatch = $cand->id;
				}
			}

			if ($bestMatch) {
				$rows[$idx]['match_tx_id'] = $bestMatch;
				$rows[$idx]['match_score'] = $bestScore;
			}
		}

		return $rows;
	}

	/** @return array<string,int> map normalised-name → column index */
	private function mapColumns(array $header): array
	{
		$map = [];
		foreach ($header as $i => $col) {
			$colNorm = mb_strtolower(trim((string) $col));
			foreach (self::ALIASES as $key => $aliases) {
				if (in_array($colNorm, array_map('mb_strtolower', $aliases), true)) {
					// first match wins
					if (! isset($map[$key])) {
						$map[$key] = $i;
					}
					break;
				}
			}
		}
		return $map;
	}

	/** @return array{date:?string,amount:?float,description:?string,counterparty:?string,iban:?string,reference:?string,type:string,raw:array} */
	private function normalizeRow(array $raw, array $map): array
	{
		$pick = fn (string $key) => isset($map[$key]) ? trim((string) ($raw[$map[$key]] ?? '')) : null;

		$dateRaw = $pick('date');
		$amountRaw = $pick('amount');

		$date = null;
		if ($dateRaw) {
			foreach (['Y-m-d', 'd-m-Y', 'd/m/Y', 'Y/m/d', 'd.m.Y'] as $fmt) {
				try {
					$d = CarbonImmutable::createFromFormat('!' . $fmt, $dateRaw);
					if ($d !== false && $d !== null && $d->format($fmt) === $dateRaw) {
						$date = $d->toDateString();
						break;
					}
				} catch (\Throwable) {
					// continue
				}
			}
		}

		$amount = null;
		if ($amountRaw !== null && $amountRaw !== '') {
			// Handle NL (1.234,56) and en (1,234.56) formats
			$clean = $amountRaw;
			if (preg_match('/^-?\d{1,3}(\.\d{3})*,\d{2}$/', $clean)) {
				$clean = str_replace('.', '', $clean);
				$clean = str_replace(',', '.', $clean);
			} else {
				$clean = str_replace(',', '', $clean);
			}
			if (is_numeric($clean)) {
				$amount = (float) $clean;
			}
		}

		return [
			'date' => $date,
			'amount' => $amount,
			'type' => $amount !== null && $amount < 0 ? 'expense' : 'income',
			'description' => $pick('description'),
			'counterparty' => $pick('counterparty'),
			'iban' => $pick('iban'),
			'reference' => $pick('reference'),
			'raw' => $raw,
		];
	}
}
