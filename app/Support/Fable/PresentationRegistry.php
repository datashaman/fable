<?php

namespace App\Support\Fable;

use App\Models\Belief;
use App\Models\ChangeEntry;
use App\Models\Claim;
use App\Models\Event;
use App\Models\Milieu;
use App\Models\Relationship;
use App\Models\Rule;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use InvalidArgumentException;

class PresentationRegistry
{
    /** @var array<string, string> */
    private array $changeEntryTitles = [];

    /** @var array<string, array{label: string, plural: string, stratum: string, title: string, summary: list<string>, status?: string, continuity?: bool}> */
    private const DEFINITIONS = [
        'milieu' => ['label' => 'Milieu', 'plural' => 'Milieus', 'stratum' => 'world', 'title' => 'name', 'summary' => ['genre', 'description'], 'status' => 'status'],
        'continuity' => ['label' => 'Continuity', 'plural' => 'Continuities', 'stratum' => 'world', 'title' => 'name', 'summary' => ['canonical_status', 'description'], 'status' => 'canonical_status'],
        'ontology_type' => ['label' => 'Ontology type', 'plural' => 'Ontology', 'stratum' => 'world', 'title' => 'name', 'summary' => ['category', 'key']],
        'entity' => ['label' => 'Entity', 'plural' => 'Entities', 'stratum' => 'canon', 'title' => 'name', 'summary' => ['canonical_status', 'description'], 'status' => 'canonical_status'],
        'relationship' => ['label' => 'Relationship', 'plural' => 'Relationships', 'stratum' => 'canon', 'title' => 'description', 'summary' => ['description'], 'status' => 'canonical_status', 'continuity' => true],
        'event' => ['label' => 'Event', 'plural' => 'Events', 'stratum' => 'canon', 'title' => 'name', 'summary' => ['start_time', 'description'], 'status' => 'canonical_status', 'continuity' => true],
        'rule' => ['label' => 'Rule', 'plural' => 'Rules', 'stratum' => 'canon', 'title' => 'name', 'summary' => ['valid_from', 'description'], 'status' => 'canonical_status'],
        'claim' => ['label' => 'Claim', 'plural' => 'Claims', 'stratum' => 'knowledge', 'title' => 'predicate', 'summary' => ['description']],
        'belief' => ['label' => 'Belief', 'plural' => 'Beliefs', 'stratum' => 'knowledge', 'title' => 'description', 'summary' => ['acquired_at', 'description'], 'status' => 'canonical_status', 'continuity' => true],
        'perspective' => ['label' => 'Perspective', 'plural' => 'Perspectives', 'stratum' => 'knowledge', 'title' => 'name', 'summary' => ['temporal_position', 'description'], 'continuity' => true],
        'scenario' => ['label' => 'Scenario', 'plural' => 'Scenarios', 'stratum' => 'possibility', 'title' => 'name', 'summary' => ['status', 'premise'], 'status' => 'status'],
        'goal' => ['label' => 'Goal', 'plural' => 'Goals', 'stratum' => 'possibility', 'title' => 'objective', 'summary' => ['status', 'motivation'], 'status' => 'status', 'continuity' => true],
        'conflict' => ['label' => 'Conflict', 'plural' => 'Conflicts', 'stratum' => 'possibility', 'title' => 'description', 'summary' => ['status'], 'status' => 'status', 'continuity' => true],
        'story' => ['label' => 'Story', 'plural' => 'Stories', 'stratum' => 'narrative', 'title' => 'title', 'summary' => ['form', 'canonical_status'], 'status' => 'canonical_status', 'continuity' => true],
        'scene' => ['label' => 'Scene', 'plural' => 'Scenes', 'stratum' => 'narrative', 'title' => 'name', 'summary' => ['sequence', 'description']],
        'disclosure' => ['label' => 'Disclosure', 'plural' => 'Disclosures', 'stratum' => 'narrative', 'title' => 'description', 'summary' => [], 'continuity' => true],
        'saga' => ['label' => 'Saga', 'plural' => 'Sagas', 'stratum' => 'narrative', 'title' => 'title', 'summary' => ['kind', 'canonical_status'], 'status' => 'canonical_status', 'continuity' => true],
    ];

    /** @var array<string, list<string>> */
    private const STRATA = [
        'world' => ['continuity', 'ontology_type'],
        'canon' => ['entity', 'relationship', 'event', 'rule'],
        'knowledge' => ['claim', 'belief', 'perspective'],
        'possibility' => ['scenario', 'goal', 'conflict'],
        'narrative' => ['story', 'scene', 'disclosure', 'saga'],
    ];

    public function __construct(private DomainRegistry $domainRegistry) {}

    /** @return array<string, array{label: string, plural: string, stratum: string, title: string, summary: list<string>, status?: string, continuity?: bool}> */
    public function definitions(): array
    {
        return self::DEFINITIONS;
    }

    /** @return array{label: string, plural: string, stratum: string, title: string, summary: list<string>, status?: string, continuity?: bool} */
    public function definition(string $type): array
    {
        return self::DEFINITIONS[$type] ?? throw new InvalidArgumentException("Unknown presentation type [{$type}].");
    }

    /** @return array<string, list<string>> */
    public function strata(): array
    {
        return self::STRATA;
    }

    /** @return Builder<Model> */
    public function query(Milieu $milieu, string $type): Builder
    {
        $class = $this->domainRegistry->modelClass($type);
        $query = $class::query();

        if ($type === 'relationship') {
            $query->with(['source:id,name', 'type:id,name', 'target:id,name']);
        }

        if ($type === 'entity') {
            $query->with('type:id,name');
        }

        if ($type === 'claim') {
            $query->with(['subject:id,name', 'object:id,name']);
        }

        if ($type === 'belief') {
            $query->with([
                'holder:id,name',
                'claim:id,subject_id,predicate,object_id,object_value',
                'claim.subject:id,name',
                'claim.object:id,name',
            ]);
        }

        if ($type === 'story') {
            $query->with('scenario:id,name');
        }

        if ($type === 'milieu') {
            return $query->whereKey($milieu->getKey());
        }

        if ($type === 'scene') {
            return $query->whereHas('story', fn (Builder $query): Builder => $query->whereBelongsTo($milieu));
        }

        return $query->whereBelongsTo($milieu);
    }

    /** @return Builder<Model> */
    public function searchableQuery(
        Milieu $milieu,
        string $type,
        string $search = '',
        ?int $continuityId = null,
        ?int $ontologyTypeId = null,
        ?string $status = null,
        string $sort = 'recent',
    ): Builder {
        $query = $this->query($milieu, $type);
        $search = Str::squish($search);

        if ($type === 'ontology_type') {
            $query->withCount(['entities', 'relationships', 'events', 'rules']);
        }

        if ($search !== '') {
            $query->whereAny($this->domainRegistry->searchFields($type), 'like', "%{$search}%");
        }

        if ($continuityId !== null && ($this->definition($type)['continuity'] ?? false)) {
            $query->where('continuity_id', $continuityId);
        }

        if ($type === 'entity' && $ontologyTypeId !== null) {
            $query->where('type_id', $ontologyTypeId);
        }

        $statusField = $this->definition($type)['status'] ?? null;

        if ($status !== null && $statusField !== null) {
            $query->where($statusField, $status);
        }

        if ($type === 'ontology_type') {
            return $query->orderBy('category')->orderBy('name');
        }

        if ($sort === 'alphabetical') {
            return $query->orderBy($this->definition($type)['title'])->orderBy('id');
        }

        return $query->latest('updated_at')->latest('id');
    }

    public function title(string $type, Model $record): string
    {
        if ($type === 'claim' && $record instanceof Claim) {
            return $this->claimStatement($record);
        }

        if ($type === 'belief' && $record instanceof Belief) {
            return "{$record->holder->name} {$record->stance->value}: {$this->claimStatement($record->claim)}";
        }

        if ($type === 'relationship' && $record instanceof Relationship) {
            $arrow = $record->symmetric ? '↔' : '→';

            return "{$record->source->name} - {$record->type->name} {$arrow} {$record->target->name}";
        }

        $field = $this->definition($type)['title'];
        $title = $record->getAttribute($field);

        return filled($title) ? Str::limit((string) $title, 90) : $this->definition($type)['label'].' #'.$record->getKey();
    }

    public function status(string $type, Model $record): ?string
    {
        $field = Arr::get($this->definition($type), 'status');
        $value = $field === null ? null : $record->getAttribute($field);

        return $value instanceof \BackedEnum ? (string) $value->value : ($value === null ? null : (string) $value);
    }

    public function changeEntryTitle(Milieu $milieu, ChangeEntry $entry): string
    {
        if ($entry->record_type === 'milieu') {
            return $milieu->name;
        }

        $definition = self::DEFINITIONS[$entry->record_type] ?? null;

        if ($definition === null || $entry->record_id === null) {
            return str($entry->record_type)->headline()->toString();
        }

        $cacheKey = "{$milieu->id}:{$entry->record_type}:{$entry->record_id}";

        if (isset($this->changeEntryTitles[$cacheKey])) {
            return $this->changeEntryTitles[$cacheKey];
        }

        $record = $this->query($milieu, $entry->record_type)->find($entry->record_id);

        if ($record !== null) {
            return $this->changeEntryTitles[$cacheKey] = $this->title($entry->record_type, $record);
        }

        $snapshot = $entry->after ?? $entry->before ?? [];
        $snapshotTitle = Arr::get($snapshot, $definition['title']);

        return $this->changeEntryTitles[$cacheKey] = filled($snapshotTitle)
            ? Str::limit((string) $snapshotTitle, 90)
            : $definition['label'];
    }

    /** @return list<array{field: string, label: string, value: mixed}> */
    public function summary(string $type, Model $record): array
    {
        if ($type === 'event' && $record instanceof Event) {
            return array_values(array_filter([
                [
                    'field' => 'temporal_range',
                    'label' => 'When',
                    'value' => $this->temporalRange($record->start_time, $record->end_time),
                ],
                [
                    'field' => 'description',
                    'label' => 'Description',
                    'value' => $record->description,
                ],
            ], fn (array $item): bool => filled($item['value'])));
        }

        if ($type === 'rule' && $record instanceof Rule) {
            return array_values(array_filter([
                [
                    'field' => 'temporal_range',
                    'label' => 'Validity',
                    'value' => $this->temporalRange($record->valid_from, $record->valid_until),
                ],
                [
                    'field' => 'description',
                    'label' => 'Description',
                    'value' => $record->description,
                ],
            ], fn (array $item): bool => filled($item['value'])));
        }

        if ($type === 'belief' && $record instanceof Belief) {
            return array_values(array_filter([
                [
                    'field' => 'temporal_range',
                    'label' => 'Held',
                    'value' => filled($record->valid_until)
                        ? $this->temporalRange($record->acquired_at, $record->valid_until)
                        : $record->acquired_at,
                ],
                [
                    'field' => 'description',
                    'label' => 'Description',
                    'value' => $record->description,
                ],
            ], fn (array $item): bool => filled($item['value'])));
        }

        $definition = $this->definition($type);
        $statusField = $definition['status'] ?? null;

        return array_values(collect($definition['summary'])
            ->reject(fn (string $field): bool => $field === $statusField)
            ->map(function (string $field) use ($record): array {
                $value = $record->getAttribute($field);

                return [
                    'field' => $field,
                    'label' => Str::headline($field),
                    'value' => $value instanceof \BackedEnum ? $value->value : $value,
                ];
            })
            ->filter(fn (array $item): bool => filled($item['value']))
            ->values()
            ->all());
    }

    private function temporalRange(?string $start, ?string $end): ?string
    {
        if (filled($start) && filled($end)) {
            return $start === $end ? $start : "{$start} → {$end}";
        }

        if (filled($start)) {
            return "From {$start}";
        }

        return filled($end) ? "Until {$end}" : null;
    }

    private function claimStatement(Claim $claim): string
    {
        $predicate = Str::of($claim->predicate)->replace('_', ' ')->lower();
        $object = $claim->object_id !== null ? $claim->object->name : $claim->object_value;

        return Str::squish("{$claim->subject->name} {$predicate} {$object}");
    }

    /** @return list<array{field: string, label: string, value: mixed, reference_type: string|null}> */
    public function fields(Model $record): array
    {
        return array_values(collect($record->attributesToArray())
            ->except(['milieu_id', 'created_at', 'updated_at'])
            ->map(fn (mixed $value, string $field): array => [
                'field' => $field,
                'label' => $this->fieldLabel($field),
                'value' => $value,
                'reference_type' => $this->referenceType($field),
            ])
            ->values()
            ->all());
    }

    public function referenceType(string $field): ?string
    {
        return $this->domainRegistry->referenceType($field);
    }

    private function fieldLabel(string $field): string
    {
        $displayField = $this->referenceType($field) === null
            ? $field
            : Str::beforeLast($field, '_id');

        return Str::headline($displayField);
    }

    /** @return array<string, array{record_type: string, description: string, pivot_fields: array<string, string>, ordered?: bool}> */
    public function relationDefinitions(string $type): array
    {
        return $this->domainRegistry->relationDefinitions($type);
    }
}
