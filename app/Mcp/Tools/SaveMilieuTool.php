<?php

namespace App\Mcp\Tools;

use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;

#[Name('save-milieu')]
#[Description('Create or revision-safely update a milieu. New milieus are owned by the authenticated user.')]
class SaveMilieuTool extends AggregateMutationTool
{
    protected function recordType(): string
    {
        return 'milieu';
    }
}
