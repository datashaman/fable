<?php

namespace App\Mcp\Tools;

use Laravel\Mcp\Server\Attributes\Description;

#[Description('Create or update a bounded viewpoint and its beliefs and known records.')]
class SavePerspective extends AggregateMutation
{
    protected function recordType(): string
    {
        return 'perspective';
    }
}
