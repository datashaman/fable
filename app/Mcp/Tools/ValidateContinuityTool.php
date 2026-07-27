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
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[Description('Check a continuity for cross-milieu references and ontology-category mismatches.')]
#[Name('validate-continuity')]
#[IsReadOnly]
class ValidateContinuityTool extends Tool
{
    public function handle(Request $request, ContinuityValidator $validator): ResponseFactory
    {
        $validated = $request->validate(['continuity_id' => ['required', 'integer']]);
        /** @var User $user */
        $user = $request->user();
        $continuity = Continuity::query()->with(['milieu', 'parent'])->findOrFail((int) $validated['continuity_id']);
        abort_unless($continuity->milieu->canView($user), 403);

        return Response::structured($validator->validate($continuity));
    }

    /** @return array<string, Type> */
    public function schema(JsonSchema $schema): array
    {
        return ['continuity_id' => $schema->integer()->required()];
    }
}
