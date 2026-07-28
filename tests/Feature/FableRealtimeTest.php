<?php

use App\Enums\MilieuRole;
use App\Events\StateChanged;
use App\Models\ChangeEntry;
use App\Models\ChangeSet;
use App\Models\Milieu;
use App\Models\MilieuMembership;
use App\Models\User;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\DevCommands;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Event;
use Livewire\Livewire;

test('state changes broadcast synchronously after commit', function () {
    $event = new StateChanged(1);

    expect($event)->toBeInstanceOf(ShouldBroadcastNow::class)
        ->and($event)->toBeInstanceOf(ShouldDispatchAfterCommit::class)
        ->and($event->broadcastAs())->toBe('fable.state-changed');
});

test('the dev server starts reverb with the application hostname', function () {
    $hostname = parse_url((string) config('app.url'), PHP_URL_HOST);
    $reverb = collect(DevCommands::commands())->firstWhere('name', 'reverb');

    expect($reverb)
        ->not->toBeNull()
        ->and($reverb['command'])->toBe("php artisan reverb:start --hostname={$hostname}");
});

test('creating an audit entry dispatches a realtime state event', function () {
    $changeSet = ChangeSet::factory()->create();
    Event::fake([StateChanged::class]);

    $entry = ChangeEntry::factory()->for($changeSet)->create();

    Event::assertDispatched(
        StateChanged::class,
        fn (StateChanged $event): bool => $event->changeEntryId === $entry->id,
    );
});

test('broadcast audience includes owner current members and explicitly affected users', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $removedMember = User::factory()->create();
    $milieu = Milieu::factory()->for($owner, 'owner')->create();
    MilieuMembership::factory()->for($milieu)->for($member)->create(['role' => MilieuRole::Editor]);
    $changeSet = ChangeSet::factory()->for($milieu)->create([
        'metadata' => ['affected_user_ids' => [$removedMember->id]],
    ]);
    Event::fake([StateChanged::class]);
    $entry = ChangeEntry::factory()->for($changeSet)->create();

    $channels = collect((new StateChanged($entry->id))->broadcastOn());

    expect($channels)->each->toBeInstanceOf(PrivateChannel::class)
        ->and($channels->pluck('name')->sort()->values()->all())->toBe([
            "private-users.{$owner->id}.fable",
            "private-users.{$member->id}.fable",
            "private-users.{$removedMember->id}.fable",
        ]);
});

test('broadcast payload is compact and reports only changed field names', function () {
    $changeSet = ChangeSet::factory()->create(['tool_name' => 'save-entity']);
    Event::fake([StateChanged::class]);
    $entry = ChangeEntry::factory()->for($changeSet)->create([
        'record_type' => 'entity',
        'record_id' => 42,
        'before' => ['revision' => 1, 'name' => 'Old name', 'description' => 'Stable'],
        'after' => ['revision' => 2, 'name' => 'New name', 'description' => 'Stable'],
    ]);

    $payload = (new StateChanged($entry->id))->broadcastWith();

    expect($payload['changed_fields'])->toBe(['revision', 'name'])
        ->and($payload['revision'])->toBe(2)
        ->and($payload)->not->toHaveKeys(['before', 'after']);
});

test('a revoked member is redirected away from an open milieu', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $milieu = Milieu::factory()->for($owner, 'owner')->create();
    $membership = MilieuMembership::factory()->for($milieu)->for($member)->create(['role' => MilieuRole::Viewer]);

    $component = Livewire::actingAs($member)->test('pages::milieu-overview', ['milieu' => $milieu]);
    $membership->delete();

    $component->call('handleStateChanged', [
        'milieu_id' => $milieu->id,
        'record_type' => 'milieu_membership',
        'record_id' => $membership->id,
        'changed_fields' => [],
        'changed_relations' => [],
        'access_change' => 'revoked',
    ])->assertRedirect(route('dashboard'));
});

test('users may authorize only their own private realtime channel', function () {
    config()->set('broadcasting.default', 'reverb');
    Broadcast::forgetDrivers();
    require base_path('routes/channels.php');
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $payload = [
        'socket_id' => '1234.5678',
        'channel_name' => "private-users.{$user->id}.fable",
    ];

    $this->actingAs($user)->post('/broadcasting/auth', $payload)->assertSuccessful();
    $this->actingAs($otherUser)->post('/broadcasting/auth', $payload)->assertForbidden();
});
