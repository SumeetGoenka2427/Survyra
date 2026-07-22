<?php

namespace Database\Seeders;

use App\Models\QuestionType;
use App\Services\QuestionTypeRegistry;
use Illuminate\Database\Seeder;

class QuestionTypeSeeder extends Seeder
{
    public function run(QuestionTypeRegistry $registry): void
    {
        $settingsSchemas = [
            'nps' => ['scale_min' => 0, 'scale_max' => 10, 'low_label' => 'Not likely', 'high_label' => 'Very likely'],
            'csat' => ['scale_min' => 1, 'scale_max' => 5, 'low_label' => 'Very dissatisfied', 'high_label' => 'Very satisfied'],
            'ces' => ['scale_min' => 1, 'scale_max' => 7, 'low_label' => 'Very difficult', 'high_label' => 'Very easy'],
            'rating_stars' => ['max_stars' => 5],
            'emoji_rating' => ['max_stars' => 5],
            'matrix' => ['scale_min' => 1, 'scale_max' => 5, 'low_label' => 'Poor', 'high_label' => 'Excellent'],
            'slider' => ['scale_min' => 0, 'scale_max' => 10, 'low_label' => 'Low', 'high_label' => 'High'],
        ];

        foreach ($registry->all() as $contract) {
            QuestionType::query()->updateOrCreate(
                ['key' => $contract->key()],
                [
                    'label' => $contract->label(),
                    'scoring_type' => $contract->scoringType(),
                    'settings_schema' => $settingsSchemas[$contract->key()] ?? null,
                    'is_active' => true,
                ]
            );
        }
    }
}
