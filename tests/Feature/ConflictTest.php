<?php

use App\Enums\ConflictStatus;
use App\Models\Conflict;
use App\Models\Continuity;
use App\Models\Entity;
use App\Models\Goal;
use App\Models\Milieu;
use App\Models\Scenario;

test('a conflict can be created with default factory state', function () {
    $conflict = Conflict::factory()->create();

    expect($conflict)
        ->description->toBeString()
        ->status->toBeInstanceOf(ConflictStatus::class);
});

test('a conflict belongs to a milieu, a continuity, and optionally a scenario and subject', function () {
    $milieu = Milieu::factory()->create();
    $continuity = Continuity::factory()->create();
    $scenario = Scenario::factory()->create();
    $subject = Entity::factory()->create();

    $conflict = Conflict::factory()->create([
        'milieu_id' => $milieu->id,
        'continuity_id' => $continuity->id,
        'scenario_id' => $scenario->id,
        'subject_id' => $subject->id,
    ]);

    expect($conflict->milieu->is($milieu))->toBeTrue()
        ->and($conflict->continuity->is($continuity))->toBeTrue()
        ->and($conflict->scenario->is($scenario))->toBeTrue()
        ->and($conflict->subject->is($subject))->toBeTrue()
        ->and($milieu->conflicts->first()->is($conflict))->toBeTrue()
        ->and($continuity->conflicts->first()->is($conflict))->toBeTrue()
        ->and($scenario->conflicts->first()->is($conflict))->toBeTrue()
        ->and($subject->conflictsAsSubject->first()->is($conflict))->toBeTrue();
});

test('a conflict connects incompatible goals', function () {
    $conflict = Conflict::factory()->create();
    $first = Goal::factory()->create();
    $second = Goal::factory()->create();

    $conflict->goals()->attach([$first->id, $second->id]);

    expect($conflict->goals)->toHaveCount(2)
        ->and($first->conflicts->first()->is($conflict))->toBeTrue();
});
