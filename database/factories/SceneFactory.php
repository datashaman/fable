<?php

namespace Database\Factories;

use App\Models\Scene;
use App\Models\Story;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Scene>
 */
class SceneFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'story_id' => Story::factory(),
            'name' => fake()->sentence(3),
            'description' => fake()->sentence(),
            'sequence' => fake()->numberBetween(0, 10),
        ];
    }
}
