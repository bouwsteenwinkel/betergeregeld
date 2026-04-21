<?php

namespace App\Services\Billing;

use App\Models\BillingIntent;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use Mollie\Laravel\Facades\Mollie;

/**
 * Thin wrapper around the Mollie Payments API.
 *
 * When MOLLIE_KEY is empty or starts with 'fake_', creation returns a local
 * redirect URL that points at our own /billing/fake-confirm endpoint — this
 * lets the full checkout flow be tested locally without ngrok/HTTPS.
 * Production picks up a real test_/live_ key automatically.
 */
class MollieGateway
{
	public function __construct(private readonly ?string $apiKey = null) {}

	public function isFake(): bool
	{
		$key = $this->apiKey ?: (string) config('mollie.key', env('MOLLIE_KEY', ''));
		// Empty, fake_-prefixed, or the package's default placeholder (contains 'xxxx').
		return $key === ''
			|| str_starts_with($key, 'fake_')
			|| str_contains($key, 'xxxx');
	}

	/**
	 * Create a Mollie payment for this intent and return the checkout URL + Mollie id.
	 * @return array{checkout_url:string, mollie_payment_id:string}
	 */
	public function createPayment(BillingIntent $intent, string $description): array
	{
		$returnUrl = URL::route('billing.return', ['locale' => app()->getLocale(), 'intent' => $intent->id]);

		if ($this->isFake()) {
			$fakeId = 'tr_fake_' . bin2hex(random_bytes(6));
			$checkoutUrl = URL::route('billing.fake-confirm', ['locale' => app()->getLocale(), 'intent' => $intent->id]);
			Log::info('MollieGateway: fake-mode payment', [
				'intent' => $intent->id, 'amount' => $intent->amount, 'description' => $description,
			]);
			return ['checkout_url' => $checkoutUrl, 'mollie_payment_id' => $fakeId];
		}

		$payment = Mollie::api()->payments->create([
			'amount' => [
				'currency' => $intent->currency,
				'value' => number_format((float) $intent->amount, 2, '.', ''),
			],
			'description' => $description,
			'redirectUrl' => $returnUrl,
			'webhookUrl' => URL::route('webhooks.mollie'),
			'metadata' => [
				'intent_id' => $intent->id,
				'plan_id' => $intent->plan_id,
				'tenant_id' => $intent->tenant_id,
				'period' => $intent->period,
			],
		]);

		return [
			'checkout_url' => (string) $payment->getCheckoutUrl(),
			'mollie_payment_id' => $payment->id,
		];
	}

	/**
	 * Fetch the latest status from Mollie. Returns one of:
	 *   open, pending, authorized, paid, canceled, expired, failed
	 * For fake mode, always returns 'paid' (fake-confirm endpoint flips status locally).
	 */
	public function fetchStatus(string $molliePaymentId): string
	{
		if ($this->isFake() || str_starts_with($molliePaymentId, 'tr_fake_')) {
			return 'paid';
		}
		$payment = Mollie::api()->payments->get($molliePaymentId);
		return (string) $payment->status;
	}
}
