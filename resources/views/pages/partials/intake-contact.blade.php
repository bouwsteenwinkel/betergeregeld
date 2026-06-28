@php
    $inp = 'w-full rounded-lg border border-[#dcdce0] bg-white px-3 py-2.5 text-sm focus:border-[#111] focus:ring-2 focus:ring-[#111]/10 outline-none';
    $lbl = 'block text-sm font-medium text-[#333] mb-1.5';
@endphp
<div class="rounded-2xl border border-[#e7e7ea] bg-white p-6">
    <h2 class="text-lg font-semibold mb-4">Contact & afspraak-vorm</h2>
    <div class="grid sm:grid-cols-2 gap-4">
        <div>
            <label class="{{ $lbl }}">Naam *</label>
            <input type="text" name="contact_name" value="{{ old('contact_name') }}" class="{{ $inp }}" required>
            @error('contact_name')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="{{ $lbl }}">E-mail *</label>
            <input type="email" name="email" value="{{ old('email') }}" class="{{ $inp }}" required>
            @error('email')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="{{ $lbl }}">Telefoon *</label>
            <input type="text" name="phone" value="{{ old('phone') }}" class="{{ $inp }}" required>
            @error('phone')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
        </div>
        <div class="grid grid-cols-2 gap-3">
            <div>
                <label class="{{ $lbl }}">Postcode *</label>
                <input type="text" name="postcode" value="{{ old('postcode') }}" placeholder="1400 AA" class="{{ $inp }}" required>
                @error('postcode')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="{{ $lbl }}">Plaats</label>
                <input type="text" name="city" value="{{ old('city') }}" class="{{ $inp }}">
            </div>
        </div>
    </div>

    <div class="mt-4">
        <label class="{{ $lbl }}">Hoe wil je het gesprek? *</label>
        <div class="grid sm:grid-cols-2 gap-2">
            <label class="flex items-start gap-2.5 rounded-lg border border-[#e3e3e6] px-3 py-2.5 cursor-pointer hover:border-[#111]/30">
                <input type="radio" name="appointment_pref" value="onsite" @checked(old('appointment_pref') === 'onsite') class="mt-0.5 text-[#111] focus:ring-[#111]/20">
                <span class="text-sm text-[#222]"><strong>Bij ons langs / wij komen</strong><br><span class="text-[#777] text-xs">Binnen {{ $radiusKm }} km van Bussum</span></span>
            </label>
            <label class="flex items-start gap-2.5 rounded-lg border border-[#e3e3e6] px-3 py-2.5 cursor-pointer hover:border-[#111]/30">
                <input type="radio" name="appointment_pref" value="meet" @checked(old('appointment_pref', 'meet') === 'meet') class="mt-0.5 text-[#111] focus:ring-[#111]/20">
                <span class="text-sm text-[#222]"><strong>Online via Google Meet</strong><br><span class="text-[#777] text-xs">Overal, ook buiten de regio</span></span>
            </label>
        </div>
    </div>
</div>
