<?php

namespace App\Http\Controllers\Tools;

use App\Http\Controllers\Controller;
use App\Services\Dns\SecurityHeadersAnalyzer;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HeadersAnalyzerController extends Controller
{
	public function __construct(private readonly SecurityHeadersAnalyzer $analyzer) {}

	public function show(Request $request, string $locale): View
	{
		return view('tools.headers-analyzer', [
			'url'    => '',
			'result' => null,
		]);
	}

	public function check(Request $request, string $locale): View
	{
		$input = $request->validate([
			'url' => ['required', 'string', 'max:500'],
		]);

		return view('tools.headers-analyzer', [
			'url'    => $input['url'],
			'result' => $this->analyzer->analyze($input['url']),
		]);
	}
}
