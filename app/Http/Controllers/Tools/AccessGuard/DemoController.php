<?php

namespace App\Http\Controllers\Tools\AccessGuard;

use App\Http\Controllers\Controller;
use App\Models\AccessGuard\AccessCell;
use App\Models\AccessGuard\BusinessSystem;
use App\Models\AccessGuard\Cycle;
use App\Models\AccessGuard\CycleItem;
use App\Models\AccessGuard\Person;
use App\Models\AccessGuard\RiskFlag;
use App\Services\AccessGuard\DemoSeeder;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class DemoController extends Controller
{
	public function __construct(private readonly DemoSeeder $seeder) {}

	public function show(Request $request, string $locale): View
	{
		$tenantId = (string) config('accessguard.demo.tenant_id');
		$this->ensureSeeded($tenantId);

		$people = Person::query()
			->where('tenant_id', $tenantId)
			->orderByRaw("CASE status WHEN 'active' THEN 0 ELSE 1 END")
			->orderBy('job_title')
			->get();

		$systems = BusinessSystem::query()
			->where('tenant_id', $tenantId)
			->orderBy('name')
			->get();

		$cellMap = AccessCell::query()
			->where('tenant_id', $tenantId)
			->get()
			->keyBy(fn ($c) => $c->person_id . ':' . $c->system_id);

		$cycle = Cycle::query()
			->where('tenant_id', $tenantId)
			->orderByDesc('created_at')
			->first();

		$cycleItems = $cycle
			? CycleItem::query()
				->where('cycle_id', $cycle->id)
				->orderBy('person_label')
				->orderBy('system_label')
				->limit(8)
				->get()
			: collect();

		$risks = RiskFlag::query()
			->where('tenant_id', $tenantId)
			->where('status', 'open')
			->orderByDesc('severity')
			->get();

		return view('tools.accessguard.demo.show', [
			'people' => $people,
			'systems' => $systems,
			'cellMap' => $cellMap,
			'cycle' => $cycle,
			'cycleItems' => $cycleItems,
			'risks' => $risks,
			'counts' => [
				'people' => $people->count(),
				'systems' => $systems->count(),
				'has_access' => $cellMap->where('access_state', 'has_access')->count(),
				'risks' => $risks->count(),
			],
		]);
	}

	protected function ensureSeeded(string $tenantId): void
	{
		$key = 'ag_demo_seeded:' . $tenantId;
		$ttl = (int) config('accessguard.demo.reseed_after_hours', 24) * 3600;

		if (Cache::has($key) && $this->seeder->isDemoSeeded($tenantId)) {
			return;
		}

		$this->seeder->seed($tenantId, (string) config('accessguard.demo.actor_user_id'));
		Cache::put($key, CarbonImmutable::now()->toIso8601String(), $ttl);
	}
}
