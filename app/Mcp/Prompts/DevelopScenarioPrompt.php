<?php

namespace App\Mcp\Prompts;

use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;

#[Name('develop-scenario')]
#[Description('Guide a hypothetical setup through participants, goals, conflicts, and outcomes.')]
class DevelopScenarioPrompt extends GuidedWorkflowPrompt
{
    protected function workflow(): string
    {
        return 'Develop the scenario premise and conditions, add participant roles, define goals and incompatible-goal conflicts, then evaluate outcomes.';
    }
}
