<!DOCTYPE html>
<html lang="nl">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"></head>
<body style="margin:0;background:#f4f4f5;font-family:-apple-system,Segoe UI,Roboto,Helvetica,Arial,sans-serif;color:#111;">
  <div style="max-width:560px;margin:0 auto;padding:24px 16px;">
    <div style="background:#111;color:#fff;border-radius:14px 14px 0 0;padding:22px 24px;">
      <div style="font-size:18px;font-weight:700;letter-spacing:-.01em;">Beter Geregeld ICT</div>
      <div style="font-size:13px;opacity:.7;margin-top:2px;">Wij bouwen wat werkt</div>
    </div>
    <div style="background:#fff;border:1px solid #e5e5e5;border-top:none;border-radius:0 0 14px 14px;padding:24px;">
      <p style="font-size:15px;margin:0 0 14px;">Hoi {{ $lead->contact_name }},</p>
      <p style="font-size:14px;line-height:1.6;color:#333;margin:0 0 16px;">
        Bedankt voor je aanvraag voor <strong>{{ $lead->company }}</strong>. Je afspraak staat genoteerd —
        we bereiden alvast een <strong>voorbeeld-website</strong> voor zodat je bij het gesprek al iets concreets ziet.
      </p>

      <div style="background:#f7f7f8;border:1px solid #ececee;border-radius:10px;padding:14px 16px;margin:0 0 16px;">
        <table style="width:100%;font-size:14px;color:#111;border-collapse:collapse;">
          <tr><td style="padding:4px 0;color:#666;width:130px;">Afspraak</td><td style="padding:4px 0;font-weight:700;">{{ $when }}</td></tr>
          <tr><td style="padding:4px 0;color:#666;">Vorm</td><td style="padding:4px 0;">{{ $isOnsite ? 'Bezoek bij jou op locatie' : 'Online via Google Meet' }}</td></tr>
          <tr><td style="padding:4px 0;color:#666;">Branche</td><td style="padding:4px 0;">{{ $branche }}</td></tr>
        </table>
      </div>

      <p style="font-size:14px;line-height:1.6;color:#333;margin:0 0 16px;">
        @if($isOnsite)
          We komen naar je toe. Mocht het tijdstip toch niet schikken, reageer dan even op deze mail.
        @else
          Je ontvangt vóór de afspraak een Google Meet-link. Schikt het tijdstip toch niet? Reageer dan even op deze mail.
        @endif
      </p>

      <p style="font-size:14px;color:#333;margin:0;">Tot snel,<br>Team Beter Geregeld</p>
    </div>
    <p style="font-size:11px;color:#999;text-align:center;margin:14px 0 0;">Deze mail is verstuurd n.a.v. je aanvraag op betergeregeld.com.</p>
  </div>
</body>
</html>
