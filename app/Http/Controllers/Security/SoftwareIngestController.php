<?php

namespace App\Http\Controllers\Security;

use App\Http\Controllers\Controller;
use App\Models\Seo\SeoProperty;
use App\Services\Security\SoftwareIngestService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Ontvangt de software-inventaris van de companion-plugin. Auth = per-site
 * token in de URL — geen sessie, dus CSRF-exempt en buiten de locale-prefix
 * (zie bootstrap/app.php). Elke push vervangt de inventaris van de site.
 */
class SoftwareIngestController extends Controller
{
	public function __invoke(Request $request, string $token, SoftwareIngestService $service): JsonResponse
	{
		$site = SeoProperty::query()
			->where('security_ingest_token', $token)
			->where('is_active', true)
			->first();

		if (! $site) {
			return response()->json(['ok' => false, 'error' => 'invalid_token'], 403);
		}

		$data = $request->validate([
			'components'                  => ['required', 'array'],
			'components.*.type'           => ['required', 'string', 'in:core,plugin,theme'],
			'components.*.slug'           => ['required', 'string', 'max:190'],
			'components.*.name'           => ['nullable', 'string', 'max:190'],
			'components.*.version'        => ['nullable', 'string', 'max:40'],
			'components.*.latest_version' => ['nullable', 'string', 'max:40'],
			'components.*.has_update'     => ['nullable', 'boolean'],
			'components.*.active'         => ['nullable', 'boolean'],
		]);

		$result = $service->ingest($site, $data['components']);

		return response()->json(['ok' => true] + $result);
	}
}
