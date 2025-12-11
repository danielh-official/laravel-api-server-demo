<?php

namespace Database\Factories;

use App\Enum\PartnerLevel;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Partner>
 */
class PartnerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->company,
            'description' => $this->faker->paragraph,
            'website' => $this->faker->url,
            'is_featured' => $this->faker->boolean,
            'level' => $this->faker->randomElement(PartnerLevel::cases()),
            'image' => $this->faker->imageUrl,
            'location' => $this->faker->city,
            'specialties' => $this->faker->words(3, true),
        ];
    }
}
