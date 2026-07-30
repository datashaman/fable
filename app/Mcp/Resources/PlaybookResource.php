<?php

namespace App\Mcp\Resources;

use Illuminate\Support\Facades\File;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\MimeType;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Attributes\Uri;
use Laravel\Mcp\Server\Resource;

#[Name('fable-playbook')]
#[Title('Fable Domain Playbook')]
#[Description('Required guide to Fable\'s narrative domain model, invariants, and workflow.')]
#[Uri('fable://playbook')]
#[MimeType('text/markdown')]
class PlaybookResource extends Resource
{
    /**
     * Handle the resource request.
     *
     * Serves docs/domain-playbook.md verbatim so agents (via this resource)
     * and humans (reading the repo) never see diverging domain guidance.
     */
    public function handle(Request $request): Response
    {
        return Response::text(File::get(base_path('docs/domain-playbook.md')));
    }
}
