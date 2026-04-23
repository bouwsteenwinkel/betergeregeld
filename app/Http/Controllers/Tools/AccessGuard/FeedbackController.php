<?php

namespace App\Http\Controllers\Tools\AccessGuard;

use App\Http\Controllers\Controller;
use App\Models\AccessGuard\Feedback;
use App\Services\Features\FeatureResolver;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FeedbackController extends Controller
{
	public function __construct(private readonly FeatureResolver $features) {}

	public function submit(Request $request, string $locale): JsonResponse
	{
		$this->mustHaveAccess($request);

		$data = $request->validate([
			'category' => ['required', 'in:bug,feature,praise,question,other'],
			'message' => ['required', 'string', 'max:4000'],
			'page_url' => ['nullable', 'string', 'max:500'],
		]);

		Feedback::create([
			'tenant_id' => $request->user()->tenant_id,
			'user_id' => (string) $request->user()->getAuthIdentifier(),
			'category' => $data['category'],
			'message' => $data['message'],
			'page_url' => $data['page_url'] ?? $request->headers->get('referer'),
			'user_agent' => substr((string) $request->userAgent(), 0, 500),
			'status' => 'new',
		]);

		return response()->json(['ok' => true]);
	}

	protected function mustHaveAccess(Request $request): void
	{
		abort_unless($request->user(), 401);
		$bag = $this->features->forUser($request->user());
		abort_unless($bag->bool('tool.accessguard.enabled'), 402);
	}
}
