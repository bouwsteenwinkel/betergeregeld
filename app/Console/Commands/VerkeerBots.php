<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

/**
 * Mens of bot? Leest de IIS-logs op de VPS en splitst het verkeer van
 * betergeregeld.com en de channel-sites (jouw-*-website.nl) uit naar wie er
 * werkelijk aan de andere kant zat.
 *
 * Waarom dit bestaat: onze eigen tellingen (channel_events, GA4) komen uit een
 * JS-beacon. Bots die geen JS draaien staan daar niet in, en bots die WÉL JS
 * draaien (Googlebot's renderer, headless Chrome) staan er juist als bezoeker in.
 * De enige plek waar élk verzoek met adres en user-agent staat, is het IIS-log.
 *
 * Achter Cloudflare is c-ip de edge-node; het echte adres staat alleen in het log
 * als IIS een extra veld cs(CF-Connecting-IP) of cs(X-Forwarded-For) logt. Het
 * commando meldt welk veld het gebruikt, zodat je weet hoe hard de per-adres-
 * conclusies zijn.
 *
 * Gebruik (ook via POST /webhooks/artisan, de uitvoer komt in het JSON terug):
 *   artisan verkeer:bots --list            welke logmappen zijn er en zijn ze leesbaar
 *   artisan verkeer:bots --dagen=2         analyse over de logbestanden van de laatste 2 dagen
 *   artisan verkeer:bots --host=jouw-dietist-website.nl --top=30
 */
class VerkeerBots extends Command
{
    protected $signature = 'verkeer:bots
        {--list : Alleen de gevonden logmappen tonen, niets parsen}
        {--dagen=2 : Logbestanden gewijzigd in de laatste N dagen}
        {--host=* : Alleen deze hostnamen (standaard: betergeregeld.com + jouw-*-website.nl)}
        {--vhost=* : Alleen logmappen waarvan het pad dit bevat (standaard: betergeregeld, jouw-)}
        {--pad=* : Extra logmap(pen) of bestand(en) om te lezen}
        {--top=15 : Aantal verdachte adressen per host in het rapport}
        {--max-mb=48 : Per bestand maximaal dit aantal MB lezen (vanaf het einde)}';

    protected $description = 'Splits het verkeer in de IIS-logs uit naar mens, bekende bot, vermoedelijke bot en scanner';

    /** Bekende crawlers/fetchers op user-agent, in volgorde van herkenning. */
    private const BOTS = [
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

    /** Adressen die alleen een scanner opvraagt op een Laravel-site zonder WordPress. */
    private const SCAN_PAD = '~\.(php|asp|aspx|jsp|cgi|env|git|sql|bak|zip|rar|tar|gz)$|/wp-|/wordpress|/xmlrpc|/administrator|/phpmyadmin|/pma|/\.git|/\.env|/vendor/|/cgi-bin|/proc/self|/etc/passwd|/console$|/actuator|/telescope|/_ignition|/\.well-known/(?!acme)|/boaform|/HNAP1|/shell|/config\.(json|yml|yaml)$~i';

    /** Statische bestanden: laat een browser altijd meeladen, een kale HTTP-client zelden. */
    private const ASSET_PAD = '~\.(js|css|png|jpe?g|gif|svg|webp|avif|ico|woff2?|ttf|otf|eot|map|mp4|webm|json)$~i';

    /** Eigen achtergrondverkeer: telt niet mee als bezoek. */
    private const ACHTERGROND_PAD = '~^/(cron/ping|monitor/ingest|webhooks/|livewire|_ev$|cmp\.js$|cmp/|robots\.txt$|sitemap|favicon)~';

    public function handle(): int
    {
        $hosts = $this->option('host') ?: [];
        $vhostFilter = $this->option('vhost') ?: ['betergeregeld', 'jouw-'];
        $dagen = max(1, (int) $this->option('dagen'));
        $top = max(1, (int) $this->option('top'));
        $maxBytes = max(1, (int) $this->option('max-mb')) * 1024 * 1024;

        $bestanden = $this->vindLogbestanden($vhostFilter, $dagen, (array) $this->option('pad'));

        if ($this->option('list') || $bestanden === []) {
            $this->toonLogmappen($vhostFilter);
            if ($bestanden === []) {
                $this->warn('Geen leesbare logbestanden gevonden voor deze filters. Geef --pad= of --vhost= op.');

                return self::FAILURE;
            }

            return self::SUCCESS;
        }

        // Per host, per adres: wat deed het.
        $perHost = [];
        $ipVeld = null;
        $gelezen = [];
        $overgeslagen = 0;

        foreach ($bestanden as $pad) {
            $res = $this->leesLog($pad, $hosts, $maxBytes, $perHost, $ipVeld);
            if ($res === null) {
                $overgeslagen++;

                continue;
            }
            $gelezen[] = sprintf('%s  %s regels, %s', $this->kortPad($pad), number_format($res['regels'], 0, ',', '.'), $res['afgekapt'] ? 'AFGEKAPT op '.round($maxBytes / 1048576).' MB' : 'volledig');
        }

        $this->line('Gelezen logbestanden ('.count($gelezen).'; '.$overgeslagen.' overgeslagen zonder W3C-kop, bv. Plesk-statistiekbestanden):');
        foreach ($gelezen as $g) {
            $this->line('  '.$g);
        }
        $this->line('');
        $this->line('Adresveld: '.($ipVeld ?: 'c-ip').($ipVeld && $ipVeld !== 'c-ip'
            ? '  (echte client-IP achter Cloudflare)'
            : '  ⚠ achter Cloudflare is dit de edge-node, niet de bezoeker; per-adres-cijfers zijn dan een ONDERgrens van het aantal bezoekers'));
        $this->line('');

        if ($perHost === []) {
            $this->warn('Geen regels voor de gevraagde hosts. Hosts in de logs worden alleen geteld als cs-host gelogd wordt.');

            return self::FAILURE;
        }

        ksort($perHost);
        $totaal = ['klassen' => [], 'ips' => []];

        foreach ($perHost as $host => $ips) {
            $this->rapporteerHost($host, $ips, $top, $totaal);
        }

        $this->rapporteerTotaal($totaal);

        return self::SUCCESS;
    }

    // ── Logbestanden vinden ───────────────────────────────────────────────

    /** @return list<string> */
    private function kandidaatMappen(): array
    {
        $patronen = [
            'C:/inetpub/vhosts/*/logs/W3SVC*',
            'C:/inetpub/vhosts/*/logs/iis/W3SVC*',
            'C:/inetpub/vhosts/*/logs/*/W3SVC*',
            'C:/inetpub/vhosts/*/logs/*/iis/W3SVC*',
            'C:/inetpub/vhosts/*/*/logs/W3SVC*',
            'C:/inetpub/vhosts/*/*/logs/iis/W3SVC*',
            'C:/inetpub/logs/LogFiles/W3SVC*',
        ];
        // Plesk laat een glob over C:/inetpub/vhosts/* leeg; de eigen vhost-root
        // (…/betergeregeld.com, één niveau boven httpdocs) is wél direct benaderbaar.
        $eigen = str_replace('\\', '/', dirname(base_path()));
        foreach (['/logs/W3SVC*', '/logs/iis/W3SVC*', '/logs/*/W3SVC*', '/logs/iis/*/W3SVC*'] as $sub) {
            $patronen[] = $eigen.$sub;
        }
        $mappen = [];
        foreach ($patronen as $p) {
            foreach ((array) @glob($p, GLOB_ONLYDIR) as $m) {
                $mappen[str_replace('\\', '/', $m)] = true;
            }
        }
        $mappen = array_keys($mappen);
        sort($mappen);

        return $mappen;
    }

    private function toonLogmappen(array $vhostFilter): void
    {
        $this->line('Gevonden IIS-logmappen (filter op pad: '.implode(', ', $vhostFilter).'):');
        foreach ($this->kandidaatMappen() as $map) {
            $files = (array) @glob($map.'/*.log');
            $leesbaar = 0;
            $bytes = 0;
            $nieuwste = 0;
            foreach ($files as $f) {
                if (@is_readable($f) && ($fh = @fopen($f, 'rb'))) {
                    fclose($fh);
                    $leesbaar++;
                    $bytes += (int) @filesize($f);
                    $nieuwste = max($nieuwste, (int) @filemtime($f));
                }
            }
            $match = $this->padMatch($map, $vhostFilter) ? '*' : ' ';
            $this->line(sprintf('  %s %-70s %3d bestanden, %3d leesbaar, %6.1f MB, nieuwste %s',
                $match, $this->kortPad($map), count($files), $leesbaar, $bytes / 1048576,
                $nieuwste ? date('Y-m-d H:i', $nieuwste) : '-'));
        }
        $this->line('  (* = valt binnen het pad-filter)');

        // Plesk schermt vhosts van elkaar af: een glob over C:/inetpub/vhosts/* geeft dan
        // niets, terwijl een direct pad binnen de eigen vhost wél leesbaar is. Laat zien
        // wat er onder de opgegeven paden (en de eigen vhost-root) te vinden is.
        $roots = array_merge((array) $this->option('pad'), [
            'C:/inetpub/vhosts/betergeregeld.com', 'C:/inetpub/vhosts/betergeregeld.com/logs',
            dirname(base_path()), dirname(base_path()).'/logs',
        ]);
        // Wie zijn we? Nodig om leesrechten op de logmap te kunnen geven (icacls).
        $wie = function_exists('exec') ? trim((string) @exec('whoami')) : '';
        $this->line('');
        $this->line('Draait als: '.($wie !== '' ? $wie : '(onbekend, exec uit)'));
        $this->line('');
        $this->line('Directe paden:');
        foreach (array_unique($roots) as $root) {
            $root = rtrim(str_replace('\\', '/', $root), '/');
            $items = @scandir($root);
            if ($items === false) {
                $this->line('  '.$root.'  → niet leesbaar/bestaat niet');

                continue;
            }
            $items = array_values(array_diff($items, ['.', '..']));
            $this->line('  '.$root.'  → '.count($items).' items: '.implode(', ', array_slice($items, 0, 25)));
            foreach (['', '/*', '/*/*'] as $diep) {
                foreach ((array) @glob($root.$diep.'/*.log') as $f) {
                    $this->line(sprintf('      %-80s %6.1f MB  %s  %s', str_replace('\\', '/', $f), (int) @filesize($f) / 1048576,
                        date('Y-m-d H:i', (int) @filemtime($f)), @is_readable($f) ? 'leesbaar' : 'NIET leesbaar'));
                }
            }
        }
    }

    private function padMatch(string $pad, array $vhostFilter): bool
    {
        foreach ($vhostFilter as $f) {
            if ($f !== '' && stripos($pad, $f) !== false) {
                return true;
            }
        }

        return false;
    }

    /** @return list<string> */
    private function vindLogbestanden(array $vhostFilter, int $dagen, array $extra): array
    {
        $sinds = time() - $dagen * 86400;
        $uit = [];

        foreach ($this->kandidaatMappen() as $map) {
            if (! $this->padMatch($map, $vhostFilter)) {
                continue;
            }
            foreach ((array) @glob($map.'/*.log') as $f) {
                if ((int) @filemtime($f) >= $sinds && @is_readable($f)) {
                    $uit[] = str_replace('\\', '/', $f);
                }
            }
        }

        foreach ($extra as $p) {
            $p = str_replace('\\', '/', $p);
            if (is_dir($p)) {
                // Een opgegeven map mag de vhost-root zijn: tot drie niveaus diep zoeken.
                foreach (['', '/*', '/*/*', '/*/*/*'] as $diep) {
                    foreach ((array) @glob(rtrim($p, '/').$diep.'/*.log') as $f) {
                        if ((int) @filemtime($f) >= $sinds && @is_readable($f)) {
                            $uit[] = str_replace('\\', '/', $f);
                        }
                    }
                }
            } elseif (is_file($p)) {
                $uit[] = $p;
            }
        }

        $uit = array_values(array_unique($uit));
        sort($uit);

        return $uit;
    }

    private function kortPad(string $pad): string
    {
        return preg_replace('~^C:/inetpub/vhosts/~i', '…/', $pad) ?? $pad;
    }

    // ── Log lezen ─────────────────────────────────────────────────────────

    /**
     * Leest één IIS-log (W3C-formaat) en telt per host en adres.
     *
     * @param  array<string,array<string,array<string,mixed>>>  $perHost
     * @return array{regels:int,afgekapt:bool}|null
     */
    private function leesLog(string $pad, array $hosts, int $maxBytes, array &$perHost, ?string &$ipVeld): ?array
    {
        $fh = @fopen($pad, 'rb');
        if (! $fh) {
            return null;
        }

        $grootte = (int) @filesize($pad);
        $afgekapt = false;

        // Velden staan in de #Fields-kop; die kan meermaals voorkomen (na een IIS-herstart).
        $velden = null;
        $kop = '';
        $n = 0;
        while (($regel = fgets($fh)) !== false && $n++ < 40) {
            if (strncmp($regel, '#Fields:', 8) === 0) {
                $velden = preg_split('/\s+/', trim(substr($regel, 8)));
                break;
            }
        }
        if (! $velden) {
            fclose($fh);

            return null;
        }

        if ($grootte > $maxBytes) {
            // Alleen de staart; de eerste (halve) regel weggooien.
            fseek($fh, $grootte - $maxBytes);
            fgets($fh);
            $afgekapt = true;
        } else {
            rewind($fh);
        }

        $idx = array_flip($velden);
        $iDatum = $idx['date'] ?? null;
        $iTijd = $idx['time'] ?? null;
        $iMethode = $idx['cs-method'] ?? null;
        $iPad = $idx['cs-uri-stem'] ?? null;
        $iHost = $idx['cs-host'] ?? null;
        $iStatus = $idx['sc-status'] ?? null;
        $iUa = $idx['cs(User-Agent)'] ?? null;
        $iRef = $idx['cs(Referer)'] ?? null;
        $iIp = $idx['c-ip'] ?? null;
        $iCf = $idx['cs(CF-Connecting-IP)'] ?? $idx['cs(X-Forwarded-For)'] ?? $idx['cs(True-Client-IP)'] ?? null;
        if ($iCf !== null) {
            $ipVeld = $velden[$iCf];
        } elseif ($ipVeld === null && $iIp !== null) {
            $ipVeld = 'c-ip';
        }

        if ($iPad === null || $iUa === null || $iStatus === null) {
            fclose($fh);

            return null;
        }

        $aantal = count($velden);
        $regels = 0;
        $hostFilter = array_map('strtolower', $hosts);

        while (($regel = fgets($fh)) !== false) {
            if ($regel === '' || $regel[0] === '#') {
                // Nieuwe #Fields-kop halverwege: velden opnieuw indexeren.
                if (strncmp($regel, '#Fields:', 8) === 0) {
                    $velden = preg_split('/\s+/', trim(substr($regel, 8)));
                    $idx = array_flip($velden);
                    $iDatum = $idx['date'] ?? null;
                    $iTijd = $idx['time'] ?? null;
                    $iMethode = $idx['cs-method'] ?? null;
                    $iPad = $idx['cs-uri-stem'] ?? null;
                    $iHost = $idx['cs-host'] ?? null;
                    $iStatus = $idx['sc-status'] ?? null;
                    $iUa = $idx['cs(User-Agent)'] ?? null;
                    $iRef = $idx['cs(Referer)'] ?? null;
                    $iIp = $idx['c-ip'] ?? null;
                    $iCf = $idx['cs(CF-Connecting-IP)'] ?? $idx['cs(X-Forwarded-For)'] ?? $idx['cs(True-Client-IP)'] ?? null;
                    $aantal = count($velden);
                }

                continue;
            }
            $d = explode(' ', rtrim($regel, "\r\n"));
            if (count($d) < $aantal) {
                continue;
            }
            $regels++;

            $host = $iHost !== null ? strtolower($d[$iHost]) : '?';
            if (str_starts_with($host, 'www.')) {
                $host = substr($host, 4);
            }
            if ($hostFilter !== []) {
                if (! in_array($host, $hostFilter, true)) {
                    continue;
                }
            } elseif (! ($host === 'betergeregeld.com' || (str_starts_with($host, 'jouw-') && str_ends_with($host, '-website.nl')))) {
                continue;
            }

            $ip = $iCf !== null ? $d[$iCf] : ($iIp !== null ? $d[$iIp] : '?');
            if ($ip === '-' || $ip === '') {
                $ip = $iIp !== null ? $d[$iIp] : '?';
            }
            // X-Forwarded-For kan een lijst zijn; de eerste is de client.
            if (($komma = strpos($ip, ',')) !== false) {
                $ip = substr($ip, 0, $komma);
            }

            $ua = $d[$iUa];
            $uri = $d[$iPad];
            $status = (int) $d[$iStatus];
            $methode = $iMethode !== null ? $d[$iMethode] : 'GET';
            $tijd = ($iDatum !== null && $iTijd !== null) ? ($d[$iDatum].' '.$d[$iTijd]) : '';

            // Sleutel = adres + user-agent: achter Cloudflare delen bezoekers een edge-adres,
            // en ook achter een bedrijfs-NAT scheidt de user-agent mens en bot van elkaar.
            $r = &$perHost[$host][$ip.'|'.$ua];
            if ($r === null) {
                $r = [
                    'ip' => $ip, 'ua' => $ua, 'n' => 0, 'html' => 0, 'asset' => 0, 'ev' => 0, 'cmp' => 0, 'kiosk' => 0,
                    'e4' => 0, 'scan' => 0, 'ref' => 0, 'paden' => [], 'eerste' => $tijd, 'laatste' => $tijd,
                    'dagen' => [], 'perDag' => [],
                ];
            }
            $r['n']++;
            $r['laatste'] = $tijd;
            if ($tijd !== '') {
                $r['dagen'][substr($tijd, 0, 10)] = true;
            }
            if ($status >= 400 && $status < 500) {
                $r['e4']++;
            }
            if ($iRef !== null && $d[$iRef] !== '-' && $d[$iRef] !== '') {
                $r['ref']++;
            }
            if ($methode === 'POST' && $uri === '/_ev') {
                $r['ev']++;
            } elseif (preg_match('~^/cmp/~', $uri)) {
                $r['cmp']++;
            } elseif (str_starts_with($uri, '/scherm/')) {
                $r['kiosk']++;
            } elseif (preg_match(self::SCAN_PAD, $uri)) {
                $r['scan']++;
            } elseif (preg_match(self::ASSET_PAD, $uri)) {
                $r['asset']++;
            } elseif (! preg_match(self::ACHTERGROND_PAD, $uri) && $methode === 'GET') {
                $r['html']++;
                if ($tijd !== '') {
                    $dag = substr($tijd, 0, 10);
                    $r['perDag'][$dag] = ($r['perDag'][$dag] ?? 0) + 1;
                }
                if (count($r['paden']) < 6) {
                    $r['paden'][$uri] = true;
                }
            }
            unset($r);
        }
        fclose($fh);

        return ['regels' => $regels, 'afgekapt' => $afgekapt];
    }

    // ── Classificatie ─────────────────────────────────────────────────────

    private function botNaam(string $ua): ?string
    {
        if ($ua === '-' || $ua === '') {
            return 'leeg';
        }
        foreach (self::BOTS as $naam => $re) {
            if (preg_match($re, $ua)) {
                return $naam;
            }
        }

        return null;
    }

    /**
     * Eén adres → klasse. Bekende bots op naam; de rest op gedrag.
     *
     * @param  array<string,mixed>  $r
     */
    private function klasse(array $r): string
    {
        $ua = (string) $r['ua'];
        // Eigen verkeer eerst: de cache-warm-up (ChannelPlacesWarm e.d.) en het kiosk-scherm.
        if (str_contains($ua, 'BG+warm-up') || str_contains($ua, 'BG warm-up')) {
            return 'eigen: warm-up';
        }
        if ($r['html'] === 0 && $r['kiosk'] > 0) {
            return 'eigen: kiosk-scherm';
        }
        $bot = $this->botNaam($ua);
        if ($bot !== null) {
            return 'bot: '.$bot;
        }
        $n = (int) $r['n'];
        $html = (int) $r['html'];

        // Scanner: probeert webshells/.env/wp-login, of krijgt bijna alleen 4xx.
        if ($r['scan'] >= 2 || ($n >= 4 && $r['e4'] / $n >= 0.6)) {
            return 'scanner';
        }
        // Omvang: 80+ pagina's op één dag vanaf één adres+UA is geen mens meer.
        if ($html >= 80 && count($r['dagen']) <= 1) {
            return 'bot: browser-UA, massaal';
        }
        // JS-bewijs: de beacon (POST /_ev) of /cmp/loader.js (niet door Cloudflare
        // gecachet, dus bereikt IIS) is alleen door een echte browser-engine op te vragen.
        if ($html >= 1 && ($r['ev'] >= 1 || $r['cmp'] >= 1 || $r['asset'] >= 2)) {
            return 'mens';
        }
        // Alleen HTML. Achter Cloudflare zegt "geen assets" weinig: statische bestanden
        // komen uit de edge-cache en de loader kan via een ander edge-adres binnenkomen.
        // Tot 30 pagina's houden we het op "mens?", daarboven is het crawlgedrag.
        if ($html >= 30) {
            return 'bot: browser-UA, veel pagina\'s';
        }
        if ($html >= 1) {
            return 'mens?';
        }

        return 'alleen assets/achtergrond';
    }

    // ── Rapport ───────────────────────────────────────────────────────────

    /**
     * @param  array<string,array<string,mixed>>  $ips
     * @param  array{klassen:array<string,array{ips:int,html:int}>,ips:array<string,string>}  $totaal
     */
    private function rapporteerHost(string $host, array $ips, int $top, array &$totaal): void
    {
        $klassen = [];
        $verdacht = [];
        $beacon = [];
        $perDag = [];
        $js = 0;

        foreach ($ips as $sleutel => $r) {
            $ip = $r['ip'];
            $k = $this->klasse($r);
            $klassen[$k]['ips'] = ($klassen[$k]['ips'] ?? 0) + 1;
            $klassen[$k]['html'] = ($klassen[$k]['html'] ?? 0) + $r['html'];
            $klassen[$k]['n'] = ($klassen[$k]['n'] ?? 0) + $r['n'];
            $totaal['klassen'][$k]['ips'] = ($totaal['klassen'][$k]['ips'] ?? 0) + 1;
            $totaal['klassen'][$k]['html'] = ($totaal['klassen'][$k]['html'] ?? 0) + $r['html'];
            $totaal['ips'][$sleutel] = $k;

            $groep = match (true) {
                $k === 'mens' => 'mens',
                $k === 'mens?' => 'mens?',
                str_starts_with($k, 'eigen') => 'eigen',
                default => 'bot',
            };
            foreach ($r['perDag'] as $dag => $pag) {
                $perDag[$dag][$groep] = ($perDag[$dag][$groep] ?? 0) + $pag;
            }
            if ($groep === 'mens' || $groep === 'mens?') {
                $js += $r['cmp'];
            }

            if ($r['ev'] > 0) {
                $bk = $this->botNaam((string) $r['ua']) ?? ($k === 'mens' || $k === 'mens?' ? 'browser' : $k);
                $beacon[$bk] = ($beacon[$bk] ?? 0) + $r['ev'];
            }
            if (str_starts_with($k, 'bot: browser-UA') || $k === 'scanner') {
                $verdacht[$sleutel] = $r + ['klasse' => $k];
            }
        }

        uasort($klassen, fn ($a, $b) => $b['html'] <=> $a['html']);

        $this->line(str_repeat('=', 96));
        $this->line(strtoupper($host).'   ('.count($ips).' adres+user-agent-combinaties)');
        $this->line(str_repeat('=', 96));
        $this->line(sprintf('  %-34s %7s %10s %10s', 'klasse', 'adressen', 'pagina\'s', 'verzoeken'));
        foreach ($klassen as $k => $c) {
            $this->line(sprintf('  %-34s %7d %10d %10d', $k, $c['ips'], $c['html'], $c['n']));
        }

        if ($perDag !== []) {
            ksort($perDag);
            $this->line('');
            $this->line('  Paginaweergaven per dag (UTC-datum uit het log):');
            foreach ($perDag as $dag => $c) {
                $this->line(sprintf('    %s  mens %5d   mens? %5d   bot/scanner %5d   eigen %5d', $dag, $c['mens'] ?? 0, $c['mens?'] ?? 0, $c['bot'] ?? 0, $c['eigen'] ?? 0));
            }
            $this->line(sprintf('  JS-bewijs: /cmp/loader.js opgehaald door browser-UA (≈ browsersessies; max 1 per 5 min per browser): %d', $js));
        }

        if ($beacon !== []) {
            arsort($beacon);
            $this->line('');
            $this->line('  Wie vuurt de page_view-beacon (POST /_ev) — dit is wat het kiosk-scherm telt:');
            foreach ($beacon as $wie => $n) {
                $this->line(sprintf('    %-30s %6d', $wie, $n));
            }
        }

        if ($verdacht !== []) {
            uasort($verdacht, fn ($a, $b) => $b['html'] <=> $a['html']);
            $this->line('');
            $this->line('  Verdachte adressen met browser-user-agent (top '.$top.'):');
            foreach (array_slice($verdacht, 0, $top, true) as $r) {
                $this->line(sprintf('    %-39s %-28s pag %5d  4xx %4d  scan %3d  assets %3d  %s → %s',
                    $r['ip'], $r['klasse'], $r['html'], $r['e4'], $r['scan'], $r['asset'],
                    substr($r['eerste'], 11, 5), substr($r['laatste'], 11, 5)));
                $this->line('        UA: '.mb_substr((string) $r['ua'], 0, 110));
                $this->line('        bv: '.implode('  ', array_slice(array_keys($r['paden']), 0, 4)));
            }
        }
        $this->line('');
    }

    /**
     * @param  array{klassen:array<string,array{ips:int,html:int}>,ips:array<string,string>}  $totaal
     */
    private function rapporteerTotaal(array $totaal): void
    {
        $k = $totaal['klassen'];
        $som = fn (callable $f) => array_sum(array_map($f, array_keys($k), $k));

        $mensPag = $som(fn ($naam, $c) => $naam === 'mens' ? $c['html'] : 0);
        $mensMisPag = $som(fn ($naam, $c) => $naam === 'mens?' ? $c['html'] : 0);
        $eigenPag = $som(fn ($naam, $c) => str_starts_with($naam, 'eigen') ? $c['html'] : 0);
        $botPag = $som(fn ($naam, $c) => ($naam === 'mens' || $naam === 'mens?' || str_starts_with($naam, 'eigen')) ? 0 : $c['html']);
        $alle = max(1, $mensPag + $mensMisPag + $botPag);

        $uniek = array_count_values($totaal['ips']);

        $this->line(str_repeat('=', 96));
        $this->line('TOTAAL over alle gerapporteerde hosts (paginaweergaven, geen assets/achtergrond)');
        $this->line(str_repeat('=', 96));
        $this->line(sprintf('  mens          %7d  (%4.1f%%)   unieke adressen: %d', $mensPag, 100 * $mensPag / $alle, $uniek['mens'] ?? 0));
        $this->line(sprintf('  mens?         %7d  (%4.1f%%)   unieke adressen: %d   (1-3 pagina\'s zonder assets; kan cache zijn)', $mensMisPag, 100 * $mensMisPag / $alle, $uniek['mens?'] ?? 0));
        $this->line(sprintf('  bot/scanner   %7d  (%4.1f%%)', $botPag, 100 * $botPag / $alle));
        $this->line(sprintf('  eigen verkeer %7d  (buiten de percentages: warm-up + kiosk-scherm)', $eigenPag));
        $this->line('');
        $this->line('  Leeswijzer: "mens" = browser-UA die pagina\'s én scripts/assets laadde of de beacon vuurde.');
        $this->line('  Achter Cloudflare zonder CF-Connecting-IP in het log delen bezoekers één adres → tel dan');
        $this->line('  de beacon-regel en de paginaweergaven, niet de adressen.');
    }
}
