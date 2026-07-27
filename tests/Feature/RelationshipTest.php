<?php

use App\Enums\CanonicalStatus;
use App\Models\Entity;
use App\Models\Milieu;
use App\Models\OntologyType;
use App\Models\Relationship;

test('a relationship can be created with default factory state', function () {
    $relationship = Relationship::factory()->create();

    expect($relationship->type)->toBeInstanceOf(OntologyType::class);

    expect($relationship)
        ->symmetric->toBeBool()
        ->attributes->toBeArray()
        ->canonical_status->toBeInstanceOf(CanonicalStatus::class)
        ->provenance->toBeArray();
});

test('type, inverse, symmetric, attributes, canonical_status and provenance are cast correctly', function () {
    $type = OntologyType::factory()->create();

    $relationship = Relationship::factory()->create([
        'type_id' => $type->id,
        'inverse' => 'owned_by',
        'symmetric' => false,
        'attributes' => ['weight' => 3],
        'canonical_status' => CanonicalStatus::Disputed,
        'provenance' => ['source' => 'chapter_3', 'author' => 'marlinf', 'recorded_at' => '2026-07-27'],
    ]);

    $relationship->refresh();

    expect($relationship->type->is($type))->toBeTrue()
        ->and($relationship->inverse)->toBe('owned_by')
        ->and($relationship->symmetric)->toBeFalse()
        ->and($relationship->attributes)->toBe(['weight' => 3])
        ->and($relationship->canonical_status)->toBe(CanonicalStatus::Disputed)
        ->and($relationship->provenance)->toBe(['source' => 'chapter_3', 'author' => 'marlinf', 'recorded_at' => '2026-07-27']);
});

test('a relationship connects a source entity to a target entity', function () {
    $source = Entity::factory()->create();
    $target = Entity::factory()->create();

    $relationship = Relationship::factory()->create([
        'source_id' => $source->id,
        'target_id' => $target->id,
    ]);

    expect($relationship->source->is($source))->toBeTrue()
        ->and($relationship->target->is($target))->toBeTrue()
        ->and($source->sourceRelationships->first()->is($relationship))->toBeTrue()
        ->and($target->targetRelationships->first()->is($relationship))->toBeTrue();
});

test('a relationship belongs to a milieu', function () {
    $milieu = Milieu::factory()->create();
    $relationship = Relationship::factory()->for($milieu)->create();

    expect($relationship->milieu)->toBeInstanceOf(Milieu::class)
        ->and($relationship->milieu->is($milieu))->toBeTrue()
        ->and($milieu->relationships->first()->is($relationship))->toBeTrue();
});
