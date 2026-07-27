<?php

use App\Enums\CanonicalStatus;
use App\Enums\EventType;
use App\Models\Entity;
use App\Models\Event;
use App\Models\Milieu;

test('an event can be created with default factory state', function () {
    $event = Event::factory()->create();

    expect($event)
        ->name->toBeString()
        ->type->toBeInstanceOf(EventType::class)
        ->effects->toBeArray()
        ->tags->toBeArray()
        ->canonical_status->toBeInstanceOf(CanonicalStatus::class)
        ->provenance->toBeArray();
});

test('canonical_status and provenance are cast correctly', function () {
    $event = Event::factory()->create([
        'canonical_status' => CanonicalStatus::Canonical,
        'provenance' => ['source' => 'chapter_12', 'author' => 'marlinf', 'recorded_at' => '2026-07-27'],
    ]);

    $event->refresh();

    expect($event->canonical_status)->toBe(CanonicalStatus::Canonical)
        ->and($event->provenance)->toBe(['source' => 'chapter_12', 'author' => 'marlinf', 'recorded_at' => '2026-07-27']);
});

test('an event belongs to a milieu', function () {
    $milieu = Milieu::factory()->create();
    $event = Event::factory()->for($milieu)->create();

    expect($event->milieu)->toBeInstanceOf(Milieu::class)
        ->and($event->milieu->is($milieu))->toBeTrue()
        ->and($milieu->events->first()->is($event))->toBeTrue();
});

test('an event has locations', function () {
    $event = Event::factory()->create();
    $place = Entity::factory()->create();

    $event->locations()->attach($place);

    expect($event->locations()->first()->is($place))->toBeTrue();
});

test('an event has participants with roles and attributes', function () {
    $event = Event::factory()->create();
    $entity = Entity::factory()->create();

    $event->participants()->attach($entity->id, [
        'role' => 'attacker',
        'attributes' => ['morale' => 'high'],
    ]);

    $participant = $event->participants()->first();

    expect($participant->is($entity))->toBeTrue()
        ->and($participant->pivot->role)->toBe('attacker')
        ->and($participant->pivot->attributes)->toBe(['morale' => 'high']);
});

test('an event can be caused by earlier events', function () {
    $blockade = Event::factory()->create();
    $capture = Event::factory()->create();

    $capture->causedBy()->attach($blockade);

    expect($capture->causedBy()->first()->is($blockade))->toBeTrue()
        ->and($blockade->causes()->first()->is($capture))->toBeTrue();
});
