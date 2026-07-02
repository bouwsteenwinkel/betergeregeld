<?php

namespace App\Services\ChannelSites;

/**
 * Bouwt de finale, art-directed beeldprompt voor een channel-site-slot.
 *
 * Dit is de kern van "correcte beelden": het combineert het per-branche
 * onderwerp met de globale anti-AI-look-stijl uit config/channel_images.php,
 * zodat elke generatie documentair en echt aanvoelt in plaats van stock/AI.
 */
class ImagePromptBuilder
{
    /**
     * @return array{prompt:string,negative:string} de prompt + negative-prompt
     */
    public function build(string $branche, string $slot, ?string $accent = null, ?string $subjectOverride = null): array
    {
        $cfg = config('channel_images');

        $branches = $cfg['branches'] ?? [];
        $recipe   = $branches[$branche] ?? $branches['_default'] ?? [];
        // Per-kanaal override (config channel_sites.channels.<key>.images.<slot>)
        // wint van het branche-recept, zodat bv. een barbershop andere beelden
        // krijgt dan een dameskapper, ook al delen ze de branche kapper_beauty.
        // Galerij-slots delen het 'detail'-onderwerp (werk-close-up); 6 generaties
        // geven vanzelf 6 gevarieerde beelden. Anders per-slot recept, anders hero.
        $slotKey  = str_starts_with($slot, 'gallery') ? 'gallery' : $slot;
        $subject  = $subjectOverride ?: ($recipe[$slotKey] ?? ($recipe['detail'] ?? ($recipe['hero'] ?? 'Een Nederlandse vakman aan het werk in zijn eigen omgeving.')));

        $style    = (string) ($cfg['style'] ?? '');
        $negative = (string) ($cfg['negative'] ?? '');

        $palette = $accent
            ? " Het kleurpalet sluit subtiel en natuurlijk aan op de tint {$accent}, zonder geforceerd te zijn."
            : '';

        // Slot-specifieke kadrering. De hero is een breed, liggend full-width
        // beeld; detail is een liggende close-up. Voorkomt rare uitsnedes.
        $framing = match (true) {
            $slot === 'hero'   => ' Brede, liggende compositie (landschap, 3:2): de hele scene met lucht en ruimte rondom het onderwerp, het onderwerp iets uit het midden zodat er plek is voor tekst, geschikt als breed hero-beeld over de volle paginabreedte.',
            $slot === 'detail' => ' Liggende, goed gevulde close-up.',
            str_starts_with($slot, 'gallery') => ' Vierkante compositie (1:1). Een concreet werkmoment, materiaal of resultaat van dit vak, documentair en gevarieerd.',
            default => '',
        };

        $prompt = trim($subject . ' ' . $style . $palette . $framing);

        return ['prompt' => $prompt, 'negative' => $negative];
    }
}
