<?php

namespace App\Http\Controllers\Tools;

use App\Http\Controllers\Controller;
use App\Models\ToolEvent;
use App\Services\PostcodeValidator;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PostcodeCheckController extends Controller
{
	public function __construct(private readonly PostcodeValidator $validator) {}

	public function show(string $locale): View
	{
		return view('tools.postcode-check', [
			'result' => null,
			'input' => ['postcode' => '', 'city' => '', 'street' => '', 'house_number' => ''],
		]);
	}

	public function check(Request $request, string $locale): View
	{
		$input = $request->validate([
			'postcode' => ['nullable', 'string', 'max:16'],
			'city' => ['nullable', 'string', 'max:120'],
			'street' => ['nullable', 'string', 'max:160'],
			'house_number' => ['nullable', 'string', 'max:16'],
		]);

		$result = $this->validator->check(
			$input['postcode'] ?? '',
			$input['city'] ?? '',
			$input['street'] ?? '',
			$input['house_number'] ?? '',
		);

		ToolEvent::create([
			'tool' => 'postcode-check',
			'event' => 'validate',
			'session_id' => substr($request->session()->getId(), 0, 64),
			'meta_json' => [
				'valid' => $result['valid'],
				'country' => $result['country'],
				'errors' => count($result['errors']),
				'warnings' => count($result['warnings']),
			],
		]);

		return view('tools.postcode-check', [
			'result' => $result,
			'input' => [
				'postcode' => $input['postcode'] ?? '',
				'city' => $input['city'] ?? '',
				'street' => $input['street'] ?? '',
				'house_number' => $input['house_number'] ?? '',
			],
		]);
	}
}
