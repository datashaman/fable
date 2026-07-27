<?php

use App\Enums\ScenarioStatus;
use App\Models\Entity;
use App\Models\Milieu;
use App\Models\Scenario;

test('a scenario can be created with default factory state', function () {
    $scenario = Scenario::factory()->create();

    expect($scenario)
        ->name->toBeString()
        ->premise->toBeString()
        ->initial_conditions->toBeArray()
        ->tensions->toBeArray()
        ->possible_outcomes->toBeArray()
        ->status->toBeInstanceOf(ScenarioStatus::class);
});

test('a scenario belongs to a milieu', function () {
    $milieu = Milieu::factory()->create();
    $scenario = Scenario::factory()->for($milieu)->create();

    expect($scenario->milieu->is($milieu))->toBeTrue()
        ->and($milieu->scenarios->first()->is($scenario))->toBeTrue();
});

test('a scenario has participants with roles', function () {
    $scenario = Scenario::factory()->create();
    $entity = Entity::factory()->create();

    $scenario->participants()->attach($entity->id, ['role' => 'reluctant_leader']);

    $participant = $scenario->participants()->first();

    expect($participant->is($entity))->toBeTrue()
        ->and($participant->pivot->role)->toBe('reluctant_leader');
});
