<?php

namespace App\Mcp\Tools;

use Laravel\Mcp\Server\Attributes\Description;

#[Description('Create or update an entity goal in a continuity and optional scenario.')]
class SetGoal extends AggregateMutation
{
    protected function recordType(): string
    {
        return 'goal';
    }
}
