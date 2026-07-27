<?php

namespace App\Mcp\Tools;

use Laravel\Mcp\Server\Attributes\Description;

#[Description('Define or update a proposition independently of who believes it.')]
class DefineClaim extends AggregateMutation
{
    protected function recordType(): string
    {
        return 'claim';
    }
}
