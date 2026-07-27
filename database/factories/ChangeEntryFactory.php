<?php

namespace Database\Factories;

use App\Models\ChangeEntry;
use App\Models\ChangeSet;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ChangeEntry>
 */
class ChangeEntryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'change_set_id' => ChangeSet::factory(),
            'record_type' => 'entity',
            'record_id' => fake()->numberBetween(1, 1000),
            'action' => 'updated',
            'before' => [],
            'after' => [],
        ];
    }
}
