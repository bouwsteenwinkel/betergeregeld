<?php

namespace App\Http\Controllers\Tools;

use App\Http\Controllers\Controller;
use App\Models\ToolEvent;
use App\Services\TextDiffService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DiffController extends Controller
{
	public function __construct(private readonly TextDiffService $diff) {}

	public function show(string $locale): View
	{
		return view('tools.diff', [
			'result' => null,
			'input' => ['left' => '', 'right' => ''],
		]);
	}

	public function compare(Request $request, string $locale): View
	{
		$input = $request->validate([
			'left' => ['required', 'string', 'max:500000'],
			'right' => ['required', 'string', 'max:500000'],
		]);

		$result = $this->diff->diff($input['left'], $input['right']);

		ToolEvent::create([
			'tool' => 'diff',
			'event' => 'compare',
			'session_id' => substr($request->session()->getId(), 0, 64),
			'meta_json' => [
				'added' => $result['stats']['added'],
				'removed' => $result['stats']['removed'],
				'bytes' => strlen($input['left']) + strlen($input['right']),
			],
		]);

		return view('tools.diff', [
			'result' => $result,
			'input' => $input,
		]);
	}
}
