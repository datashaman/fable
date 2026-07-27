<?php

namespace Database\Factories;

use App\Enums\CanonicalStatus;
use App\Enums\RuleType;
use App\Models\Milieu;
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
            'type' => fake()->randomElement(RuleType::cases()),
            'name' => fake()->words(3, true),
            'description' => fake()->sentence(),
            'scope' => [
                'entity_types' => ['character'],
                'places' => [],
            ],
            'conditions' => [fake()->sentence()],
            'requirements' => [fake()->sentence()],
            'consequences' => [fake()->sentence()],
            'exceptions' => [],
            'priority' => fake()->numberBetween(0, 100),
            'canonical_status' => fake()->randomElement(CanonicalStatus::cases()),
        ];
    }
}
