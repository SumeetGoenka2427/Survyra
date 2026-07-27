<?php

namespace App\Services;

use App\Models\Client;
use App\Models\OnboardingChecklist;
use App\Models\Survey;

class OnboardingService
{
    /**
     * Get the onboarding checklist for a client, with every computed flag
     * refreshed against current state on every call (not just at creation).
     * `firstOrCreate` used to snapshot these once and freeze them forever -
     * `markCompleted()` was meant to update them afterwards but was never
     * called from anywhere, so a step never actually flipped to done once
     * the row existed. Recomputing live is simpler and can't drift, since
     * these are all cheap existence checks against data that already exists
     * for other reasons. `dismissed` is untouched here - it's a real user
     * action, not a derived flag.
     */
    public function checklistFor(Client $client): OnboardingChecklist
    {
        return OnboardingChecklist::query()->updateOrCreate(
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