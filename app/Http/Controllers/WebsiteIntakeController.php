<?php

namespace App\Http\Controllers;

use App\Mail\IntakeConfirmation;
use App\Models\WebsiteLead;
use App\Support\Geo\GeoBussum;
use App\Support\Intake\AppointmentSlots;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

/**
 * Publieke intake "website laten maken": branche-gestuurde uitvraag + afspraak
 * (bezoek <=50 km Bussum, anders Google Meet). Komt centraal binnen als
 * WebsiteLead in de admin, inclusief de antwoorden voor de 1-page-voorbereiding.
 */
class WebsiteIntakeController extends Controller
{
    public function show(Request $request, string $locale): View
    {
        return view('pages.intake', [
            'branches'    => WebsiteLead::BRANCHES,
            'common'      => WebsiteLead::intakeCommonQuestions(),
            'branchesDef' => (array) config('intake.branches', []),
            'slotDays'    => AppointmentSlots::upcoming(2),
            'radiusKm'    => GeoBussum::RADIUS_KM,
            'branchePreset' => (string) $request->query('branche', ''),
        ]);
    }

    public function store(Request $request, string $locale): RedirectResponse
    {
        // Honeypot: bots vullen 'website' in.
        if (filled($request->input('website'))) {
            return redirect()->route('intake.sent', ['locale' => $locale]);
        }

        $data = $request->validate([
            'company'          => ['required', 'string', 'max:190'],
            'branche'          => ['required', 'string', 'in:' . implode(',', array_keys(WebsiteLead::BRANCHES))],
            'contact_name'     => ['required', 'string', 'max:120'],
            'email'            => ['required', 'email', 'max:190'],
            'phone'            => ['required', 'string', 'max:60'],
            'postcode'         => ['required', 'string', 'max:12'],
            'city'             => ['nullable', 'string', 'max:120'],
            'current_website'  => ['nullable', 'string', 'max:255'],
            'message'          => ['nullable', 'string', 'max:4000'],
            'features'         => ['nullable', 'array'],
            'features.*'       => ['string', 'max:60'],
            'answers'          => ['nullable', 'array'],
            'appointment_slot' => ['required', 'string'],
            'appointment_pref' => ['required', 'in:onsite,meet'],
        ], [], [
            'company' => 'bedrijfsnaam', 'contact_name' => 'naam', 'phone' => 'telefoon',
            'postcode' => 'postcode', 'appointment_slot' => 'tijdslot',
        ]);

        if (! AppointmentSlots::isValid($data['appointment_slot'])) {
            return back()->withErrors(['appointment_slot' => 'Kies een geldig tijdslot.'])->withInput();
        }

        $branche = $data['branche'];

        // Antwoorden + functies saneren tot wat bij deze branche hoort.
        $validQ = array_column(WebsiteLead::intakeAllQuestions($branche), 'key');
        $answers = [];
        foreach ((array) ($data['answers'] ?? []) as $k => $v) {
            if (in_array($k, $validQ, true) && $v !== '' && $v !== null) {
                $answers[$k] = is_array($v) ? array_map('strval', $v) : (string) $v;
            }
        }
        $validF = array_keys(WebsiteLead::intakeFeaturesFor($branche));
        $features = array_values(array_intersect((array) ($data['features'] ?? []), $validF));

        // Afstand tot Bussum → bezoek of Meet.
        $distance = GeoBussum::distanceKm($data['postcode']);
        $within   = GeoBussum::withinRadius($distance);
        $type     = ($data['appointment_pref'] === 'onsite' && $within === true) ? 'onsite' : 'meet';

        $lead = WebsiteLead::create([
            'company'         => $data['company'],
            'branche'         => $branche,
            'contact_name'    => $data['contact_name'],
            'email'           => $data['email'],
            'phone'           => $data['phone'],
            'postcode'        => strtoupper(preg_replace('/\s+/', '', $data['postcode'])),
            'city'            => $data['city'] ?? null,
            'distance_km'     => $distance,
            'within_radius'   => $within,
            'current_website' => $data['current_website'] ?? null,
            'message'         => $data['message'] ?? null,
            'answers'         => $answers,
            'features'        => $features,
            'channel'         => 'intake',
            'source'          => 'intake',
            'status'          => 'appointment',
            'preview_status'  => 'todo',
            'appointment_at'  => $data['appointment_slot'],
            'appointment_type'=> $type,
            'appointment_status' => 'requested',
        ]);

        // Bevestiging naar klant + interne heads-up (best-effort).
        try {
            Mail::to($lead->email)->send(new IntakeConfirmation($lead));
        } catch (\Throwable $e) {
            Log::warning('intake_confirmation_mail: ' . $e->getMessage());
        }
        try {
            $to = config('mail.from.address');
            if ($to) {
                Mail::raw(
                    "Nieuwe website-lead via intake.\n\nBedrijf: {$lead->company}\nBranche: " . (WebsiteLead::BRANCHES[$branche] ?? $branche)
                    . "\nContact: {$lead->contact_name} · {$lead->email} · {$lead->phone}\nAfspraak: "
                    . AppointmentSlots::labelFor($data['appointment_slot']) . ' (' . ($type === 'onsite' ? 'bezoek' : 'Google Meet')
                    . ")\nAfstand Bussum: " . ($distance !== null ? $distance . ' km' : 'onbekend') . "\n\nOpvolgen in de admin → Website-leads.",
                    fn ($m) => $m->to($to)->subject('Nieuwe website-lead: ' . $lead->company)
                );
            }
        } catch (\Throwable $e) {
            Log::warning('intake_internal_mail: ' . $e->getMessage());
        }

        $request->session()->put('intake_done', [
            'name'  => $lead->contact_name,
            'when'  => AppointmentSlots::labelFor($data['appointment_slot']),
            'type'  => $type,
            'email' => $lead->email,
        ]);

        return redirect()->route('intake.sent', ['locale' => $locale]);
    }

    public function sent(Request $request, string $locale): View
    {
        $done = $request->session()->get('intake_done');
        abort_unless($done, 404);
        return view('pages.intake-sent', ['done' => $done]);
    }
}
