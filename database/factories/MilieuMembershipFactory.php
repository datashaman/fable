<?php

namespace Database\Factories;

use App\Enums\MilieuRole;
use App\Models\Milieu;
use App\Models\MilieuMembership;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MilieuMembership>
 */
class MilieuMembershipFactory extends Factory
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
            'role' => MilieuRole::Editor,
        ];
    }
}
