<?php

namespace App\Mcp\Tools;

use Laravel\Mcp\Server\Attributes\Description;

#[Description('Create or update a hypothetical scenario and its participant roles.')]
class DevelopScenario extends AggregateMutation
{
    protected function recordType(): string
    {
        return 'scenario';
    }
}
