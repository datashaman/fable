<?php

use App\Enums\MilieuRole;
use App\Enums\OntologyCategory;
use App\Mcp\Prompts\ComposeStoryPrompt;
use App\Mcp\Resources\MilieuResource;
use App\Mcp\Resources\SchemaResource;
use App\Mcp\Servers\FableServer;
use App\Mcp\Tools\SaveEntity;
use App\Mcp\Tools\SaveScene;
use App\Mcp\Tools\SearchState;
use App\Models\ChangeEntry;
use App\Models\Continuity;
use App\Models\Event;
use App\Models\Milieu;
use App\Models\MilieuMembership;
use App\Models\OntologyType;
use App\Models\Story;
use App\Models\User;

test('schema and scoped milieu resources expose compact agent state', function () {
    $owner = User::factory()->create();
    $milieu = Milieu::factory()->for($owner, 'owner')->create();

    FableServer::actingAs($owner)->resource(SchemaResource::class)
        ->assertOk()
        ->assertSee(['record_types', 'expected_revision', 'deletion', 'participants', 'pivot_fields']);

    FableServer::actingAs($owner)->resource(MilieuResource::class, ['milieuId' => $milieu->id])
        ->assertOk()
        ->assertSee([$milieu->name, 'owner', 'entities_count']);
});

test('an editor can create and revision-safely update an aggregate with an audit trail', function () {
    $owner = User::factory()->create();
    $editor = User::factory()->create();
    $milieu = Milieu::factory()->for($owner, 'owner')->create();
    MilieuMembership::factory()->for($milieu)->for($editor)->create(['role' => MilieuRole::Editor]);
    $type = OntologyType::factory()->for($milieu)->create(['category' => OntologyCategory::Entity]);

    FableServer::actingAs($editor)->tool(SaveEntity::class, [
        'data' => ['milieu_id' => $milieu->id, 'type_id' => $type->id, 'name' => 'Aster Vale'],
    ])->assertOk()->assertSee(['Aster Vale', 'change_set_id']);

    $entity = $milieu->entities()->where('name', 'Aster Vale')->firstOrFail();

    FableServer::actingAs($editor)->tool(SaveEntity::class, [
        'id' => $entity->id,
        'expected_revision' => 1,
        'data' => ['description' => 'A city at the edge of the known world.'],
    ])->assertOk()->assertSee(['revision', '2']);

    expect(ChangeEntry::query()->where('record_type', 'entity')->count())->toBe(2)
        ->and($entity->fresh()->revision)->toBe(2);
});

test('viewer mutations and stale updates are rejected', function () {
    $owner = User::factory()->create();
    $viewer = User::factory()->create();
    $milieu = Milieu::factory()->for($owner, 'owner')->create();
    MilieuMembership::factory()->for($milieu)->for($viewer)->create(['role' => MilieuRole::Viewer]);
    $type = OntologyType::factory()->for($milieu)->create(['category' => OntologyCategory::Entity]);
    $entity = $milieu->entities()->create(['type_id' => $type->id, 'name' => 'Existing']);

    FableServer::actingAs($viewer)->tool(SaveEntity::class, [
        'id' => $entity->id,
        'expected_revision' => 1,
        'data' => ['name' => 'Forbidden'],
    ])->assertHasErrors();

    FableServer::actingAs($owner)->tool(SaveEntity::class, [
        'id' => $entity->id,
        'expected_revision' => 99,
        'data' => ['name' => 'Stale'],
    ])->assertHasErrors()->assertSee('Stale revision');
});

test('aggregate mutations reject cross-milieu references and ontology mismatches', function () {
    $owner = User::factory()->create();
    $milieu = Milieu::factory()->for($owner, 'owner')->create();
    $otherMilieu = Milieu::factory()->for($owner, 'owner')->create();
    $wrongType = OntologyType::factory()->for($otherMilieu)->create(['category' => OntologyCategory::Entity]);

    FableServer::actingAs($owner)->tool(SaveEntity::class, [
        'data' => ['milieu_id' => $milieu->id, 'type_id' => $wrongType->id, 'name' => 'Impossible'],
    ])->assertHasErrors()->assertSee('Cross-milieu');

    $wrongCategory = OntologyType::factory()->for($milieu)->create(['category' => OntologyCategory::Event]);

    FableServer::actingAs($owner)->tool(SaveEntity::class, [
        'data' => ['milieu_id' => $milieu->id, 'type_id' => $wrongCategory->id, 'name' => 'Also impossible'],
    ])->assertHasErrors()->assertSee('entity category');
});

test('a scene can synchronize events without adding story ordering metadata', function () {
    $owner = User::factory()->create();
    $milieu = Milieu::factory()->for($owner, 'owner')->create();
    $continuity = Continuity::factory()->for($milieu)->create();
    $event = Event::factory()->for($milieu)->for($continuity)->create();
    $laterEvent = Event::factory()->for($milieu)->for($continuity)->create();
    $story = Story::factory()->for($milieu)->for($continuity)->create();

    FableServer::actingAs($owner)->tool(SaveScene::class, [
        'data' => [
            'story_id' => $story->id,
            'name' => 'The event is presented',
            'sequence' => 1,
        ],
        'relations' => ['events' => [$event->id]],
    ])->assertOk();

    $scene = $story->scenes()->where('name', 'The event is presented')->firstOrFail();

    expect($scene->events)->toHaveCount(1)
        ->and($scene->events->first()->is($event))->toBeTrue();

    FableServer::actingAs($owner)->tool(SaveScene::class, [
        'id' => $scene->id,
        'expected_revision' => 1,
        'data' => ['name' => $scene->name],
        'relations' => ['events' => [$event->id, $laterEvent->id]],
    ])->assertOk()->assertSee(['revision', '2']);

    expect($scene->fresh()->events)->toHaveCount(2)
        ->and($scene->fresh()->revision)->toBe(2);

    FableServer::actingAs($owner)->tool(SaveScene::class, [
        'id' => $scene->id,
        'expected_revision' => 1,
        'data' => ['name' => $scene->name],
        'relations' => ['events' => [$event->id]],
    ])->assertHasErrors()->assertSee('Stale revision');
});

test('search is milieu scoped and guided prompts enforce the playbook workflow', function () {
    $owner = User::factory()->create();
    $milieu = Milieu::factory()->for($owner, 'owner')->create();
    $otherMilieu = Milieu::factory()->create();
    $type = OntologyType::factory()->for($milieu)->create(['category' => OntologyCategory::Entity]);
    $otherType = OntologyType::factory()->for($otherMilieu)->create(['category' => OntologyCategory::Entity]);
    $milieu->entities()->create(['type_id' => $type->id, 'name' => 'Shared Needle']);
    $otherMilieu->entities()->create(['type_id' => $otherType->id, 'name' => 'Shared Needle Elsewhere']);

    FableServer::actingAs($owner)->tool(SearchState::class, [
        'milieu_id' => $milieu->id,
        'query' => 'Needle',
        'record_type' => 'entity',
    ])->assertOk()->assertSee('Shared Needle')->assertDontSee('Elsewhere');

    FableServer::actingAs($owner)->prompt(ComposeStoryPrompt::class, [
        'milieu_id' => (string) $milieu->id,
        'context' => 'Build a mystery around the existing events.',
    ])->assertOk()->assertSee(['fable://playbook', 'expected_revision', 'order them for presentation']);
});
