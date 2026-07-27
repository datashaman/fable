<?php

namespace App\Mcp\Tools;

use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;

#[Name('compose-story')]
#[Description('Create or update a story and its ordered events and perspectives.')]
class ComposeStoryTool extends AggregateMutationTool
{
    protected function recordType(): string
    {
        return 'story';
    }
}
