<!doctype html>
<html lang="nl">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex,nofollow">
<title>Wachtwoord instellen &middot; Betergeregeld</title>
<style>
  :root { color-scheme: light; }
  body { margin:0; background:#f4f5f7; font:15px/1.6 system-ui,-apple-system,"Segoe UI",sans-serif;
         color:#1f2430; display:flex; min-height:100vh; align-items:center; justify-content:center; padding:24px; }
  .kaart { background:#fff; max-width:420px; width:100%; padding:30px 32px; border-radius:14px;
           box-shadow:0 10px 40px rgba(16,24,40,.10); }
  h1 { font-size:20px; margin:0 0 6px; }
  .sub { color:#6b7280; font-size:13px; margin:0 0 22px; }
  label { display:block; font-size:13px; font-weight:600; margin:14px 0 5px; }
  input[type=password] { width:100%; padding:10px 12px; border:1px solid #d5d8de; border-radius:8px;
                         font:inherit; box-sizing:border-box; }
  input:focus { outline:none; border-color:#1f6feb; box-shadow:0 0 0 3px rgba(31,111,235,.15); }
  button { margin-top:20px; width:100%; padding:11px 16px; border:0; border-radius:8px; cursor:pointer;
           background:#1f6feb; color:#fff; font:inherit; font-weight:600; }
  button:hover { background:#1a5fd0; }
  .fout { background:#fef2f2; border:1px solid #fca5a5; color:#991b1b; padding:10px 12px;
          border-radius:8px; font-size:13.5px; margin-bottom:16px; }
  .hint { color:#6b7280; font-size:12.5px; margin-top:6px; }
  ul.fouten { margin:0 0 16px; padding:10px 12px 10px 28px; background:#fef2f2; border:1px solid #fca5a5;
              color:#991b1b; border-radius:8px; font-size:13.5px; }
</style>
</head>
<body>
<div class="kaart">
  <h1>Wachtwoord instellen</h1>

  @if ($fout)
    <div class="fout">{{ $fout }}</div>
    <p class="sub" style="margin:0">Neem contact op voor een nieuwe link.</p>
  @else
    <p class="sub">Voor <strong>{{ $email }}</strong>. Deze link werkt één keer.</p>

    @if ($errors->any())
      <ul class="fouten">
        @foreach ($errors->all() as $melding)
          <li>{{ $melding }}</li>
        @endforeach
      </ul>
    @endif

    <form method="post" action="{{ url('/wachtwoord-instellen/' . $token) }}">
      @csrf
      <label for="password">Nieuw wachtwoord</label>
      <input type="password" id="password" name="password" required autofocus
             autocomplete="new-password" minlength="12">
      <div class="hint">Minstens 12 tekens.</div>

      <label for="password_confirmation">Nog een keer</label>
      <input type="password" id="password_confirmation" name="password_confirmation" required
             autocomplete="new-password" minlength="12">

      <button type="submit">Wachtwoord opslaan</button>
    </form>
  @endif
</div>
</body>
</html>
