<?php

namespace App\Mcp\Tools;

use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;

#[Name('define-rule')]
#[Description('Define or update a milieu rule with applicability and explicit exceptions.')]
class DefineRuleTool extends AggregateMutationTool
{
    protected function recordType(): string
    {
        return 'rule';
    }
}
