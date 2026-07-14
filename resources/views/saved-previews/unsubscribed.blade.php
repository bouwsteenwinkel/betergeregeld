<!doctype html>
<html lang="nl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex,nofollow">
    <title>Afgemeld · Beter Geregeld</title>
    <style>
        body{margin:0;font:16px/1.55 system-ui,-apple-system,'Segoe UI',sans-serif;color:#0f172a;background:#f7f9fb}
        .box{max-width:520px;margin:16vh auto 0;background:#fff;border:1px solid #e2e8f0;border-radius:14px;padding:2rem;text-align:center;box-shadow:0 18px 40px -32px rgba(15,23,42,.4)}
        h1{font-size:1.35rem;margin:0 0 .5rem}
        p{color:#64748b;margin:.3rem 0}
        a{color:#1685c4}
    </style>
</head>
<body>
    <div class="box">
        <h1>Je bent afgemeld</h1>
        <p>We sturen je geen herinneringen meer over je voorbeeld. Je opgeslagen voorbeeld blijft gewoon beschikbaar via je persoonlijke link.</p>
        <p>Van gedachten veranderd? Mail ons gerust op <a href="mailto:{{ config('mail.from.address') }}">{{ config('mail.from.address') }}</a>.</p>
    </div>
</body>
</html>
