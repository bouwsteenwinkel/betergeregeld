<?php

namespace App\Services\ChannelSites;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;

/**
 * Genereert en bewaart de beelden voor een channel-site.
 *
 * - Echte modus (OPENAI_API_KEY aanwezig): roept de OpenAI images-API aan en
 *   bewaart de PNG onder public/channel-media/<key>/<slot>.png.
 * - Fake-modus (geen key, of key begint met "fake"/"xxxx"): schrijft een
 *   SVG-preview met de échte prompt erin, zodat de hele pijplijn testbaar is
 *   en je ziet wat er gegenereerd zou worden. Zelfde patroon als MollieGateway
 *   en OpenAiClient elders in de codebase.
 */
class ChannelImageGenerator
{
    public function __construct(
        private readonly ImagePromptBuilder $prompts = new ImagePromptBuilder(),
    ) {
    }

    public function isFake(): bool
    {
        $key = (string) config('accessguard.ai.api_key', '');
        return $key === '' || str_starts_with($key, 'fake') || str_starts_with($key, 'xxxx');
    }

    /** @return array<string,array{size:string,ratio:string}> */
    public function slots(): array
    {
        return (array) config('channel_images.slots', []);
    }

    /** Publieke URL van een bestaand beeld, of null. */
    public function url(string $channelKey, string $slot): ?string
    {
        // Voorkeur: grootste geoptimaliseerde WebP-variant.
        $variants = $this->variants($channelKey, $slot);
        if ($variants) {
            return reset($variants);
        }
        // Fallback: losse webp/png/jpg, of de fake-mode SVG-preview.
        $rel = $this->relativePath($channelKey, $slot);
        foreach (['webp', 'png', 'jpg', 'svg'] as $ext) {
            if (File::exists(public_path("{$rel}.{$ext}"))) {
                return asset("{$rel}.{$ext}");
            }
        }
        return null;
    }

    /**
     * Genereert één slot voor één kanaal. Slaat over als het al bestaat
     * (tenzij $force). Geeft het resultaat terug voor logging.
     *
     * @return array{slot:string,status:string,file:?string,prompt:string,fake:bool}
     */
    public function generate(string $channelKey, string $branche, string $slot, ?string $accent = null, bool $force = false): array
    {
        $override = config("channel_sites.channels.{$channelKey}.images.{$slot}");
        $built  = $this->prompts->build($branche, $slot, $accent, is_string($override) ? $override : null);
        $prompt = $built['prompt'];

        if (! $force && $this->url($channelKey, $slot) !== null) {
            return ['slot' => $slot, 'status' => 'bestaat-al', 'file' => $this->url($channelKey, $slot), 'prompt' => $prompt, 'fake' => $this->isFake()];
        }

        $dir = public_path($this->relativeDir($channelKey));
        File::ensureDirectoryExists($dir);

        // Bewaar de prompt altijd als sidecar (auditeerbaar / herhaalbaar).
        File::put("{$dir}/{$slot}.prompt.txt", $prompt . "\n\n[negative] " . $built['negative']);

        if ($this->isFake()) {
            $file = "{$dir}/{$slot}.svg";
            File::put($file, $this->placeholderSvg($branche, $slot, $prompt, $accent));
            return ['slot' => $slot, 'status' => 'fake-preview', 'file' => $this->url($channelKey, $slot), 'prompt' => $prompt, 'fake' => true];
        }

        // Echte generatie via OpenAI images-API.
        $size = (string) ($this->slots()[$slot]['size'] ?? '1024x1536');
        $resp = Http::withToken((string) config('accessguard.ai.api_key'))
            ->withOptions(['verify' => $this->verify()])
            ->timeout(120)
            ->post('https://api.openai.com/v1/images/generations', [
                'model'   => (string) config('channel_images.model', 'gpt-image-1'),
                'prompt'  => $prompt . ' Vermijd: ' . $built['negative'] . '.',
                'size'    => $size,
                'quality' => (string) config('channel_images.quality', 'high'),
                'n'       => 1,
            ]);

        if (! $resp->successful()) {
            return ['slot' => $slot, 'status' => 'fout: ' . $resp->status(), 'file' => null, 'prompt' => $prompt, 'fake' => false];
        }

        $b64 = data_get($resp->json(), 'data.0.b64_json');
        if (! $b64) {
            return ['slot' => $slot, 'status' => 'geen-beeld-terug', 'file' => null, 'prompt' => $prompt, 'fake' => false];
        }

        $file = "{$dir}/{$slot}.png";
        File::put($file, base64_decode($b64));

        $this->optimize($channelKey, $slot); // WebP-varianten voor performance

        return ['slot' => $slot, 'status' => 'gegenereerd', 'file' => $this->url($channelKey, $slot), 'prompt' => $prompt, 'fake' => false];
    }

    /**
     * Genereert een beeld voor een vrij slot met een MEEGEGEVEN prompt (bv. een
     * blog-afbeelding: slot 'blog-{slug}'). Zelfde opslag/optimalisatie als de
     * vaste slots. Faalt zacht; fake-mode geeft een SVG-placeholder.
     *
     * @return array{slot:string,status:string,file:?string,fake:bool}
     */
    public function generateRaw(string $channelKey, string $slot, string $prompt, string $size = '1536x1024', bool $force = false): array
    {
        if (! $force && $this->url($channelKey, $slot) !== null) {
            return ['slot' => $slot, 'status' => 'bestaat-al', 'file' => $this->url($channelKey, $slot), 'fake' => $this->isFake()];
        }

        $dir = public_path($this->relativeDir($channelKey));
        File::ensureDirectoryExists($dir);
        File::put("{$dir}/{$slot}.prompt.txt", $prompt);

        if ($this->isFake()) {
            File::put("{$dir}/{$slot}.svg", $this->placeholderSvg('', $slot, $prompt, null));
            return ['slot' => $slot, 'status' => 'fake-preview', 'file' => $this->url($channelKey, $slot), 'fake' => true];
        }

        $resp = Http::withToken((string) config('accessguard.ai.api_key'))
            ->withOptions(['verify' => $this->verify()])
            ->timeout(120)
            ->post('https://api.openai.com/v1/images/generations', [
                'model'   => (string) config('channel_images.model', 'gpt-image-1'),
                'prompt'  => $prompt,
                'size'    => $size,
                'quality' => (string) config('channel_images.quality', 'high'),
                'n'       => 1,
            ]);

        if (! $resp->successful()) {
            return ['slot' => $slot, 'status' => 'fout: ' . $resp->status(), 'file' => null, 'fake' => false];
        }
        $b64 = data_get($resp->json(), 'data.0.b64_json');
        if (! $b64) {
            return ['slot' => $slot, 'status' => 'geen-beeld-terug', 'file' => null, 'fake' => false];
        }

        File::put("{$dir}/{$slot}.png", base64_decode($b64));
        $this->optimize($channelKey, $slot);

        return ['slot' => $slot, 'status' => 'gegenereerd', 'file' => $this->url($channelKey, $slot), 'fake' => false];
    }

    /**
     * Maakt WebP-varianten (meerdere breedtes) van de bron-PNG voor snelle
     * paginalaadtijd (Core Web Vitals). Geeft de gemaakte breedtes terug.
     *
     * @return int[]
     */
    public function optimize(string $channelKey, string $slot): array
    {
        $dir = public_path($this->relativeDir($channelKey));
        $png = "{$dir}/{$slot}.png";
        if (! File::exists($png) || ! function_exists('imagewebp')) {
            return [];
        }
        $src = @imagecreatefrompng($png);
        if (! $src) {
            return [];
        }
        $w = imagesx($src);

        $targets = array_values(array_unique(array_merge(
            array_filter([1200, 800, 480], fn ($x) => $x < $w),
            [$w],
        )));
        rsort($targets);

        $made = [];
        foreach ($targets as $tw) {
            $img = ($tw < $w) ? imagescale($src, $tw) : $src;
            if ($img) {
                imagewebp($img, "{$dir}/{$slot}-{$tw}.webp", 80);
                if ($img !== $src) {
                    imagedestroy($img);
                }
                $made[] = $tw;
            }
        }
        imagedestroy($src);
        return $made;
    }

    /** @return array<int,string> webp-varianten, grootste eerst (breedte => url) */
    private function variants(string $channelKey, string $slot): array
    {
        $dir = str_replace('\\', '/', public_path($this->relativeDir($channelKey)));
        $out = [];
        foreach (glob("{$dir}/{$slot}-*.webp") ?: [] as $f) {
            if (preg_match('/-(\d+)\.webp$/', $f, $m)) {
                $out[(int) $m[1]] = asset($this->relativeDir($channelKey) . '/' . basename($f));
            }
        }
        krsort($out);
        return $out;
    }

    /** srcset-string voor responsive beeld, of leeg als er geen varianten zijn. */
    public function srcset(string $channelKey, string $slot): string
    {
        $parts = [];
        foreach ($this->variants($channelKey, $slot) as $w => $u) {
            $parts[] = "{$u} {$w}w";
        }
        return implode(', ', $parts);
    }

    /** CA-bundle voor SSL-verificatie (lokale dev mist soms een systeem-bundle). */
    private function verify(): string|bool
    {
        $p = storage_path('cacert.pem');
        return File::exists($p) ? $p : true;
    }

    private function relativeDir(string $channelKey): string
    {
        return trim((string) config('channel_images.path', 'channel-media'), '/') . '/' . $channelKey;
    }

    private function relativePath(string $channelKey, string $slot): string
    {
        return $this->relativeDir($channelKey) . '/' . $slot;
    }

    /** Nette SVG-preview die de echte prompt toont (fake-mode). */
    private function placeholderSvg(string $branche, string $slot, string $prompt, ?string $accent): string
    {
        $accent = $accent ?: '#ef6033';
        $lines  = $this->wrap($prompt, 52);
        $tspans = '';
        foreach (array_slice($lines, 0, 10) as $i => $line) {
            $y = 250 + $i * 26;
            $tspans .= '<text x="48" y="' . $y . '" font-family="monospace" font-size="15" fill="#cbd5e1">' . htmlspecialchars($line, ENT_QUOTES) . '</text>';
        }
        $b = htmlspecialchars(strtoupper($branche), ENT_QUOTES);
        $s = htmlspecialchars(strtoupper($slot), ENT_QUOTES);

        return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" width="1024" height="1280" viewBox="0 0 1024 1280">
  <rect width="1024" height="1280" fill="#0f1b2d"/>
  <rect x="24" y="24" width="976" height="1232" fill="none" stroke="#1e3047" stroke-width="2"/>
  <circle cx="512" cy="150" r="40" fill="none" stroke="{$accent}" stroke-width="3"/>
  <path d="M495 150l12 12 22-24" fill="none" stroke="{$accent}" stroke-width="4" stroke-linecap="round"/>
  <text x="48" y="120" font-family="sans-serif" font-size="26" font-weight="700" fill="#e2e8f0">AI-BEELD — PREVIEW</text>
  <text x="48" y="156" font-family="monospace" font-size="16" fill="{$accent}">branche: {$b} / slot: {$s}</text>
  <text x="48" y="215" font-family="sans-serif" font-size="15" fill="#7e93ad">Dit beeld wordt gegenereerd met de volgende art-directed prompt:</text>
  {$tspans}
  <text x="48" y="1230" font-family="monospace" font-size="13" fill="#52617a">fake-mode actief, zet OPENAI_API_KEY om echte foto's te genereren</text>
</svg>
SVG;
    }

    /** @return string[] */
    private function wrap(string $text, int $width): array
    {
        return explode("\n", wordwrap($text, $width, "\n", true));
    }
}
