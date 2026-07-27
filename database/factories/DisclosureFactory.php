<?php

namespace Database\Factories;

use App\Models\Belief;
use App\Models\Continuity;
use App\Models\Disclosure;
use App\Models\Milieu;
use App\Models\Scene;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Disclosure>
 */
class DisclosureFactory extends Factory
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
            'belief_id' => Belief::factory(),
            'scene_id' => Scene::factory(),
            'description' => fake()->sentence(),
        ];
    }
}
