<?php

namespace Database\Factories;

use App\Enums\CanonicalStatus;
use App\Enums\OntologyCategory;
use App\Models\Milieu;
use App\Models\OntologyType;
use App\Models\Rule;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Rule>
 */
class RuleFactory extends Factory
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
            'type_id' => function (array $attributes) {
                return OntologyType::factory()->create([
                    'milieu_id' => $attributes['milieu_id'],
                    'category' => OntologyCategory::Rule,
                ])->id;
            },
            'name' => fake()->words(3, true),
            'description' => fake()->sentence(),
            'conditions' => [fake()->sentence()],
            'requirements' => [fake()->sentence()],
            'consequences' => [fake()->sentence()],
            'priority' => fake()->numberBetween(0, 100),
            'valid_from' => fake()->year(),
            'valid_until' => null,
            'canonical_status' => fake()->randomElement(CanonicalStatus::cases()),
            'provenance' => ['source' => fake()->word(), 'author' => fake()->name(), 'recorded_at' => fake()->date()],
        ];
    }
}
