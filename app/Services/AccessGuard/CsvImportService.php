<?php

namespace App\Services\AccessGuard;

use App\Models\AccessGuard\BusinessSystem;
use App\Models\AccessGuard\Person;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Two-step CSV import for People and Systems:
 *   1. upload → parse headers + first 5 rows, stash in cache → user maps columns
 *   2. commit → re-read from cache, upsert into DB (idempotent on email / name)
 */
class CsvImportService
{
	public const KIND_PEOPLE = 'people';
	public const KIND_SYSTEMS = 'systems';

	/** Fields the target table recognises, grouped for the mapping UI. */
	public const FIELDS_PEOPLE = [
		'first_name' => 'Voornaam',
		'last_name' => 'Achternaam',
		'middle_name' => 'Tussenvoegsel',
		'email' => 'E-mail',
		'phone' => 'Telefoon',
		'job_title' => 'Functie',
		'department' => 'Afdeling',
		'type' => 'Type (employee/contractor/external)',
		'status' => 'Status (active/scheduled_in/scheduled_out/inactive)',
		'start_date' => 'Startdatum (YYYY-MM-DD)',
		'end_date' => 'Einddatum (YYYY-MM-DD)',
	];

	public const FIELDS_SYSTEMS = [
		'name' => 'Naam',
		'category' => 'Categorie (saas/on_prem/infra/finance/security/comm/other)',
		'notes' => 'Notities',
	];

	public function fieldsFor(string $kind): array
	{
		return $kind === self::KIND_PEOPLE ? self::FIELDS_PEOPLE : self::FIELDS_SYSTEMS;
	}

	/**
	 * Parse uploaded CSV, return preview with headers + first rows,
	 * stash full rows under a cache key for the commit step.
	 *
	 * @return array{key:string, headers:array, preview:array, total_rows:int, suggested_mapping:array}
	 */
	public function preview(string $tenantId, string $kind, string $contents): array
	{
		$rows = $this->parseCsv($contents);
		if (count($rows) < 2) {
			throw new RuntimeException('CSV moet minstens een headerregel + één datarij hebben');
		}

		$headers = array_map('trim', $rows[0]);
		$data = array_slice($rows, 1);
		$preview = array_slice($data, 0, 5);

		$key = 'ag-csv-import:' . $tenantId . ':' . Str::uuid();
		Cache::put($key, [
			'kind' => $kind,
			'tenant_id' => $tenantId,
			'headers' => $headers,
			'rows' => $data,
		], now()->addMinutes(30));

		return [
			'key' => $key,
			'headers' => $headers,
			'preview' => $preview,
			'total_rows' => count($data),
			'suggested_mapping' => $this->suggestMapping($kind, $headers),
		];
	}

	/**
	 * Commit the stashed CSV using the user-confirmed column mapping.
	 *
	 * @param array<string,string> $mapping column_index (as string) → target field
	 * @return array{inserted:int, updated:int, errors:array}
	 */
	public function commit(string $tenantId, string $cacheKey, array $mapping, string $userId): array
	{
		$stash = Cache::get($cacheKey);
		if (! $stash || $stash['tenant_id'] !== $tenantId) {
			throw new RuntimeException('Upload-preview verlopen — upload opnieuw');
		}

		$kind = $stash['kind'];
		$inserted = 0; $updated = 0; $errors = [];

		// Normalise mapping: flip to target field → column index.
		$byField = [];
		foreach ($mapping as $colIdx => $field) {
			if ($field === null || $field === '' || $field === '-') continue;
			$byField[$field] = (int) $colIdx;
		}

		foreach ($stash['rows'] as $rowNum => $row) {
			try {
				$result = $kind === self::KIND_PEOPLE
					? $this->importPersonRow($tenantId, $row, $byField)
					: $this->importSystemRow($tenantId, $row, $byField);
				$result === 'inserted' ? $inserted++ : $updated++;
			} catch (\Throwable $e) {
				$errors[] = "Rij " . ($rowNum + 2) . ": " . $e->getMessage();
				if (count($errors) > 20) {
					$errors[] = '... (meer fouten ingekort)';
					break;
				}
			}
		}

		Cache::forget($cacheKey);

		return ['inserted' => $inserted, 'updated' => $updated, 'errors' => $errors];
	}

	private function importPersonRow(string $tenantId, array $row, array $byField): string
	{
		$first = $this->pick($row, $byField, 'first_name');
		$last = $this->pick($row, $byField, 'last_name');
		if (! $first || ! $last) {
			throw new RuntimeException('first_name en last_name zijn verplicht');
		}
		$email = $this->pick($row, $byField, 'email');

		$existing = null;
		if ($email) {
			$existing = Person::query()->where('tenant_id', $tenantId)->where('email', $email)->first();
		}

		$data = array_filter([
			'type' => $this->pick($row, $byField, 'type') ?: 'employee',
			'first_name' => $first,
			'middle_name' => $this->pick($row, $byField, 'middle_name'),
			'last_name' => $last,
			'email' => $email,
			'phone' => $this->pick($row, $byField, 'phone'),
			'job_title' => $this->pick($row, $byField, 'job_title'),
			'department' => $this->pick($row, $byField, 'department'),
			'status' => $this->pick($row, $byField, 'status') ?: 'active',
			'start_date' => $this->pick($row, $byField, 'start_date'),
			'end_date' => $this->pick($row, $byField, 'end_date'),
		], fn ($v) => $v !== null && $v !== '');

		if ($existing) {
			$existing->update($data);
			return 'updated';
		}
		Person::create($data + ['tenant_id' => $tenantId]);
		return 'inserted';
	}

	private function importSystemRow(string $tenantId, array $row, array $byField): string
	{
		$name = $this->pick($row, $byField, 'name');
		if (! $name) {
			throw new RuntimeException('name is verplicht');
		}

		$existing = BusinessSystem::query()->where('tenant_id', $tenantId)->where('name', $name)->first();

		$data = array_filter([
			'name' => $name,
			'category' => $this->pick($row, $byField, 'category') ?: 'other',
			'notes' => $this->pick($row, $byField, 'notes'),
		], fn ($v) => $v !== null && $v !== '');

		if ($existing) {
			$existing->update($data);
			return 'updated';
		}
		BusinessSystem::create($data + ['tenant_id' => $tenantId, 'is_active' => true]);
		return 'inserted';
	}

	private function pick(array $row, array $byField, string $field): ?string
	{
		if (! isset($byField[$field])) return null;
		$v = $row[$byField[$field]] ?? null;
		if ($v === null) return null;
		$v = trim((string) $v);
		return $v === '' ? null : $v;
	}

	/**
	 * Heuristic column matching by fuzzy comparing headers to known synonyms.
	 * Returns [field => column_index] best guesses.
	 */
	public function suggestMapping(string $kind, array $headers): array
	{
		$synonyms = $kind === self::KIND_PEOPLE ? [
			'first_name' => ['first_name', 'voornaam', 'firstname', 'given name', 'first'],
			'last_name' => ['last_name', 'achternaam', 'lastname', 'surname', 'last', 'family name'],
			'middle_name' => ['middle_name', 'tussenvoegsel', 'middle'],
			'email' => ['email', 'e-mail', 'e-mailadres', 'mail', 'emailadres'],
			'phone' => ['phone', 'telefoon', 'telefoonnummer', 'tel', 'mobile'],
			'job_title' => ['job_title', 'functie', 'title', 'role', 'job'],
			'department' => ['department', 'afdeling', 'dept', 'team'],
			'type' => ['type', 'soort', 'kind'],
			'status' => ['status'],
			'start_date' => ['start_date', 'startdatum', 'start', 'startdate', 'hire_date'],
			'end_date' => ['end_date', 'einddatum', 'end', 'enddate', 'leave_date'],
		] : [
			'name' => ['name', 'naam', 'system', 'app', 'title'],
			'category' => ['category', 'categorie', 'type', 'kind'],
			'notes' => ['notes', 'notities', 'opmerking', 'remarks'],
		];

		$suggested = [];
		foreach ($headers as $idx => $header) {
			$normalized = strtolower(trim(str_replace(['-', '_', ' '], '', $header)));
			foreach ($synonyms as $field => $variants) {
				if (isset($suggested[$field])) continue; // first match wins
				foreach ($variants as $v) {
					if (strtolower(str_replace(['-', '_', ' '], '', $v)) === $normalized) {
						$suggested[$field] = $idx;
						break 2;
					}
				}
			}
		}
		return $suggested;
	}

	private function parseCsv(string $contents): array
	{
		// Strip UTF-8 BOM if present
		if (str_starts_with($contents, "\xEF\xBB\xBF")) {
			$contents = substr($contents, 3);
		}

		// Detect delimiter from the first line
		$firstLine = strtok($contents, "\n");
		$delim = ',';
		foreach ([';', "\t", ','] as $cand) {
			if (substr_count($firstLine, $cand) > substr_count($firstLine, $delim)) {
				$delim = $cand;
			}
		}

		$rows = [];
		$handle = fopen('php://memory', 'r+');
		fwrite($handle, $contents);
		rewind($handle);
		while (($row = fgetcsv($handle, 0, $delim, '"', '\\')) !== false) {
			if ($row === [null] || $row === []) continue; // empty line
			$rows[] = $row;
		}
		fclose($handle);
		return $rows;
	}
}
