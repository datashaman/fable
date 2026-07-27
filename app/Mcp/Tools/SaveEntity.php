<?php

namespace App\Mcp\Tools;

use Laravel\Mcp\Server\Attributes\Description;

#[Description('Create or update a typed person, place, organization, object, or concept.')]
class SaveEntity extends AggregateMutation
{
    protected function recordType(): string
    {
        return 'entity';
    }
}
