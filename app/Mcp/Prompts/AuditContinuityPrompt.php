<?php

namespace App\Mcp\Prompts;

use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;

#[Name('audit-continuity')]
#[Description('Guide a read-first continuity audit and targeted repair plan.')]
class AuditContinuityPrompt extends GuidedWorkflowPrompt
{
    protected function workflow(): string
    {
        return 'Inspect the continuity and relevant records, run validate-continuity, explain each issue, propose minimal repairs, and only mutate with approval.';
    }
}
