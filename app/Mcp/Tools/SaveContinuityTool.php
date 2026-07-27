<?php

namespace App\Mcp\Tools;

use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;

#[Name('save-continuity')]
#[Description('Create or update a continuity or branch within one milieu.')]
class SaveContinuityTool extends AggregateMutationTool
{
    protected function recordType(): string
    {
        return 'continuity';
    }
}
