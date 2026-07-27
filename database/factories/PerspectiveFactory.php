<?php

namespace Database\Factories;

use App\Models\Continuity;
use App\Models\Entity;
use App\Models\Milieu;
use App\Models\Perspective;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Perspective>
 */
class PerspectiveFactory extends Factory
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
            'name' => fake()->word().' '.fake()->word().'\'s Perspective',
            'holder_id' => Entity::factory(),
            'biases' => fake()->randomElements(['distrusts imperial officials', 'assumes the worst of strangers'], 1),
            'temporal_position' => fake()->year(),
            'description' => fake()->sentence(),
            'provenance' => ['source' => fake()->word(), 'author' => fake()->name(), 'recorded_at' => fake()->date()],
        ];
    }
}
