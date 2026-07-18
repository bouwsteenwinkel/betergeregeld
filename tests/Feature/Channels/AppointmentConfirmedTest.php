<?php

namespace Tests\Feature\Channels;

use Tests\TestCase;

/**
 * Aparte bevestigings-URL na een boeking, zodat een ads-conversie op een echte pageview
 * (/afspraak-bevestigd) gemeten kan worden i.p.v. op een inline melding op /afspraak.
 * Het conversie-event mag alleen na een échte boeking vuren (server-flash), niet bij een
 * refresh of direct bezoek — anders telt de conversie dubbel.
 */
class AppointmentConfirmedTest extends TestCase
{
    private string $url = '/_site/barbershop/afspraak-bevestigd';

    public function test_confirmation_page_renders_and_is_noindex(): void
    {
        $res = $this->get($this->url);

        $res->assertOk();
        $res->assertSee('Je afspraak staat gepland');
        // Thank-you page hoort niet in Google en niet los vindbaar.
        $res->assertSee('noindex,nofollow', false);
    }

    public function test_no_conversion_event_on_direct_visit(): void
    {
        // Geen server-flash → geen conversie-event (voorkomt dubbeltellen bij refresh/direct bezoek).
        $this->get($this->url)->assertDontSee('appointment_booked');
    }

    public function test_conversion_event_fires_after_a_real_booking(): void
    {
        // De boeking zet deze flash (AppointmentController::book); de volgende GET pikt 'm op.
        $res = $this->withSession(['appointment_confirmed' => true])->get($this->url);

        $res->assertOk();
        $res->assertSee('appointment_booked');
    }
}
