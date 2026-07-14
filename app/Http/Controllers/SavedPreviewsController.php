<?php

namespace App\Http\Controllers;

use App\Models\SavedPreview;
use App\Models\WebsiteLead;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Wachtwoordloze klant-pagina "mijn voorbeelden": bereikbaar via de persoonlijke
 * token-link uit de bevestigings-/reminder-mail. Toont de opgeslagen voorbeelden,
 * laat een favoriet kiezen en biedt afmelden voor reminders.
 */
class SavedPreviewsController extends Controller
{
    public function show(string $token): View
    {
        $lead = WebsiteLead::findByRevisitToken($token);
        abort_unless($lead, 404);

        return view('saved-previews.show', [
            'lead'     => $lead,
            'token'    => $token,
            'previews' => $lead->savedPreviews()->orderByDesc('favorite')->orderByDesc('created_at')->get(),
        ]);
    }

    public function favorite(string $token, Request $request): RedirectResponse
    {
        $lead = WebsiteLead::findByRevisitToken($token);
        abort_unless($lead, 404);

        $saved = SavedPreview::where('website_lead_id', $lead->id)
            ->whereKey($request->integer('saved_id'))
            ->first();

        if ($saved) {
            $lead->savedPreviews()->update(['favorite' => false]);
            $saved->update(['favorite' => true]);
            $lead->update(['preview_url' => $saved->url()]);
        }

        return redirect('/mijn-voorbeelden/' . $token);
    }

    public function unsubscribe(string $token): View
    {
        $lead = WebsiteLead::findByRevisitToken($token);
        abort_unless($lead, 404);

        $lead->update(['unsubscribed_at' => now(), 'next_reminder_at' => null]);

        return view('saved-previews.unsubscribed', ['lead' => $lead]);
    }
}
