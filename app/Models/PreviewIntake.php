<?php

namespace App\Models;

use App\Models\Channel\Site;
use Illuminate\Database\Eloquent\Model;

/**
 * De intake van een voorbeeldsite die verliep zonder dat iemand 'm bewaarde.
 *
 * Bestaat omdat de preview zelf na 48u wordt opgeruimd ({@see \App\Console\Commands\ChannelPreviewsCleanup}):
 * de bezoeker typte bedrijfsnaam, plaats, doel en USP in en verdween, en zonder dit
 * archief verdampt die salesdata zonder dat iemand ziet hoe groot de groep is.
 *
 * Er hoort geen e-mailadres bij: had de bezoeker dat achtergelaten, dan was het een
 * {@see WebsiteLead} geweest en was de preview blijven staan.
 */
class PreviewIntake extends Model
{
    protected $fillable = [
        'site_key',
        'company', 'business_type', 'place', 'goal', 'sfeer', 'usp', 'key_services',
        'source_channel',
        'preview_created_at', 'expired_at',
    ];

    protected $casts = [
        'preview_created_at' => 'datetime',
        'expired_at'         => 'datetime',
    ];

    /**
     * Archiveert de intake van een verlopen, niet-opgeëiste preview.
     *
     * updateOrCreate op site_key en niet create(): faalt het verwijderen van de site na
     * een geslaagde archivering, dan ziet de volgende cleanup-run dezelfde preview
     * opnieuw en mag dat geen tweede rij opleveren.
     *
     * Bewust alleen de velden uit meta.preview.input die iets over het BEDRIJF zeggen.
     * De kleurkeuze laten we vallen: dat is een ontwerpvoorkeur, geen salesdata.
     */
    public static function archive(Site $site, ?\Illuminate\Support\Carbon $expiredAt): self
    {
        $input = (array) data_get($site->meta, 'preview.input', []);

        return static::updateOrCreate(
            ['site_key' => $site->key],
            [
                'company'            => static::clip($input['company'] ?? null, 190),
                'business_type'      => static::clip($input['business_type'] ?? null, 190),
                'place'              => static::clip($input['place'] ?? null, 190),
                'goal'               => static::clip($input['goal'] ?? null, 120),
                'sfeer'              => static::clip($input['sfeer'] ?? null, 120),
                'usp'                => static::clip($input['usp'] ?? null),
                'key_services'       => static::clip($input['key_services'] ?? null),
                'source_channel'     => static::clip(data_get($site->meta, 'preview.source_channel'), 120),
                'preview_created_at' => $site->created_at,
                'expired_at'         => $expiredAt,
            ],
        );
    }

    /**
     * De tool valideert zijn invoer, maar deze meta is jaren oud kunnen zijn en de
     * kolommen zijn smaller dan sommige invoervelden. Afkappen vóór de insert, zodat
     * een te lange USP de hele cleanup-run niet laat klappen op een SQL-fout.
     */
    private static function clip(mixed $value, ?int $max = null): ?string
    {
        $value = is_string($value) ? trim($value) : null;

        if ($value === null || $value === '') {
            return null;
        }

        return $max ? mb_substr($value, 0, $max) : $value;
    }
}
