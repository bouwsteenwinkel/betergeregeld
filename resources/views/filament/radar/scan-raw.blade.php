@php
	/** @var \App\Models\AccessGuard\Radar\Scan $scan */
	$payload = $scan->raw_output ?? [];
@endphp

<div class="space-y-4">
	@if (! empty($payload['errors']))
		<div>
			<div class="text-sm font-medium text-danger-600 dark:text-danger-400">Errors</div>
			<pre class="text-xs bg-danger-50 dark:bg-danger-950/30 rounded p-3 overflow-x-auto">{{ json_encode($payload['errors'], JSON_PRETTY_PRINT) }}</pre>
		</div>
	@endif

	<div>
		<div class="text-sm font-medium">Detections ({{ count($payload['detections'] ?? []) }})</div>
		<pre class="text-xs bg-gray-50 dark:bg-gray-900 rounded p-3 overflow-x-auto">{{ json_encode($payload['detections'] ?? [], JSON_PRETTY_PRINT) }}</pre>
	</div>
</div>
