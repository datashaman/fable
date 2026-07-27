<?php

namespace App\Mcp\Tools;

use Laravel\Mcp\Server\Attributes\Description;

#[Description('Create or update a continuity or branch within one milieu.')]
class SaveContinuity extends AggregateMutation
{
    protected function recordType(): string
    {
        return 'continuity';
    }
}
