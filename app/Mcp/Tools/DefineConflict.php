<?php

namespace App\Mcp\Tools;

use Laravel\Mcp\Server\Attributes\Description;

#[Description('Create or update dramatic conflict and synchronize its incompatible goals.')]
class DefineConflict extends AggregateMutation
{
    protected function recordType(): string
    {
        return 'conflict';
    }
}
