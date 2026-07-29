<?php

use App\Models\User;
use Illuminate\Support\Facades\Gate;

test('Horizon access is restricted to configured email addresses', function () {
    config()->set('horizon.allowed_emails', ['operator@example.com']);

    $operator = User::factory()->create(['email' => 'operator@example.com']);
    $otherUser = User::factory()->create(['email' => 'other@example.com']);

    expect(Gate::forUser($operator)->allows('viewHorizon'))->toBeTrue()
        ->and(Gate::forUser($otherUser)->allows('viewHorizon'))->toBeFalse();
});
