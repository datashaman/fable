<?php

namespace App\Support\Fable;

use App\Models\Milieu;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use InvalidArgumentException;

class PresentationRegistry
{
    /** @var array<string, string> */
    private const REFERENCE_TYPES = [
        'continuity_id' => 'continuity',
        'parent_id' => 'continuity',
        'diverged_from_event_id' => 'event',
        'type_id' => 'ontology_type',
        'source_id' => 'entity',
        'target_id' => 'entity',
        'subject_id' => 'entity',
        'object_id' => 'entity',
        'holder_id' => 'entity',
        'source_entity_id' => 'entity',
        'focalizer_id' => 'entity',
        'narrator_id' => 'entity',
        'claim_id' => 'claim',
        'scenario_id' => 'scenario',
        'story_id' => 'story',
        'belief_id' => 'belief',
        'scene_id' => 'scene',
    ];

    /** @var array<string, array{label: string, plural: string, stratum: string, title: string, summary: list<string>, status?: string, continuity?: bool}> */
    private const DEFINITIONS = [
        'milieu' => ['label' => 'Milieu', 'plural' => 'Milieus', 'stratum' => 'world', 'title' => 'name', 'summary' => ['genre', 'description'], 'status' => 'status'],
        'continuity' => ['label' => 'Continuity', 'plural' => 'Continuities', 'stratum' => 'world', 'title' => 'name', 'summary' => ['canonical_status', 'description'], 'status' => 'canonical_status'],
        'ontology_type' => ['label' => 'Ontology type', 'plural' => 'Ontology', 'stratum' => 'world', 'title' => 'name', 'summary' => ['category', 'key']],
        'entity' => ['label' => 'Entity', 'plural' => 'Entities', 'stratum' => 'canon', 'title' => 'name', 'summary' => ['canonical_status', 'description'], 'status' => 'canonical_status'],
        'relationship' => ['label' => 'Relationship', 'plural' => 'Relationships', 'stratum' => 'canon', 'title' => 'description', 'summary' => ['canonical_status', 'inverse'], 'status' => 'canonical_status', 'continuity' => true],
        'event' => ['label' => 'Event', 'plural' => 'Events', 'stratum' => 'canon', 'title' => 'name', 'summary' => ['start_time', 'description'], 'status' => 'canonical_status', 'continuity' => true],
        'rule' => ['label' => 'Rule', 'plural' => 'Rules', 'stratum' => 'canon', 'title' => 'name', 'summary' => ['priority', 'description'], 'status' => 'canonical_status'],
        'claim' => ['label' => 'Claim', 'plural' => 'Claims', 'stratum' => 'knowledge', 'title' => 'predicate', 'summary' => ['object_value', 'description']],
        'belief' => ['label' => 'Belief', 'plural' => 'Beliefs', 'stratum' => 'knowledge', 'title' => 'description', 'summary' => ['stance', 'confidence'], 'status' => 'stance', 'continuity' => true],
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

        if ($type === 'milieu') {
            return $query->whereKey($milieu->getKey());
        }

        if ($type === 'scene') {
            return $query->whereHas('story', fn (Builder $query): Builder => $query->whereBelongsTo($milieu));
        }

        return $query->whereBelongsTo($milieu);
    }

    /** @return Builder<Model> */
    public function searchableQuery(Milieu $milieu, string $type, string $search = '', ?int $continuityId = null): Builder
    {
        $query = $this->query($milieu, $type);
        $search = Str::squish($search);

        if ($type === 'ontology_type') {
            $query->withCount(['entities', 'relationships', 'events', 'rules']);
        }

        if ($search !== '') {
            $fields = $this->domainRegistry->searchFields($type);
            $query->where(function (Builder $query) use ($fields, $search): void {
                foreach ($fields as $index => $field) {
                    $method = $index === 0 ? 'where' : 'orWhere';
                    $query->{$method}($field, 'like', "%{$search}%");
                }
            });
        }

        if ($continuityId !== null && ($this->definition($type)['continuity'] ?? false)) {
            $query->where('continuity_id', $continuityId);
        }

        if ($type === 'ontology_type') {
            return $query->orderBy('category')->orderBy('name');
        }

        return $query->latest('updated_at')->latest('id');
    }

    public function title(string $type, Model $record): string
    {
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

    /** @return list<array{field: string, label: string, value: mixed}> */
    public function summary(string $type, Model $record): array
    {
        $definition = $this->definition($type);
        $statusField = $definition['status'] ?? null;

        return array_values(collect($definition['summary'])
            ->reject(fn (string $field): bool => $field === $statusField)
            ->map(fn (string $field): array => [
                'field' => $field,
                'label' => Str::headline($field),
                'value' => $record->getAttribute($field),
            ])
            ->filter(fn (array $item): bool => filled($item['value']))
            ->values()
            ->all());
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
        return self::REFERENCE_TYPES[$field] ?? null;
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
