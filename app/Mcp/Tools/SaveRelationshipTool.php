<?php

namespace App\Mcp\Tools;

use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;

#[Name('save-relationship')]
#[Description('Create or update a typed relationship between entities in a continuity.')]
class SaveRelationshipTool extends AggregateMutationTool
{
    protected function recordType(): string
    {
        return 'relationship';
    }
}
