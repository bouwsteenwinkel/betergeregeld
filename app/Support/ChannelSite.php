<?php

namespace App\Support;

use Illuminate\Support\Str;

/**
 * Eén channel-site (uit config/channel_sites.php). Read-only wrapper met
 * accessors + helpers zodat views niet rechtstreeks in de config graven.
 */
class ChannelSite
{
    /** @param array<string,mixed> $cfg */
    public function __construct(
        public readonly string $key,
        public readonly array $cfg,
    ) {
    }

    public function get(string $path, mixed $default = null): mixed
    {
        return data_get($this->cfg, $path, $default);
    }

    public function name(): string
    {
        return (string) ($this->cfg['name'] ?? $this->key);
    }

    public function branche(): string
    {
        return (string) ($this->cfg['branche'] ?? 'overig');
    }

    public function locale(): string
    {
        return (string) ($this->cfg['locale'] ?? 'nl');
    }

    public function domain(): ?string
    {
        $d = $this->cfg['domain'] ?? null;
        return $d ? strtolower(preg_replace('/^https?:\/\//', '', trim($d))) : null;
    }

    public function isLive(): bool
    {
        return ($this->cfg['status'] ?? 'draft') === 'live' && $this->domain() !== null;
    }

    /** Basis-URL: op het eigen domein als live, anders de preview-prefix. */
    public function baseUrl(): string
    {
        if ($this->isLive()) {
            return 'https://' . $this->domain();
        }
        return url('/_site/' . $this->key);
    }

    public function url(string $path = ''): string
    {
        $path = ltrim($path, '/');
        return rtrim($this->baseUrl(), '/') . ($path ? '/' . $path : '');
    }

    /** @return array<string,string> kleur/font-tokens met defaults */
    public function theme(): array
    {
        return array_merge([
            'primary' => '#0f172a',
            'accent'  => '#2563eb',
            'ink'     => '#0f172a',
            'muted'   => '#64748b',
            'bg'      => '#ffffff',
            'surface' => '#ffffff',
            'font'    => "system-ui, -apple-system, sans-serif",
            'font_url'=> null,
            'radius'  => '14px',
        ], (array) ($this->cfg['theme'] ?? []));
    }

    /** CSS-variabelen voor in de <style> van de layout. */
    public function cssVars(): string
    {
        $t = $this->theme();
        $map = [
            '--c-primary' => $t['primary'],
            '--c-accent'  => $t['accent'],
            '--c-ink'     => $t['ink'],
            '--c-muted'   => $t['muted'],
            '--c-bg'      => $t['bg'],
            '--c-surface' => $t['surface'],
            '--font'      => $t['font'],
            '--radius'    => $t['radius'],
        ];
        return implode(';', array_map(fn ($k, $v) => "$k:$v", array_keys($map), $map));
    }

    public function brand(string $key, mixed $default = null): mixed
    {
        return data_get($this->cfg, 'brand.' . $key)
            ?? config('channel_sites.defaults.' . $key, $default);
    }

    /** Eigen logo als afbeelding (public/-pad of URL), of null → tekst-wordmark. */
    public function logoImage(): ?string
    {
        $img = $this->brand('logo_image');
        if (! $img) {
            return null;
        }
        return preg_match('#^https?://#', $img) ? $img : asset(ltrim($img, '/'));
    }

    /** Discrete moeder-endorsement onderaan, bv. "Volgens de Groeidiamant …". */
    public function endorsement(): ?string
    {
        return $this->brand('endorsement') ?: null;
    }

    public function endorsementUrl(): ?string
    {
        return $this->brand('endorsement_url') ?: null;
    }

    public function homeTitle(): string
    {
        return (string) ($this->cfg['meta']['home_title'] ?? $this->name());
    }

    public function homeDescription(): string
    {
        return (string) ($this->cfg['meta']['home_description'] ?? '');
    }

    /** Optionele bespoke homepage-view, of de generieke. */
    public function homeView(): string
    {
        $view = $this->cfg['view'] ?? null;
        return ($view && view()->exists($view)) ? $view : 'channels.home';
    }

    public static function slug(string $place): string
    {
        return Str::slug($place);
    }
}
