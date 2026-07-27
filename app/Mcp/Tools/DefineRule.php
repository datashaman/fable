<?php

namespace App\Mcp\Tools;

use Laravel\Mcp\Server\Attributes\Description;

#[Description('Define or update a milieu rule with applicability and explicit exceptions.')]
class DefineRule extends AggregateMutation
{
    protected function recordType(): string
    {
        return 'rule';
    }
}
