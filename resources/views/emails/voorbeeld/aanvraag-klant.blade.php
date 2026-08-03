@php
    // Tekstblokken hergebruiken we, dus even één stijlregel per soort.
    $p    = 'margin:0 0 14px;font-family:Segoe UI,Helvetica,Arial,sans-serif;font-size:15px;line-height:1.6;color:#2E2C29;';
    $klein= 'margin:0;font-family:Segoe UI,Helvetica,Arial,sans-serif;font-size:13px;line-height:1.6;color:#6B6864;';
@endphp

@include('emails.voorbeeld._shell', [
    'merknaam'  => $merknaam,
    'preheader' => 'We bellen je kort en dan staat je voorbeeld ' . $levertijd . ' klaar.',
    'kop'       => 'Je aanvraag is binnen, ' . $voornaam,
    'inhoud'    => new \Illuminate\Support\HtmlString(<<<HTML

<p style="{$p}">
    Dank je wel voor je aanvraag voor <strong style="color:#1A1A1A;">{$bedrijf}</strong>.
    We gaan er meteen mee aan de slag — je voorbeeld staat <strong style="color:#1A1A1A;">{$levertijd}</strong> voor je klaar.
</p>

<p style="{$p}">Dit is wat er nu gebeurt:</p>

<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin:0 0 20px;">
    <tr>
        <td width="34" valign="top" style="padding:0 0 14px;">
            <div style="width:24px;height:24px;border-radius:12px;background:#12386B;color:#ffffff;font-family:Segoe UI,Helvetica,Arial,sans-serif;font-size:12px;font-weight:700;text-align:center;line-height:24px;">1</div>
        </td>
        <td valign="top" style="padding:0 0 14px;font-family:Segoe UI,Helvetica,Arial,sans-serif;font-size:15px;line-height:1.55;color:#2E2C29;">
            <strong style="color:#1A1A1A;">We bellen je even kort.</strong><br>
            <span style="color:#4A4844;font-size:14px;">Een paar minuten, meestal nog vandaag. We vragen door op de dingen die een formulier niet vangt: je mooiste klussen, welke diensten voorop moeten, of je al foto's hebt.</span>
        </td>
    </tr>
    <tr>
        <td width="34" valign="top" style="padding:0 0 14px;">
            <div style="width:24px;height:24px;border-radius:12px;background:#12386B;color:#ffffff;font-family:Segoe UI,Helvetica,Arial,sans-serif;font-size:12px;font-weight:700;text-align:center;line-height:24px;">2</div>
        </td>
        <td valign="top" style="padding:0 0 14px;font-family:Segoe UI,Helvetica,Arial,sans-serif;font-size:15px;line-height:1.55;color:#2E2C29;">
            <strong style="color:#1A1A1A;">Wij maken het voorbeeld.</strong><br>
            <span style="color:#4A4844;font-size:14px;">Met jouw naam, jouw vak en jouw regio. Geen sjabloon met een logo erop.</span>
        </td>
    </tr>
    <tr>
        <td width="34" valign="top">
            <div style="width:24px;height:24px;border-radius:12px;background:#12386B;color:#ffffff;font-family:Segoe UI,Helvetica,Arial,sans-serif;font-size:12px;font-weight:700;text-align:center;line-height:24px;">3</div>
        </td>
        <td valign="top" style="font-family:Segoe UI,Helvetica,Arial,sans-serif;font-size:15px;line-height:1.55;color:#2E2C29;">
            <strong style="color:#1A1A1A;">Je krijgt een link, {$levertijd}.</strong><br>
            <span style="color:#4A4844;font-size:14px;">Rustig bekijken, overleggen met wie je wilt, en dan pas beslissen. Bevalt het niet, dan laat je het weten en hoor je niets meer van ons.</span>
        </td>
    </tr>
</table>

<table role="presentation" cellpadding="0" cellspacing="0" border="0" style="margin:0 0 22px;">
    <tr><td style="background:#12386B;border-radius:6px;">
        <a href="{$afspraakUrl}" style="display:inline-block;padding:13px 26px;font-family:Segoe UI,Helvetica,Arial,sans-serif;font-size:15px;font-weight:700;color:#ffffff;text-decoration:none;">
            Liever meteen bellen? Plan een moment &rarr;
        </a>
    </td></tr>
</table>

<div style="background:#ffffff;border:1px solid #E5E3DF;border-radius:6px;padding:16px 18px;">
    <div style="font-family:Segoe UI,Helvetica,Arial,sans-serif;font-size:12px;font-weight:700;color:#8A8681;text-transform:uppercase;letter-spacing:0.04em;margin-bottom:8px;">Wat je ons vertelde</div>
    {$samenvatting}
</div>

HTML),
    'voet' => new \Illuminate\Support\HtmlString(
        'Je krijgt deze mail omdat je op ' . e($siteHost) . ' een gratis voorbeeld hebt aangevraagd. '
        . 'We gebruiken je gegevens alleen om dat voorbeeld te maken en met je door te nemen. '
        . 'Wil je dat we stoppen? Antwoord op deze mail met &laquo;stop&raquo; en we verwijderen alles.'
    ),
])
