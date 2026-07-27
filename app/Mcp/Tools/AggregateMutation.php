<?php

namespace App\Mcp\Tools;

use App\Models\User;
use App\Support\Fable\DomainRegistry;
use App\Support\Fable\MutationService;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\JsonSchema\Types\Type;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Tool;

abstract class AggregateMutation extends Tool
{
    public function __construct(private DomainRegistry $registry) {}

    abstract protected function recordType(): string;

    public function handle(Request $request, MutationService $mutations): ResponseFactory
    {
        $validated = $request->validate([
            'id' => ['nullable', 'integer', 'min:1'],
            'expected_revision' => ['nullable', 'integer', 'min:1', 'required_with:id'],
            'data' => ['required', 'array'],
            'relations' => ['sometimes', 'array'],
        ]);

        /** @var User $user */
        $user = $request->user();

        return Response::structured($mutations->save($user, $this->recordType(), $validated, $this->name()));
    }

    /** @return array<string, Type> */
    public function schema(JsonSchema $schema): array
    {
        return [
            'id' => $schema->integer()->description('Existing record id. Omit to create.'),
            'expected_revision' => $schema->integer()->description('Required on update; must equal the current revision.'),
            'data' => $schema->object()->description('Partial aggregate fields. Read fable://schema for accepted fields.')->required(),
            'relations' => $this->relationsSchema($schema),
        ];
    }

    private function relationsSchema(JsonSchema $schema): Type
    {
        $properties = [];

        foreach ($this->registry->relationDefinitions($this->recordType()) as $name => $definition) {
            $itemProperties = [
                'id' => $schema->integer()->description("Related {$definition['record_type']} id.")->required(),
            ];

            foreach ($definition['pivot_fields'] as $field => $type) {
                $itemProperties[$field] = match ($type) {
                    'integer' => $schema->integer(),
                    default => $schema->string(),
                };
            }

            $item = $schema->anyOf([
                $schema->integer(),
                $schema->object($itemProperties)->withoutAdditionalProperties(),
            ]);

            $properties[$name] = $schema->array()
                ->items($item)
                ->description($definition['description']);
        }

        return $schema->object($properties)
            ->withoutAdditionalProperties()
            ->description('Optional full replacements for supported association lists. Items may be ids or objects with id and advertised pivot fields.');
    }
}
