<?php

namespace App\Mcp\Prompts;

use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;

#[Name('bootstrap-milieu')]
#[Description('Guide discovery and creation of a milieu, base continuity, ontology, and initial rules.')]
class BootstrapMilieuPrompt extends GuidedWorkflowPrompt
{
    protected function workflow(): string
    {
        return 'Establish the world scope, create the milieu and base continuity, reuse or define ontology types, then encode initial rules and validate.';
    }
}
