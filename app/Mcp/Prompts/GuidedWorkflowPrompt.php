<?php

namespace App\Mcp\Prompts;

use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Prompt;
use Laravel\Mcp\Server\Prompts\Argument;

abstract class GuidedWorkflowPrompt extends Prompt
{
    abstract protected function workflow(): string;

    /** @return array<int, Response> */
    public function handle(Request $request): array
    {
        $context = $request->get('context', 'No additional context supplied.');
        $milieuId = $request->get('milieu_id', 'not selected yet');

        return [
            Response::text('Read fable://playbook and fable://schema before acting. Inspect existing state, preserve milieu and continuity boundaries, use expected_revision for updates, preview event effects, and validate continuity before finishing.')->asAssistant(),
            Response::text("Workflow: {$this->workflow()}\nMilieu: {$milieuId}\nContext: {$context}"),
        ];
    }

    /** @return array<int, Argument> */
    public function arguments(): array
    {
        return [
            new Argument(name: 'milieu_id', description: 'Existing milieu id, if this workflow operates in one.', required: false),
            new Argument(name: 'context', description: 'Creative intent, source material, constraints, or desired outcome.', required: true),
        ];
    }
}
