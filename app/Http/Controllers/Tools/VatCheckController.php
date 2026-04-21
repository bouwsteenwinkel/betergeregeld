<?php

namespace App\Http\Controllers\Tools;

use App\Http\Controllers\Controller;
use App\Models\ToolEvent;
use App\Services\VatChecker;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VatCheckController extends Controller
{
	public function __construct(private readonly VatChecker $checker) {}

	public function show(string $locale): View
	{
		return view('tools.vat-check', [
			'result' => null,
			'input' => ['country' => '', 'vat' => ''],
		]);
	}

	public function check(Request $request, string $locale): View
	{
		$input = $request->validate([
			'country' => ['nullable', 'string', 'max:2'],
			'vat' => ['required', 'string', 'max:32'],
		]);

		$result = $this->checker->check($input['country'] ?? '', $input['vat']);

		ToolEvent::create([
			'tool' => 'vat-check',
			'event' => 'validate',
			'session_id' => substr($request->session()->getId(), 0, 64),
			'meta_json' => [
				'ok' => $result['ok'],
				'valid' => $result['ok'] ? ($result['vies']['valid'] ?? false) : null,
				'country' => $result['input']['country'] ?? null,
			],
		]);

		return view('tools.vat-check', [
			'result' => $result,
			'input' => [
				'country' => $input['country'] ?? '',
				'vat' => $input['vat'],
			],
		]);
	}
}
