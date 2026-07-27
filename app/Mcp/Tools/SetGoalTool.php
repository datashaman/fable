<?php

namespace App\Mcp\Tools;

use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;

#[Name('set-goal')]
#[Description('Create or update an entity goal in a continuity and optional scenario.')]
class SetGoalTool extends AggregateMutationTool
{
    protected function recordType(): string
    {
        return 'goal';
    }
}
