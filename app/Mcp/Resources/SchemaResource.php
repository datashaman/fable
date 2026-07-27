<?php

namespace App\Mcp\Resources;

use App\Support\Fable\DomainRegistry;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\MimeType;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Uri;
use Laravel\Mcp\Server\Resource;

#[Name('fable-schema')]
#[Description('Machine-readable Fable record types, writable fields, roles, and mutation contract.')]
#[Uri('fable://schema')]
#[MimeType('application/json')]
class SchemaResource extends Resource
{
    public function handle(Request $request, DomainRegistry $registry): Response
    {
        return Response::json($registry->schema());
    }
}
