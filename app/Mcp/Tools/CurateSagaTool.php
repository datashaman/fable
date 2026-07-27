<?php

namespace App\Mcp\Tools;

use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;

#[Name('curate-saga')]
#[Description('Create or update an ordered story collection and recurring conflicts.')]
class CurateSagaTool extends AggregateMutationTool
{
    protected function recordType(): string
    {
        return 'saga';
    }
}
