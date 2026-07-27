<?php

use App\Enums\BeliefStance;
use App\Enums\BeliefVisibility;
use App\Enums\CanonicalStatus;
use App\Models\Belief;
use App\Models\Entity;
use App\Models\Milieu;

test('a belief can be created with default factory state', function () {
    $belief = Belief::factory()->create();

    expect($belief)
        ->claim->toBeArray()
        ->stance->toBeInstanceOf(BeliefStance::class)
        ->confidence->toBeFloat()
        ->source->toBeArray()
        ->visibility->toBeInstanceOf(BeliefVisibility::class)
        ->canonical_status->toBeInstanceOf(CanonicalStatus::class);
});

test('claim, stance, source, visibility and canonical_status are cast correctly', function () {
    $belief = Belief::factory()->create([
        'claim' => ['subject' => 'character_royal_adviser', 'predicate' => 'murdered', 'object' => 'character_king'],
        'stance' => BeliefStance::Accepts,
        'source' => ['type' => 'entity', 'id' => 'character_informant'],
        'visibility' => BeliefVisibility::Secret,
        'canonical_status' => CanonicalStatus::Canonical,
    ]);

    $belief->refresh();

    expect($belief->claim)->toBe(['subject' => 'character_royal_adviser', 'predicate' => 'murdered', 'object' => 'character_king'])
        ->and($belief->stance)->toBe(BeliefStance::Accepts)
        ->and($belief->source)->toBe(['type' => 'entity', 'id' => 'character_informant'])
        ->and($belief->visibility)->toBe(BeliefVisibility::Secret)
        ->and($belief->canonical_status)->toBe(CanonicalStatus::Canonical);
});

test('a belief belongs to a milieu and is held by an entity', function () {
    $milieu = Milieu::factory()->create();
    $holder = Entity::factory()->create();

    $belief = Belief::factory()->for($milieu)->create(['holder_id' => $holder->id]);

    expect($belief->milieu->is($milieu))->toBeTrue()
        ->and($belief->holder->is($holder))->toBeTrue()
        ->and($milieu->beliefs->first()->is($belief))->toBeTrue();
});
