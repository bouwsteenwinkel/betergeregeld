<?php

namespace App\Services\AccessGuard;

use App\Models\AccessGuard\AccessCell;
use App\Models\AccessGuard\BusinessSystem;
use App\Models\AccessGuard\Cycle;
use App\Models\AccessGuard\Person;
use App\Models\AccessGuard\ReviewLogEntry;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CsvExportService
{
	public function matrixWide(string $tenantId): StreamedResponse
	{
		return $this->streamCsv('accessguard-matrix-wide.csv', function ($handle) use ($tenantId) {
			$people = Person::query()->where('tenant_id', $tenantId)->orderBy('last_name')->orderBy('first_name')->get();
			$systems = BusinessSystem::query()->where('tenant_id', $tenantId)->where('is_active', true)->orderBy('name')->get();

			$cells = AccessCell::query()->where('tenant_id', $tenantId)->get()
				->groupBy(fn ($c) => $c->person_id . '_' . $c->system_id);

			// Header row
			$header = ['Person ID', 'First name', 'Middle name', 'Last name', 'Email', 'Status'];
			foreach ($systems as $s) $header[] = $s->name;
			fputcsv($handle, $header, ',', '"', '\\');

			foreach ($people as $p) {
				$row = [$p->id, $p->first_name, $p->middle_name, $p->last_name, $p->email, $p->status];
				foreach ($systems as $s) {
					$cell = $cells->get($p->id . '_' . $s->id)?->first();
					$row[] = $cell?->access_state ?? 'unknown';
				}
				fputcsv($handle, $row, ',', '"', '\\');
			}
		});
	}

	public function matrixLong(string $tenantId): StreamedResponse
	{
		return $this->streamCsv('accessguard-matrix-long.csv', function ($handle) use ($tenantId) {
			fputcsv($handle, [
				'person_id', 'person_full_name', 'person_status', 'email',
				'system_id', 'system_name', 'system_category',
				'access_state', 'last_verified_at',
			], ',', '"', '\\');

			AccessCell::query()
				->where('tenant_id', $tenantId)
				->join('accessguard_people as p', 'p.id', '=', 'accessguard_access_cells.person_id')
				->join('accessguard_systems as s', 's.id', '=', 'accessguard_access_cells.system_id')
				->select([
					'p.id as person_id', 'p.first_name', 'p.middle_name', 'p.last_name', 'p.status as person_status', 'p.email',
					's.id as system_id', 's.name as system_name', 's.category',
					'accessguard_access_cells.access_state', 'accessguard_access_cells.last_verified_at',
				])
				->orderBy('p.last_name')->orderBy('s.name')
				->chunk(500, function ($rows) use ($handle) {
					foreach ($rows as $r) {
						$fullName = trim($r->first_name . ' ' . ($r->middle_name ?? '') . ' ' . $r->last_name);
						fputcsv($handle, [
							$r->person_id, $fullName, $r->person_status, $r->email,
							$r->system_id, $r->system_name, $r->category,
							$r->access_state, $r->last_verified_at,
						], ',', '"', '\\');
					}
				});
		});
	}

	public function cycleLog(string $tenantId, int $cycleId): StreamedResponse
	{
		$cycle = Cycle::query()->where('tenant_id', $tenantId)->findOrFail($cycleId);

		return $this->streamCsv('accessguard-cycle-' . $cycleId . '-log.csv', function ($handle) use ($tenantId, $cycleId) {
			fputcsv($handle, [
				'occurred_at', 'event', 'person_id', 'system_id', 'actor_user_id', 'payload',
			], ',', '"', '\\');

			ReviewLogEntry::query()
				->where('tenant_id', $tenantId)
				->where('cycle_id', $cycleId)
				->orderBy('created_at')
				->chunk(500, function ($rows) use ($handle) {
					foreach ($rows as $l) {
						fputcsv($handle, [
							$l->created_at?->toIso8601String(),
							$l->event,
							$l->person_id,
							$l->system_id,
							$l->actor_user_id,
							json_encode($l->payload ?? [], JSON_UNESCAPED_UNICODE),
						], ',', '"', '\\');
					}
				});
		});
	}

	private function streamCsv(string $filename, callable $writer): StreamedResponse
	{
		return new StreamedResponse(function () use ($writer) {
			$handle = fopen('php://output', 'w');
			// UTF-8 BOM so Excel opens it correctly
			fwrite($handle, "\xEF\xBB\xBF");
			$writer($handle);
			fclose($handle);
		}, 200, [
			'Content-Type' => 'text/csv; charset=utf-8',
			'Content-Disposition' => 'attachment; filename="' . $filename . '"',
			'Cache-Control' => 'no-store',
		]);
	}
}
