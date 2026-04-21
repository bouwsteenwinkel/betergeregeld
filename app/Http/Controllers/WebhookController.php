<?php

namespace App\Http\Controllers;

use App\Services\Billing\BillingService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class WebhookController extends Controller
{
	public function __construct(private readonly BillingService $billing) {}

	/**
	 * Mollie webhook endpoint. Mollie POSTs a single field `id` with the
	 * payment id; we re-fetch the status from Mollie's API (never trust
	 * the webhook body content beyond the id).
	 *
	 * Always returns 200 — Mollie considers 4xx/5xx as "retry please".
	 */
	public function mollie(Request $request): Response
	{
		$paymentId = (string) $request->input('id', '');
		if ($paymentId === '') {
			return response('missing id', 200);
		}

		$this->billing->syncFromGateway($paymentId);

		return response('ok', 200);
	}
}
