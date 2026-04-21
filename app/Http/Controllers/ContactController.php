<?php

namespace App\Http\Controllers;

use App\Models\ContactMessage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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

		ContactMessage::create([
			'public_id' => 'BGR-' . strtoupper(Str::random(12)),
			'created_at' => now(),
			'status' => 'new',
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

		return redirect(route('contact.sent'))->with('submitted', true);
	}

	public function sent(string $locale): View
	{
		abort_unless(session('submitted'), 404);
		return view('pages.contact-sent');
	}
}
