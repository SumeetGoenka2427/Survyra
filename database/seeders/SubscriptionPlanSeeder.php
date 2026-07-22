<?php

namespace Database\Seeders;

use App\Models\SubscriptionPlan;
use Illuminate\Database\Seeder;

class SubscriptionPlanSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            [
                'name' => 'Starter',
                'slug' => 'starter',
                'max_active_surveys' => 3,
                'max_monthly_responses' => 500,
                'max_monthly_campaign_sends' => 1000,
                'price' => 1999,
            ],
            [
                'name' => 'Growth',
                'slug' => 'growth',
                'max_active_surveys' => 10,
                'max_monthly_responses' => 5000,
                'max_monthly_campaign_sends' => 10000,
                'price' => 4999,
            ],
            [
                'name' => 'Enterprise',
                'slug' => 'enterprise',
                'max_active_surveys' => null,
                'max_monthly_responses' => null,
                'max_monthly_campaign_sends' => null,
                'price' => 14999,
            ],
        ];

        foreach ($plans as $plan) {
            SubscriptionPlan::query()->updateOrCreate(['slug' => $plan['slug']], $plan);
        }
    }
}
