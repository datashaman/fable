<?php

use App\Models\ChangeEntry;
use App\Models\ChangeSet;
use App\Models\Entity;
use App\Models\Milieu;
use App\Models\Scene;
use App\Models\Story;
use App\Models\User;

test('guests are redirected to the login page', function () {
    $response = $this->get(route('dashboard'));
    $response->assertRedirect(route('login'));
});

test('authenticated users can visit the dashboard', function () {
    $user = User::factory()->create();
    $milieu = Milieu::factory()->for($user, 'owner')->create();
    $this->actingAs($user);

    $response = $this->get(route('dashboard'));
    $response->assertOk()
        ->assertSee('Archive')
        ->assertSee("#{$milieu->id}")
        ->assertSeeTextInOrder(['milieu', 'canon', 'knowledge', 'possibility', 'narrative'])
        ->assertSee('Explore the worlds, histories, and possibilities gathered in each milieu.');
});

test('change history uses the recent changes label throughout the interface', function () {
    $user = User::factory()->create();
    $milieu = Milieu::factory()->for($user, 'owner')->create();

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertSuccessful()
        ->assertSee('Recent Changes');

    $this->get(route('milieus.show', $milieu))
        ->assertSuccessful()
        ->assertSeeTextInOrder(['Activity', 'Recent Changes'])
        ->assertSee('No changes have been recorded yet.');

    $this->get(route('milieus.activity', $milieu))
        ->assertSuccessful()
        ->assertSee('Recent Changes');
});

test('milieu navigation reads as a compact atlas hierarchy', function () {
    $user = User::factory()->create();
    $milieu = Milieu::factory()->for($user, 'owner')->create(['name' => 'The Imperial Frontier']);

    $this->actingAs($user)
        ->get(route('milieus.show', $milieu))
        ->assertSuccessful()
        ->assertSee('<h2 id="strata-title" class="fable-section-title">Layers</h2>', false)
        ->assertSeeTextInOrder([
            'Milieu shelf',
            'The Imperial Frontier',
            'Overview',
            'Milieu',
            'Continuities',
            'Ontology',
            'Canon',
            'Entities',
            'Relationships',
            'Events',
            'Rules',
            'Knowledge',
            'Possibility',
            'Narrative',
            'Recent Changes',
        ])
        ->assertSee('<h3>Milieu</h3>', false)
        ->assertSee('fable-milieu-switcher', false)
        ->assertSee('fable-nav-stratum-label', false);
});

test('milieu layers preserve padding at both outer edges', function () {
    $stylesheet = file_get_contents(resource_path('css/app.css'));

    expect($stylesheet)
        ->toContain(".fable-stratum:first-child {\n        padding-inline-start: 1rem;")
        ->toContain(".fable-stratum:last-child {\n        padding-inline-end: 1rem;");
});

test('the milieu shelf is searchable and exposes useful collection links', function () {
    $user = User::factory()->create();
    $frontier = Milieu::factory()->for($user, 'owner')->create([
        'name' => 'The Imperial Frontier',
        'genre' => 'science fantasy',
    ]);
    $hiddenBySearch = Milieu::factory()->for($user, 'owner')->create(['name' => 'The Quiet Archive']);
    $entity = Entity::factory()->for($frontier)->create();
    $changeSet = ChangeSet::factory()->for($frontier)->for($user)->create([
        'summary' => 'Updated the frontier archive.',
        'created_at' => now(),
    ]);
    ChangeEntry::factory()->for($changeSet)->create([
        'record_type' => 'entity',
        'record_id' => $entity->id,
        'action' => 'updated',
    ]);

    $response = $this->actingAs($user)
        ->get(route('dashboard', ['q' => 'science']))
        ->assertSuccessful()
        ->assertSee('The Imperial Frontier')
        ->assertSee('Search milieus')
        ->assertSee(route('milieus.explore', [$frontier, 'entity']), false)
        ->assertSee('fable-mobile-activity', false)
        ->assertSee('Updated the frontier archive.')
        ->assertSeeTextInOrder(['Entities', '1', 'Relationships', '0', 'Claims', '0', 'Stories', '0']);

    expect(substr_count($response->getContent(), 'fable-milieu-folio group'))->toBe(1);
});

test('milieu layers surface their latest activity and link change entries to records', function () {
    $user = User::factory()->create();
    $milieu = Milieu::factory()->for($user, 'owner')->create();
    $entity = Entity::factory()->for($milieu)->create(['name' => 'Aria Venn']);
    $changeSet = ChangeSet::factory()->for($milieu)->for($user)->create([
        'summary' => 'Updated Aria after the gate opened.',
        'created_at' => now(),
    ]);
    ChangeEntry::factory()->for($changeSet)->create([
        'record_type' => 'entity',
        'record_id' => $entity->id,
        'action' => 'updated',
    ]);

    $this->actingAs($user)
        ->get(route('milieus.show', $milieu))
        ->assertSuccessful()
        ->assertSee('fable-strata-band', false)
        ->assertSeeTextInOrder(['Canon', 'Entities', '1', '1 today', 'Updated Aria after the gate opened.'])
        ->assertSee(route('milieus.explore', [$milieu, 'entity', $entity]), false)
        ->assertSeeText('Aria Venn updated');
});

test('recent changes identify records by their human readable title', function () {
    $user = User::factory()->create();
    $milieu = Milieu::factory()->for($user, 'owner')->create();
    $story = Story::factory()->for($milieu)->create();
    $scene = Scene::factory()->for($story)->create(['name' => 'The Pulse Resolves']);
    $changeSet = ChangeSet::factory()->for($milieu)->for($user)->create([
        'tool_name' => 'save-scene',
        'summary' => "Updated scene #{$scene->id}",
    ]);
    ChangeEntry::factory()->for($changeSet)->create([
        'record_type' => 'scene',
        'record_id' => $scene->id,
        'action' => 'updated',
    ]);

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertSuccessful()
        ->assertSeeTextInOrder(['save-scene', $milieu->name, 'The Pulse Resolves updated']);
});
