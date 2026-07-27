<?php

namespace App\Mcp\Tools;

use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;

#[Name('save-scene')]
#[Description('Create or update a scene and the world events it presents.')]
class SaveSceneTool extends AggregateMutationTool
{
    protected function recordType(): string
    {
        return 'scene';
    }
}
