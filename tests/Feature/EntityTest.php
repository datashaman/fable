<?php

use App\Enums\CanonicalStatus;
use App\Enums\EntityType;
use App\Models\Entity;
use App\Models\Milieu;

test('an entity can be created with default factory state', function () {
    $entity = Entity::factory()->create();

    expect($entity)
        ->name->toBeString()
        ->type->toBeInstanceOf(EntityType::class)
        ->aliases->toBeArray()
        ->attributes->toBeArray()
        ->tags->toBeArray()
        ->canonical_status->toBeInstanceOf(CanonicalStatus::class)
        ->provenance->toBeArray();
});

test('type, aliases, attributes, tags, canonical_status and provenance are cast correctly', function () {
    $entity = Entity::factory()->create([
        'type' => EntityType::Character,
        'aliases' => ['The Wanderer'],
        'attributes' => ['strength' => 7],
        'tags' => ['protagonist'],
        'canonical_status' => CanonicalStatus::Disputed,
        'provenance' => ['source' => 'chapter_1', 'author' => 'marlinf', 'recorded_at' => '2026-07-27'],
    ]);

    $entity->refresh();

    expect($entity->type)->toBe(EntityType::Character)
        ->and($entity->aliases)->toBe(['The Wanderer'])
        ->and($entity->attributes)->toBe(['strength' => 7])
        ->and($entity->tags)->toBe(['protagonist'])
        ->and($entity->canonical_status)->toBe(CanonicalStatus::Disputed)
        ->and($entity->provenance)->toBe(['source' => 'chapter_1', 'author' => 'marlinf', 'recorded_at' => '2026-07-27']);
});

test('an entity belongs to a milieu', function () {
    $milieu = Milieu::factory()->create();
    $entity = Entity::factory()->for($milieu)->create();

    expect($entity->milieu)->toBeInstanceOf(Milieu::class)
        ->and($entity->milieu->is($milieu))->toBeTrue()
        ->and($milieu->entities->first()->is($entity))->toBeTrue();
});
