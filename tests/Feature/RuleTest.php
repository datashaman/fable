<?php

use App\Enums\CanonicalStatus;
use App\Models\Milieu;
use App\Models\OntologyType;
use App\Models\Rule;

test('a rule can be created with default factory state', function () {
    $rule = Rule::factory()->create();

    expect($rule->type)->toBeInstanceOf(OntologyType::class);

    expect($rule)
        ->name->toBeString()
        ->description->toBeString()
        ->scope->toBeArray()
        ->conditions->toBeArray()
        ->requirements->toBeArray()
        ->consequences->toBeArray()
        ->exceptions->toBeArray()
        ->priority->toBeInt()
        ->canonical_status->toBeInstanceOf(CanonicalStatus::class)
        ->provenance->toBeArray();
});

test('type, scope, conditions, requirements, consequences, exceptions, priority, validity, canonical_status and provenance are cast correctly', function () {
    $type = OntologyType::factory()->create();

    $rule = Rule::factory()->create([
        'type_id' => $type->id,
        'scope' => ['places' => ['polity_imperial_territory']],
        'conditions' => ['subject is attempting faster-than-light travel'],
        'requirements' => ['an active gate exists at the origin'],
        'consequences' => ['travel succeeds when all requirements are satisfied'],
        'exceptions' => [['entity' => 'object_void_engine', 'description' => 'The Void Engine can travel without gates.']],
        'priority' => 100,
        'valid_from' => '410',
        'valid_until' => '487-03-17',
        'canonical_status' => CanonicalStatus::Canonical,
        'provenance' => ['source' => 'chapter_12', 'author' => 'marlinf', 'recorded_at' => '2026-07-27'],
    ]);

    $rule->refresh();

    expect($rule->type->is($type))->toBeTrue()
        ->and($rule->scope)->toBe(['places' => ['polity_imperial_territory']])
        ->and($rule->conditions)->toBe(['subject is attempting faster-than-light travel'])
        ->and($rule->requirements)->toBe(['an active gate exists at the origin'])
        ->and($rule->consequences)->toBe(['travel succeeds when all requirements are satisfied'])
        ->and($rule->exceptions)->toBe([['entity' => 'object_void_engine', 'description' => 'The Void Engine can travel without gates.']])
        ->and($rule->priority)->toBe(100)
        ->and($rule->valid_from)->toBe('410')
        ->and($rule->valid_until)->toBe('487-03-17')
        ->and($rule->canonical_status)->toBe(CanonicalStatus::Canonical)
        ->and($rule->provenance)->toBe(['source' => 'chapter_12', 'author' => 'marlinf', 'recorded_at' => '2026-07-27']);
});

test('a rule belongs to a milieu', function () {
    $milieu = Milieu::factory()->create();
    $rule = Rule::factory()->for($milieu)->create();

    expect($rule->milieu)->toBeInstanceOf(Milieu::class)
        ->and($rule->milieu->is($milieu))->toBeTrue()
        ->and($milieu->rules->first()->is($rule))->toBeTrue();
});
