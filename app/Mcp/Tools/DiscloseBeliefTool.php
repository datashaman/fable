<?php

namespace App\Mcp\Tools;

use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;

#[Name('disclose-belief')]
#[Description('Create or update when a belief is revealed to the audience in a scene.')]
class DiscloseBeliefTool extends AggregateMutationTool
{
    protected function recordType(): string
    {
        return 'disclosure';
    }
}
