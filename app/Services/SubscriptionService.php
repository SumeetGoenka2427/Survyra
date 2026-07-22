<?php

namespace App\Services;

use App\Models\Client;
use App\Models\SubscriptionPlan;

class SubscriptionService
{
    /**
     * Get the active subscription plan for a client.
     */
    public function activePlan(Client $client): ?SubscriptionPlan
    {
        if (! $client->subscription_plan_id) {
            return null;
        }

        return $client->subscriptionPlan;
    }

    /**
     * Check if a client has an active subscription.
     */
    public function hasActiveSubscription(Client $client): bool
    {
        return $client->subscription_plan_id !== null
            && $client->subscriptionPlan !== null
            && $client->subscriptionPlan->is_active;
    }
}