<?php

use App\Models\Event;
use App\Models\Scene;
use App\Models\Story;

test('a scene can be created with default factory state', function () {
    $scene = Scene::factory()->create();

    expect($scene->name)->toBeString()
        ->and($scene->sequence)->toBeInt();
});

test('a scene belongs to a story and presents events', function () {
    $story = Story::factory()->create();
    $scene = Scene::factory()->for($story)->create();
    $event = Event::factory()->create();

    $scene->events()->attach($event);

    expect($scene->story->is($story))->toBeTrue()
        ->and($story->scenes->first()->is($scene))->toBeTrue()
        ->and($scene->events->first()->is($event))->toBeTrue();
});

test('a story orders its scenes by sequence', function () {
    $story = Story::factory()->create();
    Scene::factory()->for($story)->create(['sequence' => 2, 'name' => 'Third']);
    Scene::factory()->for($story)->create(['sequence' => 0, 'name' => 'First']);
    Scene::factory()->for($story)->create(['sequence' => 1, 'name' => 'Second']);

    expect($story->scenes->pluck('name')->all())->toBe(['First', 'Second', 'Third']);
});
