<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            SubscriptionPlanSeeder::class,
            DemoDataSeeder::class,
            QuestionTypeSeeder::class,
            SurveyThemeSeeder::class,
            SurveyTemplateSeeder::class,
            SectionTypeSeeder::class,
            WebsiteThemeSeeder::class,
            WebsiteTemplateSeeder::class,
            DemoClientsSeeder::class,
            WebsiteDemoSeeder::class,
        ]);
    }
}
