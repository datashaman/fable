<?php

namespace Database\Factories;

use App\Enums\CanonicalStatus;
use App\Models\Continuity;
use App\Models\Milieu;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Continuity>
 */
class ContinuityFactory extends Factory
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
            'parent_id' => null,
            'name' => fake()->words(2, true).' Continuity',
            'description' => fake()->sentence(),
            'diverges_at' => null,
            'canonical_status' => fake()->randomElement(CanonicalStatus::cases()),
        ];
    }
}
