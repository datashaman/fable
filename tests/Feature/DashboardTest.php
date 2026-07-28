<?php

use App\Models\Milieu;
use App\Models\User;

test('guests are redirected to the login page', function () {
    $response = $this->get(route('dashboard'));
    $response->assertRedirect(route('login'));
});

test('authenticated users can visit the dashboard', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get(route('dashboard'));
    $response->assertOk()->assertSee('Archive');
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
        ->assertSee('Recent Changes');

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
        ->assertSee('fable-milieu-switcher', false)
        ->assertSee('fable-nav-stratum-label', false);
});
