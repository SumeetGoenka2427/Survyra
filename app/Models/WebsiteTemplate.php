<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A read-only, industry-specific starter blueprint for a new Website:
 * `structure` holds the same pages->sections JSON shape as
 * `websites.published_snapshot`, materialized into real WebsitePage/
 * WebsiteSection rows by WebsiteService::createFromTemplate().
 *
 * Deliberately seeder-only for now (no admin CRUD UI), unlike the older
 * SurveyTemplate/SurveyTemplateQuestion system which is fully relational and
 * portal/admin-editable. If template authoring ever needs a UI, that's a
 * real follow-on piece of work (migrating this JSON into relational rows or
 * building a structure editor) - not a free upgrade of this model.
 */
class WebsiteTemplate extends Model
{
    protected $fillable = [
        'key',
        'name',
        'industry',
        'description',
        'preview_image',
        'theme_id',
        'structure',
        'is_active',
        'usage_count',
    ];

    protected function casts(): array
    {
        return [
            'structure' => 'array',
            'is_active' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<WebsiteTheme, $this>
     */
    public function theme(): BelongsTo
    {
        return $this->belongsTo(WebsiteTheme::class, 'theme_id');
    }
}
