{{--
    Mail-omhulsel in de huisstijl van jouw-bedrijfswebsite.nl.

    Bewust tabellen en inline-stijlen: mailclients (Outlook voorop) doen niets met
    externe stylesheets, flexbox of grid. Kleuren komen één op één van de site:
    diepblauw #12386B, inkt #1A1A1A, papier #FAF9F7 en lijn #E5E3DF.

    Slots: $preheader (regel in het inbox-overzicht), $kop, $inhoud, $voet.
--}}
<!doctype html>
<html lang="nl">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>{{ $kop ?? '' }}</title>
</head>
<body style="margin:0;padding:0;background:#F1EFEB;">

{{-- Preheader: wat de inbox toont naast het onderwerp. Onzichtbaar in de mail zelf. --}}
<div style="display:none;max-height:0;overflow:hidden;opacity:0;color:transparent;height:0;width:0">
    {{ $preheader ?? '' }}
</div>

<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#F1EFEB;">
<tr><td align="center" style="padding:28px 12px;">

    <table role="presentation" width="600" cellpadding="0" cellspacing="0" border="0" style="width:600px;max-width:100%;">

        {{-- Kop: merknaam op diepblauw, zoals de knoppen op de site --}}
        <tr><td style="background:#12386B;padding:22px 30px;border-radius:8px 8px 0 0;">
            <div style="font-family:Segoe UI,Helvetica,Arial,sans-serif;font-size:17px;font-weight:700;color:#ffffff;letter-spacing:-0.01em;">
                {{ $merknaam ?? 'Jouw Bedrijfswebsite' }}
            </div>
            <div style="font-family:Segoe UI,Helvetica,Arial,sans-serif;font-size:12px;color:#DCE4F0;margin-top:3px;">
                Websites voor mkb-ondernemers en vakmensen
            </div>
        </td></tr>

        {{-- Inhoud --}}
        <tr><td style="background:#FAF9F7;padding:32px 30px;border-left:1px solid #E5E3DF;border-right:1px solid #E5E3DF;">
            @isset($kop)
                <h1 style="margin:0 0 16px;font-family:Segoe UI,Helvetica,Arial,sans-serif;font-size:23px;line-height:1.25;font-weight:800;color:#1A1A1A;letter-spacing:-0.02em;">
                    {{ $kop }}
                </h1>
            @endisset
            {{ $inhoud }}
        </td></tr>

        {{-- Voet --}}
        <tr><td style="background:#FAF9F7;padding:0 30px 28px;border-left:1px solid #E5E3DF;border-right:1px solid #E5E3DF;border-bottom:1px solid #E5E3DF;border-radius:0 0 8px 8px;">
            <div style="border-top:1px solid #E5E3DF;padding-top:16px;font-family:Segoe UI,Helvetica,Arial,sans-serif;font-size:12px;line-height:1.6;color:#8A8681;">
                {{ $voet ?? '' }}
            </div>
        </td></tr>

    </table>

</td></tr>
</table>
</body>
</html>
