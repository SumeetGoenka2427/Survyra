<?php

namespace App\Models;

use App\Models\Concerns\BelongsToClient;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Website extends Model
{
    use BelongsToClient, HasFactory, LogsActivity, SoftDeletes;

    protected $fillable = [
        'client_id',
        'theme_id',
        'name',
        'slug',
        'status',
        'custom_domain',
        'published_snapshot',
        'published_at',
        'meta_description',
        'favicon_path',
        'og_image',
        'social_links',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'published_snapshot' => 'array',
            'social_links' => 'array',
            'published_at' => 'datetime',
        ];
    }

    /**
     * Every social platform the builder/footer supports, keyed by the field
     * name used in `social_links`. Centralized here so the portal form, the
     * public footer, and the preview footer all render the same set from one
     * place - add a platform once, it shows up everywhere.
     *
     * @return array<string, array{label: string, icon: string, placeholder: string}>
     */
    public static function socialPlatforms(): array
    {
        return [
            'facebook' => ['label' => 'Facebook', 'icon' => 'bi-facebook', 'placeholder' => 'https://facebook.com/yourpage'],
            'instagram' => ['label' => 'Instagram', 'icon' => 'bi-instagram', 'placeholder' => 'https://instagram.com/yourhandle'],
            'twitter' => ['label' => 'X (Twitter)', 'icon' => 'bi-twitter-x', 'placeholder' => 'https://x.com/yourhandle'],
            'linkedin' => ['label' => 'LinkedIn', 'icon' => 'bi-linkedin', 'placeholder' => 'https://linkedin.com/company/yourcompany'],
            'youtube' => ['label' => 'YouTube', 'icon' => 'bi-youtube', 'placeholder' => 'https://youtube.com/@yourchannel'],
            'tiktok' => ['label' => 'TikTok', 'icon' => 'bi-tiktok', 'placeholder' => 'https://tiktok.com/@yourhandle'],
            'pinterest' => ['label' => 'Pinterest', 'icon' => 'bi-pinterest', 'placeholder' => 'https://pinterest.com/yourhandle'],
            'whatsapp' => ['label' => 'WhatsApp', 'icon' => 'bi-whatsapp', 'placeholder' => 'https://wa.me/15551234567'],
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'slug', 'status'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    /**
     * @return BelongsTo<WebsiteTheme, $this>
     */
    public function theme(): BelongsTo
    {
        return $this->belongsTo(WebsiteTheme::class, 'theme_id');
    }

    /**
     * @return BelongsTo<ClientUser, $this>
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(ClientUser::class, 'created_by');
    }

    /**
     * @return HasMany<WebsitePage, $this>
     */
    public function pages(): HasMany
    {
        return $this->hasMany(WebsitePage::class)->orderBy('order');
    }

    /**
     * @return HasMany<WebsiteLead, $this>
     */
    public function leads(): HasMany
    {
        return $this->hasMany(WebsiteLead::class);
    }
}
