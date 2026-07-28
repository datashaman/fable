<?php

use App\Enums\BeliefStance;
use App\Enums\CanonicalStatus;
use App\Enums\MilieuRole;
use App\Enums\NarrativeCollectionKind;
use App\Enums\NarrativeForm;
use App\Enums\OntologyCategory;
use App\Models\Belief;
use App\Models\Claim;
use App\Models\Conflict;
use App\Models\Continuity;
use App\Models\Entity;
use App\Models\Event;
use App\Models\Goal;
use App\Models\Milieu;
use App\Models\MilieuMembership;
use App\Models\OntologyType;
use App\Models\Perspective;
use App\Models\Relationship;
use App\Models\Rule;
use App\Models\Saga;
use App\Models\Scenario;
use App\Models\Scene;
use App\Models\Story;
use App\Models\User;
use App\Support\Fable\DomainRegistry;

test('the milieu shelf shows only milieus accessible to the user', function () {
    $user = User::factory()->create();
    $owned = Milieu::factory()->for($user, 'owner')->create(['name' => 'Owned World']);
    $shared = Milieu::factory()->create(['name' => 'Shared World']);
    $hidden = Milieu::factory()->create(['name' => 'Hidden World']);
    MilieuMembership::factory()->for($shared)->for($user)->create(['role' => MilieuRole::Viewer]);

    $response = $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertSuccessful()
        ->assertSee($owned->name)
        ->assertSee($shared->name)
        ->assertDontSee($hidden->name);

    expect(substr_count($response->getContent(), 'class="fable-connection"'))->toBe(1);
});

test('owners and members may inspect a milieu while outsiders may not', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $outsider = User::factory()->create();
    $milieu = Milieu::factory()->for($owner, 'owner')->create();
    MilieuMembership::factory()->for($milieu)->for($member)->create(['role' => MilieuRole::Viewer]);

    $this->actingAs($owner)->get(route('milieus.show', $milieu))->assertSuccessful();
    $this->actingAs($member)->get(route('milieus.explore', [$milieu, 'entity']))->assertSuccessful();
    $this->actingAs($outsider)->get(route('milieus.show', $milieu))->assertForbidden();
    $this->actingAs($outsider)->get(route('milieus.activity', $milieu))->assertForbidden();
});

test('the empty explorer uses milieu language and domain-facing history copy', function () {
    $user = User::factory()->create();
    $milieu = Milieu::factory()->for($user, 'owner')->create(['name' => 'The Imperial Frontier']);

    $this->actingAs($user)
        ->get(route('milieus.explore', [$milieu, 'continuity']))
        ->assertSuccessful()
        ->assertSeeTextInOrder(['The Imperial Frontier', 'Milieu', 'Continuities'])
        ->assertSee('Select an item from the collection to inspect its state, links, provenance, and recent changes.');
});

test('entities are grouped and filterable by ontology type', function () {
    $user = User::factory()->create();
    $milieu = Milieu::factory()->for($user, 'owner')->create();
    $placeType = OntologyType::factory()->for($milieu)->create([
        'category' => OntologyCategory::Entity,
        'name' => 'Place',
    ]);
    $characterType = OntologyType::factory()->for($milieu)->create([
        'category' => OntologyCategory::Entity,
        'name' => 'Character',
    ]);
    Entity::factory()->for($milieu)->for($placeType, 'type')->create(['name' => 'Vestra']);
    Entity::factory()->for($milieu)->for($characterType, 'type')->create(['name' => 'Aria Venn']);

    $this->actingAs($user)
        ->get(route('milieus.explore', [$milieu, 'entity']))
        ->assertSuccessful()
        ->assertSee('aria-label="Filter by entity type"', false)
        ->assertSeeTextInOrder(['Character', '1 entity', 'Aria Venn', 'Place', '1 entity', 'Vestra']);

    $this->get(route('milieus.explore', [$milieu, 'entity', 'type' => $characterType->id]))
        ->assertSuccessful()
        ->assertSee('Aria Venn')
        ->assertDontSee('Vestra');
});

test('every registered record type has an explorer', function (string $recordType) {
    $user = User::factory()->create();
    $milieu = Milieu::factory()->for($user, 'owner')->create();

    $this->actingAs($user)
        ->get(route('milieus.explore', [$milieu, $recordType]))
        ->assertSuccessful();
})->with(fn (): array => collect(app(DomainRegistry::class)->types())
    ->mapWithKeys(fn (string $recordType): array => [$recordType => [$recordType]])
    ->all());

test('a record from another milieu cannot be opened through the current milieu', function () {
    $user = User::factory()->create();
    $milieu = Milieu::factory()->for($user, 'owner')->create();
    $otherMilieu = Milieu::factory()->for($user, 'owner')->create();
    $entity = Entity::factory()->for($otherMilieu)->create();

    $this->actingAs($user)
        ->get(route('milieus.explore', [$milieu, 'entity', $entity]))
        ->assertNotFound();
});

test('the explorer can search within the selected milieu', function () {
    $user = User::factory()->create();
    $milieu = Milieu::factory()->for($user, 'owner')->create();
    Entity::factory()->for($milieu)->create(['name' => 'The Brass Cartographer']);
    Entity::factory()->for($milieu)->create(['name' => 'The Glass Regent']);

    $this->actingAs($user)
        ->get(route('milieus.explore', [$milieu, 'entity', 'q' => 'Brass']))
        ->assertSuccessful()
        ->assertSee('The Brass Cartographer')
        ->assertDontSee('The Glass Regent');
});

test('collection summaries do not repeat the status shown beside the record name', function () {
    $user = User::factory()->create();
    $milieu = Milieu::factory()->for($user, 'owner')->create();
    Entity::factory()->for($milieu)->create([
        'name' => 'The Brass Cartographer',
        'canonical_status' => CanonicalStatus::Canonical,
        'description' => 'Keeper of the celestial charts.',
    ]);

    $this->actingAs($user)
        ->get(route('milieus.explore', [$milieu, 'entity']))
        ->assertSuccessful()
        ->assertSee('aria-label="Status: Canonical"', false)
        ->assertSee('data-fable-canonical-status="canonical"', false)
        ->assertSee('data-fable-status-position="row-end"', false)
        ->assertSee('size-[1.125rem] translate-y-1', false)
        ->assertDontSee('class="fable-status"', false)
        ->assertSee('Keeper of the celestial charts.')
        ->assertDontSee('&quot;canonical&quot;', false);
});

test('continuity summaries do not repeat the canonical status badge', function () {
    $user = User::factory()->create();
    $milieu = Milieu::factory()->for($user, 'owner')->create();
    Continuity::factory()->for($milieu)->create([
        'name' => 'Primary',
        'canonical_status' => CanonicalStatus::Canonical,
        'description' => 'The default canonical timeline.',
    ]);

    $this->actingAs($user)
        ->get(route('milieus.explore', [$milieu, 'continuity']))
        ->assertSuccessful()
        ->assertSee('aria-label="Status: Canonical"', false)
        ->assertSee('data-fable-canonical-status="canonical"', false)
        ->assertDontSee('class="fable-status"', false)
        ->assertSee('The default canonical timeline.')
        ->assertDontSee('&quot;canonical&quot;', false);
});

test('relationship collection rows use the semantic triple as their title', function () {
    $user = User::factory()->create();
    $milieu = Milieu::factory()->for($user, 'owner')->create();
    $continuity = Continuity::factory()->for($milieu)->create();
    $relationshipType = OntologyType::factory()->for($milieu)->create([
        'category' => OntologyCategory::Relationship,
        'name' => 'Controls',
    ]);
    $source = Entity::factory()->for($milieu)->create(['name' => 'The Empire']);
    $target = Entity::factory()->for($milieu)->create(['name' => 'Vestra']);
    $relationship = Relationship::factory()->for($milieu)->for($continuity)->create([
        'type_id' => $relationshipType->id,
        'source_id' => $source->id,
        'target_id' => $target->id,
        'symmetric' => false,
        'inverse' => 'controlled_by',
        'description' => 'Authority transferred after the frontier revolt.',
        'canonical_status' => CanonicalStatus::Canonical,
    ]);

    $this->actingAs($user)
        ->get(route('milieus.explore', [$milieu, 'relationship']))
        ->assertSuccessful()
        ->assertSee('The Empire - Controls → Vestra')
        ->assertSee('Authority transferred after the frontier revolt.')
        ->assertSee('aria-label="Status: Canonical"', false)
        ->assertDontSee('controlled_by')
        ->assertDontSee('Relationship #')
        ->assertDontSee("#{$relationship->id}");
});

test('event collection rows preserve the complete temporal range', function () {
    $user = User::factory()->create();
    $milieu = Milieu::factory()->for($user, 'owner')->create();
    $continuity = Continuity::factory()->for($milieu)->create();
    $eventType = OntologyType::factory()->for($milieu)->create([
        'category' => OntologyCategory::Event,
    ]);
    Event::factory()->for($milieu)->for($continuity)->create([
        'type_id' => $eventType->id,
        'name' => 'Capture of Vestra',
        'start_time' => '487-03-14',
        'end_time' => '487-03-17',
        'description' => 'The Ashen Fleet seized Vestra after a three-day blockade.',
    ]);

    $this->actingAs($user)
        ->get(route('milieus.explore', [$milieu, 'event']))
        ->assertSuccessful()
        ->assertSee('487-03-14 → 487-03-17')
        ->assertSee('shrink-0 whitespace-nowrap font-mono', false)
        ->assertSee('The Ashen Fleet seized Vestra after a three-day blockade.');
});

test('rule collection rows show validity instead of execution priority', function () {
    $user = User::factory()->create();
    $milieu = Milieu::factory()->for($user, 'owner')->create();
    $ruleType = OntologyType::factory()->for($milieu)->create([
        'category' => OntologyCategory::Rule,
    ]);
    Rule::factory()->for($milieu)->create([
        'type_id' => $ruleType->id,
        'name' => 'Imperial Gate Monopoly',
        'priority' => 50,
        'valid_from' => '410',
        'valid_until' => '487',
        'description' => 'Private ownership of transit gates is prohibited.',
    ]);

    $this->actingAs($user)
        ->get(route('milieus.explore', [$milieu, 'rule']))
        ->assertSuccessful()
        ->assertSee('410 → 487')
        ->assertSee('shrink-0 whitespace-nowrap font-mono', false)
        ->assertSee('Private ownership of transit gates is prohibited.')
        ->assertDontSee('Priority 50');
});

test('claim collection rows read as complete propositions', function () {
    $user = User::factory()->create();
    $milieu = Milieu::factory()->for($user, 'owner')->create();
    $adviser = Entity::factory()->for($milieu)->create(['name' => 'The Royal Adviser']);
    $king = Entity::factory()->for($milieu)->create(['name' => 'The King']);

    Claim::factory()->for($milieu)->create([
        'subject_id' => $adviser->id,
        'predicate' => 'murdered',
        'object_id' => $king->id,
        'object_value' => null,
        'description' => 'The adviser murdered the king.',
    ]);
    Claim::factory()->for($milieu)->create([
        'subject_id' => $king->id,
        'predicate' => 'died_of',
        'object_id' => null,
        'object_value' => 'illness',
        'description' => "The official account of the King's death.",
    ]);

    $this->actingAs($user)
        ->get(route('milieus.explore', [$milieu, 'claim']))
        ->assertSuccessful()
        ->assertSee('The Royal Adviser murdered The King')
        ->assertSee('The King died of illness')
        ->assertSee('The adviser murdered the king.')
        ->assertSee("The official account of the King's death.")
        ->assertDontSee('died_of');
});

test('belief collection rows identify the holder stance and complete claim', function () {
    $user = User::factory()->create();
    $milieu = Milieu::factory()->for($user, 'owner')->create();
    $continuity = Continuity::factory()->for($milieu)->create();
    $aria = Entity::factory()->for($milieu)->create(['name' => 'Aria Venn']);
    $adviser = Entity::factory()->for($milieu)->create(['name' => 'The Royal Adviser']);
    $king = Entity::factory()->for($milieu)->create(['name' => 'The King']);
    $claim = Claim::factory()->for($milieu)->create([
        'subject_id' => $adviser->id,
        'predicate' => 'murdered',
        'object_id' => $king->id,
        'object_value' => null,
    ]);
    Belief::factory()->for($milieu)->for($continuity)->create([
        'holder_id' => $aria->id,
        'claim_id' => $claim->id,
        'stance' => BeliefStance::Accepts,
        'confidence' => 0.8,
        'acquired_at' => '487-04-02',
        'valid_until' => null,
        'description' => 'Aria believes the adviser personally killed the king.',
        'canonical_status' => CanonicalStatus::Canonical,
    ]);

    $this->actingAs($user)
        ->get(route('milieus.explore', [$milieu, 'belief']))
        ->assertSuccessful()
        ->assertSee('Aria Venn accepts: The Royal Adviser murdered The King')
        ->assertSee('487-04-02')
        ->assertSee('Aria believes the adviser personally killed the king.')
        ->assertSee('aria-label="Status: Canonical"', false)
        ->assertDontSee('0.8');
});

test('story collection rows are grouped by scenario and render form values plainly', function () {
    $user = User::factory()->create();
    $milieu = Milieu::factory()->for($user, 'owner')->create();
    $continuity = Continuity::factory()->for($milieu)->create();
    $scenario = Scenario::factory()->for($milieu)->create(['name' => 'The gate opens']);
    Story::factory()->for($milieu)->for($continuity)->for($scenario)->create([
        'title' => 'Gatebreaker',
        'form' => NarrativeForm::Novella,
    ]);

    $this->actingAs($user)
        ->get(route('milieus.explore', [$milieu, 'story']))
        ->assertSuccessful()
        ->assertSeeTextInOrder(['The gate opens', '1 story', 'Gatebreaker', 'novella'])
        ->assertSee('>novella<', false);
});

test('saga collection rows render kind values plainly', function () {
    $user = User::factory()->create();
    $milieu = Milieu::factory()->for($user, 'owner')->create();
    $continuity = Continuity::factory()->for($milieu)->create();
    Saga::factory()->for($milieu)->for($continuity)->create([
        'title' => 'Ashen Frontier',
        'kind' => NarrativeCollectionKind::Saga,
    ]);

    $this->actingAs($user)
        ->get(route('milieus.explore', [$milieu, 'saga']))
        ->assertSuccessful()
        ->assertSeeTextInOrder(['Ashen Frontier', 'saga'])
        ->assertSee('>saga<', false);
});

test('scene collection rows keep their sequence label readable', function () {
    $user = User::factory()->create();
    $milieu = Milieu::factory()->for($user, 'owner')->create();
    $continuity = Continuity::factory()->for($milieu)->create();
    $story = Story::factory()->for($milieu)->for($continuity)->create();
    Scene::factory()->for($story)->create([
        'name' => 'The Gate Falls',
        'sequence' => 2,
        'description' => 'Aria leads the boarding action.',
    ]);

    $this->actingAs($user)
        ->get(route('milieus.explore', [$milieu, 'scene']))
        ->assertSuccessful()
        ->assertSeeTextInOrder(['The Gate Falls', 'Scene 2', 'Aria leads the boarding action.']);
});

test('a selected record renders its structured read only detail', function () {
    $user = User::factory()->create();
    $milieu = Milieu::factory()->for($user, 'owner')->create();
    $type = OntologyType::factory()->for($milieu)->create([
        'category' => OntologyCategory::Entity,
        'name' => 'Character',
    ]);
    $entity = Entity::factory()->for($milieu)->create([
        'type_id' => $type->id,
        'name' => 'The Brass Cartographer',
        'aliases' => ['The Gatebreaker'],
        'attributes' => ['instrument' => 'astrolabe'],
        'tags' => ['human', 'frontier'],
    ]);

    $this->actingAs($user)
        ->get(route('milieus.explore', [$milieu, 'entity', $entity]))
        ->assertSuccessful()
        ->assertSee('The Brass Cartographer')
        ->assertSee('Character')
        ->assertSee('Type')
        ->assertDontSee('Type ID')
        ->assertDontSee("Ontology type #{$type->id}")
        ->assertSee('Instrument')
        ->assertSee('astrolabe')
        ->assertDontSee('&quot;instrument&quot;', false)
        ->assertSee('The Gatebreaker')
        ->assertSee('class="fable-value-list"', false)
        ->assertDontSee('&quot;The Gatebreaker&quot;', false)
        ->assertSee('class="fable-entity-identifiers fable-tag-list"', false)
        ->assertSee('class="fable-tag"', false)
        ->assertSee('human')
        ->assertSee('frontier')
        ->assertSee('Entity record navigation')
        ->assertSee('1 of 1')
        ->assertSee('Recent Changes')
        ->assertDontSee('Save');
});

test('entities can be filtered by status and sorted alphabetically within type groups', function () {
    $user = User::factory()->create();
    $milieu = Milieu::factory()->for($user, 'owner')->create();
    $characterType = OntologyType::factory()->for($milieu)->create([
        'category' => OntologyCategory::Entity,
        'name' => 'Character',
    ]);
    $placeType = OntologyType::factory()->for($milieu)->create([
        'category' => OntologyCategory::Entity,
        'name' => 'Place',
    ]);

    Entity::factory()->for($milieu)->for($characterType, 'type')->create([
        'name' => 'Zora',
        'canonical_status' => CanonicalStatus::Canonical,
    ]);
    Entity::factory()->for($milieu)->for($characterType, 'type')->create([
        'name' => 'Aria',
        'canonical_status' => CanonicalStatus::Canonical,
    ]);
    Entity::factory()->for($milieu)->for($placeType, 'type')->create([
        'name' => 'Vestra',
        'canonical_status' => CanonicalStatus::Disputed,
    ]);

    $this->actingAs($user)
        ->get(route('milieus.explore', [$milieu, 'entity', 'status' => 'canonical', 'sort' => 'alphabetical']))
        ->assertSuccessful()
        ->assertSeeTextInOrder(['Character', '2 entities', 'Aria', 'Zora'])
        ->assertDontSee('Vestra')
        ->assertSee('Recently changed')
        ->assertSee('Alphabetical');
});

test('entity details lead with graph knowledge and possibility context before attributes', function () {
    $user = User::factory()->create();
    $milieu = Milieu::factory()->for($user, 'owner')->create();
    $continuity = Continuity::factory()->for($milieu)->create();
    $entity = Entity::factory()->for($milieu)->create([
        'name' => 'Aria Venn',
        'attributes' => ['occupation' => 'smuggler'],
    ]);
    $target = Entity::factory()->for($milieu)->create(['name' => 'The King']);
    Claim::factory()->for($milieu)->create([
        'subject_id' => $entity->id,
        'predicate' => 'warned',
        'object_id' => $target->id,
        'object_value' => null,
    ]);
    Goal::factory()->for($milieu)->for($continuity)->create([
        'holder_id' => $entity->id,
        'objective' => 'Open the frontier gate',
    ]);
    Conflict::factory()->for($milieu)->for($continuity)->create([
        'subject_id' => $entity->id,
        'description' => 'The gate remains blockaded',
    ]);

    $this->actingAs($user)
        ->get(route('milieus.explore', [$milieu, 'entity', $entity]))
        ->assertSuccessful()
        ->assertSeeTextInOrder([
            'Relationships',
            'Knowledge',
            'Aria Venn warned The King',
            'Possibility',
            'Open the frontier gate',
            'The gate remains blockaded',
            'Attributes',
            'Occupation',
            'smuggler',
            'Recent Changes',
        ]);
});

test('an entity detail shows incoming and outgoing relationships as domain statements', function () {
    $user = User::factory()->create();
    $milieu = Milieu::factory()->for($user, 'owner')->create();
    $continuity = Continuity::factory()->for($milieu)->create(['name' => 'Primary']);
    $branch = Continuity::factory()->for($milieu)->create(['name' => 'Branch Timeline']);
    $relationshipType = OntologyType::factory()->for($milieu)->create([
        'category' => OntologyCategory::Relationship,
        'name' => 'Commands',
    ]);
    $entity = Entity::factory()->for($milieu)->create(['name' => 'Aria Venn']);
    $fleet = Entity::factory()->for($milieu)->create(['name' => 'Ashen Fleet']);
    $empire = Entity::factory()->for($milieu)->create(['name' => 'The Empire']);
    $oldRegent = Entity::factory()->for($milieu)->create(['name' => 'The Old Regent']);
    $unrelated = Entity::factory()->for($milieu)->create(['name' => 'The Unrelated Observer']);

    Relationship::factory()->for($milieu)->for($continuity)->create([
        'type_id' => $relationshipType->id,
        'source_id' => $entity->id,
        'target_id' => $fleet->id,
        'symmetric' => false,
        'started_at' => '456',
        'ended_at' => '472',
        'description' => 'Aria directs the fleet through the frontier.',
        'canonical_status' => CanonicalStatus::Canonical,
    ]);
    Relationship::factory()->for($milieu)->for($branch)->create([
        'type_id' => $relationshipType->id,
        'source_id' => $empire->id,
        'target_id' => $entity->id,
        'symmetric' => false,
        'started_at' => '473',
        'ended_at' => null,
        'description' => 'The Empire commands Aria by royal decree.',
        'canonical_status' => CanonicalStatus::Disputed,
    ]);
    Relationship::factory()->for($milieu)->for($continuity)->create([
        'type_id' => $relationshipType->id,
        'source_id' => $fleet->id,
        'target_id' => $unrelated->id,
    ]);
    Relationship::factory()->for($milieu)->for($continuity)->create([
        'type_id' => $relationshipType->id,
        'source_id' => $oldRegent->id,
        'target_id' => $entity->id,
        'symmetric' => false,
        'started_at' => '400',
        'ended_at' => '455',
        'description' => 'The old regent commanded Aria first.',
    ]);

    $response = $this->actingAs($user)
        ->get(route('milieus.explore', [$milieu, 'entity', $entity, 'q' => 'Aria']))
        ->assertSuccessful()
        ->assertSee('Relationships')
        ->assertSee('3 relationships')
        ->assertSeeTextInOrder(['Branch Timeline', 'The Empire', 'Commands', 'Aria Venn'])
        ->assertSeeTextInOrder(['Primary', 'The Old Regent', 'Commands', 'Aria Venn', 'Aria Venn', 'Commands', 'Ashen Fleet'])
        ->assertSee('Primary')
        ->assertSee('canonical')
        ->assertSee('disputed')
        ->assertSee('From 456')
        ->assertSee('Until 472')
        ->assertSee('From 473')
        ->assertSee('From 400')
        ->assertSee('Until 455')
        ->assertSee('Aria directs the fleet through the frontier.')
        ->assertSee('The Empire commands Aria by royal decree.')
        ->assertSee('The old regent commanded Aria first.')
        ->assertDontSee('The Unrelated Observer');

    expect(substr_count($response->getContent(), 'class="fable-relationship-group"'))->toBe(2);
});

test('ontology types show their instance counts and entries', function () {
    $user = User::factory()->create();
    $milieu = Milieu::factory()->for($user, 'owner')->create();
    $type = OntologyType::factory()->for($milieu)->create([
        'category' => OntologyCategory::Entity,
        'name' => 'Character',
    ]);
    Entity::factory()->for($milieu)->create([
        'type_id' => $type->id,
        'name' => 'The Brass Cartographer',
    ]);
    Entity::factory()->for($milieu)->create([
        'type_id' => $type->id,
        'name' => 'The Glass Regent',
    ]);

    $this->actingAs($user)
        ->get(route('milieus.explore', [$milieu, 'ontology_type', $type]))
        ->assertSuccessful()
        ->assertSee('2 instances')
        ->assertSee('Entries')
        ->assertSee('The Brass Cartographer')
        ->assertSee('The Glass Regent')
        ->assertDontSee('Linked records')
        ->assertDontSee('Records classified as this ontology type.');
});

test('linked records span the full detail width', function () {
    $user = User::factory()->create();
    $milieu = Milieu::factory()->for($user, 'owner')->create();
    $continuity = Continuity::factory()->for($milieu)->create();
    $holder = Entity::factory()->for($milieu)->create();
    $knownEntity = Entity::factory()->for($milieu)->create(['name' => 'The Royal Adviser']);
    $perspective = Perspective::factory()->for($milieu)->for($continuity)->create([
        'holder_id' => $holder->id,
    ]);
    $perspective->knownEntities()->attach($knownEntity);

    $this->actingAs($user)
        ->get(route('milieus.explore', [$milieu, 'perspective', $perspective]))
        ->assertSuccessful()
        ->assertSee('Known Entities')
        ->assertSee('The Royal Adviser')
        ->assertSee('col-span-full mt-3 flex flex-wrap gap-2', false);
});

test('the ontology index groups types by category', function () {
    $user = User::factory()->create();
    $milieu = Milieu::factory()->for($user, 'owner')->create();

    foreach (OntologyCategory::cases() as $category) {
        OntologyType::factory()->for($milieu)->create([
            'category' => $category,
            'name' => str($category->value)->headline().' Type',
        ]);
    }
    OntologyType::factory()->for($milieu)->create([
        'category' => OntologyCategory::Entity,
        'name' => 'Second Entity Type',
    ]);

    $this->actingAs($user)
        ->get(route('milieus.explore', [$milieu, 'ontology_type']))
        ->assertSuccessful()
        ->assertSeeTextInOrder(['Entities', 'Relationships', 'Events', 'Rules'])
        ->assertSee('2 types')
        ->assertSee('1 type');
});

test('ontology index rows show only the type name and instance count', function () {
    $user = User::factory()->create();
    $milieu = Milieu::factory()->for($user, 'owner')->create();
    $type = OntologyType::factory()->for($milieu)->create([
        'category' => OntologyCategory::Entity,
        'key' => 'character-key',
        'name' => 'Character',
    ]);
    Entity::factory()->count(2)->for($milieu)->create(['type_id' => $type->id]);

    $this->actingAs($user)
        ->get(route('milieus.explore', [$milieu, 'ontology_type']))
        ->assertSuccessful()
        ->assertSee('Character')
        ->assertSee('2 instances')
        ->assertDontSee('character-key')
        ->assertDontSee('&quot;entity&quot;', false);
});

test('domain browsing exposes no mutation routes', function () {
    $domainRoutes = collect(app('router')->getRoutes()->getRoutes())
        ->filter(fn ($route): bool => str_starts_with($route->uri(), 'milieus'));

    expect($domainRoutes)->not->toBeEmpty()
        ->and($domainRoutes->every(fn ($route): bool => $route->methods() === ['GET', 'HEAD']))->toBeTrue();
});
