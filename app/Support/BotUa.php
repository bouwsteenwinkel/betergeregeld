<?php

namespace App\Support;

/**
 * Herkenning van crawlers, fetchers en meetgereedschap op de User-Agent.
 *
 * Eén lijst voor twee gebruikers: `verkeer:bots` (IIS-log achteraf) en de
 * event-beacon `/_ev` (live, vóór het wegschrijven). Zo telt het kiosk-scherm
 * niet wat het log daarna als bot ontmaskert — op 16-09-2026 waren 9 van de 34
 * "bezoekers" op het scherm Bingbot, Meta en een naamloze crawler die de beacon
 * gewoon vuurden, want die renderen JavaScript.
 *
 * De UA wordt nergens opgeslagen; alleen de uitkomst (wel/niet tellen) telt.
 */
final class BotUa
{
    /** Bekende crawlers/fetchers op user-agent, in volgorde van herkenning. */
    public const PATRONEN = [
        'Googlebot' => '~Googlebot|Google-InspectionTool|Storebot-Google|GoogleOther|APIs-Google|AdsBot-Google|Mediapartners-Google~i',
        'Bingbot' => '~bingbot|BingPreview|msnbot~i',
        'Applebot' => '~Applebot~i',
        'ClaudeBot' => '~ClaudeBot|anthropic-ai~i',
        'Claude-User' => '~Claude-User|Claude-SearchBot~i',
        'GPTBot' => '~GPTBot~i',
        'OpenAI-zoek' => '~OAI-SearchBot|ChatGPT-User~i',
        'Perplexity' => '~Perplexity~i',
        'Amazonbot' => '~Amazonbot~i',
        'Meta' => '~meta-externalagent|meta-externalfetcher|facebookexternalhit|FacebookBot|meta-webindexer~i',
        'CCBot' => '~CCBot~i',
        'Bytespider' => '~Bytespider|TikTokSpider~i',
        'Ahrefs' => '~AhrefsBot|AhrefsSiteAudit~i',
        'Semrush' => '~SemrushBot|SiteAuditBot~i',
        'Majestic' => '~MJ12bot~i',
        'DotBot' => '~DotBot~i',
        'Barkrowler' => '~Barkrowler~i',
        'DataForSeo' => '~DataForSeoBot~i',
        'Yandex' => '~YandexBot|YandexImages~i',
        'PetalBot' => '~PetalBot~i',
        'Seznam' => '~SeznamBot~i',
        'DuckDuck' => '~DuckDuckBot|DuckAssistBot~i',
        'Baidu' => '~Baiduspider~i',
        'Headless' => '~HeadlessChrome|PhantomJS|Puppeteer|Playwright|Selenium~i',
        'Lighthouse' => '~Chrome-Lighthouse|Lighthouse|PageSpeed|GTmetrix|PSI~',
        'Uptime' => '~UptimeRobot|Pingdom|StatusCake|Site24x7|BetterUptime|Uptime-Kuma|monitor~i',
        'gereedschap' => '~curl/|Wget/|python-requests|python-urllib|aiohttp|Go-http-client|Java/|okhttp|axios/|node-fetch|undici|libwww|Scrapy|HttpClient|Apache-HttpClient|PostmanRuntime|Faraday|Ruby|PHP/|Guzzle|Symfony|Laravel~i',
        'overige-bot' => '~bot|crawl|spider|scrap|fetch|slurp|archive\.org|ia_archiver|Screaming|SiteCheck|LinkCheck|Validator|Dataprovider|Netcraft|censys|shodan|zgrab|masscan|nmap~i',
    ];

    /** Naam van de herkende bot, of null als de UA er als browser uitziet. */
    public static function naam(?string $ua): ?string
    {
        $ua = trim((string) $ua);
        if ($ua === '' || $ua === '-') {
            return 'leeg';
        }
        foreach (self::PATRONEN as $naam => $re) {
            if (preg_match($re, $ua)) {
                return $naam;
            }
        }
        // Elke browser-engine (Chrome, Safari, Firefox, Edge, Samsung, in-app
        // webviews) meldt zich als "Mozilla/5.0 (...)". Een UA die daar niet mee
        // begint — "pc", "Dart/3.2", een merknaam — is een script, geen bezoeker.
        if (! str_starts_with($ua, 'Mozilla/')) {
            return 'geen-browser';
        }
        // Een browser van vóór versie 130 (Chrome oktober 2024) is in 2026 geen
        // mens meer: Chrome, Edge en Firefox werken zichzelf bij. Wat zich zo
        // meldt zijn proxy-pools met een vastgeplakte UA. Gemeten 13-16 sept 2026:
        // Chrome/99 (705x, elf hosts), Chrome/79 en Chrome/83; op 18-09 dezelfde
        // Chrome/117.0.5938.132 op vijf sites en een "Chrome/125 … Safari/537.36
        // Edge" zonder Edge-versienummer. Geen van die combinaties laadde ooit een
        // tweede pagina of een asset. Zelfde grens als bouwsteenwinkel_v3.
        if (preg_match('~(?<![A-Za-z])(?:Chrome|CriOS|Edg|Firefox|FxiOS)/(\d+)\.~', $ua, $m) && (int) $m[1] < 130) {
            return 'browser-verouderd';
        }
        // Desktop-Chrome op X11/Linux zonder verdere kenmerken is de UA van
        // headless Chromium (Puppeteer/Playwright zonder "Headless"). Gemeten
        // 16-09 (Chrome/150) en 18-09-2026 00:16 (Chrome/152): één homepage per
        // site, alfabetisch, via Cloudflare-edges en Google Cloud NL. Een echte
        // Linux-desktopgebruiker telt hierdoor ook niet mee; die zijn op deze
        // sites zeldzamer dan de pool.
        if (preg_match('~^Mozilla/5\.0 \(X11; Linux x86_64\) AppleWebKit/537\.36 \(KHTML, like Gecko\) Chrome/\d+\.0\.0\.0 Safari/537\.36$~', $ua)) {
            return 'linux-pool';
        }

        return null;
    }

    public static function isBot(?string $ua): bool
    {
        return self::naam($ua) !== null;
    }
}
