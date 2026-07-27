<?php

use App\Models\Belief;
use App\Models\Entity;
use App\Models\Event;
use App\Models\Milieu;
use App\Models\Perspective;

test('a perspective can be created with default factory state', function () {
    $perspective = Perspective::factory()->create();

    expect($perspective)
        ->name->toBeString()
        ->biases->toBeArray();
});

test('a perspective belongs to a milieu and has a holder', function () {
    $milieu = Milieu::factory()->create();
    $holder = Entity::factory()->create();

    $perspective = Perspective::factory()->for($milieu)->create(['holder_id' => $holder->id]);

    expect($perspective->milieu->is($milieu))->toBeTrue()
        ->and($perspective->holder->is($holder))->toBeTrue()
        ->and($milieu->perspectives->first()->is($perspective))->toBeTrue();
});

test('a perspective knows entities, events and beliefs', function () {
    $perspective = Perspective::factory()->create();
    $entity = Entity::factory()->create();
    $event = Event::factory()->create();
    $belief = Belief::factory()->create();

    $perspective->knownEntities()->attach($entity);
    $perspective->knownEvents()->attach($event);
    $perspective->beliefs()->attach($belief);

    expect($perspective->knownEntities()->first()->is($entity))->toBeTrue()
        ->and($perspective->knownEvents()->first()->is($event))->toBeTrue()
        ->and($perspective->beliefs()->first()->is($belief))->toBeTrue();
});
