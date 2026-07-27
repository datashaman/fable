<?php

namespace App\Mcp\Tools;

use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;

#[Name('record-event')]
#[Description('Record or update an event, including participants, locations, causes, and unapplied effects.')]
class RecordEventTool extends AggregateMutationTool
{
    protected function recordType(): string
    {
        return 'event';
    }
}
