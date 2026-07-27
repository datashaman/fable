<?php

namespace App\Mcp\Tools;

use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;

#[Name('define-claim')]
#[Description('Define or update a proposition independently of who believes it.')]
class DefineClaimTool extends AggregateMutationTool
{
    protected function recordType(): string
    {
        return 'claim';
    }
}
