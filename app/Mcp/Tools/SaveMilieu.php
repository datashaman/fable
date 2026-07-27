<?php

namespace App\Mcp\Tools;

use Laravel\Mcp\Server\Attributes\Description;

#[Description('Create or revision-safely update a milieu. New milieus are owned by the authenticated user.')]
class SaveMilieu extends AggregateMutation
{
    protected function recordType(): string
    {
        return 'milieu';
    }
}
