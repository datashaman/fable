<?php

namespace Database\Factories;

use App\Enums\ScenarioStatus;
use App\Models\Milieu;
use App\Models\Scenario;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Scenario>
 */
class ScenarioFactory extends Factory
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
            'name' => fake()->words(3, true),
            'premise' => fake()->sentence(),
            'based_on_at' => fake()->year(),
            'initial_conditions' => [fake()->word(), fake()->word()],
            'tensions' => [fake()->sentence()],
            'possible_outcomes' => [fake()->sentence(), fake()->sentence()],
            'status' => fake()->randomElement(ScenarioStatus::cases()),
        ];
    }
}
