<?php

namespace App\Mcp\Tools;

use App\Models\ChangeEntry;
use App\Models\Milieu;
use App\Models\User;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\JsonSchema\Types\Type;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[Description('Read auditable changes for a milieu, optionally filtered to one record.')]
#[IsReadOnly]
class GetChangeHistory extends Tool
{
    public function handle(Request $request): ResponseFactory
    {
        $validated = $request->validate([
            'milieu_id' => ['required', 'integer'],
            'record_type' => ['nullable', 'string'],
            'record_id' => ['nullable', 'integer', 'required_with:record_type'],
            'limit' => ['nullable', 'integer', 'between:1,100'],
        ]);
        /** @var User $user */
        $user = $request->user();
        $milieu = Milieu::query()->findOrFail((int) $validated['milieu_id']);
        abort_unless($milieu->canView($user), 403);

        $entries = ChangeEntry::query()->with('changeSet.user:id,name,email')
            ->whereHas('changeSet', fn ($query) => $query->where('milieu_id', $milieu->id))
            ->when(isset($validated['record_type']), fn ($query) => $query->where('record_type', $validated['record_type'])->where('record_id', $validated['record_id']))
            ->latest('created_at')
            ->limit($validated['limit'] ?? 25)
            ->get();

        return Response::structured(['count' => $entries->count(), 'entries' => $entries->toArray()]);
    }

    /** @return array<string, Type> */
    public function schema(JsonSchema $schema): array
    {
        return [
            'milieu_id' => $schema->integer()->required(),
            'record_type' => $schema->string(),
            'record_id' => $schema->integer(),
            'limit' => $schema->integer()->default(25),
        ];
    }
}
