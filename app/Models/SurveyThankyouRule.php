<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SurveyThankyouRule extends Model
{
    protected $fillable = [
        'survey_id',
        'sentiment',
        'min_score',
        'max_score',
        'thank_you_message',
        'show_google_review',
        'show_facebook',
        'show_instagram',
        'show_website',
        'show_coupon',
        'coupon_code',
        'show_complaint_form',
        'show_support_number',
        'show_whatsapp_button',
        'manager_contact',
    ];

    protected function casts(): array
    {
        return [
            'show_google_review' => 'boolean',
            'show_facebook' => 'boolean',
            'show_instagram' => 'boolean',
            'show_website' => 'boolean',
            'show_coupon' => 'boolean',
            'show_complaint_form' => 'boolean',
            'show_support_number' => 'boolean',
            'show_whatsapp_button' => 'boolean',
            'manager_contact' => 'array',
        ];
    }

    /**
     * @return BelongsTo<Survey, $this>
     */
    public function survey(): BelongsTo
    {
        return $this->belongsTo(Survey::class);
    }
}
