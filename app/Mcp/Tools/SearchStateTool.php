<?php

namespace App\Mcp\Tools;

use App\Models\Milieu;
use App\Models\User;
use App\Support\Fable\DomainRegistry;
use App\Support\Fable\StateSearch;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\JsonSchema\Types\Type;
use Illuminate\Validation\Rule;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[Description('Lexically search accessible Fable state by record type, returning compact revisioned records.')]
#[Name('search-state')]
#[IsReadOnly]
class SearchStateTool extends Tool
{
    public function handle(Request $request, StateSearch $search, DomainRegistry $registry): ResponseFactory
    {
        $validated = $request->validate([
            'milieu_id' => ['required', 'integer'],
            'query' => ['required', 'string', 'max:200'],
            'record_type' => ['nullable', Rule::in($registry->types())],
            'limit' => ['nullable', 'integer', 'between:1,50'],
        ]);
        /** @var User $user */
        $user = $request->user();
        $milieu = Milieu::query()->findOrFail((int) $validated['milieu_id']);
        abort_unless($milieu->canView($user), 403);

        return Response::structured($search->search($milieu, $validated['query'], $validated['record_type'] ?? null, $validated['limit'] ?? 20));
    }

    /** @return array<string, Type> */
    public function schema(JsonSchema $schema): array
    {
        return [
            'milieu_id' => $schema->integer()->required(),
            'query' => $schema->string()->required(),
            'record_type' => $schema->string()->description('Optional type from fable://schema.'),
            'limit' => $schema->integer()->default(20),
        ];
    }
}
