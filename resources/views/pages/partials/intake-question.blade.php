@php
    $key  = $q['key'];
    $type = $q['type'] ?? 'text';
    $name = "answers[$key]";
    $old  = old("answers.$key");
    $cls  = 'w-full rounded-lg border border-[#dcdce0] bg-white px-3 py-2.5 text-sm focus:border-[#111] focus:ring-2 focus:ring-[#111]/10 outline-none';
@endphp
<div>
    <label class="block text-sm font-medium text-[#333] mb-1.5">{{ $q['label'] }}</label>
    @if ($type === 'textarea')
        <textarea name="{{ $name }}" rows="2" class="{{ $cls }}">{{ $old }}</textarea>
    @elseif ($type === 'select')
        <select name="{{ $name }}" class="{{ $cls }}">
            <option value="">— kies —</option>
            @foreach (($q['options'] ?? []) as $ov => $ol)
                <option value="{{ $ov }}" @selected($old === $ov)>{{ $ol }}</option>
            @endforeach
        </select>
    @elseif ($type === 'boolean')
        <select name="{{ $name }}" class="{{ $cls }}">
            <option value="">—</option>
            <option value="ja" @selected($old === 'ja')>Ja</option>
            <option value="nee" @selected($old === 'nee')>Nee</option>
        </select>
    @else
        <input type="text" name="{{ $name }}" value="{{ $old }}" class="{{ $cls }}">
    @endif
</div>
