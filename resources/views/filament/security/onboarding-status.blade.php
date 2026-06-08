@php
    $done = collect($steps)->where('done', true)->count();
    $total = count($steps);
@endphp

<div class="space-y-4 text-sm text-gray-700 dark:text-gray-300">
    <p>
        <strong class="text-gray-900 dark:text-white">{{ $done }} / {{ $total }}</strong> stappen voltooid.
        De meeste stappen lopen automatisch (uptime, PageSpeed, scans via de planner); alleen GSC-toegang
        en de beveiligingsagent vragen een actie aan klantkant.
    </p>

    <div class="space-y-2">
        @foreach ($steps as $step)
            <div class="flex items-start gap-3 rounded-lg border border-gray-200 p-3 dark:border-white/10">
                @if ($step['done'])
                    <span class="mt-0.5 flex h-5 w-5 flex-none items-center justify-center rounded-full bg-success-500 text-xs font-bold text-white">&check;</span>
                @else
                    <span class="mt-0.5 flex h-5 w-5 flex-none items-center justify-center rounded-full border-2 border-gray-300 text-xs dark:border-white/20">&nbsp;</span>
                @endif
                <div>
                    <div class="font-medium {{ $step['done'] ? 'text-gray-900 dark:text-white' : 'text-gray-500 dark:text-gray-400' }}">{{ $step['label'] }}</div>
                    <div class="text-xs text-gray-500 dark:text-gray-400">{{ $step['hint'] }}</div>
                </div>
            </div>
        @endforeach
    </div>

    <p class="text-xs text-gray-500 dark:text-gray-400">
        Acties staan als knoppen bij de site: <strong>Toegang testen</strong>, <strong>Nu importeren</strong>,
        <strong>Beveiligingsagent</strong>.
    </p>
</div>
