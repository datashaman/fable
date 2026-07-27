<?php

namespace App\Mcp\Tools;

use Laravel\Mcp\Server\Attributes\Description;

#[Description('Create or update a holder’s stance toward a claim in a continuity.')]
class SetBelief extends AggregateMutation
{
    protected function recordType(): string
    {
        return 'belief';
    }
}
