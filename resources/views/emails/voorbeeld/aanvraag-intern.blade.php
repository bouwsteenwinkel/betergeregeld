@php
    $p = 'margin:0 0 14px;font-family:Segoe UI,Helvetica,Arial,sans-serif;font-size:15px;line-height:1.6;color:#2E2C29;';
@endphp

@include('emails.voorbeeld._shell', [
    'merknaam'  => $merknaam,
    'preheader' => $bedrijf . ' uit ' . $plaats . ' — eerst bellen, dan voorbeeld maken.',
    'kop'       => 'Voorbeeld aangevraagd: ' . $bedrijf,
    'inhoud'    => new \Illuminate\Support\HtmlString(<<<HTML

<div style="background:#DCE4F0;border-left:4px solid #12386B;padding:14px 16px;border-radius:0 6px 6px 0;margin:0 0 20px;">
    <div style="font-family:Segoe UI,Helvetica,Arial,sans-serif;font-size:15px;font-weight:700;color:#12386B;margin-bottom:3px;">Eerst bellen, dan maken</div>
    <div style="font-family:Segoe UI,Helvetica,Arial,sans-serif;font-size:14px;line-height:1.55;color:#2E2C29;">
        {$voornaam} verwacht een telefoontje van een paar minuten en daarna een voorbeeld <strong>{$levertijd}</strong>.
    </div>
</div>

<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin:0 0 20px;border:1px solid #E5E3DF;border-radius:6px;background:#ffffff;">
    <tr>
        <td colspan="2" style="padding:12px 16px;border-bottom:1px solid #E5E3DF;font-family:Segoe UI,Helvetica,Arial,sans-serif;font-size:12px;font-weight:700;color:#8A8681;text-transform:uppercase;letter-spacing:0.04em;">Contact</td>
    </tr>
    {$contactRijen}
</table>

<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin:0 0 20px;border:1px solid #E5E3DF;border-radius:6px;background:#ffffff;">
    <tr>
        <td colspan="2" style="padding:12px 16px;border-bottom:1px solid #E5E3DF;font-family:Segoe UI,Helvetica,Arial,sans-serif;font-size:12px;font-weight:700;color:#8A8681;text-transform:uppercase;letter-spacing:0.04em;">Wat ze invulden</td>
    </tr>
    {$antwoordRijen}
</table>

<p style="{$p}">
    <a href="{$adminUrl}" style="color:#12386B;font-weight:700;text-decoration:none;">Open de lead in de admin &rarr;</a>
</p>

HTML),
    'voet' => new \Illuminate\Support\HtmlString(
        'Automatische melding van ' . e($siteHost) . '. Bron: ' . e($bron) . '.'
        . ($herkomst ? '<br>Herkomst: ' . e($herkomst) : '')
    ),
])
