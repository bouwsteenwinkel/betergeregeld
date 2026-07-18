<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Eén first-party funnel-event (zie de migratie). Append-only; alleen created_at.
 */
class ChannelEvent extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = ['event', 'site_key', 'visit_ref', 'path', 'params', 'created_at'];

    protected $casts = ['params' => 'array', 'created_at' => 'datetime'];

    /**
     * De events die we server-side vastleggen. Bewust een allowlist: micro-events als
     * section_view/cta_click (die vaak per pagina vuren) laten we buiten om ruis en
     * schrijfvolume te beperken. De sleutel-funnel + de Meta-conversies staan erin.
     *
     * @var array<int,string>
     */
    public const ALLOWED = [
        'page_view',
        'preview_start',
        'preview_ready',
        'preview_failed',
        'preview_saved',
        'planner_opened',
        'lead_submit',
        'appointment_booked',
    ];
}
