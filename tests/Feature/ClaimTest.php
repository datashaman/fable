<?php

use App\Models\Belief;
use App\Models\Claim;
use App\Models\Entity;
use App\Models\Milieu;

test('a claim can be created with default factory state', function () {
    $claim = Claim::factory()->create();

    expect($claim->predicate)->toBeString();
});

test('a claim belongs to a milieu and connects a subject to an entity object', function () {
    $milieu = Milieu::factory()->create();
    $subject = Entity::factory()->create();
    $object = Entity::factory()->create();

    $claim = Claim::factory()->create([
        'milieu_id' => $milieu->id,
        'subject_id' => $subject->id,
        'predicate' => 'murdered',
        'object_id' => $object->id,
    ]);

    expect($claim->milieu->is($milieu))->toBeTrue()
        ->and($claim->subject->is($subject))->toBeTrue()
        ->and($claim->object->is($object))->toBeTrue()
        ->and($milieu->claims->first()->is($claim))->toBeTrue()
        ->and($subject->claimsAsSubject->first()->is($claim))->toBeTrue()
        ->and($object->claimsAsObject->first()->is($claim))->toBeTrue();
});

test('a claim can point to a non-entity object value instead', function () {
    $claim = Claim::factory()->create([
        'predicate' => 'died_of',
        'object_id' => null,
        'object_value' => 'illness',
    ]);

    expect($claim->object)->toBeNull()
        ->and($claim->object_value)->toBe('illness');
});

test('multiple beliefs can share the same claim', function () {
    $claim = Claim::factory()->create();
    $first = Belief::factory()->create(['claim_id' => $claim->id]);
    $second = Belief::factory()->create(['claim_id' => $claim->id]);

    expect($claim->beliefs)->toHaveCount(2)
        ->and($first->claim->is($claim))->toBeTrue()
        ->and($second->claim->is($claim))->toBeTrue();
});
