<?php

namespace App\Mcp\Tools;

use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;

#[Name('save-perspective')]
#[Description('Create or update a bounded viewpoint and its beliefs and known records.')]
class SavePerspectiveTool extends AggregateMutationTool
{
    protected function recordType(): string
    {
        return 'perspective';
    }
}
