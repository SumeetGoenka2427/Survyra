<?php

namespace App\Services;

use App\Models\Client;
use App\Models\Response;
use App\Services\SubscriptionService;

class UsageService
{
    public function __construct(
        private readonly SubscriptionService $subscriptions,
    ) {
    }

    /**
     * Check if the client can create a new active survey.
     */
    public function canCreateSurvey(Client $client): bool
    {
        $plan = $this->subscriptions->activePlan($client);

        if (! $plan || ! $plan->max_active_surveys) {
            return true; // unlimited
        }

        $activeCount = $client->surveys()->where('status', 'published')->count();

        return $activeCount < $plan->max_active_surveys;
    }

    /**
     * Check if the client has exceeded their monthly response limit.
     */
    public function canAcceptResponse(Client $client): bool
    {
        $plan = $this->subscriptions->activePlan($client);

        if (! $plan || ! $plan->max_monthly_responses) {
            return true; // unlimited
        }

        $monthlyCount = Response::query()
            ->where('client_id', $client->id)
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        return $monthlyCount < $plan->max_monthly_responses;
    }

    /**
     * Check if the client can send more campaigns this month.
     */
    public function canSendCampaign(Client $client): bool
    {
        $plan = $this->subscriptions->activePlan($client);

        if (! $plan || ! $plan->max_monthly_campaign_sends) {
            return true; // unlimited
        }

        $monthlySends = \App\Models\Campaign::query()
            ->where('client_id', $client->id)
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        return $monthlySends < $plan->max_monthly_campaign_sends;
    }

    /**
     * Get usage statistics for the client.
     *
     * @return array<string, mixed>
     */
    public function getUsageStats(Client $client): array
    {
        $plan = $this->subscriptions->activePlan($client);

        $activeSurveys = $client->surveys()->where('status', 'published')->count();
        $monthlyResponses = Response::query()
            ->where('client_id', $client->id)
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();
        $monthlyCampaignSends = \App\Models\Campaign::query()
            ->where('client_id', $client->id)
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        return [
            'active_surveys' => $activeSurveys,
            'max_active_surveys' => $plan?->max_active_surveys,
            'monthly_responses' => $monthlyResponses,
            'max_monthly_responses' => $plan?->max_monthly_responses,
            'monthly_campaign_sends' => $monthlyCampaignSends,
            'max_monthly_campaign_sends' => $plan?->max_monthly_campaign_sends,
            'survey_percent' => $plan?->max_active_surveys ? round($activeSurveys / $plan->max_active_surveys * 100) : 0,
            'response_percent' => $plan?->max_monthly_responses ? round($monthlyResponses / $plan->max_monthly_responses * 100) : 0,
            'campaign_percent' => $plan?->max_monthly_campaign_sends ? round($monthlyCampaignSends / $plan->max_monthly_campaign_sends * 100) : 0,
        ];
    }
}