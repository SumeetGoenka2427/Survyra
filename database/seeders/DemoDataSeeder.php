<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\ClientUser;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Illuminate\Database\Seeder;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $superAdmin = User::query()->updateOrCreate(
            ['email' => 'sumeet@podup.com'],
            ['name' => 'Sumeet', 'password' => 'password', 'is_active' => true]
        );
        $superAdmin->syncRoles(['super_admin']);

        $survyraAdmin = User::query()->updateOrCreate(
            ['email' => 'admin@survyra.com'],
            ['name' => 'Survyra Admin', 'password' => 'password', 'is_active' => true]
        );
        $survyraAdmin->syncRoles(['survyra_admin']);

        $client = Client::query()->updateOrCreate(
            ['company_name' => 'Demo Cafe'],
            [
                'industry' => 'Cafe',
                'email' => 'contact@democafe.test',
                'status' => 'active',
                'timezone' => 'Asia/Kolkata',
                'language' => 'en',
                'subscription_plan_id' => SubscriptionPlan::query()->where('slug', 'growth')->value('id'),
                'created_by' => $survyraAdmin->id,
            ]
        );

        ClientUser::query()->updateOrCreate(
            ['email' => 'owner@democafe.test'],
            [
                'client_id' => $client->id,
                'name' => 'Demo Cafe Owner',
                'password' => 'password',
                'role' => 'owner',
                'is_active' => true,
            ]
        );
    }
}
