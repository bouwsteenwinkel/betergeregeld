<?php

namespace App\Http\Controllers\Tools;

use App\Http\Controllers\Controller;
use App\Services\Dns\DomainInspector;
use App\Services\Dns\SslCertChecker;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SslCheckController extends Controller
{
	public function __construct(
		private readonly DomainInspector $inspector,
		private readonly SslCertChecker $checker,
	) {}

	public function show(Request $request, string $locale): View
	{
		return view('tools.ssl-check', [
			'domain' => '',
			'result' => null,
			'error'  => null,
		]);
	}

	public function check(Request $request, string $locale): View
	{
		$input = $request->validate([
			'domain' => ['required', 'string', 'max:253'],
		]);

		try {
			$domain = $this->inspector->normalizeDomain($input['domain']);
		} catch (\InvalidArgumentException $e) {
			return view('tools.ssl-check', [
				'domain' => $input['domain'],
				'result' => null,
				'error'  => $e->getMessage(),
			]);
		}

		return view('tools.ssl-check', [
			'domain' => $domain,
			'result' => $this->checker->check($domain),
			'error'  => null,
		]);
	}
}
