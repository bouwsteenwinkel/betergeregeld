<?php

namespace App\Http\Controllers\Tools;

use App\Http\Controllers\Controller;
use App\Services\Dns\DomainInspector;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DnsInspectorController extends Controller
{
	public function __construct(private readonly DomainInspector $inspector) {}

	public function show(Request $request, string $locale): View
	{
		return view('tools.dns-inspector', [
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
			return view('tools.dns-inspector', [
				'domain' => $input['domain'],
				'result' => null,
				'error'  => $e->getMessage(),
			]);
		}

		$types = [
			'A'     => DNS_A,
			'AAAA'  => DNS_AAAA,
			'MX'    => DNS_MX,
			'NS'    => DNS_NS,
			'TXT'   => DNS_TXT,
			'CNAME' => DNS_CNAME,
			'SOA'   => DNS_SOA,
		];

		$result = ['domain' => $domain, 'records' => []];
		foreach ($types as $name => $type) {
			$rows = $this->inspector->dnsRecords($domain, $type);
			$result['records'][$name] = $rows;
		}

		return view('tools.dns-inspector', [
			'domain' => $domain,
			'result' => $result,
			'error'  => null,
		]);
	}
}
