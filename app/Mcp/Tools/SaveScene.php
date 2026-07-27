<?php

namespace App\Mcp\Tools;

use Laravel\Mcp\Server\Attributes\Description;

#[Description('Create or update a scene and the world events it presents.')]
class SaveScene extends AggregateMutation
{
    protected function recordType(): string
    {
        return 'scene';
    }
}
