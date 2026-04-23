<?php

namespace App\Http\Controllers\Tools;

use App\Http\Controllers\Controller;
use App\Models\ToolEvent;
use App\Services\LegoElementLookupService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LegoLookupController extends Controller
{
	public function __construct(private readonly LegoElementLookupService $service) {}

	public function show(Request $request, string $locale): View
	{
		return view('tools.lego-lookup', [
			'result' => null,
			'input' => ['element_id' => ''],
		]);
	}

	public function check(Request $request, string $locale): View
	{
		$input = $request->validate([
			'element_id' => ['required', 'string', 'max:16'],
		]);

		$result = $this->service->lookup($input['element_id']);

		ToolEvent::create([
			'tool' => 'lego-lookup',
			'event' => 'lookup',
			'session_id' => substr($request->session()->getId(), 0, 64),
			'meta_json' => [
				'element_id' => $result['element_id'],
				'found' => $result['found'],
				'from_cache' => $result['from_cache'],
			],
		]);

		return view('tools.lego-lookup', [
			'result' => $result,
			'input' => ['element_id' => $input['element_id']],
		]);
	}
}
