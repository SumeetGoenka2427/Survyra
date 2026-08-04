<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WebsiteSection extends Model
{
    protected $fillable = [
        'page_id',
        'section_type_id',
        'content',
        'settings',
        'order',
    ];

    protected function casts(): array
    {
        return [
            'content' => 'array',
            'settings' => 'array',
        ];
    }

    /**
     * @return BelongsTo<WebsitePage, $this>
     */
    public function page(): BelongsTo
    {
        return $this->belongsTo(WebsitePage::class, 'page_id');
    }

    /**
     * @return BelongsTo<SectionType, $this>
     */
    public function sectionType(): BelongsTo
    {
        return $this->belongsTo(SectionType::class);
    }
}
