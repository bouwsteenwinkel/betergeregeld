<?php

namespace App\Http\Controllers\Tools;

use App\Http\Controllers\Controller;
use App\Models\ToolEvent;
use App\Services\Features\FeatureResolver;
use App\Services\IbanValidator;
use Illuminate\Http\Request;
use Illuminate\View\View;

class IbanCheckController extends Controller
{
	public function __construct(
		private readonly IbanValidator $validator,
		private readonly FeatureResolver $features,
	) {}

	public function show(Request $request, string $locale): View
	{
		return view('tools.iban-check', [
			'result' => null,
			'input' => ['name' => '', 'iban' => ''],
			'features' => $this->features->forRequest($request->user()),
		]);
	}

	public function check(Request $request, string $locale): View
	{
		$input = $request->validate([
			'name' => ['nullable', 'string', 'max:190'],
			'iban' => ['required', 'string', 'max:50'],
		]);

		$result = $this->validator->check($input['name'] ?? '', $input['iban']);

		ToolEvent::create([
			'tool' => 'iban-check',
			'event' => 'validate',
			'session_id' => substr($request->session()->getId(), 0, 64),
			'meta_json' => [
				'valid' => $result['valid'],
				'risk_count' => count($result['risks']),
			],
		]);

		return view('tools.iban-check', [
			'result' => $result,
			'input' => ['name' => $input['name'] ?? '', 'iban' => $input['iban']],
			'features' => $this->features->forRequest($request->user()),
		]);
	}
}
