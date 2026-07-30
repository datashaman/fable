<?php

namespace App\Mcp\Resources;

use App\Models\Milieu;
use App\Models\User;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\MimeType;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Uri;
use Laravel\Mcp\Server\Resource;

#[Name('fable-workspace')]
#[Description('The authenticated user’s accessible milieus and role in each.')]
#[Uri('fable://workspace')]
#[MimeType('application/json')]
class WorkspaceResource extends Resource
{
    public function handle(Request $request): Response
    {
        /** @var User $user */
        $user = $request->user();
        $milieus = Milieu::query()
            ->where('owner_id', $user->id)
            ->orWhereHas('memberships', fn ($query) => $query->where('user_id', $user->id))
            ->with(['memberships' => fn ($query) => $query->where('user_id', $user->id)])
            ->orderBy('name')
            ->get()
            ->map(fn (Milieu $milieu): array => [
                'id' => $milieu->id,
                'name' => $milieu->name,
                'status' => $milieu->status->value,
                'revision' => $milieu->revision,
                'role' => $milieu->roleFor($user),
                'resource' => "fable://milieus/{$milieu->id}",
            ]);

        return Response::json(['user' => ['id' => $user->id, 'email' => $user->email], 'milieus' => $milieus]);
    }
}
