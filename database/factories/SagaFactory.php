<?php

namespace Database\Factories;

use App\Enums\CanonicalStatus;
use App\Enums\NarrativeCollectionKind;
use App\Models\Continuity;
use App\Models\Milieu;
use App\Models\Saga;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Saga>
 */
class SagaFactory extends Factory
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
            'title' => fake()->sentence(3),
            'kind' => fake()->randomElement(NarrativeCollectionKind::cases()),
            'overarching_conflicts' => [fake()->sentence(), fake()->sentence()],
            'ordering_type' => 'chronological',
            'canonical_status' => fake()->randomElement(CanonicalStatus::cases()),
            'provenance' => ['source' => fake()->word(), 'author' => fake()->name(), 'recorded_at' => fake()->date()],
        ];
    }
}
