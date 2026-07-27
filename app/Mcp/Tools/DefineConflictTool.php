<?php

namespace App\Mcp\Tools;

use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;

#[Name('define-conflict')]
#[Description('Create or update dramatic conflict and synchronize its incompatible goals.')]
class DefineConflictTool extends AggregateMutationTool
{
    protected function recordType(): string
    {
        return 'conflict';
    }
}
