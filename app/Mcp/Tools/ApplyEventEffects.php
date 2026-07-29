<?php

namespace App\Mcp\Tools;

use App\Models\ChangeEntry;
use App\Models\ChangeSet;
use App\Models\Event;
use App\Models\User;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\JsonSchema\Types\Type;
use Illuminate\Support\Facades\DB;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsIdempotent;
use Throwable;

#[Description('Preview an event’s state changes by default, or explicitly apply them once.')]
#[IsIdempotent]
class ApplyEventEffects extends Tool
{
    use ReturnsMcpErrors;

    public function handle(Request $request): ResponseFactory
    {
        try {
            $validated = $request->validate([
                'event_id' => ['required', 'integer'],
                'apply' => ['nullable', 'boolean'],
            ]);
            /** @var User $user */
            $user = $request->user();
            $event = Event::query()->with('milieu')->findOrFail((int) $validated['event_id']);
            abort_unless($event->milieu->canEdit($user), 403);

            if (! ($validated['apply'] ?? false)) {
                return Response::structured([
                    'mode' => 'preview',
                    'event_id' => $event->id,
                    'already_applied' => $event->effects_applied_at !== null,
                    'effects' => $event->effects ?? [],
                ]);
            }

            return DB::transaction(function () use ($event, $user): ResponseFactory {
                $lockedEvent = Event::query()->lockForUpdate()->findOrFail($event->id);

                if ($lockedEvent->effects_applied_at !== null) {
                    return Response::structured(['ok' => true, 'event_id' => $lockedEvent->id, 'already_applied' => true]);
                }

                $before = $lockedEvent->attributesToArray();
                $lockedEvent->applyEffects();
                $lockedEvent->setAttribute('effects_applied_at', now());
                $lockedEvent->save();
                $changeSet = ChangeSet::query()->create([
                    'milieu_id' => $lockedEvent->milieu_id,
                    'user_id' => $user->id,
                    'tool_name' => $this->name(),
                    'summary' => "Applied effects for event #{$lockedEvent->id}",
                ]);
                ChangeEntry::query()->create([
                    'change_set_id' => $changeSet->id,
                    'record_type' => 'event',
                    'record_id' => $lockedEvent->id,
                    'action' => 'effects_applied',
                    'before' => $before,
                    'after' => $lockedEvent->fresh()->attributesToArray(),
                ]);

                return Response::structured(['ok' => true, 'event_id' => $lockedEvent->id, 'already_applied' => false, 'change_set_id' => $changeSet->id]);
            });
        } catch (Throwable $throwable) {
            return $this->mcpError($throwable);
        }
    }

    /** @return array<string, Type> */
    public function schema(JsonSchema $schema): array
    {
        return [
            'event_id' => $schema->integer()->required(),
            'apply' => $schema->boolean()->description('False/omitted previews. True applies effects once.')->default(false),
        ];
    }
}
