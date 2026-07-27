<?php

namespace App\Mcp\Tools;

use Laravel\Mcp\Server\Attributes\Description;

#[Description('Create or update when a belief is revealed to the audience in a scene.')]
class DiscloseBelief extends AggregateMutation
{
    protected function recordType(): string
    {
        return 'disclosure';
    }
}
