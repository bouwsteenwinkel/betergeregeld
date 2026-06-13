<?php

namespace App\Http\Controllers\Monitor;

use App\Http\Controllers\Controller;
use App\Models\Monitor\SocketLabsEvent;
use App\Models\Monitor\SocketLabsStatus;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;

/**
 * Ontvangt SocketLabs event-webhooks (Account-level Event Webhook, V2).
 *
 * Elke notificatie bevat een SecretKey + ServerID; matchen die niet met onze
 * config, dan 401. Validatie-/test-requests en Tracking-events negeren we (we
 * antwoorden wel 200, anders weigert SocketLabs de endpoint). Bezorg-/queue-
 * events landen in socketlabs_events; de evaluatie/alerting draait apart via
 * monitor:check-alerts.
 *
 * Route: POST /monitor/socketlabs/webhook  (CSRF-uitgezonderd in bootstrap/app.php)
 */
class SocketLabsWebhookController extends Controller
{
	public function __invoke(Request $request): Response
	{
		$payload = $request->json()->all();
		if (! is_array($payload) || empty($payload)) {
			// Lege body of niet-JSON: SocketLabs-validatie wil simpelweg 200 zien.
			return response('', 200);
		}

		// Payload kan één event (assoc) of een batch (lijst) zijn.
		$events = array_is_list($payload) ? $payload : [$payload];

		// SecretKey/ServerID zitten per event; we valideren op het eerste event.
		$first = $events[0] ?? [];
		$secret = $this->pick($first, ['SecretKey', 'secretKey']);
		$serverId = $this->pick($first, ['ServerID', 'ServerId', 'serverId']);

		$expectedSecret = config('socketlabs.webhook_secret');
		if ($expectedSecret && ! hash_equals((string) $expectedSecret, (string) $secret)) {
			return response('Invalid secret key', 401);
		}
		$expectedServer = config('socketlabs.server_id');
		if ($expectedServer && (string) $serverId !== '' && (string) $serverId !== (string) $expectedServer) {
			return response('Unexpected server id', 401);
		}

		$storeTracking = (bool) config('socketlabs.store_tracking', false);
		$now = now();
		$rows = [];
		$lastEventAt = null;

		foreach ($events as $e) {
			if (! is_array($e)) {
				continue;
			}
			$type = (string) $this->pick($e, ['Type', 'type']);
			if ($type === '' || strcasecmp($type, 'Validation') === 0) {
				continue; // validatie-/test-event: niet opslaan
			}
			if (! $storeTracking && strcasecmp($type, 'Tracking') === 0) {
				continue; // engagement, geen bezorg-/queue-signaal
			}

			$occurredAt = $this->parseDate($this->pick($e, ['DateTime', 'dateTime']));
			if ($occurredAt && (! $lastEventAt || $occurredAt->gt($lastEventAt))) {
				$lastEventAt = $occurredAt;
			}

			$rows[] = [
				'type'          => substr($type, 0, 32),
				'message_id'    => $this->str($this->pick($e, ['MessageID', 'MessageId', 'messageId'])),
				'server_id'     => $this->str($serverId, 32),
				'to_address'    => $this->str($this->pick($e, ['Address', 'address'])),
				'from_address'  => $this->str($this->pick($e, ['FromAddress', 'fromAddress'])),
				'subject'       => $this->str($this->pick($e, ['Subject', 'subject'])),
				'failure_type'  => $this->str($this->pick($e, ['FailureType', 'failureType']), 32),
				'failure_code'  => $this->str($this->pick($e, ['FailureCode', 'failureCode']), 16),
				'deferral_code' => $this->str($this->pick($e, ['DeferralCode', 'deferralCode']), 16),
				'reason'        => $this->str($this->pick($e, ['Reason', 'reason', 'DiagnosticCode']), 2000),
				'occurred_at'   => ($occurredAt ?? $now)->toDateTimeString(),
				'created_at'    => $now->toDateTimeString(),
			];
		}

		if (! empty($rows)) {
			SocketLabsEvent::query()->insert($rows);
			SocketLabsStatus::instance()->forceFill([
				'last_event_at' => $lastEventAt ?? $now,
			])->save();
		}

		return response('', 200);
	}

	/** Eerste niet-lege waarde uit $data voor één van $keys. */
	private function pick(array $data, array $keys): mixed
	{
		foreach ($keys as $k) {
			if (array_key_exists($k, $data) && $data[$k] !== null && $data[$k] !== '') {
				return $data[$k];
			}
		}
		return null;
	}

	private function str(mixed $v, int $max = 255): ?string
	{
		if ($v === null) {
			return null;
		}
		return substr((string) $v, 0, $max);
	}

	private function parseDate(mixed $v): ?Carbon
	{
		if (! $v) {
			return null;
		}
		try {
			return Carbon::parse((string) $v);
		} catch (\Throwable $e) {
			return null;
		}
	}
}
