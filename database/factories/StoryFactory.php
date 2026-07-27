<?php

namespace Database\Factories;

use App\Enums\CanonicalStatus;
use App\Enums\NarrativeForm;
use App\Models\Continuity;
use App\Models\Milieu;
use App\Models\Story;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Story>
 */
class StoryFactory extends Factory
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
            'title' => fake()->sentence(3),
            'form' => fake()->randomElement(NarrativeForm::cases()),
            'starts_at' => fake()->year(),
            'ends_at' => null,
            'themes' => fake()->randomElements(['autonomy', 'sacrifice', 'memory', 'loyalty'], 2),
            'canonical_status' => fake()->randomElement(CanonicalStatus::cases()),
        ];
    }
}
