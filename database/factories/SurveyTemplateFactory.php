<?php

namespace Database\Factories;

use App\Models\SurveyTemplate;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SurveyTemplate>
 */
class SurveyTemplateFactory extends Factory
{
    protected $model = SurveyTemplate::class;

    public function definition(): array
    {
        return [
            'name' => fake()->words(3, true),
            'industry_category' => fake()->randomElement(['Healthcare', 'Restaurant', 'Retail', 'Education', 'Customer Support']),
            'description' => fake()->sentence(),
            'is_active' => true,
        ];
    }
}
