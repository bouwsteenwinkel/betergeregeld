@php
    // ->locale('nl') expliciet: deze mail vertrekt vanuit de scheduler (CLI), waar
    // SetLocale niet draait en app.locale dus op 'en' staat. Zonder dit staat er
    // "Thursday 16 July" midden in een Nederlandse mail.
    $start = \Carbon\Carbon::parse($appt->starts_at)->setTimezone($tz)->locale('nl');
@endphp
<!DOCTYPE html>
<html lang="nl">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"></head>
<body style="margin:0;background:#f4f7fa;font-family:Arial,Helvetica,sans-serif;color:#1c2530">
    <div style="max-width:560px;margin:0 auto;padding:28px 20px">
        <div style="background:#fff;border-radius:14px;padding:28px 26px;box-shadow:0 10px 30px -18px rgba(0,0,0,.3)">
            @if ($kind === 'day_of')
                <h1 style="font-size:20px;margin:0 0 6px">Vandaag spreken we elkaar</h1>
                <p style="margin:0 0 18px;color:#5b6b78">Hoi {{ $appt->name }}, je gesprek staat vandaag om {{ $start->format('H:i') }} uur. Tot straks!</p>
            @else
                <h1 style="font-size:20px;margin:0 0 6px">Herinnering aan je afspraak</h1>
                {{-- Geen "over twee dagen": lead_days is instelbaar en na scheduler-uitval
                     kan deze mail later vertrekken dan bedoeld. De datum liegt nooit. --}}
                <p style="margin:0 0 18px;color:#5b6b78">Hoi {{ $appt->name }}, we spreken elkaar {{ $start->translatedFormat('l j F') }}. Even een seintje, zodat het je niet overvalt.</p>
            @endif

            <table style="width:100%;border-collapse:collapse;font-size:15px">
                <tr><td style="padding:8px 0;color:#5b6b78;width:110px">Datum</td><td style="padding:8px 0;font-weight:700">{{ $start->translatedFormat('l j F Y') }}</td></tr>
                <tr><td style="padding:8px 0;color:#5b6b78">Tijd</td><td style="padding:8px 0;font-weight:700">{{ $start->format('H:i') }} uur</td></tr>
                <tr><td style="padding:8px 0;color:#5b6b78">Vorm</td><td style="padding:8px 0;font-weight:700">Online via Google Meet</td></tr>
            </table>

            @if ($appt->meet_url)
                <p style="margin:20px 0 8px">Deelnemen aan het gesprek:</p>
                <p style="margin:0 0 4px">
                    <a href="{{ $appt->meet_url }}" style="display:inline-block;background:#f59e0b;color:#1c2530;font-weight:700;text-decoration:none;padding:12px 22px;border-radius:6px">Open Google Meet</a>
                </p>
                <p style="margin:8px 0 0;font-size:13px;color:#5b6b78;word-break:break-all">{{ $appt->meet_url }}</p>
            @else
                <p style="margin:20px 0 0;color:#5b6b78">De Google Meet-link sturen we je vlak voor het gesprek toe.</p>
            @endif

            @if ($cancelUrl)
                <p style="margin:22px 0 0;font-size:13px;color:#5b6b78">Komt het toch niet uit? Je kunt je afspraak zelf
                    <a href="{{ $cancelUrl }}" style="color:#1685c4">verzetten of annuleren</a>. Dat scheelt ons allebei een lege plek in de agenda.</p>
            @else
                <p style="margin:22px 0 0;font-size:13px;color:#5b6b78">Komt het toch niet uit? Beantwoord deze mail, dan verzetten we het.</p>
            @endif
        </div>
        <p style="text-align:center;color:#98a6b3;font-size:12px;margin:16px 0 0">{{ config('scheduling.organizer_name', 'Betergeregeld ICT') }}</p>
    </div>
</body>
</html>
