<?php

namespace Database\Factories;

use App\Models\ChangeSet;
use App\Models\Milieu;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ChangeSet>
 */
class ChangeSetFactory extends Factory
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
            'user_id' => User::factory(),
            'tool_name' => 'save-entity',
            'summary' => fake()->sentence(),
            'metadata' => [],
        ];
    }
}
