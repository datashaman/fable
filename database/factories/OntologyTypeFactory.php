<?php

namespace Database\Factories;

use App\Enums\OntologyCategory;
use App\Models\Milieu;
use App\Models\OntologyType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OntologyType>
 */
class OntologyTypeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->word();

        return [
            'milieu_id' => Milieu::factory(),
            'category' => fake()->randomElement(OntologyCategory::cases()),
            'key' => str($name)->snake()->value(),
            'name' => str($name)->title()->value(),
            'description' => fake()->sentence(),
        ];
    }
}
