<?php

use App\Enums\CanonicalStatus;
use App\Enums\NarrationMode;
use App\Enums\NarrationPerson;
use App\Enums\NarrationReliability;
use App\Enums\NarrativeForm;
use App\Models\Continuity;
use App\Models\Entity;
use App\Models\Event;
use App\Models\Milieu;
use App\Models\Perspective;
use App\Models\Scenario;
use App\Models\Story;

test('a story can be created with default factory state', function () {
    $story = Story::factory()->create();

    expect($story)
        ->title->toBeString()
        ->form->toBeInstanceOf(NarrativeForm::class)
        ->themes->toBeArray()
        ->canonical_status->toBeInstanceOf(CanonicalStatus::class);
});

test('a story belongs to a milieu, a continuity and optionally a scenario', function () {
    $milieu = Milieu::factory()->create();
    $continuity = Continuity::factory()->create();
    $scenario = Scenario::factory()->create();

    $story = Story::factory()->create([
        'milieu_id' => $milieu->id,
        'continuity_id' => $continuity->id,
        'scenario_id' => $scenario->id,
    ]);

    expect($story->milieu->is($milieu))->toBeTrue()
        ->and($story->continuity->is($continuity))->toBeTrue()
        ->and($story->scenario->is($scenario))->toBeTrue()
        ->and($milieu->stories->first()->is($story))->toBeTrue()
        ->and($scenario->stories->first()->is($story))->toBeTrue();
});

test('a story presents events in narrative order and has perspectives', function () {
    $story = Story::factory()->create();
    $first = Event::factory()->create();
    $second = Event::factory()->create();
    $perspective = Perspective::factory()->create();

    $story->events()->attach($second->id, ['sequence' => 1]);
    $story->events()->attach($first->id, ['sequence' => 0]);
    $story->perspectives()->attach($perspective);

    expect($story->events->pluck('id')->all())->toBe([$first->id, $second->id])
        ->and($story->perspectives->first()->is($perspective))->toBeTrue();
});

test('a story can declare its narration and focalizer', function () {
    $focalizer = Entity::factory()->create();
    $narrator = Entity::factory()->create();

    $story = Story::factory()->create([
        'narration_person' => NarrationPerson::Third,
        'narration_mode' => NarrationMode::Limited,
        'focalizer_id' => $focalizer->id,
        'narrator_id' => $narrator->id,
        'narration_reliability' => NarrationReliability::MostlyReliable,
    ]);

    expect($story->narration_person)->toBe(NarrationPerson::Third)
        ->and($story->narration_mode)->toBe(NarrationMode::Limited)
        ->and($story->narration_reliability)->toBe(NarrationReliability::MostlyReliable)
        ->and($story->focalizer->is($focalizer))->toBeTrue()
        ->and($story->narrator->is($narrator))->toBeTrue();
});
