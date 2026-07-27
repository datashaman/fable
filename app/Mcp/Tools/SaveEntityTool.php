<?php

namespace App\Mcp\Tools;

use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;

#[Name('save-entity')]
#[Description('Create or update a typed person, place, organization, object, or concept.')]
class SaveEntityTool extends AggregateMutationTool
{
    protected function recordType(): string
    {
        return 'entity';
    }
}
