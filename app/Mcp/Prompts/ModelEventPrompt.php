<?php

namespace App\Mcp\Prompts;

use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;

#[Name('model-event')]
#[Description('Guide modeling an event, participants, causal links, and state effects.')]
class ModelEventPrompt extends GuidedWorkflowPrompt
{
    protected function workflow(): string
    {
        return 'Resolve scope and existing entities, record the event and links, preview effects, apply only with explicit intent, then validate continuity.';
    }
}
