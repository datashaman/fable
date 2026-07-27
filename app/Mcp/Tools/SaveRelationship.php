<?php

namespace App\Mcp\Tools;

use Laravel\Mcp\Server\Attributes\Description;

#[Description('Create or update a typed relationship between entities in a continuity.')]
class SaveRelationship extends AggregateMutation
{
    protected function recordType(): string
    {
        return 'relationship';
    }
}
