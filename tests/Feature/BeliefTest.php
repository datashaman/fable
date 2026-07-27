<?php

use App\Enums\BeliefStance;
use App\Enums\BeliefVisibility;
use App\Enums\CanonicalStatus;
use App\Models\Belief;
use App\Models\Claim;
use App\Models\Entity;
use App\Models\Milieu;

test('a belief can be created with default factory state', function () {
    $belief = Belief::factory()->create();

    expect($belief->claim)->toBeInstanceOf(Claim::class);

    expect($belief)
        ->stance->toBeInstanceOf(BeliefStance::class)
        ->confidence->toBeFloat()
        ->source->toBeArray()
        ->visibility->toBeInstanceOf(BeliefVisibility::class)
        ->canonical_status->toBeInstanceOf(CanonicalStatus::class)
        ->provenance->toBeArray();
});

test('claim, stance, source, visibility, canonical_status and provenance are cast correctly', function () {
    $claim = Claim::factory()->create();

    $belief = Belief::factory()->create([
        'claim_id' => $claim->id,
        'stance' => BeliefStance::Accepts,
        'valid_until' => '487-07-01',
        'source' => ['type' => 'entity', 'id' => 'character_informant'],
        'visibility' => BeliefVisibility::Secret,
        'canonical_status' => CanonicalStatus::Canonical,
        'provenance' => ['source' => 'chapter_12', 'author' => 'marlinf', 'recorded_at' => '2026-07-27'],
    ]);

    $belief->refresh();

    expect($belief->claim->is($claim))->toBeTrue()
        ->and($belief->stance)->toBe(BeliefStance::Accepts)
        ->and($belief->valid_until)->toBe('487-07-01')
        ->and($belief->source)->toBe(['type' => 'entity', 'id' => 'character_informant'])
        ->and($belief->visibility)->toBe(BeliefVisibility::Secret)
        ->and($belief->canonical_status)->toBe(CanonicalStatus::Canonical)
        ->and($belief->provenance)->toBe(['source' => 'chapter_12', 'author' => 'marlinf', 'recorded_at' => '2026-07-27']);
});

test('a belief belongs to a milieu and is held by an entity', function () {
    $milieu = Milieu::factory()->create();
    $holder = Entity::factory()->create();

    $belief = Belief::factory()->for($milieu)->create(['holder_id' => $holder->id]);

    expect($belief->milieu->is($milieu))->toBeTrue()
        ->and($belief->holder->is($holder))->toBeTrue()
        ->and($milieu->beliefs->first()->is($belief))->toBeTrue();
});
