<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class ServiceController extends Controller
{
	public function index(string $locale): View
	{
		return view('pages.services-index', [
			'services' => config('services_catalog'),
		]);
	}

	public function show(string $locale, string $slug): View
	{
		$service = config("services_catalog.$slug");
		abort_if(! $service, 404);

		return view('pages.service', [
			'slug' => $slug,
			'service' => $service,
		]);
	}
}
