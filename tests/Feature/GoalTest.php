<?php

use App\Enums\GoalStatus;
use App\Models\Continuity;
use App\Models\Entity;
use App\Models\Goal;
use App\Models\Milieu;
use App\Models\Scenario;

test('a goal can be created with default factory state', function () {
    $goal = Goal::factory()->create();

    expect($goal)
        ->objective->toBeString()
        ->motivation->toBeString()
        ->stakes->toBeArray()
        ->status->toBeInstanceOf(GoalStatus::class);
});

test('a goal belongs to a milieu, a continuity and a holder', function () {
    $milieu = Milieu::factory()->create();
    $continuity = Continuity::factory()->create();
    $holder = Entity::factory()->create();

    $goal = Goal::factory()->create([
        'milieu_id' => $milieu->id,
        'continuity_id' => $continuity->id,
        'holder_id' => $holder->id,
    ]);

    expect($goal->milieu->is($milieu))->toBeTrue()
        ->and($goal->continuity->is($continuity))->toBeTrue()
        ->and($goal->holder->is($holder))->toBeTrue()
        ->and($milieu->goals->first()->is($goal))->toBeTrue()
        ->and($continuity->goals->first()->is($goal))->toBeTrue()
        ->and($holder->goals->first()->is($goal))->toBeTrue();
});

test('a goal can optionally arise within a scenario', function () {
    $scenario = Scenario::factory()->create();
    $goal = Goal::factory()->for($scenario)->create();

    expect($goal->scenario->is($scenario))->toBeTrue()
        ->and($scenario->goals->first()->is($goal))->toBeTrue();
});
