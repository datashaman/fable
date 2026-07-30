<?php

use App\Livewire\ReadonlyPage;
use App\Enums\CanonicalStatus;
use App\Enums\OntologyCategory;
use App\Models\Belief;
use App\Models\Claim;
use App\Models\ChangeEntry;
use App\Models\ChangeSet;
use App\Models\Continuity;
use App\Models\Entity;
use App\Models\Milieu;
use App\Models\OntologyType;
use App\Models\Relationship;
use App\Models\Story;
use App\Support\Fable\PresentationRegistry;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\WithPagination;

new #[Title('Milieu explorer')] class extends ReadonlyPage {
    use WithPagination;

    public Milieu $milieu;
    public string $recordType;
    public ?int $record = null;

    #[Url(as: 'q', except: '')]
    public string $search = '';

    #[Url(except: null)]
    public ?int $continuity = null;

    #[Url(as: 'type', except: null)]
    public ?int $entityType = null;

    #[Url(as: 'status', except: null)]
    public ?string $entityStatus = null;

    #[Url(except: 'recent')]
    public string $sort = 'recent';

    protected PresentationRegistry $presentation;

    public function boot(PresentationRegistry $presentation): void
    {
        $this->presentation = $presentation;
    }

    public function mount(Milieu $milieu, string $recordType, ?int $record = null): void
    {
        Gate::authorize('view', $milieu);
        $this->presentation->definition($recordType);

        if ($continuity = request()->integer('continuity')) {
            abort_unless($milieu->continuities()->whereKey($continuity)->exists(), 404);
        }

        if ($entityType = request()->integer('type')) {
            abort_unless($recordType === 'entity' && $milieu->ontologyTypes()
                ->whereKey($entityType)
                ->where('category', OntologyCategory::Entity->value)
                ->exists(), 404);
        }

        if ($entityStatus = request()->string('status')->toString()) {
            abort_unless($recordType === 'entity' && CanonicalStatus::tryFrom($entityStatus), 404);
        }

        abort_unless(in_array(request()->string('sort', 'recent')->toString(), ['recent', 'alphabetical'], true), 404);

        $this->milieu = $milieu;
        $this->recordType = $recordType;
        $this->record = $record;
    }

    public function updatedSearch(): void
    {
        $this->resetPage('records');
    }

    public function updatedContinuity(?int $continuity): void
    {
        if ($continuity !== null) {
            abort_unless($this->milieu->continuities()->whereKey($continuity)->exists(), 404);
        }

        $this->resetPage('records');
    }

    public function updatedEntityType(?int $entityType): void
    {
        if ($entityType !== null) {
            abort_unless($this->recordType === 'entity' && $this->milieu->ontologyTypes()
                ->whereKey($entityType)
                ->where('category', OntologyCategory::Entity->value)
                ->exists(), 404);
        }

        $this->resetPage('records');
    }

    public function updatedEntityStatus(?string $entityStatus): void
    {
        if ($entityStatus !== null) {
            abort_unless($this->recordType === 'entity' && CanonicalStatus::tryFrom($entityStatus), 404);
        }

        $this->resetPage('records');
    }

    public function updatedSort(string $sort): void
    {
        abort_unless(in_array($sort, ['recent', 'alphabetical'], true), 404);
        $this->resetPage('records');
    }

    /** @return LengthAwarePaginator<int, Model>|Collection<int, Model> */
    #[Computed]
    public function records(): LengthAwarePaginator|Collection
    {
        $query = $this->presentation->searchableQuery(
            $this->milieu,
            $this->recordType,
            $this->search,
            $this->continuity,
            $this->entityType,
            $this->entityStatus,
            $this->sort,
        );

        if ($this->recordType === 'entity') {
            return $query->get();
        }

        return $query->paginate($this->recordType === 'ontology_type' ? 100 : 18, pageName: 'records');
    }

    /** @return Collection<int, OntologyType> */
    #[Computed]
    public function entityTypes(): Collection
    {
        if ($this->recordType !== 'entity') {
            return new Collection;
        }

        return $this->milieu->ontologyTypes()
            ->where('category', OntologyCategory::Entity->value)
            ->withCount('entities')
            ->orderBy('name')
            ->get(['id', 'milieu_id', 'name']);
    }

    /** @return array<string, int> */
    #[Computed]
    public function entityStatusCounts(): array
    {
        if ($this->recordType !== 'entity') {
            return [];
        }

        return $this->presentation
            ->searchableQuery($this->milieu, 'entity', $this->search, null, $this->entityType)
            ->reorder()
            ->selectRaw('canonical_status, count(*) as aggregate')
            ->groupBy('canonical_status')
            ->pluck('aggregate', 'canonical_status')
            ->map(fn ($count): int => (int) $count)
            ->all();
    }

    #[Computed]
    public function recordsCount(): int
    {
        return $this->records instanceof Collection ? $this->records->count() : $this->records->total();
    }

    /** @return array{position: int, total: int, next_id: int|string|null}|null */
    #[Computed]
    public function selectedRecordNavigation(): ?array
    {
        if (! $this->selectedRecord instanceof Entity) {
            return null;
        }

        $records = ($this->records instanceof Collection
            ? $this->records
            : $this->records->getCollection())->values();
        $position = $records->search(
            fn (Model $record): bool => $record->getKey() === $this->selectedRecord?->getKey(),
        );

        if ($position === false) {
            return null;
        }

        return [
            'position' => $position + 1,
            'total' => $records->count(),
            'next_id' => $records->get($position + 1)?->getKey(),
        ];
    }

    #[Computed]
    public function changedToday(): int
    {
        return ChangeEntry::query()
            ->where('record_type', $this->recordType)
            ->whereHas('changeSet', fn ($query) => $query
                ->whereBelongsTo($this->milieu)
                ->whereDate('created_at', today()))
            ->count();
    }

    /** @return list<array{key: string, label: string|null, records: Collection<int, Model>}> */
    #[Computed]
    public function recordGroups(): array
    {
        $records = $this->records instanceof Collection
            ? $this->records
            : $this->records->getCollection();

        if ($records->isEmpty()) {
            return [];
        }

        if ($this->recordType === 'entity') {
            $groups = [];

            foreach ($records as $record) {
                if (! $record instanceof Entity) {
                    continue;
                }

                $key = (string) $record->type_id;
                $groups[$key] ??= [
                    'key' => $key,
                    'label' => $record->type->name,
                    'records' => new Collection,
                ];
                $groups[$key]['records']->push($record);
            }

            return collect($groups)
                ->sortBy('label', SORT_NATURAL | SORT_FLAG_CASE)
                ->values()
                ->all();
        }

        if ($this->recordType === 'story') {
            $groups = [];

            foreach ($records as $record) {
                if (! $record instanceof Story) {
                    continue;
                }

                $key = $record->scenario_id === null ? 'unassigned' : (string) $record->scenario_id;
                $groups[$key] ??= [
                    'key' => $key,
                    'label' => $record->scenario?->name ?? 'No scenario',
                    'records' => new Collection,
                ];
                $groups[$key]['records']->push($record);
            }

            return array_values($groups);
        }

        if ($this->recordType !== 'ontology_type') {
            return [['key' => 'records', 'label' => null, 'records' => $records]];
        }

        return array_values(collect(OntologyCategory::cases())
            ->map(function (OntologyCategory $category) use ($records): array {
                /** @var Collection<int, Model> $categoryRecords */
                $categoryRecords = $records
                    ->filter(fn (Model $record): bool => $record instanceof OntologyType && $record->category === $category)
                    ->values();

                return [
                    'key' => $category->value,
                    'label' => str($category->value)->plural()->headline()->toString(),
                    'records' => $categoryRecords,
                ];
            })
            ->filter(fn (array $group): bool => $group['records']->isNotEmpty())
            ->all());
    }

    #[Computed]
    public function selectedRecord(): ?Model
    {
        if ($this->record === null) {
            return null;
        }

        return $this->presentation->query($this->milieu, $this->recordType)->findOrFail($this->record);
    }

    /** @return Collection<int, Continuity> */
    #[Computed]
    public function continuities(): Collection
    {
        if (! ($this->definition['continuity'] ?? false)) {
            return new Collection;
        }

        return $this->milieu->continuities()->orderBy('name')->get();
    }

    /** @return array{label: string, plural: string, stratum: string, title: string, summary: list<string>, status?: string, continuity?: bool} */
    #[Computed]
    public function definition(): array
    {
        return $this->presentation->definition($this->recordType);
    }

    /** @return array{field: string, value: string}|null */
    #[Computed]
    public function selectedLead(): ?array
    {
        if ($this->selectedRecord === null) {
            return null;
        }

        foreach (['description', 'premise', 'motivation'] as $field) {
            $value = $this->selectedRecord->getAttribute($field);

            if (is_string($value) && filled($value) && ! $this->fieldRendersAsTitle($field, $value)) {
                return ['field' => $field, 'value' => $value];
            }
        }

        return null;
    }

    /**
     * Whether a field's raw value is what the record's displayed title actually shows.
     *
     * The title config only names which field a type's heading is *usually* built from;
     * some types (belief, relationship, claim, disclosure) compute a different string from
     * several fields. Comparing field names alone would hide the raw field from the detail
     * view in those cases even though it never actually appears anywhere as the heading.
     */
    private function fieldRendersAsTitle(string $field, mixed $value): bool
    {
        if ($field !== $this->definition['title'] || ! is_string($value)) {
            return false;
        }

        return Str::limit($value, 90) === $this->recordTitle($this->selectedRecord);
    }

    /** @return list<array{field: string, label: string, value: mixed, reference_type: string|null, reference_title: string|null}> */
    #[Computed]
    public function selectedFields(): array
    {
        if ($this->selectedRecord === null) {
            return [];
        }

        $titleField = $this->definition['title'];

        $hiddenFields = array_filter([
            'id',
            'revision',
            $this->fieldRendersAsTitle($titleField, $this->selectedRecord->getAttribute($titleField)) ? $titleField : null,
            $this->definition['status'] ?? null,
            $this->selectedLead['field'] ?? null,
        ]);

        if ($this->selectedRecord instanceof Entity) {
            $hiddenFields = [...$hiddenFields, 'type_id', 'aliases', 'tags'];
        }

        $fields = array_values(array_filter(
            $this->presentation->fields($this->selectedRecord),
            fn (array $field): bool => ! in_array($field['field'], $hiddenFields, true) && filled($field['value']),
        ));
        $references = [];

        foreach ($fields as $field) {
            if ($field['reference_type'] !== null && filled($field['value'])) {
                $references[$field['reference_type']][] = $field['value'];
            }
        }

        $referenceTitles = [];

        foreach ($references as $type => $ids) {
            foreach ($this->presentation->query($this->milieu, $type)->whereKey(array_unique($ids))->get() as $record) {
                $referenceTitles[$type][(string) $record->getKey()] = $this->presentation->title($type, $record);
            }
        }

        return array_map(fn (array $field): array => [
            ...$field,
            'reference_title' => $field['reference_type'] === null
                ? null
                : ($referenceTitles[$field['reference_type']][(string) $field['value']] ?? null),
        ], $fields);
    }

    /** @return list<array{key: string, label: string, type: string, records: list<array{id: int|string, title: string}>}> */
    #[Computed]
    public function selectedEntityKnowledge(): array
    {
        if (! $this->selectedRecord instanceof Entity) {
            return [];
        }

        $groups = [
            ['key' => 'claims-subject', 'label' => 'As subject', 'type' => 'claim', 'records' => $this->selectedRecord->claimsAsSubject()->with(['subject:id,name', 'object:id,name'])->get()],
            ['key' => 'claims-object', 'label' => 'As object', 'type' => 'claim', 'records' => $this->selectedRecord->claimsAsObject()->with(['subject:id,name', 'object:id,name'])->get()],
            ['key' => 'beliefs-held', 'label' => 'Beliefs held', 'type' => 'belief', 'records' => $this->presentation->query($this->milieu, 'belief')->where('holder_id', $this->selectedRecord->id)->get()],
            ['key' => 'perspectives', 'label' => 'Seen from', 'type' => 'perspective', 'records' => $this->presentation->query($this->milieu, 'perspective')->where('holder_id', $this->selectedRecord->id)->get()],
        ];

        return collect($groups)
            ->map(fn (array $group): array => [
                ...$group,
                'records' => $group['records']->map(fn (Model $record): array => [
                    'id' => $record->getKey(),
                    'title' => $this->presentation->title($group['type'], $record),
                ])->all(),
            ])
            ->filter(fn (array $group): bool => $group['records'] !== [])
            ->values()
            ->all();
    }

    /** @return list<array{key: string, label: string, type: string, records: list<array{id: int|string, title: string, status: string|null}>}> */
    #[Computed]
    public function selectedEntityPossibility(): array
    {
        if (! $this->selectedRecord instanceof Entity) {
            return [];
        }

        $groups = [
            ['key' => 'goals', 'label' => 'Goals held', 'type' => 'goal', 'records' => $this->selectedRecord->goals()->get()],
            ['key' => 'conflicts', 'label' => 'Contested in', 'type' => 'conflict', 'records' => $this->selectedRecord->conflictsAsSubject()->get()],
        ];

        return collect($groups)
            ->map(fn (array $group): array => [
                ...$group,
                'records' => $group['records']->map(fn (Model $record): array => [
                    'id' => $record->getKey(),
                    'title' => $this->presentation->title($group['type'], $record),
                    'status' => $this->presentation->status($group['type'], $record),
                ])->all(),
            ])
            ->filter(fn (array $group): bool => $group['records'] !== [])
            ->values()
            ->all();
    }

    /** @return list<array{name: string, label: string, description: string, type: string, records: list<array{id: int|string, title: string}>}> */
    #[Computed]
    public function selectedRelations(): array
    {
        if ($this->selectedRecord === null) {
            return [];
        }

        $relations = [];

        foreach ($this->presentation->relationDefinitions($this->recordType) as $name => $definition) {
            $records = $this->selectedRecord->{$name}()->get()
                ->map(fn (Model $record): array => [
                    'id' => $record->getKey(),
                    'title' => $this->presentation->title($definition['record_type'], $record),
                ])
                ->values()
                ->all();

            $relations[] = [
                'name' => $name,
                'label' => str($name)->headline()->toString(),
                'description' => $definition['description'],
                'type' => $definition['record_type'],
                'records' => $records,
            ];
        }

        return $relations;
    }

    /** @return array{type: string, records: list<array{id: int|string, title: string}>}|null */
    #[Computed]
    public function selectedEntries(): ?array
    {
        if (! $this->selectedRecord instanceof OntologyType) {
            return null;
        }

        $relationName = $this->selectedRecord->instanceRelationName();
        $recordType = $this->selectedRecord->category->value;
        $records = $this->selectedRecord->{$relationName}()
            ->latest('updated_at')
            ->latest('id')
            ->get()
            ->map(fn (Model $record): array => [
                'id' => $record->getKey(),
                'title' => $this->presentation->title($recordType, $record),
            ])
            ->values()
            ->all();

        return [
            'type' => $recordType,
            'records' => $records,
        ];
    }

    /** @return list<array{id: int, source_id: int, source_name: string, target_id: int, target_name: string, type_id: int, type_name: string, continuity_id: int, continuity_name: string, symmetric: bool, status: string|null, started_at: string|null, ended_at: string|null, description: string|null}> */
    #[Computed]
    public function selectedEntityRelationships(): array
    {
        if (! $this->selectedRecord instanceof Entity) {
            return [];
        }

        $relationships = $this->selectedRecord->sourceRelationships()
            ->with(['source:id,name', 'target:id,name', 'type:id,name', 'continuity:id,name'])
            ->get()
            ->merge($this->selectedRecord->targetRelationships()
                ->with(['source:id,name', 'target:id,name', 'type:id,name', 'continuity:id,name'])
                ->get())
            ->unique(fn (Relationship $relationship): int => $relationship->id);

        return $relationships
            ->map(fn (Relationship $relationship): array => [
                'id' => $relationship->id,
                'source_id' => $relationship->source_id,
                'source_name' => $relationship->source->name,
                'target_id' => $relationship->target_id,
                'target_name' => $relationship->target->name,
                'type_id' => $relationship->type_id,
                'type_name' => $relationship->type->name,
                'continuity_id' => $relationship->continuity_id,
                'continuity_name' => $relationship->continuity->name,
                'symmetric' => $relationship->symmetric,
                'status' => $this->presentation->status('relationship', $relationship),
                'started_at' => $relationship->started_at,
                'ended_at' => $relationship->ended_at,
                'description' => $relationship->description,
            ])
            ->values()
            ->all();
    }

    /** @return list<array{continuity_id: int, continuity_name: string, relationships: list<array{id: int, source_id: int, source_name: string, target_id: int, target_name: string, type_id: int, type_name: string, continuity_id: int, continuity_name: string, symmetric: bool, status: string|null, started_at: string|null, ended_at: string|null, description: string|null}>}> */
    #[Computed]
    public function selectedEntityRelationshipGroups(): array
    {
        $groups = [];

        foreach ($this->selectedEntityRelationships as $relationship) {
            $continuityId = $relationship['continuity_id'];
            $groups[$continuityId] ??= [
                'continuity_id' => $continuityId,
                'continuity_name' => $relationship['continuity_name'],
                'relationships' => [],
            ];
            $groups[$continuityId]['relationships'][] = $relationship;
        }

        foreach ($groups as &$group) {
            usort($group['relationships'], fn (array $left, array $right): int => $this->compareRelationshipDates($left, $right));
        }
        unset($group);

        usort($groups, fn (array $left, array $right): int => strnatcasecmp($left['continuity_name'], $right['continuity_name']));

        return array_values($groups);
    }

    /**
     * @param  array{started_at: string|null, ended_at: string|null, id: int}  $left
     * @param  array{started_at: string|null, ended_at: string|null, id: int}  $right
     */
    private function compareRelationshipDates(array $left, array $right): int
    {
        $startedAtComparison = $this->compareNullableDates($left['started_at'], $right['started_at']);

        if ($startedAtComparison !== 0) {
            return $startedAtComparison;
        }

        $endedAtComparison = $this->compareNullableDates($left['ended_at'], $right['ended_at']);

        return $endedAtComparison !== 0 ? $endedAtComparison : $left['id'] <=> $right['id'];
    }

    private function compareNullableDates(?string $left, ?string $right): int
    {
        if ($left === $right) {
            return 0;
        }

        if ($left === null || $left === '') {
            return 1;
        }

        if ($right === null || $right === '') {
            return -1;
        }

        return strnatcasecmp($left, $right);
    }

    /** @return Collection<int, ChangeSet> */
    #[Computed]
    public function selectedActivity(): Collection
    {
        if ($this->selectedRecord === null) {
            return new Collection;
        }

        return $this->milieu->changeSets()
            ->whereHas('entries', fn ($query) => $query
                ->where('record_type', $this->recordType)
                ->where('record_id', $this->selectedRecord?->getKey()))
            ->with(['user:id,name', 'entries'])
            ->latest()
            ->limit(8)
            ->get();
    }

    public function recordTitle(Model $record): string
    {
        return $this->presentation->title($this->recordType, $record);
    }

    public function recordStatus(Model $record): ?string
    {
        return $this->presentation->status($this->recordType, $record);
    }

    public function ontologyInstanceLabel(OntologyType $record): string
    {
        $count = (int) $record->getAttribute($record->instanceRelationName().'_count');

        return trans_choice('{0} No instances|{1} 1 instance|[2,*] :count instances', $count, ['count' => $count]);
    }

    /** @return list<array{field: string, label: string, value: mixed}> */
    public function recordSummary(Model $record): array
    {
        $summary = $this->presentation->summary($this->recordType, $record);

        if ($this->recordType === 'scene') {
            $summary = array_map(fn (array $item): array => $item['field'] === 'sequence'
                ? [...$item, 'value' => 'Scene '.$item['value']]
                : $item, $summary);
        }

        if ($record instanceof OntologyType) {
            $summary[] = [
                'field' => 'instances',
                'label' => 'Instances',
                'value' => $this->ontologyInstanceLabel($record),
            ];
        }

        return $summary;
    }

    /** @param array<string, mixed> $event */
    protected function refreshState(array $event): void
    {
        unset($this->records, $this->entityTypes, $this->entityStatusCounts, $this->recordsCount, $this->selectedRecordNavigation, $this->changedToday, $this->recordGroups, $this->selectedRecord, $this->selectedLead, $this->selectedFields, $this->selectedRelations, $this->selectedEntries, $this->selectedEntityKnowledge, $this->selectedEntityPossibility, $this->selectedEntityRelationships, $this->selectedEntityRelationshipGroups, $this->selectedActivity);
    }
}; ?>

<div @class(['fable-explorer', 'has-selected-record' => $this->selectedRecord instanceof Entity])>
    <header class="fable-explorer-header">
        <div class="min-w-0">
            <div class="flex items-center gap-2 text-xs text-fable-tertiary">
                <a href="{{ route('milieus.show', $milieu) }}" wire:navigate class="hover:text-brass">{{ $milieu->name }}</a>
                <span>/</span>
                <span>{{ $this->definition['stratum'] === 'world' ? 'Milieu' : str($this->definition['stratum'])->headline() }}</span>
            </div>
            <h1 class="fable-display mt-2">{{ $this->definition['plural'] }}</h1>
        </div>
        <span class="fable-explorer-count">
            {{ $this->recordsCount }} records
            @if ($this->changedToday > 0)
                · <strong>{{ $this->changedToday }} changed today</strong>
            @endif
        </span>
    </header>

    <div class="fable-explorer-layout">
        <section class="fable-index-pane" aria-label="{{ $this->definition['plural'] }} collection">
            <div class="fable-collection-controls">
                <flux:input wire:model.live.debounce.300ms="search" icon="magnifying-glass" placeholder="Search {{ str($this->definition['plural'])->lower() }}…" clearable />

                @if ($this->continuities->isNotEmpty())
                    <flux:select wire:model.live="continuity" aria-label="Filter by continuity">
                        <flux:select.option :value="null">All continuities</flux:select.option>
                        @foreach ($this->continuities as $continuityOption)
                            <flux:select.option :value="$continuityOption->id" wire:key="continuity-option-{{ $continuityOption->id }}">
                                {{ $continuityOption->name }}
                            </flux:select.option>
                        @endforeach
                    </flux:select>
                @endif

                @if ($this->entityTypes->isNotEmpty())
                    <div class="fable-filter-chips" aria-label="Filter by entity type">
                        <button type="button" wire:click="$set('entityType', null)" @class(['is-active' => $entityType === null])>
                            All <span>{{ $this->entityTypes->sum('entities_count') }}</span>
                        </button>
                        @foreach ($this->entityTypes as $entityTypeOption)
                            <button
                                type="button"
                                wire:click="$set('entityType', {{ $entityTypeOption->id }})"
                                @class(['is-active' => $entityType === $entityTypeOption->id])
                                wire:key="entity-type-option-{{ $entityTypeOption->id }}"
                            >
                                {{ $entityTypeOption->name }} <span>{{ $entityTypeOption->entities_count }}</span>
                            </button>
                        @endforeach
                    </div>

                    <div class="fable-entity-filter-row">
                        <div class="fable-status-filters" aria-label="Filter by canonical status">
                            <span>Status</span>
                            @foreach (CanonicalStatus::cases() as $statusOption)
                                <button
                                    type="button"
                                    wire:click="$set('entityStatus', {{ $entityStatus === $statusOption->value ? 'null' : "'{$statusOption->value}'" }})"
                                    @class(['is-active' => $entityStatus === $statusOption->value])
                                    title="{{ $statusOption->name }} ({{ $this->entityStatusCounts[$statusOption->value] ?? 0 }})"
                                    aria-label="Filter by {{ $statusOption->value }} status"
                                >
                                    <x-fable.canonical-status :status="$statusOption->value" />
                                </button>
                            @endforeach
                        </div>

                        <flux:select wire:model.live="sort" size="sm" aria-label="Sort entities">
                            <flux:select.option value="recent">Recently changed</flux:select.option>
                            <flux:select.option value="alphabetical">Alphabetical</flux:select.option>
                        </flux:select>
                    </div>
                @endif
            </div>

            <div class="fable-record-list">
                @forelse ($this->recordGroups as $group)
                    <section class="fable-record-group" wire:key="record-group-{{ $group['key'] }}">
                        @if ($group['label'])
                            <div class="fable-record-group-heading">
                                <h2>{{ $group['label'] }}</h2>
                                <span>{{ match ($recordType) {
                                    'ontology_type' => trans_choice('{1} 1 type|[2,*] :count types', $group['records']->count(), ['count' => $group['records']->count()]),
                                    'entity' => trans_choice('{1} 1 entity|[2,*] :count entities', $group['records']->count(), ['count' => $group['records']->count()]),
                                    default => trans_choice('{1} 1 story|[2,*] :count stories', $group['records']->count(), ['count' => $group['records']->count()]),
                                } }}</span>
                            </div>
                        @endif

                        @foreach ($group['records'] as $item)
                            <a
                                href="{{ route('milieus.explore', [$milieu, $recordType, $item->getKey(), 'q' => $search, 'continuity' => $continuity, 'type' => $entityType, 'status' => $entityStatus, 'sort' => $sort]) }}"
                                wire:navigate
                                wire:key="{{ $recordType }}-{{ $item->getKey() }}"
                                @class([
                                    'fable-record-index-row',
                                    'is-ontology' => $item instanceof OntologyType,
                                    'is-selected' => $record === $item->getKey(),
                                ])
                                @if (($lastChange['record_type'] ?? null) === $recordType && ($lastChange['record_id'] ?? null) === $item->getKey()) data-fable-changed @endif
                            >
                                @if ($item instanceof OntologyType)
                                    <h3 class="min-w-0 flex-1 truncate font-medium text-fable-primary">{{ $this->recordTitle($item) }}</h3>
                                    <span class="shrink-0 text-xs text-fable-tertiary">{{ $this->ontologyInstanceLabel($item) }}</span>
                                @else
                                    <div class="min-w-0 flex-1">
                                        <div class="flex items-start gap-2">
                                            <h3 @class([
                                                'min-w-0 flex-1 font-medium text-fable-primary',
                                                'truncate text-base/5' => ! $item instanceof Relationship && ! $item instanceof Claim && ! $item instanceof Belief,
                                                'wrap-normal text-sm/5' => $item instanceof Relationship || $item instanceof Claim || $item instanceof Belief,
                                            ])>{{ $this->recordTitle($item) }}</h3>
                                            @if ($status = $this->recordStatus($item))
                                                @if (CanonicalStatus::tryFrom($status))
                                                    <x-fable.canonical-status
                                                        :$status
                                                        class="size-[1.125rem] translate-y-1"
                                                        data-fable-status-position="row-end"
                                                    />
                                                @else
                                                    <span class="fable-status shrink-0">{{ $status }}</span>
                                                @endif
                                            @endif
                                        </div>
                                        <div class="mt-1 flex min-w-0 items-center gap-2 text-xs text-fable-tertiary">
                                            @foreach ($this->recordSummary($item) as $summary)
                                                <span @class([
                                                    'min-w-0 truncate' => ! in_array($summary['field'], ['temporal_range', 'sequence'], true),
                                                    'shrink-0 whitespace-nowrap font-mono text-[0.6875rem] tabular-nums' => in_array($summary['field'], ['temporal_range', 'sequence'], true),
                                                ])>{{ is_scalar($summary['value']) ? $summary['value'] : json_encode($summary['value']) }}</span>
                                            @endforeach
                                            @if ($item instanceof Entity)
                                                <time class="ml-auto shrink-0 font-mono text-[0.625rem] {{ $item->updated_at?->isToday() ? 'text-brass' : 'text-fable-muted' }}">
                                                    {{ $item->updated_at?->shortAbsoluteDiffForHumans() }}
                                                </time>
                                            @endif
                                        </div>
                                    </div>
                                @endif
                            </a>
                        @endforeach
                    </section>
                @empty
                    <div class="fable-empty">
                        <p>No {{ str($this->definition['plural'])->lower() }} match this view.</p>
                        @if ($search !== '')
                            <flux:button variant="ghost" size="sm" wire:click="$set('search', '')">Clear search</flux:button>
                        @endif
                    </div>
                @endforelse
            </div>

            @if ($this->records instanceof LengthAwarePaginator && $this->records->hasPages())
                <div class="border-t border-fable-soft p-3">
                    <flux:pagination :paginator="$this->records" />
                </div>
            @endif
        </section>

        <section class="fable-reading-pane" aria-label="Selected record detail">
            @if ($this->selectedRecord)
                @if ($navigation = $this->selectedRecordNavigation)
                    <nav class="fable-mobile-record-nav" aria-label="Entity record navigation">
                        <a href="{{ route('milieus.explore', [$milieu, $recordType, 'q' => $search, 'type' => $entityType, 'status' => $entityStatus, 'sort' => $sort]) }}" wire:navigate>
                            <span aria-hidden="true">‹</span>
                            <span>Entities</span>
                        </a>
                        <span>{{ $navigation['position'] }} of {{ $navigation['total'] }}</span>
                        @if ($navigation['next_id'])
                            <a
                                href="{{ route('milieus.explore', [$milieu, $recordType, $navigation['next_id'], 'q' => $search, 'type' => $entityType, 'status' => $entityStatus, 'sort' => $sort]) }}"
                                aria-label="Next entity"
                                wire:navigate
                            >›</a>
                        @else
                            <span aria-hidden="true">›</span>
                        @endif
                    </nav>
                @endif
                <article class="fable-record-article" @if (($lastChange['record_type'] ?? null) === $recordType && ($lastChange['record_id'] ?? null) === $record) data-fable-changed @endif>
                    <header class="fable-record-masthead">
                        <div class="min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="fable-eyebrow">{{ $this->definition['label'] }}</span>
                                @if ($this->selectedRecord instanceof Entity)
                                    <span class="text-fable-muted" aria-hidden="true">·</span>
                                    <a class="fable-record-type" href="{{ route('milieus.explore', [$milieu, 'ontology_type', $this->selectedRecord->type_id]) }}" aria-label="Type: {{ $this->selectedRecord->type->name }}" wire:navigate>
                                        {{ $this->selectedRecord->type->name }}
                                    </a>
                                @endif
                                @if ($status = $this->recordStatus($this->selectedRecord))
                                    @if (CanonicalStatus::tryFrom($status))
                                        <x-fable.canonical-status :$status />
                                    @else
                                        <span class="fable-status">{{ $status }}</span>
                                    @endif
                                @endif
                            </div>
                            <h2 class="fable-record-title">
                                {{ $this->recordTitle($this->selectedRecord) }}
                            </h2>
                            @if ($this->selectedLead)
                                <p class="fable-record-lead">{{ $this->selectedLead['value'] }}</p>
                            @endif
                            @if ($this->selectedRecord instanceof Entity && (filled($this->selectedRecord->tags) || filled($this->selectedRecord->aliases)))
                                <div class="fable-entity-identifiers fable-tag-list">
                                    @foreach ($this->selectedRecord->tags ?? [] as $tag)
                                        <span class="fable-tag">{{ $tag }}</span>
                                    @endforeach
                                    @if (filled($this->selectedRecord->aliases))
                                        <span>also known as</span>
                                        <span class="fable-value-list">
                                            @foreach ($this->selectedRecord->aliases as $alias)
                                                <span class="fable-value-list-item">{{ $alias }}</span>
                                            @endforeach
                                        </span>
                                    @endif
                                </div>
                            @endif
                        </div>
                        <div class="fable-record-margin">
                            <span>{{ $this->definition['label'] }} #{{ $this->selectedRecord->getKey() }}</span>
                            @if ($this->selectedRecord->getAttribute('revision') !== null)
                                <span>revision {{ $this->selectedRecord->getAttribute('revision') }}</span>
                            @endif
                            @if (($lastChange['record_type'] ?? null) === $recordType && ($lastChange['record_id'] ?? null) === $record && filled($lastChange['changed_fields'] ?? []))
                                <span class="text-brass">{{ count($lastChange['changed_fields']) }} fields changed</span>
                            @endif
                        </div>
                    </header>

                    @unless ($this->selectedRecord instanceof Entity)
                        <dl class="fable-facts">
                            @foreach ($this->selectedFields as $field)
                                <div class="fable-fact" wire:key="field-{{ $field['field'] }}" @if (in_array($field['field'], $lastChange['changed_fields'] ?? [], true)) data-fable-changed @endif>
                                    <dt>{{ $field['label'] }}</dt>
                                    <dd>
                                        @if ($field['reference_type'] && filled($field['value']))
                                            <a class="fable-reference" href="{{ route('milieus.explore', [$milieu, $field['reference_type'], $field['value']]) }}" wire:navigate>
                                                {{ $field['reference_title'] ?? str($field['reference_type'])->headline().' #'.$field['value'] }}
                                            </a>
                                        @else
                                            <x-fable.value :value="$field['value']" :field="$field['field']" />
                                        @endif
                                    </dd>
                                </div>
                            @endforeach
                        </dl>
                    @endunless

                    @if ($entries = $this->selectedEntries)
                        <section class="fable-article-section" aria-labelledby="entries-title">
                            <div class="flex items-baseline gap-3">
                                <h3 id="entries-title" class="fable-section-title">Entries</h3>
                                <span class="font-mono text-xs text-fable-muted">{{ count($entries['records']) }}</span>
                            </div>

                            <div class="fable-entry-list">
                                @forelse ($entries['records'] as $entry)
                                    <a
                                        class="fable-entry-row"
                                        href="{{ route('milieus.explore', [$milieu, $entries['type'], $entry['id']]) }}"
                                        wire:navigate
                                        wire:key="ontology-entry-{{ $entries['type'] }}-{{ $entry['id'] }}"
                                    >
                                        <span class="fable-entry-title">{{ $entry['title'] }}</span>
                                        <span class="font-mono text-[0.6875rem] text-fable-muted">{{ str($entries['type'])->headline() }} #{{ $entry['id'] }}</span>
                                    </a>
                                @empty
                                    <p class="py-4 text-sm text-fable-muted">No entries use this type yet.</p>
                                @endforelse
                            </div>
                        </section>
                    @endif

                    @if ($this->selectedRecord instanceof Entity)
                        <section class="fable-article-section" aria-labelledby="entity-relationships-title">
                            <div class="flex items-baseline gap-3">
                                <h3 id="entity-relationships-title" class="fable-section-title">Relationships</h3>
                                <span class="font-mono text-xs text-fable-muted">
                                    {{ trans_choice('{0} No relationships|{1} 1 relationship|[2,*] :count relationships', count($this->selectedEntityRelationships), ['count' => count($this->selectedEntityRelationships)]) }}
                                </span>
                            </div>

                            <div class="fable-relationship-groups">
                                @forelse ($this->selectedEntityRelationshipGroups as $group)
                                    <section class="fable-relationship-group" wire:key="entity-relationship-continuity-{{ $group['continuity_id'] }}">
                                        <div class="fable-relationship-group-heading">
                                            <a href="{{ route('milieus.explore', [$milieu, 'continuity', $group['continuity_id']]) }}" wire:navigate>{{ $group['continuity_name'] }}</a>
                                            <span>{{ trans_choice('{1} 1 relationship|[2,*] :count relationships', count($group['relationships']), ['count' => count($group['relationships'])]) }}</span>
                                        </div>

                                        <div class="fable-relationship-list">
                                            @foreach ($group['relationships'] as $relationship)
                                                <article class="fable-relationship-row" wire:key="entity-relationship-{{ $relationship['id'] }}">
                                                    <div class="fable-relationship-statement">
                                                        @if ($relationship['source_id'] === $this->selectedRecord->getKey())
                                                            <span class="fable-relationship-entity is-current">{{ $relationship['source_name'] }}</span>
                                                        @else
                                                            <a class="fable-relationship-entity fable-reference" href="{{ route('milieus.explore', [$milieu, 'entity', $relationship['source_id']]) }}" wire:navigate>
                                                                {{ $relationship['source_name'] }}
                                                            </a>
                                                        @endif

                                                        <span class="fable-relationship-predicate">
                                                            <span aria-hidden="true">-</span>
                                                            <a href="{{ route('milieus.explore', [$milieu, 'ontology_type', $relationship['type_id']]) }}" wire:navigate>
                                                                {{ $relationship['type_name'] }}
                                                            </a>
                                                            <span aria-hidden="true">{{ $relationship['symmetric'] ? '↔' : '→' }}</span>
                                                        </span>

                                                        @if ($relationship['target_id'] === $this->selectedRecord->getKey())
                                                            <span class="fable-relationship-entity is-current">{{ $relationship['target_name'] }}</span>
                                                        @else
                                                            <a class="fable-relationship-entity fable-reference" href="{{ route('milieus.explore', [$milieu, 'entity', $relationship['target_id']]) }}" wire:navigate>
                                                                {{ $relationship['target_name'] }}
                                                            </a>
                                                        @endif
                                                    </div>

                                                    <div class="fable-relationship-meta">
                                                        @if ($relationship['status'])
                                                            <x-fable.canonical-status :status="$relationship['status']" />
                                                        @endif
                                                        @if ($relationship['started_at'])
                                                            <span>From {{ $relationship['started_at'] }}</span>
                                                        @endif
                                                        @if ($relationship['ended_at'])
                                                            <span>Until {{ $relationship['ended_at'] }}</span>
                                                        @endif
                                                        <a href="{{ route('milieus.explore', [$milieu, 'relationship', $relationship['id']]) }}" wire:navigate>Relationship #{{ $relationship['id'] }}</a>
                                                    </div>

                                                    @if ($relationship['description'])
                                                        <p class="fable-relationship-description">{{ $relationship['description'] }}</p>
                                                    @endif
                                                </article>
                                            @endforeach
                                        </div>
                                    </section>
                                @empty
                                    <p class="py-4 text-sm text-fable-muted">No relationships involve this entity yet.</p>
                                @endforelse
                            </div>
                        </section>

                        @if ($this->selectedEntityKnowledge !== [])
                            <section class="fable-article-section" aria-labelledby="entity-knowledge-title">
                                <p class="fable-eyebrow">Knowledge</p>
                                <div class="mt-1 flex items-baseline gap-3">
                                    <h3 id="entity-knowledge-title" class="fable-section-title">What is claimed and believed</h3>
                                    <span class="font-mono text-xs text-fable-muted">{{ collect($this->selectedEntityKnowledge)->sum(fn (array $group): int => count($group['records'])) }}</span>
                                </div>
                                <dl class="fable-context-groups">
                                    @foreach ($this->selectedEntityKnowledge as $group)
                                        <div class="fable-context-group" wire:key="entity-knowledge-{{ $group['key'] }}">
                                            <dt>{{ $group['label'] }}</dt>
                                            <dd>
                                                @foreach ($group['records'] as $related)
                                                    <a href="{{ route('milieus.explore', [$milieu, $group['type'], $related['id']]) }}" wire:navigate>{{ $related['title'] }}</a>
                                                @endforeach
                                            </dd>
                                        </div>
                                    @endforeach
                                </dl>
                            </section>
                        @endif

                        @if ($this->selectedEntityPossibility !== [])
                            <section class="fable-article-section" aria-labelledby="entity-possibility-title">
                                <p class="fable-eyebrow">Possibility</p>
                                <div class="mt-1 flex items-baseline gap-3">
                                    <h3 id="entity-possibility-title" class="fable-section-title">What this entity wants and what opposes it</h3>
                                    <span class="font-mono text-xs text-fable-muted">{{ collect($this->selectedEntityPossibility)->sum(fn (array $group): int => count($group['records'])) }}</span>
                                </div>
                                <dl class="fable-context-groups">
                                    @foreach ($this->selectedEntityPossibility as $group)
                                        <div class="fable-context-group" wire:key="entity-possibility-{{ $group['key'] }}">
                                            <dt>{{ $group['label'] }}</dt>
                                            <dd>
                                                @foreach ($group['records'] as $related)
                                                    <a href="{{ route('milieus.explore', [$milieu, $group['type'], $related['id']]) }}" wire:navigate>
                                                        {{ $related['title'] }}
                                                        @if ($related['status'])
                                                            <span>{{ $related['status'] }}</span>
                                                        @endif
                                                    </a>
                                                @endforeach
                                            </dd>
                                        </div>
                                    @endforeach
                                </dl>
                            </section>
                        @endif

                        @if ($this->selectedFields !== [])
                            <section class="fable-article-section" aria-labelledby="entity-attributes-title">
                                <p class="fable-eyebrow">Record</p>
                                <h3 id="entity-attributes-title" class="fable-section-title mt-1">Attributes</h3>
                                <dl class="fable-facts mt-4">
                                    @foreach ($this->selectedFields as $field)
                                        <div class="fable-fact" wire:key="entity-field-{{ $field['field'] }}" @if (in_array($field['field'], $lastChange['changed_fields'] ?? [], true)) data-fable-changed @endif>
                                            <dt>{{ $field['label'] }}</dt>
                                            <dd>
                                                @if ($field['reference_type'] && filled($field['value']))
                                                    <a class="fable-reference" href="{{ route('milieus.explore', [$milieu, $field['reference_type'], $field['value']]) }}" wire:navigate>
                                                        {{ $field['reference_title'] ?? str($field['reference_type'])->headline().' #'.$field['value'] }}
                                                    </a>
                                                @else
                                                    <x-fable.value :value="$field['value']" :field="$field['field']" />
                                                @endif
                                            </dd>
                                        </div>
                                    @endforeach
                                </dl>
                            </section>
                        @endif
                    @endif

                    @if ($this->selectedRelations !== [])
                        <section class="fable-article-section" aria-labelledby="relations-title">
                            <p class="fable-eyebrow">Graph</p>
                            <h3 id="relations-title" class="fable-section-title mt-1">Linked records</h3>
                            <div class="fable-link-groups">
                                @foreach ($this->selectedRelations as $relation)
                                    <div class="fable-link-group" wire:key="relation-{{ $relation['name'] }}">
                                        <div class="flex items-center justify-between gap-2">
                                            <h4>{{ $relation['label'] }}</h4>
                                            <span class="font-mono text-xs text-fable-muted">{{ count($relation['records']) }}</span>
                                        </div>
                                        <p class="mt-1 text-xs leading-5 text-fable-tertiary">{{ $relation['description'] }}</p>
                                        <div class="col-span-full mt-3 flex flex-col gap-1.5">
                                            @forelse ($relation['records'] as $related)
                                                <a class="fable-reference" href="{{ route('milieus.explore', [$milieu, $relation['type'], $related['id']]) }}" wire:navigate>
                                                    {{ $related['title'] }}
                                                </a>
                                            @empty
                                                <span class="text-xs text-fable-muted">No linked records</span>
                                            @endforelse
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </section>
                    @endif

                    <section class="fable-article-section" aria-labelledby="record-history-title">
                        <p class="fable-eyebrow">Provenance</p>
                        <h3 id="record-history-title" class="fable-section-title mt-1">Recent Changes</h3>
                        <x-fable.activity-list class="mt-3" :change-sets="$this->selectedActivity" compact />
                    </section>
                </article>
            @else
                <div class="fable-reading-empty">
                    <x-app-logo-icon class="size-12 text-brass" />
                    <h2 class="font-serif text-2xl font-semibold text-fable-primary">Choose a record</h2>
                    <p>Select an item from the collection to inspect its state, links, provenance, and recent changes.</p>
                </div>
            @endif
        </section>
    </div>
</div>
