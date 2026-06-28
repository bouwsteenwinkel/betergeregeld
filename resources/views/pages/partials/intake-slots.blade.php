<div class="rounded-2xl border border-[#e7e7ea] bg-white p-6">
    <h2 class="text-lg font-semibold mb-1">Kies een tijdslot</h2>
    <p class="text-sm text-[#666] mb-4">Binnen 1–2 werkdagen — zo zit je voorbeeld-website snel klaar.</p>
    @error('appointment_slot')<p class="text-xs text-red-600 mb-2">{{ $message }}</p>@enderror

    @forelse ($slotDays as $day)
        <div class="mb-4">
            <div class="text-sm font-semibold text-[#111] mb-2 capitalize">{{ $day['label'] }}</div>
            <div class="flex flex-wrap gap-2">
                @foreach ($day['slots'] as $slot)
                    <label class="cursor-pointer">
                        <input type="radio" name="appointment_slot" value="{{ $slot['value'] }}" class="peer sr-only" @checked(old('appointment_slot') === $slot['value']) required>
                        <span class="inline-block rounded-lg border border-[#dcdce0] px-4 py-2 text-sm peer-checked:bg-[#111] peer-checked:text-white peer-checked:border-[#111] hover:border-[#111]/50">{{ $slot['time'] }}</span>
                    </label>
                @endforeach
            </div>
        </div>
    @empty
        <p class="text-sm text-[#666]">Geen slots beschikbaar — bel of mail ons, dan plannen we het direct.</p>
    @endforelse
</div>
