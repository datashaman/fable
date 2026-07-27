<?php

use App\Models\Entity;
use App\Models\Event;
use App\Models\OntologyType;
use App\Models\Relationship;

test('a set_attribute effect merges into the entity\'s existing attributes', function () {
    $entity = Entity::factory()->create(['attributes' => ['age' => 31, 'occupation' => 'smuggler']]);
    $event = Event::factory()->create([
        'effects' => [
            ['type' => 'set_attribute', 'entity_id' => $entity->id, 'attribute' => 'occupation', 'value' => 'rebel'],
        ],
    ]);

    $event->applyEffects();

    expect($entity->fresh()->attributes)->toBe(['age' => 31, 'occupation' => 'rebel']);
});

test('an end_relationship effect sets the relationship\'s ended_at from the event', function () {
    $relationship = Relationship::factory()->create(['ended_at' => null]);
    $event = Event::factory()->create([
        'end_time' => '487-03-17',
        'effects' => [
            ['type' => 'end_relationship', 'relationship_id' => $relationship->id],
        ],
    ]);

    $event->applyEffects();

    expect($relationship->fresh()->ended_at)->toBe('487-03-17');
});

test('a create_relationship effect creates a new relationship in the event\'s continuity', function () {
    $source = Entity::factory()->create();
    $target = Entity::factory()->create();
    $type = OntologyType::factory()->create();
    $event = Event::factory()->create([
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
