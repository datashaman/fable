<?php

use App\Models\Belief;
use App\Models\Continuity;
use App\Models\Disclosure;
use App\Models\Milieu;
use App\Models\Scene;
use App\Models\Story;

test('a disclosure can be created with default factory state', function () {
    $disclosure = Disclosure::factory()->create();

    expect($disclosure->description)->toBeString();
});

test('a disclosure ties a claim to the scene where the audience learns it', function () {
    $milieu = Milieu::factory()->create();
    $continuity = Continuity::factory()->create();
    $belief = Belief::factory()->create();
    $story = Story::factory()->create();
    $scene = Scene::factory()->for($story)->create();

    $disclosure = Disclosure::factory()->create([
        'milieu_id' => $milieu->id,
        'continuity_id' => $continuity->id,
        'belief_id' => $belief->id,
        'scene_id' => $scene->id,
    ]);

    expect($disclosure->milieu->is($milieu))->toBeTrue()
        ->and($disclosure->continuity->is($continuity))->toBeTrue()
        ->and($disclosure->belief->is($belief))->toBeTrue()
        ->and($disclosure->scene->is($scene))->toBeTrue()
        ->and($belief->disclosures->first()->is($disclosure))->toBeTrue()
        ->and($scene->disclosures->first()->is($disclosure))->toBeTrue()
        ->and($story->disclosures->first()->is($disclosure))->toBeTrue();
});
