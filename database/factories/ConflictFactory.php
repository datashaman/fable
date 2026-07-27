<?php

namespace Database\Factories;

use App\Enums\ConflictStatus;
use App\Models\Conflict;
use App\Models\Continuity;
use App\Models\Milieu;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Conflict>
 */
class ConflictFactory extends Factory
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
            'subject_id' => null,
            'description' => fake()->sentence(),
            'status' => ConflictStatus::Unresolved,
        ];
    }
}
