<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

/**
 * Eén centrale lead voor "website laten maken": komt binnen via de intake-flow
 * van elk kanaal/branche en wordt in de betergeregeld-admin opgevolgd. Per lead
 * is de branche zichtbaar zodat we vooraf een passende voorbeeldsite klaarzetten.
 *
 * Dient tevens als lichtgewicht, wachtwoordloos KLANT-ACCOUNT voor de voorbeeld-tool:
 * een klant (op e-mail) kan meerdere voorbeeldsites opslaan ({@see SavedPreview}), krijgt
 * een persoonlijke maglink (token_hash) en reminder-mails (consent + reminder_stage).
 */
class WebsiteLead extends Model
{
    protected $fillable = [
        'company', 'contact_name', 'email', 'phone',
        'postcode', 'city', 'address', 'distance_km', 'within_radius',
        'branche', 'facet', 'channel',
        'current_website', 'message', 'source',
        'answers', 'features',
        'preview_url', 'preview_status',
        'status', 'assigned_to', 'notes', 'contacted_at',
        'appointment_at', 'appointment_type', 'appointment_status', 'meet_link',
        'google_event_id', 'google_calendar_id',
        // Klant-account (voorbeeld opslaan + reminders)
        'consent', 'token_hash', 'token_expires_at',
        'reminder_stage', 'next_reminder_at', 'unsubscribed_at', 'saved_at',
    ];

    protected $casts = [
        'within_radius'    => 'boolean',
        'distance_km'      => 'integer',
        'contacted_at'     => 'datetime',
        'appointment_at'   => 'datetime',
        'answers'          => 'array',
        'features'         => 'array',
        'consent'          => 'boolean',
        'token_expires_at' => 'datetime',
        'next_reminder_at' => 'datetime',
        'unsubscribed_at'  => 'datetime',
        'saved_at'         => 'datetime',
    ];

    /** Door de klant opgeslagen voorbeeldsites. */
    public function savedPreviews(): HasMany
    {
        return $this->hasMany(SavedPreview::class);
    }

    /**
     * De wachtwoordloze revisit-token (aangemaakt indien nog leeg). Bewust een
     * PERMANENTE, niet-gehashte random token in de token_hash-kolom: reminders worden
     * later verstuurd en moeten dezelfde link kunnen meesturen (een sha256-hash is niet
     * te reconstrueren). Low-stakes (toont enkel de eigen, niet-gevoelige voorbeelden),
     * consistent met het bestaande unsubscribe_token-patroon.
     */
    public function revisitToken(): string
    {
        if (! $this->token_hash) {
            $this->forceFill(['token_hash' => Str::random(64)])->save();
        }

        return $this->token_hash;
    }

    /**
     * De persoonlijke "mijn voorbeelden"-URL. Altijd op het HOOFDDOMEIN
     * (config('app.url')): het opslaan gebeurt op een channel-domein en de mail wordt
     * vanuit de CLI verstuurd, dus url()/de huidige host is hier niet betrouwbaar.
     */
    public function revisitUrl(): string
    {
        return rtrim((string) config('app.url'), '/') . '/mijn-voorbeelden/' . $this->revisitToken();
    }

    /** Afmeldlink (stopt reminders) op het hoofddomein. */
    public function unsubscribeUrl(): string
    {
        return $this->revisitUrl() . '/afmelden';
    }

    /** Zoekt een lead bij een revisit-token; null als onbekend. */
    public static function findByRevisitToken(string $token): ?self
    {
        return strlen($token) >= 20 ? static::where('token_hash', $token)->first() : null;
    }

    // ── Branche-gestuurde intake-definities (uit config/intake.php) ──────

    /** Gemeenschappelijke vragen voor elke lead. */
    public static function intakeCommonQuestions(): array
    {
        return (array) config('intake.common', []);
    }

    /** Branche-specifieke extra vragen. */
    public static function intakeQuestionsFor(?string $branche): array
    {
        return (array) data_get(config('intake.branches'), ($branche ?: '') . '.questions', []);
    }

    /** Gewenste functies (feature-keys → label) voor een branche. */
    public static function intakeFeaturesFor(?string $branche): array
    {
        return (array) data_get(config('intake.branches'), ($branche ?: '') . '.features', []);
    }

    /** Alle vragen (common + branche) voor een gegeven branche. */
    public static function intakeAllQuestions(?string $branche): array
    {
        return array_merge(self::intakeCommonQuestions(), self::intakeQuestionsFor($branche));
    }

    // ── Groeidiamant-facetten (uit config/groeidiamant.php) ──────────────

    /** Alle facetten (key => definitie). */
    public static function facets(): array
    {
        return (array) config('groeidiamant.facets', []);
    }

    /** facet-key => label, voor selects/filters in de admin. */
    public static function facetOptions(): array
    {
        $out = [];
        foreach (self::facets() as $key => $def) {
            $out[$key] = ($def['label'] ?? $key);
        }
        return $out;
    }

    /** Geldig facet of de default. */
    public static function normalizeFacet(?string $facet): string
    {
        $facet = (string) $facet;
        return array_key_exists($facet, self::facets()) ? $facet : (string) config('groeidiamant.default', 'website');
    }

    /** Pijplijn-statussen (label per key) voor de admin. */
    public const STATUSES = [
        'new'         => 'Nieuw',
        'contacted'   => 'Benaderd',
        'appointment' => 'Afspraak gepland',
        'quoted'      => 'Offerte',
        'won'         => 'Klant',
        'lost'        => 'Verloren',
    ];

    /** Voorbeeldsite-status. */
    public const PREVIEW_STATUSES = [
        'todo'     => 'Te doen',
        'building' => 'In aanbouw',
        'ready'    => 'Klaar',
        'sent'     => 'Gedeeld',
    ];

    /** Branches (sectoren) die we benaderen — bepaalt de voorbeeldsite. */
    public const BRANCHES = [
        'horeca'        => 'Horeca / restaurant',
        'kapper_beauty' => 'Kapper / schoonheid',
        'bouw_installatie' => 'Bouw / installatie (loodgieter, elektra)',
        'detailhandel'  => 'Winkel / detailhandel',
        'zzp_diensten'  => 'ZZP / dienstverlening',
        'sport_fitness' => 'Sport / fitness',
        'zorg'          => 'Zorg / praktijk',
        'automotive'    => 'Automotive / garage',
        'vastgoed'      => 'Makelaar / vastgoed',
        'onderwijs_opvang'    => 'Onderwijs / opvang',
        'recreatie_vrije_tijd' => 'Recreatie / vrije tijd',
        'transport_logistiek'  => 'Transport / logistiek',
        'agrarisch_dieren'     => 'Agrarisch / dieren',
        'overig'        => 'Overig',
    ];

    /** Afspraak-type. */
    public const APPOINTMENT_TYPES = [
        'onsite' => 'Bezoek (≤ 50 km Bussum)',
        'meet'   => 'Google Meet',
    ];

    /** Afspraak-status. */
    public const APPOINTMENT_STATUSES = [
        'requested' => 'Aangevraagd',
        'confirmed' => 'Bevestigd',
        'done'      => 'Gehad',
        'cancelled' => 'Geannuleerd',
    ];
}
