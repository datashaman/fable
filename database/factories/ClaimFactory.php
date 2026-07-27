<?php

namespace Database\Factories;

use App\Models\Claim;
use App\Models\Entity;
use App\Models\Milieu;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Claim>
 */
class ClaimFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'milieu_id' => Milieu::factory(),
            'subject_id' => Entity::factory(),
            'predicate' => fake()->word(),
            'object_id' => Entity::factory(),
            'object_value' => null,
            'description' => fake()->sentence(),
        ];
    }
}
