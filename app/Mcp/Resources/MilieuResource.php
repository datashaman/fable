<?php

namespace App\Mcp\Resources;

use App\Models\Milieu;
use App\Models\User;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\MimeType;
use Laravel\Mcp\Server\Contracts\HasUriTemplate;
use Laravel\Mcp\Server\Resource;
use Laravel\Mcp\Support\UriTemplate;

#[Description('Compact overview of a milieu, its continuities, collaborators, and aggregate counts.')]
#[MimeType('application/json')]
class MilieuResource extends Resource implements HasUriTemplate
{
    public function uriTemplate(): UriTemplate
    {
        return new UriTemplate('fable://milieus/{milieuId}');
    }

    public function handle(Request $request): Response
    {
        /** @var User $user */
        $user = $request->user();
        $milieu = Milieu::query()->with(['continuities:id,milieu_id,name,canonical_status,revision', 'memberships.user:id,name,email'])
            ->withCount(['entities', 'relationships', 'events', 'rules', 'claims', 'beliefs', 'perspectives', 'scenarios', 'goals', 'conflicts', 'stories', 'sagas'])
            ->findOrFail((int) $request->get('milieuId'));
        abort_unless($milieu->canView($user), 403);

        return Response::json([
            'milieu' => $milieu->attributesToArray(),
            'role' => $milieu->roleFor($user),
            'continuities' => $milieu->continuities,
            'collaborators' => $milieu->memberships->map(fn ($membership) => [
                'user_id' => $membership->user_id,
                'name' => $membership->user->name,
                'email' => $membership->user->email,
                'role' => $membership->role->value,
            ]),
        ]);
    }
}
