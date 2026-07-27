<?php

namespace Database\Factories;

use App\Enums\CanonicalStatus;
use App\Enums\EntityType;
use App\Models\Entity;
use App\Models\Milieu;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Entity>
 */
class EntityFactory extends Factory
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
            'type' => fake()->randomElement(EntityType::cases()),
            'name' => fake()->unique()->name(),
            'description' => fake()->sentence(),
            'aliases' => fake()->randomElements(['The Wanderer', 'Old One', 'The Unnamed'], 1),
            'attributes' => ['strength' => fake()->numberBetween(1, 10)],
            'tags' => fake()->randomElements(['protagonist', 'antagonist', 'ancient', 'hidden', 'royal'], 2),
            'existed_from' => fake()->year(),
            'ended_at' => null,
            'canonical_status' => fake()->randomElement(CanonicalStatus::cases()),
            'provenance' => ['source' => fake()->word(), 'author' => fake()->name(), 'recorded_at' => fake()->date()],
        ];
    }
}
