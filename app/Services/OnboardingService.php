<?php

namespace App\Services;

use App\Models\Client;
use App\Models\OnboardingChecklist;
use App\Models\Survey;

class OnboardingService
{
    /**
     * Get or create the onboarding checklist for a client.
     */
    public function checklistFor(Client $client): OnboardingChecklist
    {
        return OnboardingChecklist::firstOrCreate(
            ['client_id' => $client->id],
            [
                'profile_completed' => $this->checkProfileCompleted($client),
                'first_survey_created' => $client->surveys()->exists(),
                'first_survey_published' => $client->surveys()->where('status', 'published')->exists(),
                'first_campaign_sent' => $client->campaigns()->where('status', 'sent')->exists(),
                'theme_customized' => $client->surveys()->whereNotNull('theme_id')->exists(),
                'integrations_configured' => $this->checkIntegrations($client),
            ]
        );
    }

    /**
     * Mark a checklist item as completed.
     */
    public function markCompleted(Client $client, string $key): void
    {
        $checklist = $this->checklistFor($client);

        if (in_array($key, (new OnboardingChecklist())->getFillable(), true)) {
            $checklist->update([$key => true]);
        }
    }

    /**
     * Dismiss the onboarding checklist.
     */
    public function dismiss(Client $client): void
    {
        $checklist = $this->checklistFor($client);
        $checklist->update(['dismissed' => true]);
    }

    /**
     * Check if profile fields are filled in.
     */
    protected function checkProfileCompleted(Client $client): bool
    {
        $required = ['company_name', 'email', 'phone'];
        foreach ($required as $field) {
            if (empty($client->{$field})) {
                return false;
            }
        }
        return true;
    }

    /**
     * Check if any integrations are configured.
     */
    protected function checkIntegrations(Client $client): bool
    {
        return $client->surveys()->whereNotNull('ga_tracking_id')->exists()
            || $client->surveys()->whereNotNull('meta_pixel_id')->exists()
            || \App\Models\SlackIntegration::where('client_id', $client->id)->where('is_active', true)->exists()
            || \App\Models\Webhook::where('client_id', $client->id)->where('is_active', true)->exists();
    }
}