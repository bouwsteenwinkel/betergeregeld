<?php

namespace App\Console\Commands;

use App\Models\Channel\Site;
use App\Services\ChannelSiteResolver;
use Illuminate\Console\Command;

/**
 * Genereert per channel-site een echte 1200x630 social-/OG-afbeelding: het
 * site-logo netjes op een witte kaart, op een achtergrond in de themekleur.
 * PNG (universeel ondersteund, i.t.t. WebP). Puur een GD-compositie, geen AI.
 *
 *   php artisan channel:og badkamerspecialist
 *   php artisan channel:og                 (alle sites met een logo)
 */
class ChannelOgImage extends Command
{
    protected $signature = 'channel:og {channel? : channel-key, leeg = alle sites met een logo} {--force}';
    protected $description = 'Genereer een 1200x630 OG-/social-afbeelding per site (logo op merk-achtergrond)';

    public function handle(ChannelSiteResolver $resolver): int
    {
        if (! function_exists('imagecreatetruecolor')) {
            $this->error('GD-extensie niet beschikbaar.');
            return self::FAILURE;
        }

        $keys = $this->argument('channel')
            ? [(string) $this->argument('channel')]
            : Site::query()->pluck('key')->all();

        $done = 0;
        foreach ($keys as $key) {
            $site = $resolver->byKey($key);
            if (! $site) {
                $this->warn("Onbekend channel: {$key}");
                continue;
            }
            $logoRel = (string) $site->brand('footer_logo');
            if ($logoRel === '') {
                $this->line("· {$key}: geen logo, overgeslagen");
                continue;
            }
            $logoPath = public_path(ltrim($logoRel, '/'));
            if (! is_file($logoPath)) {
                $this->warn("· {$key}: logo-bestand niet gevonden ({$logoRel})");
                continue;
            }

            $out = public_path("channel-media/{$key}/og.png");
            if (is_file($out) && ! $this->option('force')) {
                $this->line("· {$key}: og.png bestaat al (gebruik --force)");
                continue;
            }

            $theme = $site->theme();
            $this->compose($logoPath, $theme['primary'] ?? '#0f172a', $theme['accent'] ?? '#2563eb', $out);
            $this->info("✓ {$key}: {$out}");
            $done++;
        }

        $this->info("Klaar. {$done} OG-afbeelding(en) gegenereerd.");
        return self::SUCCESS;
    }

    private function compose(string $logoPath, string $primary, string $accent, string $out): void
    {
        $W = 1200; $H = 630;
        $canvas = imagecreatetruecolor($W, $H);
        imagealphablending($canvas, true);

        [$pr, $pg, $pb] = $this->hex($primary);
        [$ar, $ag, $ab] = $this->hex($accent);

        // Achtergrond: themekleur met een subtiele verticale verdonkering onderin.
        for ($y = 0; $y < $H; $y++) {
            $f = 1 - ($y / $H) * 0.22;
            $col = imagecolorallocate($canvas, (int) ($pr * $f), (int) ($pg * $f), (int) ($pb * $f));
            imageline($canvas, 0, $y, $W, $y, $col);
        }
        // Accent-balk onderaan.
        $accCol = imagecolorallocate($canvas, $ar, $ag, $ab);
        imagefilledrectangle($canvas, 0, $H - 12, $W, $H, $accCol);

        // Witte afgeronde kaart in het midden.
        $cardW = 860; $cardH = 400;
        $cx = (int) (($W - $cardW) / 2);
        $cy = (int) (($H - $cardH) / 2) - 6;
        $this->roundedRect($canvas, $cx, $cy, $cardW, $cardH, 30, 255, 255, 255);

        // Logo laden (webp/png/jpg), schalen binnen de kaart met ruime padding.
        $logo = $this->loadImage($logoPath);
        if ($logo) {
            $lw = imagesx($logo); $lh = imagesy($logo);
            $maxW = $cardW - 180; $maxH = $cardH - 180;
            $scale = min($maxW / $lw, $maxH / $lh);
            $dw = max(1, (int) round($lw * $scale));
            $dh = max(1, (int) round($lh * $scale));
            $dx = $cx + (int) (($cardW - $dw) / 2);
            $dy = $cy + (int) (($cardH - $dh) / 2);
            imagecopyresampled($canvas, $logo, $dx, $dy, 0, 0, $dw, $dh, $lw, $lh);
            imagedestroy($logo);
        }

        if (! is_dir(dirname($out))) {
            @mkdir(dirname($out), 0775, true);
        }
        imagepng($canvas, $out, 6);
        imagedestroy($canvas);
    }

    /** Afgeronde gevulde rechthoek (GD heeft dit niet native). */
    private function roundedRect($img, int $x, int $y, int $w, int $h, int $r, int $cr, int $cg, int $cb): void
    {
        $c = imagecolorallocate($img, $cr, $cg, $cb);
        imagefilledrectangle($img, $x + $r, $y, $x + $w - $r, $y + $h, $c);
        imagefilledrectangle($img, $x, $y + $r, $x + $w, $y + $h - $r, $c);
        imagefilledellipse($img, $x + $r, $y + $r, $r * 2, $r * 2, $c);
        imagefilledellipse($img, $x + $w - $r, $y + $r, $r * 2, $r * 2, $c);
        imagefilledellipse($img, $x + $r, $y + $h - $r, $r * 2, $r * 2, $c);
        imagefilledellipse($img, $x + $w - $r, $y + $h - $r, $r * 2, $r * 2, $c);
    }

    /** @return array{0:int,1:int,2:int} */
    private function hex(string $hex): array
    {
        $hex = ltrim(trim($hex), '#');
        if (strlen($hex) === 3) {
            $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        }
        if (strlen($hex) < 6) {
            return [15, 23, 42];
        }
        return [hexdec(substr($hex, 0, 2)), hexdec(substr($hex, 2, 2)), hexdec(substr($hex, 4, 2))];
    }

    private function loadImage(string $path)
    {
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $img = match ($ext) {
            'webp' => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($path) : null,
            'png'  => @imagecreatefrompng($path),
            'jpg', 'jpeg' => @imagecreatefromjpeg($path),
            default => null,
        };
        if ($img) {
            imagealphablending($img, true);
        }
        return $img ?: null;
    }
}
