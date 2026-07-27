<?php

namespace Database\Factories;

use App\Enums\CanonicalStatus;
use App\Enums\OntologyCategory;
use App\Models\Continuity;
use App\Models\Event;
use App\Models\Milieu;
use App\Models\OntologyType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Event>
 */
class EventFactory extends Factory
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
            'type_id' => function (array $attributes) {
                return OntologyType::factory()->create([
                    'milieu_id' => $attributes['milieu_id'],
                    'category' => OntologyCategory::Event,
                ])->id;
            },
            'name' => fake()->catchPhrase(),
            'description' => fake()->sentence(),
            'start_time' => fake()->year(),
            'end_time' => null,
            'effects' => [
                [
                    'type' => 'set_attribute',
                    'entity_id' => fake()->randomNumber(),
                    'attribute' => 'status',
                    'value' => fake()->word(),
                ],
            ],
            'tags' => fake()->randomElements(['military', 'political', 'cultural', 'natural'], 2),
            'canonical_status' => fake()->randomElement(CanonicalStatus::cases()),
            'provenance' => ['source' => fake()->word(), 'author' => fake()->name(), 'recorded_at' => fake()->date()],
        ];
    }
}
