<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Survey extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $fillable = [
        'client_id',
        'survey_template_id',
        'theme_id',
        'primary_score_question_id',
        'title',
        'slug',
        'version',
        'layout',
        'status',
        'settings',
        'welcome_screen',
        'expires_at',
        'max_responses',
        'is_anonymous',
        'gdpr_enabled',
        'gdpr_text',
        'privacy_policy_url',
        'ga_tracking_id',
        'meta_pixel_id',
        'published_at',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'settings' => 'array',
            'welcome_screen' => 'array',
            'published_at' => 'datetime',
            'expires_at' => 'datetime',
            'max_responses' => 'integer',
            'is_anonymous' => 'boolean',
            'gdpr_enabled' => 'boolean',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['title', 'status', 'layout', 'settings', 'is_anonymous', 'gdpr_enabled'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    public function isResponseLimitReached(): bool
    {
        return $this->max_responses !== null
            && $this->responses()->where('status', 'completed')->count() >= $this->max_responses;
    }

    /**
     * @return BelongsTo<Client, $this>
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * @return BelongsTo<SurveyTemplate, $this>
     */
    public function template(): BelongsTo
    {
        return $this->belongsTo(SurveyTemplate::class, 'survey_template_id');
    }

    /**
     * @return BelongsTo<SurveyTheme, $this>
     */
    public function theme(): BelongsTo
    {
        return $this->belongsTo(SurveyTheme::class, 'theme_id');
    }

    /**
     * @return BelongsTo<SurveyQuestion, $this>
     */
    public function primaryScoreQuestion(): BelongsTo
    {
        return $this->belongsTo(SurveyQuestion::class, 'primary_score_question_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * @return HasMany<SurveyQuestion, $this>
     */
    public function questions(): HasMany
    {
        return $this->hasMany(SurveyQuestion::class)->orderBy('order');
    }

    /**
     * @return HasMany<SurveyLogicRule, $this>
     */
    public function logicRules(): HasMany
    {
        return $this->hasMany(SurveyLogicRule::class);
    }

    /**
     * @return HasMany<SurveyThankyouRule, $this>
     */
    public function thankyouRules(): HasMany
    {
        return $this->hasMany(SurveyThankyouRule::class);
    }

    /**
     * @return HasMany<Response, $this>
     */
    public function responses(): HasMany
    {
        return $this->hasMany(Response::class);
    }

    /**
     * @return HasMany<Campaign, $this>
     */
    public function campaigns(): HasMany
    {
        return $this->hasMany(Campaign::class);
    }

    /**
     * @return HasMany<QrCode, $this>
     */
    public function qrCodes(): HasMany
    {
        return $this->hasMany(QrCode::class);
    }
}
