<?php

use App\Enums\MilieuStatus;
use App\Models\Milieu;

test('a milieu can be created with default factory state', function () {
    $milieu = Milieu::factory()->create();

    expect($milieu)
        ->name->toBeString()
        ->themes->toBeArray()
        ->tone->toBeArray()
        ->status->toBeInstanceOf(MilieuStatus::class);
});

test('themes and tone are cast to arrays', function () {
    $milieu = Milieu::factory()->create([
        'themes' => ['Betrayal', 'Redemption'],
        'tone' => ['Grim', 'Hopeful'],
    ]);

    $milieu->refresh();

    expect($milieu->themes)->toBe(['Betrayal', 'Redemption'])
        ->and($milieu->tone)->toBe(['Grim', 'Hopeful']);
});

test('status is cast to the MilieuStatus enum', function () {
    $milieu = Milieu::factory()->create(['status' => MilieuStatus::Canonical]);

    $milieu->refresh();

    expect($milieu->status)->toBe(MilieuStatus::Canonical);
});
