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
            'name' => fake()->word().' '.fake()->word().' Continuity',
            'description' => fake()->sentence(),
            'diverged_from_event_id' => null,
            'canonical_status' => fake()->randomElement(CanonicalStatus::cases()),
            'provenance' => ['source' => fake()->word(), 'author' => fake()->name(), 'recorded_at' => fake()->date()],
        ];
    }
}
