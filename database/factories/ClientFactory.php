<?php

namespace Database\Factories;

use App\Models\Client;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Client>
 */
class ClientFactory extends Factory
{
    protected $model = Client::class;

    public function definition(): array
    {
        return [
            'company_name' => fake()->company(),
            'industry' => fake()->randomElement(['Cafe', 'Clinic', 'Salon', 'Hotel', 'Restaurant']),
            'status' => 'active',
            'timezone' => 'Asia/Kolkata',
            'language' => 'en',
        ];
    }
}
