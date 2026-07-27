<?php

namespace App\Mcp\Tools;

use Laravel\Mcp\Server\Attributes\Description;

#[Description('Record or update an event, including participants, locations, causes, and unapplied effects.')]
class RecordEvent extends AggregateMutation
{
    protected function recordType(): string
    {
        return 'event';
    }
}
