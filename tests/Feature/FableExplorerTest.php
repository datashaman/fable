<?php

use App\Enums\CanonicalStatus;
use App\Enums\MilieuRole;
use App\Enums\OntologyCategory;
use App\Models\Continuity;
use App\Models\Entity;
use App\Models\Milieu;
use App\Models\MilieuMembership;
use App\Models\OntologyType;
use App\Models\Relationship;
use App\Models\User;
use App\Support\Fable\DomainRegistry;

test('the world shelf shows only milieus accessible to the user', function () {
    $user = User::factory()->create();
    $owned = Milieu::factory()->for($user, 'owner')->create(['name' => 'Owned World']);
    $shared = Milieu::factory()->create(['name' => 'Shared World']);
    $hidden = Milieu::factory()->create(['name' => 'Hidden World']);
    MilieuMembership::factory()->for($shared)->for($user)->create(['role' => MilieuRole::Viewer]);

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertSuccessful()
        ->assertSee($owned->name)
        ->assertSee($shared->name)
        ->assertDontSee($hidden->name)
        ->assertSee('Read only · MCP managed');
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

test('every registered record type has an explorer', function (string $recordType) {
    $user = User::factory()->create();
    $milieu = Milieu::factory()->for($user, 'owner')->create();

    $this->actingAs($user)
        ->get(route('milieus.explore', [$milieu, $recordType]))
        ->assertSuccessful()
        ->assertSee('MCP managed');
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
        ->assertSee('canonical')
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
        ->assertSee('canonical')
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
        ->assertSee('canonical')
        ->assertDontSee('controlled_by')
        ->assertDontSee('Relationship #')
        ->assertDontSee("#{$relationship->id}");
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
        ->assertSee('class="fable-tag-list"', false)
        ->assertSee('class="fable-tag"', false)
        ->assertSee('human')
        ->assertSee('frontier')
        ->assertSee('Recent changes')
        ->assertDontSee('Save');
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
