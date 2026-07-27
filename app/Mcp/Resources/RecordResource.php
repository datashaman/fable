<?php

namespace App\Mcp\Resources;

use App\Models\User;
use App\Support\Fable\DomainRegistry;
use App\Support\Fable\StatePresenter;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\MimeType;
use Laravel\Mcp\Server\Contracts\HasUriTemplate;
use Laravel\Mcp\Server\Resource;
use Laravel\Mcp\Support\UriTemplate;

#[Description('A single revisioned Fable record by type and identifier.')]
#[MimeType('application/json')]
class RecordResource extends Resource implements HasUriTemplate
{
    public function uriTemplate(): UriTemplate
    {
        return new UriTemplate('fable://records/{recordType}/{recordId}');
    }

    public function handle(Request $request, DomainRegistry $registry, StatePresenter $presenter): Response
    {
        /** @var User $user */
        $user = $request->user();
        $type = (string) $request->get('recordType');
        $class = $registry->modelClass($type);
        $record = $class::query()->findOrFail((int) $request->get('recordId'));
        abort_unless($registry->milieuFor($record)->canView($user), 403);

        return Response::json($presenter->record($type, $record));
    }
}
