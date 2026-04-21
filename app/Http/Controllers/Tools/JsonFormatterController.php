<?php

namespace App\Http\Controllers\Tools;

use App\Http\Controllers\Controller;
use App\Models\ToolEvent;
use App\Services\JsonFormatter;
use Illuminate\Http\Request;
use Illuminate\View\View;

class JsonFormatterController extends Controller
{
	public function __construct(private readonly JsonFormatter $formatter) {}

	public function show(string $locale): View
	{
		return view('tools.json-formatter', [
			'result' => null,
			'input' => ['json' => '', 'mode' => 'format', 'indent' => 2, 'sort_keys' => false],
		]);
	}

	public function run(Request $request, string $locale): View
	{
		$input = $request->validate([
			'json' => ['required', 'string', 'max:1000000'],
			'mode' => ['nullable', 'in:format,minify,validate'],
			'indent' => ['nullable', 'integer', 'min:2', 'max:8'],
			'sort_keys' => ['nullable', 'boolean'],
		]);

		$result = $this->formatter->run(
			$input['json'],
			$input['mode'] ?? 'format',
			(int) ($input['indent'] ?? 2),
			(bool) ($input['sort_keys'] ?? false),
		);

		ToolEvent::create([
			'tool' => 'json-formatter',
			'event' => $input['mode'] ?? 'format',
			'session_id' => substr($request->session()->getId(), 0, 64),
			'meta_json' => [
				'ok' => $result['ok'],
				'bytes_in' => $result['stats']['bytes_in'] ?? null,
			],
		]);

		return view('tools.json-formatter', [
			'result' => $result,
			'input' => [
				'json' => $input['json'],
				'mode' => $input['mode'] ?? 'format',
				'indent' => (int) ($input['indent'] ?? 2),
				'sort_keys' => (bool) ($input['sort_keys'] ?? false),
			],
		]);
	}
}
