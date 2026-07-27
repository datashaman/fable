<?php

namespace App\Mcp\Tools;

use Laravel\Mcp\Server\Attributes\Description;

#[Description('Create or update a story and its ordered events and perspectives.')]
class ComposeStory extends AggregateMutation
{
    protected function recordType(): string
    {
        return 'story';
    }
}
