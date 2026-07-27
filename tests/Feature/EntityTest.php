<?php

use App\Enums\CanonicalStatus;
use App\Models\Entity;
use App\Models\Milieu;
use App\Models\OntologyType;

test('an entity can be created with default factory state', function () {
    $entity = Entity::factory()->create();

    expect($entity->type)->toBeInstanceOf(OntologyType::class);

    expect($entity)
        ->name->toBeString()
        ->aliases->toBeArray()
        ->attributes->toBeArray()
        ->tags->toBeArray()
        ->canonical_status->toBeInstanceOf(CanonicalStatus::class)
        ->provenance->toBeArray();
});

test('type, aliases, attributes, tags, canonical_status and provenance are cast correctly', function () {
    $type = OntologyType::factory()->create();

    $entity = Entity::factory()->create([
        'type_id' => $type->id,
        'aliases' => ['The Wanderer'],
        'attributes' => ['strength' => 7],
        'tags' => ['protagonist'],
        'canonical_status' => CanonicalStatus::Disputed,
        'provenance' => ['source' => 'chapter_1', 'author' => 'marlinf', 'recorded_at' => '2026-07-27'],
    ]);

    $entity->refresh();

    expect($entity->type->is($type))->toBeTrue()
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
