<?php

namespace Database\Factories;

use App\Enums\GoalStatus;
use App\Models\Continuity;
use App\Models\Entity;
use App\Models\Goal;
use App\Models\Milieu;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Goal>
 */
class GoalFactory extends Factory
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
            'scenario_id' => null,
            'holder_id' => Entity::factory(),
            'objective' => fake()->sentence(),
            'motivation' => fake()->sentence(),
            'stakes' => ['success' => fake()->sentence(), 'failure' => fake()->sentence()],
            'status' => GoalStatus::Active,
        ];
    }
}
