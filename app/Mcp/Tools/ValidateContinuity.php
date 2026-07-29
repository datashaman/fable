<?php

namespace App\Mcp\Tools;

use App\Models\Continuity;
use App\Models\User;
use App\Support\Fable\ContinuityValidator;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\JsonSchema\Types\Type;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;
use Throwable;

#[Description('Check a continuity for cross-milieu references and ontology-category mismatches.')]
#[IsReadOnly]
class ValidateContinuity extends Tool
{
    use ReturnsMcpErrors;

    public function handle(Request $request, ContinuityValidator $validator): ResponseFactory
    {
        try {
            $validated = $request->validate(['continuity_id' => ['required', 'integer']]);
            /** @var User $user */
            $user = $request->user();
            $continuity = Continuity::query()->with(['milieu', 'parent'])->findOrFail((int) $validated['continuity_id']);
            abort_unless($continuity->milieu->canView($user), 403);

            return Response::structured($validator->validate($continuity));
        } catch (Throwable $throwable) {
            return $this->mcpError($throwable);
        }
    }

    /** @return array<string, Type> */
    public function schema(JsonSchema $schema): array
    {
        return ['continuity_id' => $schema->integer()->required()];
    }
}
