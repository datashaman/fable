<?php

namespace App\Mcp\Tools;

use Laravel\Mcp\Server\Attributes\Description;

#[Description('Create or update an ordered story collection and recurring conflicts.')]
class CurateSaga extends AggregateMutation
{
    protected function recordType(): string
    {
        return 'saga';
    }
}
