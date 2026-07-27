<?php

use App\Enums\CanonicalStatus;
use App\Models\Belief;
use App\Models\Continuity;
use App\Models\Event;
use App\Models\Milieu;
use App\Models\Relationship;

test('a continuity can be created with default factory state', function () {
    $continuity = Continuity::factory()->create();

    expect($continuity)
        ->name->toBeString()
        ->canonical_status->toBeInstanceOf(CanonicalStatus::class);
});

test('a continuity belongs to a milieu', function () {
    $milieu = Milieu::factory()->create();
    $continuity = Continuity::factory()->for($milieu)->create();

    expect($continuity->milieu->is($milieu))->toBeTrue()
        ->and($milieu->continuities->first()->is($continuity))->toBeTrue();
});

test('a continuity can branch from a parent continuity', function () {
    $primary = Continuity::factory()->create(['name' => 'Primary']);
    $branch = Continuity::factory()->create([
        'milieu_id' => $primary->milieu_id,
        'parent_id' => $primary->id,
        'diverges_at' => '487-06-01',
    ]);

    expect($branch->parent->is($primary))->toBeTrue()
        ->and($branch->diverges_at)->toBe('487-06-01')
        ->and($primary->branches->first()->is($branch))->toBeTrue();
});

test('events, relationships and beliefs belong to a continuity', function () {
    $continuity = Continuity::factory()->create();
    $event = Event::factory()->for($continuity)->create();
    $relationship = Relationship::factory()->for($continuity)->create();
    $belief = Belief::factory()->for($continuity)->create();

    expect($event->continuity->is($continuity))->toBeTrue()
        ->and($relationship->continuity->is($continuity))->toBeTrue()
        ->and($belief->continuity->is($continuity))->toBeTrue()
        ->and($continuity->events->first()->is($event))->toBeTrue()
        ->and($continuity->relationships->first()->is($relationship))->toBeTrue()
        ->and($continuity->beliefs->first()->is($belief))->toBeTrue();
});
