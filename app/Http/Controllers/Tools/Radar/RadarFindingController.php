<?php

namespace App\Http\Controllers\Tools\Radar;

use App\Http\Controllers\Controller;
use App\Models\AccessGuard\Radar\Finding;
use App\Services\Features\FeatureResolver;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class RadarFindingController extends Controller
{
	private const ALLOWED_TRANSITIONS = [
		'confirmed', 'in_progress', 'planned',
		'accepted_risk', 'ignored', 'resolved', 'false_positive',
		'new', // reopen
	];

	public function __construct(private readonly FeatureResolver $features) {}

	public function updateStatus(Request $request, string $locale, int $id): RedirectResponse
	{
		RadarAccess::require($request, $this->features);
		$tenantId = $request->user()->tenant_id;

		$finding = Finding::query()
			->where('tenant_id', $tenantId)
			->where('id', $id)
			->firstOrFail();

		$data = $request->validate([
			'status' => 'required|in:' . implode(',', self::ALLOWED_TRANSITIONS),
			'notes'  => 'nullable|string|max:1000',
		]);

		$update = ['status' => $data['status']];

		if (in_array($data['status'], ['resolved', 'patched', 'mitigated'], true)) {
			$update['resolved_at'] = Carbon::now();
		}
		if ($data['status'] === 'new') {
			// Reopen — kap resolved_at af zodat 'm weer in actieve telling valt
			$update['resolved_at'] = null;
		}
		if (! empty($data['notes'])) {
			$update['resolution_notes'] = $data['notes'];
		}

		$finding->update($update);

		return back()->with('status', __('Status bijgewerkt.'));
	}
}
