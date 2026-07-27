<?php

namespace App\Mcp\Prompts;

use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;

#[Name('model-knowledge')]
#[Description('Guide separating claims, beliefs, viewpoints, and audience disclosure.')]
class ModelKnowledgePrompt extends GuidedWorkflowPrompt
{
    protected function workflow(): string
    {
        return 'Reuse or define claims, set each holder’s belief separately, assemble perspectives, and schedule disclosures without changing world facts.';
    }
}
