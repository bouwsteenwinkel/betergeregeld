<?php

namespace App\Http\Controllers;

use App\Services\Scheduling\BookingService;
use App\Services\Scheduling\SlotEngine;
use App\Services\Scheduling\SlotTakenException;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Publieke afspraken-API, gedeeld door alle trigger-sites (same-origin per domein).
 */
class AppointmentController extends Controller
{
    public function availability(Request $request, SlotEngine $engine): JsonResponse
    {
        $tz   = (string) config('scheduling.timezone', 'Europe/Amsterdam');
        $from = $request->filled('from') ? CarbonImmutable::parse($request->query('from'), $tz) : null;
        $to   = $request->filled('to') ? CarbonImmutable::parse($request->query('to'), $tz) : null;

        return response()->json(['days' => $engine->slots($from, $to)]);
    }

    public function book(Request $request, BookingService $service): JsonResponse
    {
        $data = $request->validate([
            'name'        => 'required|string|max:120',
            'email'       => 'required|email|max:190',
            'phone'       => 'nullable|string|max:40',
            'starts_at'   => 'required|date',
            'note'        => 'nullable|string|max:1000',
            'source_site' => 'nullable|string|max:80',
            'website'     => 'nullable|max:0', // honeypot: moet leeg zijn (geen autofill)
        ]);

        try {
            $appt = $service->book($data);
        } catch (SlotTakenException) {
            return response()->json([
                'ok'      => false,
                'message' => 'Dit tijdstip is net bezet. Kies een ander moment.',
            ], 409);
        }

        $tz = (string) config('scheduling.timezone', 'Europe/Amsterdam');

        return response()->json([
            'ok'        => true,
            'meet_url'  => $appt->meet_url,
            'starts_at' => $appt->starts_at->setTimezone($tz)->format('Y-m-d H:i'),
            'message'   => $appt->meet_url
                ? 'Je afspraak staat! De Google Meet-link staat in je bevestigingsmail.'
                : 'Je afspraak staat! Je ontvangt de bevestiging en de Meet-link per e-mail.',
        ]);
    }
}
