<?php

namespace App\Mcp\Tools;

use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;

#[Name('develop-scenario')]
#[Description('Create or update a hypothetical scenario and its participant roles.')]
class DevelopScenarioTool extends AggregateMutationTool
{
    protected function recordType(): string
    {
        return 'scenario';
    }
}
