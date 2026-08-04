<?php

namespace App\Models;

use App\Models\Concerns\BelongsToClient;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WebsiteLead extends Model
{
    use BelongsToClient;

    public const STATUSES = ['new', 'handled', 'spam'];

    protected $fillable = [
        'client_id',
        'website_id',
        'website_page_id',
        'section_id',
        'data',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'data' => 'array',
        ];
    }

    /**
     * @return BelongsTo<Website, $this>
     */
    public function website(): BelongsTo
    {
        return $this->belongsTo(Website::class);
    }

    /**
     * @return BelongsTo<WebsitePage, $this>
     */
    public function page(): BelongsTo
    {
        return $this->belongsTo(WebsitePage::class, 'website_page_id');
    }

    /**
     * @return BelongsTo<WebsiteSection, $this>
     */
    public function section(): BelongsTo
    {
        return $this->belongsTo(WebsiteSection::class, 'section_id');
    }
}
