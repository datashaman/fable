<?php

namespace Database\Factories;

use App\Enums\MilieuStatus;
use App\Models\Milieu;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Milieu>
 */
class MilieuFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->city().' Setting',
            'description' => fake()->paragraph(),
            'genre' => fake()->randomElement(['Fantasy', 'Science Fiction', 'Horror', 'Mystery', 'Historical Fiction']),
            'tone' => fake()->randomElements(['Whimsical', 'Grim', 'Hopeful', 'Satirical', 'Melancholic'], 2),
            'themes' => fake()->randomElements(['Betrayal', 'Redemption', 'Identity', 'Power', 'Survival', 'Love'], 2),
            'current_time' => fake()->year(),
            'time_system' => fake()->randomElement(['Gregorian calendar', 'Imperial reckoning', 'Cycle count']),
            'spatial_scope' => fake()->randomElement(['A single city', 'A continent', 'A star system', 'A small village']),
            'technological_level' => fake()->randomElement(['Bronze Age', 'Industrial', 'Modern', 'Interstellar']),
            'supernatural_model' => fake()->randomElement(['None', 'Elemental magic', 'Divine intervention', 'Psionics']),
            'default_perspective' => fake()->randomElement(['Third-person omniscient', 'First-person', 'Close third-person']),
            'status' => fake()->randomElement(MilieuStatus::cases()),
        ];
    }
}
