<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Client extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $fillable = [
        'company_name',
        'industry',
        'logo_path',
        'email',
        'phone',
        'website',
        'address',
        'google_review_url',
        'facebook_url',
        'instagram_url',
        'linkedin_url',
        'youtube_url',
        'support_number',
        'whatsapp_number',
        'brand_color',
        'secondary_color',
        'timezone',
        'language',
        'status',
        'subscription_plan_id',
        'created_by',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['company_name', 'email', 'phone', 'status', 'subscription_plan_id'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    protected function casts(): array
    {
        return [
            'support_number' => 'encrypted',
            'whatsapp_number' => 'encrypted',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Client $client) {
            $client->uuid ??= (string) Str::uuid();
        });
    }

    /**
     * Fields a Client portal user is allowed to self-manage (§5: "Manage company profile").
     *
     * @var list<string>
     */
    public static function companyProfileFields(): array
    {
        return [
            'logo_path',
            'phone',
            'website',
            'address',
            'google_review_url',
            'facebook_url',
            'instagram_url',
            'linkedin_url',
            'youtube_url',
            'support_number',
            'whatsapp_number',
        ];
    }

    /**
     * @return BelongsTo<SubscriptionPlan, $this>
     */
    public function subscriptionPlan(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPlan::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * @return HasMany<ClientUser, $this>
     */
    public function clientUsers(): HasMany
    {
        return $this->hasMany(ClientUser::class);
    }

    /**
     * @return HasMany<Contact, $this>
     */
    public function contacts(): HasMany
    {
        return $this->hasMany(Contact::class);
    }

    /**
     * @return HasMany<ContactTag, $this>
     */
    public function contactTags(): HasMany
    {
        return $this->hasMany(ContactTag::class);
    }

    /**
     * @return HasMany<Survey, $this>
     */
    public function surveys(): HasMany
    {
        return $this->hasMany(Survey::class);
    }

    /**
     * @return HasMany<Campaign, $this>
     */
    public function campaigns(): HasMany
    {
        return $this->hasMany(Campaign::class);
    }

    /**
     * @return HasMany<Report, $this>
     */
    public function reports(): HasMany
    {
        return $this->hasMany(Report::class);
    }

    public function getSlackWebhookUrl(): ?string
    {
        return \App\Models\Setting::where('client_id', $this->id)
            ->where('group', 'integrations')
            ->where('key', 'slack_webhook_url')
            ->value('value');
    }
}
