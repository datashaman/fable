<?php

namespace App\Mcp\Tools;

use App\Models\Milieu;
use App\Models\MilieuMembership;
use App\Models\User;
use App\Support\Fable\ChangeLogger;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\JsonSchema\Types\Type;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;
use Throwable;

#[Description('Owner-only: add, change, or remove an existing user’s editor/viewer access by email.')]
class ManageCollaborator extends Tool
{
    use ReturnsMcpErrors;

    public function handle(Request $request, ChangeLogger $changeLogger): ResponseFactory
    {
        try {
            $validated = $request->validate([
                'milieu_id' => ['required', 'integer'],
                'email' => ['required', 'email'],
                'role' => ['required', Rule::in(['editor', 'viewer', 'remove'])],
            ]);
            /** @var User $actor */
            $actor = $request->user();
            $milieu = Milieu::query()->findOrFail((int) $validated['milieu_id']);
            abort_unless($milieu->owner_id === $actor->id, 403, 'Only the milieu owner can manage collaborators.');
            $user = User::query()->where('email', $validated['email'])->firstOrFail();
            abort_if($user->id === $actor->id, 422, 'The owner cannot be added as a collaborator.');

            return DB::transaction(function () use ($actor, $milieu, $user, $validated, $changeLogger): ResponseFactory {
                $membership = MilieuMembership::query()->whereBelongsTo($milieu)->whereBelongsTo($user)->first();
                $before = $membership?->attributesToArray();

                if ($validated['role'] === 'remove') {
                    $membership?->delete();
                    $after = null;
                } else {
                    $membership = MilieuMembership::query()->updateOrCreate(
                        ['milieu_id' => $milieu->id, 'user_id' => $user->id],
                        ['role' => $validated['role']],
                    );
                    $after = $membership->attributesToArray();
                }

                $changeSet = $changeLogger->record(
                    milieu: $milieu,
                    user: $actor,
                    toolName: $this->name(),
                    summary: "Set {$user->email} collaborator access to {$validated['role']}",
                    recordType: 'milieu_membership',
                    recordId: $membership?->id,
                    action: $validated['role'] === 'remove' ? 'removed' : ($before === null ? 'created' : 'updated'),
                    before: $before,
                    after: $after,
                    metadata: ['affected_user_ids' => [$user->id]],
                );

                return Response::structured(['ok' => true, 'change_set_id' => $changeSet->id, 'membership' => $after]);
            });
        } catch (Throwable $throwable) {
            return $this->mcpError($throwable);
        }
    }

    /** @return array<string, Type> */
    public function schema(JsonSchema $schema): array
    {
        return [
            'milieu_id' => $schema->integer()->required(),
            'email' => $schema->string()->description('Email of an existing Fable user.')->required(),
            'role' => $schema->string()->enum(['editor', 'viewer', 'remove'])->required(),
        ];
    }
}
