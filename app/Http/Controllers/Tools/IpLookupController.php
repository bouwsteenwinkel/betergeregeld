<?php

namespace App\Http\Controllers\Tools;

use App\Http\Controllers\Controller;
use App\Models\ToolEvent;
use App\Services\Features\FeatureResolver;
use App\Services\IpLookupService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class IpLookupController extends Controller
{
	public function __construct(
		private readonly IpLookupService $service,
		private readonly FeatureResolver $features,
	) {}

	public function show(Request $request, string $locale): View
	{
		$bag = $this->features->forRequest($request->user());
		$asnDetail = $bag->bool('tool.ip_lookup.asn_detail');

		$result = $this->service->lookup($request, $asnDetail);

		ToolEvent::create([
			'tool' => 'ip-lookup',
			'event' => 'view',
			'session_id' => substr($request->session()->getId(), 0, 64),
			'meta_json' => [
				'version' => $result['ip_version'],
				'private' => $result['is_private'],
				'country' => $result['geo']['country_code'] ?? null,
				'geo_available' => $result['geo_available'],
			],
		]);

		return view('tools.ip-lookup', [
			'result' => $result,
			'features' => $bag,
		]);
	}
}
