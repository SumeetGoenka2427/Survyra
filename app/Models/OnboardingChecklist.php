<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OnboardingChecklist extends Model
{
    protected $fillable = [
        'client_id',
        'profile_completed',
        'first_survey_created',
        'first_survey_published',
        'first_campaign_sent',
        'theme_customized',
        'integrations_configured',
        'dismissed',
    ];

    protected function casts(): array
    {
        return [
            'profile_completed' => 'boolean',
            'first_survey_created' => 'boolean',
            'first_survey_published' => 'boolean',
            'first_campaign_sent' => 'boolean',
            'theme_customized' => 'boolean',
            'integrations_configured' => 'boolean',
            'dismissed' => 'boolean',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * @return array<int, array{key: string, label: string, done: bool}>
     */
    public function toChecklistArray(): array
    {
        return [
            ['key' => 'profile_completed', 'label' => 'Complete company profile', 'done' => $this->profile_completed],
            ['key' => 'first_survey_created', 'label' => 'Create your first survey', 'done' => $this->first_survey_created],
            ['key' => 'first_survey_published', 'label' => 'Publish a survey', 'done' => $this->first_survey_published],
            ['key' => 'first_campaign_sent', 'label' => 'Send your first campaign', 'done' => $this->first_campaign_sent],
            ['key' => 'theme_customized', 'label' => 'Customize your survey theme', 'done' => $this->theme_customized],
            ['key' => 'integrations_configured', 'label' => 'Set up integrations', 'done' => $this->integrations_configured],
        ];
    }

    public function progressPercent(): int
    {
        $items = $this->toChecklistArray();
        $done = count(array_filter($items, fn ($item) => $item['done']));
        $total = count($items);

        return $total > 0 ? (int) round($done / $total * 100) : 0;
    }
}