<?php

namespace Database\Factories;

use App\Enums\BeliefStance;
use App\Enums\BeliefVisibility;
use App\Enums\CanonicalStatus;
use App\Models\Belief;
use App\Models\Claim;
use App\Models\Continuity;
use App\Models\Entity;
use App\Models\Milieu;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Belief>
 */
class BeliefFactory extends Factory
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
            'continuity_id' => Continuity::factory(),
            'holder_id' => Entity::factory(),
            'claim_id' => Claim::factory(),
            'stance' => fake()->randomElement(BeliefStance::cases()),
            'confidence' => fake()->randomFloat(2, 0, 1),
            'acquired_at' => fake()->year(),
            'valid_until' => null,
            'source_entity_id' => null,
            'visibility' => fake()->randomElement(BeliefVisibility::cases()),
            'description' => fake()->sentence(),
            'canonical_status' => fake()->randomElement(CanonicalStatus::cases()),
            'provenance' => ['source' => fake()->word(), 'author' => fake()->name(), 'recorded_at' => fake()->date()],
        ];
    }
}
