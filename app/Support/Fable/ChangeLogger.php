<?php

namespace App\Support\Fable;

use App\Models\ChangeEntry;
use App\Models\ChangeSet;
use App\Models\Milieu;
use App\Models\User;

class ChangeLogger
{
    /**
     * @param  array<string, mixed>|null  $before
     * @param  array<string, mixed>|null  $after
     * @param  array<string, mixed>  $metadata
     */
    public function record(
        Milieu $milieu,
        User $user,
        string $toolName,
        string $summary,
        string $recordType,
        ?int $recordId,
        string $action,
        ?array $before,
        ?array $after,
        array $metadata = [],
    ): ChangeSet {
        $changeSet = ChangeSet::query()->create([
            'milieu_id' => $milieu->id,
            'user_id' => $user->id,
            'tool_name' => $toolName,
            'summary' => $summary,
            'metadata' => $metadata,
        ]);

        ChangeEntry::query()->create([
            'change_set_id' => $changeSet->id,
            'record_type' => $recordType,
            'record_id' => $recordId,
            'action' => $action,
            'before' => $before,
            'after' => $after,
        ]);

        return $changeSet;
    }
}
