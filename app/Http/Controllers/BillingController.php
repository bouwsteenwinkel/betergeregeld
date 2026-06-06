<?php

namespace App\Http\Controllers;

use App\Models\BillingIntent;
use App\Services\Billing\BillingService;
use App\Services\Billing\MollieGateway;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\View\View;

class BillingController extends Controller
{
	public function __construct(
		private readonly BillingService $billing,
		private readonly MollieGateway $gateway,
	) {}

	public function startTrial(Request $request, string $locale): RedirectResponse
	{
		$data = $request->validate([
			'plan' => ['required', 'string', 'in:tools_pro,tools_business'],
		]);

		$sub = $this->billing->startTrial($request->user(), $data['plan']);

		return redirect()
			->route('dashboard', ['locale' => $locale])
			->with('billing_message', __('Proefperiode geactiveerd, geniet van :plan tot :date.', [
				'plan' => $sub->plan->name,
				'date' => $sub->trial_ends_at->format('d-m-Y'),
			]));
	}

	public function startCheckout(Request $request, string $locale): RedirectResponse
	{
		$data = $request->validate([
			'plan' => ['required', 'string', 'in:tools_pro,tools_business'],
			'period' => ['nullable', 'in:monthly,yearly'],
		]);

		$intent = $this->billing->startCheckout(
			$request->user(),
			$data['plan'],
			$data['period'] ?? 'monthly',
		);

		return redirect()->away($intent->mollie_checkout_url);
	}

	public function return(Request $request, string $locale, string $intent): View|RedirectResponse
	{
		$intentModel = BillingIntent::where('id', $intent)
			->where('tenant_id', $request->user()->tenant_id)
			->with('plan')
			->firstOrFail();

		// Nudge Mollie to check status (cheap no-op for fake mode, single GET for real mode)
		if ($intentModel->isPending() && $intentModel->mollie_payment_id) {
			$this->billing->syncFromGateway($intentModel->mollie_payment_id);
			$intentModel->refresh();
		}

		if ($intentModel->isPaid()) {
			return redirect()
				->route('dashboard', ['locale' => $locale])
				->with('billing_message', __('Betaling gelukt, :plan is nu actief.', [
					'plan' => $intentModel->plan->name,
				]));
		}

		return view('pages.billing.return', [
			'intent' => $intentModel,
			'isFakeMode' => $this->gateway->isFake(),
		]);
	}

	/**
	 * Dev-mode only: simulates the Mollie webhook locally. Reached via the
	 * fake checkout URL produced by MollieGateway::createPayment when no
	 * real key is configured.
	 */
	public function fakeConfirm(Request $request, string $locale, string $intent): RedirectResponse
	{
		abort_unless($this->gateway->isFake() || App::environment('local'), 404);

		$intentModel = BillingIntent::where('id', $intent)
			->where('tenant_id', $request->user()->tenant_id)
			->firstOrFail();

		if ($intentModel->mollie_payment_id) {
			$this->billing->syncFromGateway($intentModel->mollie_payment_id);
		}

		return redirect()->route('billing.return', ['locale' => $locale, 'intent' => $intent]);
	}
}
