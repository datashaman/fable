<?php

namespace Database\Factories;

use App\Enums\CanonicalStatus;
use App\Enums\RelationshipType;
use App\Models\Continuity;
use App\Models\Entity;
use App\Models\Milieu;
use App\Models\Relationship;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Relationship>
 */
class RelationshipFactory extends Factory
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
            'type' => fake()->randomElement(RelationshipType::cases()),
            'inverse' => null,
            'symmetric' => fake()->boolean(),
            'source_id' => Entity::factory(),
            'target_id' => Entity::factory(),
            'description' => fake()->optional()->sentence(),
            'attributes' => ['strength' => fake()->numberBetween(1, 10)],
            'started_at' => fake()->year(),
            'ended_at' => null,
            'canonical_status' => fake()->randomElement(CanonicalStatus::cases()),
            'provenance' => ['source' => fake()->word(), 'author' => fake()->name(), 'recorded_at' => fake()->date()],
        ];
    }
}
