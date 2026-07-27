<?php

namespace App\Mcp\Prompts;

use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;

#[Name('compose-story')]
#[Description('Guide narrative selection, event ordering, scenes, perspectives, and disclosures.')]
class ComposeStoryPrompt extends GuidedWorkflowPrompt
{
    protected function workflow(): string
    {
        return 'Select existing world events, order them for presentation, choose narration and perspectives, structure scenes, then schedule audience disclosures.';
    }
}
