<?php

namespace App\Http\Controllers;

use App\Models\ContactMessage;
use App\Services\Security\Turnstile;
use App\Support\ContactSpam;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ContactController extends Controller
{
	public function show(Request $request, string $locale): View
	{
		return view('pages.contact', [
			'topic' => $request->query('topic', ''),
		]);
	}

	public function store(Request $request, string $locale): RedirectResponse
	{
		$data = $request->validate([
			'name' => ['required', 'string', 'max:120'],
			'email' => ['required', 'email', 'max:190'],
			'topic' => ['nullable', 'string', 'max:100'],
			'subject' => ['nullable', 'string', 'max:190'],
			'message' => ['required', 'string', 'max:5000'],
			'website' => ['nullable', 'string', 'max:255'],
			'company' => ['nullable', 'string', 'max:190'],
			'phone' => ['nullable', 'string', 'max:60'],
		]);

		// Drie drempels tegen de spamstroom van 12-09-2026 (ruim 100 van de 648 berichten):
		// het lokvakje, de Cloudflare-mensencheck en een inhoudelijke score. De snelheids-
		// limiet per IP staat op de route.
		if (trim((string) $request->input('bedrijfsnaam_2')) !== '') {
			// Stil weigeren: een bot hoort niet te weten waaróm het niet werkte.
			Log::info('contact_honeypot: ' . $request->ip());

			return redirect(route('contact.sent'))->with('submitted', true);
		}

		$turnstile = app(Turnstile::class);
		if (! $turnstile->verify($request->input('cf-turnstile-response'), $request->ip())) {
			return back()
				->withInput()
				->withErrors(['contact' => __('Bevestig even dat je geen robot bent en verstuur het opnieuw.')]);
		}

		// Hoe vaak stuurde dit adres of dit IP in de afgelopen 24 uur iets? Alleen dat venster:
		// over de hele geschiedenis tellen zou vaste klanten en kantoor-IP's raken.
		$eerder = ContactMessage::query()
			->where('created_at', '>=', now()->subDay())
			->where(fn ($q) => $q->where('email', $data['email'])->orWhere('ip', (string) $request->ip()))
			->count();

		$oordeel = ContactSpam::beoordeel($data + ['eerdere_inzendingen_24u' => $eerder]);
		$isSpam = $oordeel['score'] >= ContactSpam::DREMPEL;

		$subject = $data['subject'] ?: ($data['topic'] ? __('Aanvraag:') . ' ' . $data['topic'] : __('Contact via website'));

		$payload = [
			'created_at' => now()->toIso8601String(),
			'name' => $data['name'],
			'email' => $data['email'],
			'subject' => $subject,
			'message' => $data['message'],
			'page_uri' => $request->path(),
			'locale' => $locale,
		];

		// Eerst opslaan, dan mailen: loopt de mail vast, dan staat het bericht er al.
		$bericht = ContactMessage::create([
			'public_id' => 'BGR-' . strtoupper(Str::random(12)),
			'created_at' => now(),
			// Spam blijft bewaard (vangnet en controleerbaar), maar met eigen status en zonder mail.
			'status' => $isSpam ? 'spam' : 'new',
			'name' => $data['name'],
			'email' => $data['email'],
			'topic' => ($data['topic'] ?? '') ?: null,
			'website' => ($data['website'] ?? '') ?: null,
			'company' => ($data['company'] ?? '') ?: null,
			'phone' => ($data['phone'] ?? '') ?: null,
			'subject' => $subject,
			'message' => $data['message'],
			'ip' => $request->ip() ?? '',
			'user_agent' => substr((string) $request->userAgent(), 0, 255),
			'referer' => substr((string) $request->headers->get('referer', ''), 0, 255) ?: null,
			'page_uri' => substr('/' . $request->path(), 0, 255),
			'session_id' => substr($request->session()->getId(), 0, 128),
			'payload_hash' => hash('sha256', json_encode($payload)),
			'payload_json' => $payload,
			'user_id' => Auth::id(),
		]);

		if ($isSpam) {
			Log::info("contact_spam ({$bericht->public_id}): score {$oordeel['score']} — " . implode(', ', $oordeel['redenen']));
		} else {
			$this->meldIntern($bericht);
		}

		return redirect(route('contact.sent'))->with('submitted', true);
	}

	/**
	 * Interne melding van een nieuwe inzending. Tot 11-09-2026 bestond die niet: aanvragen
	 * stonden alleen in de admin en niemand kreeg bericht (zie config/contact.php).
	 *
	 * [WEBFORM] vooraan in het onderwerp is geen versiering: de Gmail-regel op dennis@
	 * filtert daarop en zet de mail in Primair. Nooit weghalen of vertalen, zelfde
	 * afspraak als bij de formulieren van Bouwsteenwinkel.
	 *
	 * Een mislukte mail mag de inzending niet breken; het bericht staat al opgeslagen.
	 */
	private function meldIntern(ContactMessage $bericht): void
	{
		try {
			$aan = (string) config('contact.notify_email');
			if ($aan === '') {
				return;
			}

			$regels = [
				'Nieuwe aanvraag via het contactformulier van betergeregeld.com.',
				'',
				'Naam: ' . $bericht->name . ($bericht->company ? " ({$bericht->company})" : ''),
				'E-mail: ' . $bericht->email,
				'Telefoon: ' . ($bericht->phone ?: '—'),
				'Website: ' . ($bericht->website ?: '—'),
				'Onderwerp: ' . ($bericht->topic ?: '—'),
				'Pagina: ' . $bericht->page_uri,
				'Referentie: ' . $bericht->public_id,
				'',
				$bericht->message,
				'',
				'Beantwoorden kan rechtstreeks op deze mail. Terug te vinden in de admin onder ContactMessages.',
			];

			Mail::raw(implode("\n", $regels), function ($m) use ($aan, $bericht) {
				$m->to($aan)
					->replyTo($bericht->email, $bericht->name)
					->subject('[WEBFORM] Betergeregeld — ' . Str::limit($bericht->subject, 120));
			});
		} catch (\Throwable $e) {
			Log::error("contact_notify_mail ({$bericht->public_id}): " . $e->getMessage());
			report($e);
		}
	}

	public function sent(string $locale): View
	{
		abort_unless(session('submitted'), 404);
		return view('pages.contact-sent');
	}
}
