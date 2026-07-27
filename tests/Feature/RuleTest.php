<?php

use App\Enums\CanonicalStatus;
use App\Enums\RuleType;
use App\Models\Milieu;
use App\Models\Rule;

test('a rule can be created with default factory state', function () {
    $rule = Rule::factory()->create();

    expect($rule)
        ->name->toBeString()
        ->description->toBeString()
        ->type->toBeInstanceOf(RuleType::class)
        ->scope->toBeArray()
        ->conditions->toBeArray()
        ->requirements->toBeArray()
        ->consequences->toBeArray()
        ->exceptions->toBeArray()
        ->priority->toBeInt()
        ->canonical_status->toBeInstanceOf(CanonicalStatus::class);
});

test('type, scope, conditions, requirements, consequences, exceptions, priority and canonical_status are cast correctly', function () {
    $rule = Rule::factory()->create([
        'type' => RuleType::Legal,
        'scope' => ['places' => ['polity_imperial_territory']],
        'conditions' => ['subject is attempting faster-than-light travel'],
        'requirements' => ['an active gate exists at the origin'],
        'consequences' => ['travel succeeds when all requirements are satisfied'],
        'exceptions' => [['entity' => 'object_void_engine', 'description' => 'The Void Engine can travel without gates.']],
        'priority' => 100,
        'canonical_status' => CanonicalStatus::Canonical,
    ]);

    $rule->refresh();

    expect($rule->type)->toBe(RuleType::Legal)
        ->and($rule->scope)->toBe(['places' => ['polity_imperial_territory']])
        ->and($rule->conditions)->toBe(['subject is attempting faster-than-light travel'])
        ->and($rule->requirements)->toBe(['an active gate exists at the origin'])
        ->and($rule->consequences)->toBe(['travel succeeds when all requirements are satisfied'])
        ->and($rule->exceptions)->toBe([['entity' => 'object_void_engine', 'description' => 'The Void Engine can travel without gates.']])
        ->and($rule->priority)->toBe(100)
        ->and($rule->canonical_status)->toBe(CanonicalStatus::Canonical);
});

test('a rule belongs to a milieu', function () {
    $milieu = Milieu::factory()->create();
    $rule = Rule::factory()->for($milieu)->create();

    expect($rule->milieu)->toBeInstanceOf(Milieu::class)
        ->and($rule->milieu->is($milieu))->toBeTrue()
        ->and($milieu->rules->first()->is($rule))->toBeTrue();
});
