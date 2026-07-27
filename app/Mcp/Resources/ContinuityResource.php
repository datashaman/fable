<?php

namespace App\Mcp\Resources;

use App\Models\Continuity;
use App\Models\User;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\MimeType;
use Laravel\Mcp\Server\Contracts\HasUriTemplate;
use Laravel\Mcp\Server\Resource;
use Laravel\Mcp\Support\UriTemplate;

#[Description('Compact continuity state with branch information and aggregate counts.')]
#[MimeType('application/json')]
class ContinuityResource extends Resource implements HasUriTemplate
{
    public function uriTemplate(): UriTemplate
    {
        return new UriTemplate('fable://continuities/{continuityId}');
    }

    public function handle(Request $request): Response
    {
        /** @var User $user */
        $user = $request->user();
        $continuity = Continuity::query()->with(['milieu', 'parent:id,name', 'branches:id,parent_id,name'])
            ->withCount(['events', 'relationships', 'beliefs', 'perspectives', 'goals', 'conflicts', 'stories', 'sagas'])
            ->findOrFail((int) $request->get('continuityId'));
        abort_unless($continuity->milieu->canView($user), 403);

        return Response::json($continuity->toArray());
    }
}
