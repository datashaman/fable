<?php

namespace App\Mcp\Tools;

use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;

#[Name('set-belief')]
#[Description('Create or update a holder’s stance toward a claim in a continuity.')]
class SetBeliefTool extends AggregateMutationTool
{
    protected function recordType(): string
    {
        return 'belief';
    }
}
