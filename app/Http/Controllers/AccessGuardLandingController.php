<?php

namespace App\Http\Controllers;

use App\Services\Features\FeatureResolver;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AccessGuardLandingController extends Controller
{
	public function __construct(private readonly FeatureResolver $features) {}

	public function show(Request $request, string $locale): View
	{
		$user = $request->user();
		$hasAccess = false;
		if ($user) {
			$hasAccess = $this->features->forUser($user)->bool('tool.accessguard.enabled');
		}

		return view('pages.accessguard', [
			'hasAccess' => $hasAccess,
			'isAuthed' => $user !== null,
		]);
	}
}
