<?php

namespace App\Events;

use App\Models\ChangeEntry;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class StateChanged implements ShouldBroadcastNow, ShouldDispatchAfterCommit
{
    use Dispatchable, InteractsWithSockets;

    public function __construct(public int $changeEntryId) {}

    /** @return list<PrivateChannel> */
    public function broadcastOn(): array
    {
        $changeEntry = $this->changeEntry();
        $changeSet = $changeEntry->changeSet;
        $metadata = $changeSet->metadata ?? [];
        $affectedUserIds = Arr::wrap(Arr::get($metadata, 'affected_user_ids', []));
        $userIds = $changeSet->milieu->memberships()->pluck('user_id')
            ->push($changeSet->milieu->owner_id)
            ->merge($affectedUserIds)
            ->filter()
            ->map(fn (mixed $userId): int => (int) $userId)
            ->unique();

        $channels = $userIds
            ->map(fn (int $userId): PrivateChannel => new PrivateChannel("users.{$userId}.fable"))
            ->values()
            ->all();

        return array_values($channels);
    }

    public function broadcastAs(): string
    {
        return 'fable.state-changed';
    }

    /** @return array<string, mixed> */
    public function broadcastWith(): array
    {
        $changeEntry = $this->changeEntry();
        $changeSet = $changeEntry->changeSet;
        $metadata = $changeSet->metadata ?? [];
        $before = $changeEntry->before ?? [];
        $after = $changeEntry->after ?? [];
        $allFields = collect(array_keys($before))->merge(array_keys($after))->unique();
        $changedFields = $allFields
            ->filter(fn (string $field): bool => Arr::get($before, $field) !== Arr::get($after, $field))
            ->reject(fn (string $field): bool => in_array($field, ['created_at', 'updated_at'], true))
            ->values()
            ->all();

        return [
            'change_set_id' => $changeSet->id,
            'milieu_id' => $changeSet->milieu_id,
            'actor' => $changeSet->user === null ? null : [
                'id' => $changeSet->user->id,
                'name' => $changeSet->user->name,
            ],
            'tool_name' => $changeSet->tool_name,
            'summary' => $changeSet->summary,
            'record_type' => $changeEntry->record_type,
            'record_id' => $changeEntry->record_id,
            'action' => $changeEntry->action,
            'revision' => Arr::get($after, 'revision'),
            'changed_fields' => $changedFields,
            'changed_relations' => Arr::wrap(Arr::get($metadata, 'relations', [])),
            'occurred_at' => $changeEntry->created_at->toIso8601String(),
            'access_change' => $changeEntry->record_type === 'milieu_membership'
                ? Str::of($changeEntry->action)->replace('removed', 'revoked')->toString()
                : null,
        ];
    }

    private function changeEntry(): ChangeEntry
    {
        return ChangeEntry::query()
            ->with(['changeSet.user', 'changeSet.milieu'])
            ->findOrFail($this->changeEntryId);
    }
}
