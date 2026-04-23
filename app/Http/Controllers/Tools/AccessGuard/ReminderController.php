<?php

namespace App\Http\Controllers\Tools\AccessGuard;

use App\Http\Controllers\Controller;
use App\Models\AccessGuard\Reminder;
use App\Services\AccessGuard\ReminderBuilder;
use App\Services\Features\FeatureResolver;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReminderController extends Controller
{
	public function __construct(
		private readonly FeatureResolver $features,
		private readonly ReminderBuilder $builder,
	) {}

	public function index(Request $request, string $locale): View
	{
		$this->mustHaveAccess($request);

		$status = $request->query('status', 'open');
		$reminders = Reminder::query()
			->where('tenant_id', $request->user()->tenant_id)
			->when(in_array($status, Reminder::STATUSES, true), fn ($q) => $q->where('status', $status))
			->orderBy('due_at')
			->orderByDesc('created_at')
			->paginate(50);

		return view('tools.accessguard.reminders.index', compact('reminders', 'status'));
	}

	public function buildNow(Request $request, string $locale): RedirectResponse
	{
		$this->mustHaveAccess($request);
		$counts = $this->builder->build($request->user()->tenant_id);
		$total = array_sum($counts);
		return back()->with('status', __(':n reminders gegenereerd.', ['n' => $total]));
	}

	public function markDone(Request $request, string $locale, int $id): RedirectResponse
	{
		$this->mustHaveAccess($request);
		$reminder = $this->findOwned($request, $id);
		$reminder->update(['status' => 'done']);
		return back()->with('status', __('Reminder als klaar gemarkeerd.'));
	}

	public function dismiss(Request $request, string $locale, int $id): RedirectResponse
	{
		$this->mustHaveAccess($request);
		$reminder = $this->findOwned($request, $id);
		$reminder->update([
			'status' => 'dismissed',
			'dismissed_at' => CarbonImmutable::now(),
			'dismissed_by_user_id' => (string) $request->user()->getAuthIdentifier(),
		]);
		return back()->with('status', __('Reminder verwijderd.'));
	}

	private function findOwned(Request $request, int $id): Reminder
	{
		return Reminder::query()
			->where('tenant_id', $request->user()->tenant_id)
			->findOrFail($id);
	}

	protected function mustHaveAccess(Request $request): void
	{
		abort_unless($request->user(), 401);
		$bag = $this->features->forUser($request->user());
		abort_unless($bag->bool('tool.accessguard.enabled'), 402);
	}
}
