<?php

use App\Enums\CanonicalStatus;
use App\Enums\NarrativeCollectionKind;
use App\Models\Continuity;
use App\Models\Milieu;
use App\Models\Saga;
use App\Models\Story;

test('a saga can be created with default factory state', function () {
    $saga = Saga::factory()->create();

    expect($saga)
        ->title->toBeString()
        ->kind->toBeInstanceOf(NarrativeCollectionKind::class)
        ->overarching_conflicts->toBeArray()
        ->canonical_status->toBeInstanceOf(CanonicalStatus::class);
});

test('a saga belongs to a milieu and a continuity', function () {
    $milieu = Milieu::factory()->create();
    $continuity = Continuity::factory()->create();

    $saga = Saga::factory()->create([
        'milieu_id' => $milieu->id,
        'continuity_id' => $continuity->id,
    ]);

    expect($saga->milieu->is($milieu))->toBeTrue()
        ->and($saga->continuity->is($continuity))->toBeTrue()
        ->and($milieu->sagas->first()->is($saga))->toBeTrue();
});

test('a saga references stories in order rather than duplicating their events', function () {
    $saga = Saga::factory()->create();
    $first = Story::factory()->create();
    $second = Story::factory()->create();

    $saga->stories()->attach($second->id, ['sequence' => 1]);
    $saga->stories()->attach($first->id, ['sequence' => 0]);

    expect($saga->stories->pluck('id')->all())->toBe([$first->id, $second->id])
        ->and($first->sagas->first()->is($saga))->toBeTrue();
});
