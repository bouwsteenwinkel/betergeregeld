{{-- Floating feedback button + modal, included on every AccessGuard page --}}
<style>
.ag-fb-btn {
	position: fixed;
	bottom: 24px;
	right: 24px;
	z-index: 40;
	background: #0f172a;
	color: #fff;
	padding: 10px 16px;
	border-radius: 999px;
	font-weight: 600;
	font-size: 13px;
	box-shadow: 0 10px 30px rgba(15,23,42,.25);
	cursor: pointer;
	border: none;
	display: inline-flex;
	align-items: center;
	gap: 6px;
	transition: transform .12s ease;
}
.ag-fb-btn:hover { transform: translateY(-2px); background: #1e293b; }
.ag-fb-modal { position: fixed; inset: 0; z-index: 50; background: rgba(0,0,0,.5); display: none; align-items: center; justify-content: center; padding: 16px; }
.ag-fb-modal.open { display: flex; }
.ag-fb-card { background: #fff; border-radius: 12px; max-width: 520px; width: 100%; max-height: 90vh; overflow-y: auto; box-shadow: 0 24px 64px rgba(0,0,0,.25); }
.ag-fb-head { padding: 20px 24px 12px; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: start; }
.ag-fb-body { padding: 20px 24px; }
.ag-fb-foot { padding: 12px 24px 20px; display: flex; gap: 8px; justify-content: flex-end; align-items: center; }
.ag-fb-close { background: none; border: none; font-size: 22px; color: #94a3b8; cursor: pointer; line-height: 1; }
.ag-fb-cats { display: grid; grid-template-columns: repeat(5, 1fr); gap: 6px; margin-bottom: 12px; }
.ag-fb-cat {
	padding: 10px 4px;
	border: 1px solid #e2e8f0;
	border-radius: 8px;
	background: #fff;
	cursor: pointer;
	font-size: 11px;
	text-align: center;
	transition: all .12s;
}
.ag-fb-cat:hover { border-color: #cbd5e1; background: #f8fafc; }
.ag-fb-cat.active { border-color: #14b8a6; background: rgba(20,184,166,.08); }
.ag-fb-cat-icon { font-size: 18px; display: block; margin-bottom: 2px; }
.ag-fb-ta { width: 100%; min-height: 110px; padding: 10px; border: 1px solid #e2e8f0; border-radius: 8px; font-family: inherit; font-size: 14px; resize: vertical; }
.ag-fb-ta:focus { outline: none; border-color: #14b8a6; box-shadow: 0 0 0 3px rgba(20,184,166,.12); }
.ag-fb-status { font-size: 12px; color: #64748b; margin-right: auto; }
.ag-fb-submit { background: #14b8a6; color: #fff; border: none; padding: 9px 18px; border-radius: 8px; font-weight: 600; cursor: pointer; font-size: 14px; }
.ag-fb-submit:hover { background: #0d9488; }
.ag-fb-submit:disabled { opacity: .5; cursor: wait; }
.ag-fb-cancel { background: #fff; color: #475569; border: 1px solid #e2e8f0; padding: 9px 14px; border-radius: 8px; cursor: pointer; font-size: 14px; }
</style>

<button type="button" class="ag-fb-btn" id="ag-fb-open" title="{{ __('Deel feedback') }}">
	<span>💬</span> {{ __('Feedback') }}
</button>

<div class="ag-fb-modal" id="ag-fb-modal" role="dialog" aria-labelledby="ag-fb-title">
	<div class="ag-fb-card">
		<div class="ag-fb-head">
			<div>
				<h3 id="ag-fb-title" style="margin: 0 0 4px; font-size: 17px; font-weight: 700;">💬 {{ __('Feedback sturen') }}</h3>
				<p style="margin: 0; font-size: 12px; color: #64748b;">{{ __('Direct naar het AccessGuard-team. We lezen alles.') }}</p>
			</div>
			<button type="button" class="ag-fb-close" id="ag-fb-close" aria-label="{{ __('Sluiten') }}">×</button>
		</div>
		<form id="ag-fb-form" class="ag-fb-body">
			@csrf
			<label style="font-size: 12px; font-weight: 600; display: block; margin-bottom: 6px;">{{ __('Wat is het?') }}</label>
			<div class="ag-fb-cats">
				<button type="button" class="ag-fb-cat" data-cat="bug"><span class="ag-fb-cat-icon">🐛</span>{{ __('Bug') }}</button>
				<button type="button" class="ag-fb-cat" data-cat="feature"><span class="ag-fb-cat-icon">💡</span>{{ __('Wens') }}</button>
				<button type="button" class="ag-fb-cat" data-cat="question"><span class="ag-fb-cat-icon">❓</span>{{ __('Vraag') }}</button>
				<button type="button" class="ag-fb-cat" data-cat="praise"><span class="ag-fb-cat-icon">❤️</span>{{ __('Compliment') }}</button>
				<button type="button" class="ag-fb-cat" data-cat="other"><span class="ag-fb-cat-icon">✏️</span>{{ __('Anders') }}</button>
			</div>
			<input type="hidden" name="category" id="ag-fb-cat" value="">

			<label style="font-size: 12px; font-weight: 600; display: block; margin: 12px 0 6px;">{{ __('Je bericht') }}</label>
			<textarea class="ag-fb-ta" name="message" id="ag-fb-msg" placeholder="{{ __('Zo specifiek mogelijk helpt het meest…') }}" required></textarea>

			<p style="font-size: 11px; color: #94a3b8; margin: 8px 0 0;">
				{{ __('We zien erbij: je e-mail, de pagina waar je bent en wanneer. Geen extra tracking.') }}
			</p>
		</form>
		<div class="ag-fb-foot">
			<span class="ag-fb-status" id="ag-fb-status"></span>
			<button type="button" class="ag-fb-cancel" id="ag-fb-cancel">{{ __('Annuleren') }}</button>
			<button type="button" class="ag-fb-submit" id="ag-fb-send" disabled>{{ __('Verstuur') }}</button>
		</div>
	</div>
</div>

<script>
(function () {
	const openBtn = document.getElementById('ag-fb-open');
	const modal = document.getElementById('ag-fb-modal');
	const closeBtn = document.getElementById('ag-fb-close');
	const cancelBtn = document.getElementById('ag-fb-cancel');
	const sendBtn = document.getElementById('ag-fb-send');
	const catInput = document.getElementById('ag-fb-cat');
	const msg = document.getElementById('ag-fb-msg');
	const status = document.getElementById('ag-fb-status');
	const url = @json(route('tools.accessguard.feedback.submit', ['locale' => app()->getLocale()]));
	const csrf = document.querySelector('meta[name="csrf-token"]').content;

	function setCat(c) {
		catInput.value = c;
		document.querySelectorAll('.ag-fb-cat').forEach(b => b.classList.toggle('active', b.dataset.cat === c));
		checkValid();
	}
	function checkValid() {
		sendBtn.disabled = !(catInput.value && msg.value.trim().length > 3);
	}
	function open() { modal.classList.add('open'); setTimeout(() => msg.focus(), 100); }
	function close() { modal.classList.remove('open'); reset(); }
	function reset() { catInput.value = ''; msg.value = ''; status.textContent = ''; document.querySelectorAll('.ag-fb-cat').forEach(b => b.classList.remove('active')); sendBtn.disabled = true; }

	openBtn.addEventListener('click', open);
	closeBtn.addEventListener('click', close);
	cancelBtn.addEventListener('click', close);
	modal.addEventListener('click', e => { if (e.target === modal) close(); });
	document.querySelectorAll('.ag-fb-cat').forEach(b => b.addEventListener('click', () => setCat(b.dataset.cat)));
	msg.addEventListener('input', checkValid);

	sendBtn.addEventListener('click', async () => {
		sendBtn.disabled = true;
		status.textContent = @json(__('Verzenden…'));
		try {
			const resp = await fetch(url, {
				method: 'POST',
				headers: {'Content-Type':'application/json','X-CSRF-TOKEN':csrf,'Accept':'application/json'},
				body: JSON.stringify({
					category: catInput.value,
					message: msg.value,
					page_url: window.location.pathname,
				}),
			});
			const body = await resp.json();
			if (!resp.ok || !body.ok) {
				status.textContent = body.error || 'Kon niet versturen';
				sendBtn.disabled = false;
				return;
			}
			status.textContent = @json(__('Bedankt! 🙏'));
			setTimeout(close, 1400);
		} catch (e) {
			status.textContent = 'Fout: ' + e.message;
			sendBtn.disabled = false;
		}
	});
})();
</script>
