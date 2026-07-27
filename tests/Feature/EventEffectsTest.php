<?php

use App\Enums\OntologyCategory;
use App\Models\Continuity;
use App\Models\Entity;
use App\Models\Event;
use App\Models\Milieu;
use App\Models\OntologyType;
use App\Models\Relationship;

test('a set_attribute effect merges into the entity\'s existing attributes', function () {
    $milieu = Milieu::factory()->create();
    $continuity = Continuity::factory()->for($milieu)->create();
    $entity = Entity::factory()->for($milieu)->create(['attributes' => ['age' => 31, 'occupation' => 'smuggler']]);
    $event = Event::factory()->create([
        'milieu_id' => $milieu->id,
        'continuity_id' => $continuity->id,
        'effects' => [
            ['type' => 'set_attribute', 'entity_id' => $entity->id, 'attribute' => 'occupation', 'value' => 'rebel'],
        ],
    ]);

    $event->applyEffects();

    expect($entity->fresh()->attributes)->toBe(['age' => 31, 'occupation' => 'rebel']);
});

test('an end_relationship effect sets the relationship\'s ended_at from the event', function () {
    $milieu = Milieu::factory()->create();
    $continuity = Continuity::factory()->for($milieu)->create();
    $relationship = Relationship::factory()->create([
        'milieu_id' => $milieu->id,
        'continuity_id' => $continuity->id,
        'ended_at' => null,
    ]);
    $event = Event::factory()->create([
        'milieu_id' => $milieu->id,
        'continuity_id' => $continuity->id,
        'end_time' => '487-03-17',
        'effects' => [
            ['type' => 'end_relationship', 'relationship_id' => $relationship->id],
        ],
    ]);

    $event->applyEffects();

    expect($relationship->fresh()->ended_at)->toBe('487-03-17');
});

test('a create_relationship effect creates a new relationship in the event\'s continuity', function () {
    $milieu = Milieu::factory()->create();
    $continuity = Continuity::factory()->for($milieu)->create();
    $source = Entity::factory()->for($milieu)->create();
    $target = Entity::factory()->for($milieu)->create();
    $type = OntologyType::factory()->for($milieu)->create(['category' => OntologyCategory::Relationship]);
    $event = Event::factory()->create([
        'milieu_id' => $milieu->id,
        'continuity_id' => $continuity->id,
        'end_time' => '487-03-17',
        'effects' => [
            ['type' => 'create_relationship', 'relationship' => [
                'type_id' => $type->id,
                'source_id' => $source->id,
                'target_id' => $target->id,
            ]],
        ],
    ]);

    $event->applyEffects();

    $relationship = Relationship::where('source_id', $source->id)->where('target_id', $target->id)->sole();

    expect($relationship->milieu_id)->toBe($event->milieu_id)
        ->and($relationship->continuity_id)->toBe($event->continuity_id)
        ->and($relationship->started_at)->toBe('487-03-17');
});
