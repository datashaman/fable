<?php

use App\Livewire\ReadonlyPage;
use App\Enums\CanonicalStatus;
use App\Enums\OntologyCategory;
use App\Models\Belief;
use App\Models\Claim;
use App\Models\ChangeSet;
use App\Models\Continuity;
use App\Models\Entity;
use App\Models\Milieu;
use App\Models\OntologyType;
use App\Models\Relationship;
use App\Support\Fable\PresentationRegistry;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Gate;
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

    /** @return LengthAwarePaginator<int, Model> */
    #[Computed]
    public function records(): LengthAwarePaginator
    {
        return $this->presentation
            ->searchableQuery($this->milieu, $this->recordType, $this->search, $this->continuity)
            ->paginate($this->recordType === 'ontology_type' ? 100 : 18, pageName: 'records');
    }

    /** @return list<array{key: string, label: string|null, records: Collection<int, Model>}> */
    #[Computed]
    public function recordGroups(): array
    {
        $records = $this->records->getCollection();

        if ($records->isEmpty()) {
            return [];
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

            if ($field !== $this->definition['title'] && is_string($value) && filled($value)) {
                return ['field' => $field, 'value' => $value];
            }
        }

        return null;
    }

    /** @return list<array{field: string, label: string, value: mixed, reference_type: string|null, reference_title: string|null}> */
    #[Computed]
    public function selectedFields(): array
    {
        if ($this->selectedRecord === null) {
            return [];
        }

        $hiddenFields = array_filter([
            'id',
            'revision',
            $this->definition['title'],
            $this->definition['status'] ?? null,
            $this->selectedLead['field'] ?? null,
        ]);
        $fields = array_values(array_filter(
            $this->presentation->fields($this->selectedRecord),
            fn (array $field): bool => ! in_array($field['field'], $hiddenFields, true),
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
        unset($this->records, $this->recordGroups, $this->selectedRecord, $this->selectedLead, $this->selectedFields, $this->selectedRelations, $this->selectedEntries, $this->selectedEntityRelationships, $this->selectedEntityRelationshipGroups, $this->selectedActivity);
    }
}; ?>

<div class="fable-explorer">
    <header class="fable-explorer-header">
        <div class="min-w-0">
            <div class="flex items-center gap-2 text-xs text-fable-tertiary">
                <a href="{{ route('milieus.show', $milieu) }}" wire:navigate class="hover:text-brass">{{ $milieu->name }}</a>
                <span>/</span>
                <span class="capitalize">{{ $this->definition['stratum'] }}</span>
            </div>
            <h1 class="fable-display mt-2">{{ $this->definition['plural'] }}</h1>
        </div>
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
            </div>

            <div class="fable-record-list">
                @forelse ($this->recordGroups as $group)
                    <section class="fable-record-group" wire:key="record-group-{{ $group['key'] }}">
                        @if ($group['label'])
                            <div class="fable-record-group-heading">
                                <h2>{{ $group['label'] }}</h2>
                                <span>{{ trans_choice('{1} 1 type|[2,*] :count types', $group['records']->count(), ['count' => $group['records']->count()]) }}</span>
                            </div>
                        @endif

                        @foreach ($group['records'] as $item)
                            <a
                                href="{{ route('milieus.explore', [$milieu, $recordType, $item->getKey(), 'q' => $search, 'continuity' => $continuity]) }}"
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
                                                    'min-w-0 truncate' => $summary['field'] !== 'temporal_range',
                                                    'shrink-0 whitespace-nowrap font-mono text-[0.6875rem] tabular-nums' => $summary['field'] === 'temporal_range',
                                                ])>{{ is_scalar($summary['value']) ? $summary['value'] : json_encode($summary['value']) }}</span>
                                            @endforeach
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

            @if ($this->records->hasPages())
                <div class="border-t border-fable-soft p-3">
                    <flux:pagination :paginator="$this->records" />
                </div>
            @endif
        </section>

        <section class="fable-reading-pane" aria-label="Selected record detail">
            @if ($this->selectedRecord)
                <article class="fable-record-article" @if (($lastChange['record_type'] ?? null) === $recordType && ($lastChange['record_id'] ?? null) === $record) data-fable-changed @endif>
                    <header class="fable-record-masthead">
                        <div class="min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="fable-eyebrow">{{ $this->definition['label'] }}</span>
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
                        </div>
                        <div class="fable-record-margin">
                            <span>{{ $this->definition['label'] }} #{{ $this->selectedRecord->getKey() }}</span>
                            @if ($this->selectedRecord->getAttribute('revision') !== null)
                                <span>revision {{ $this->selectedRecord->getAttribute('revision') }}</span>
                            @endif
                        </div>
                    </header>

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
                                        <div class="col-span-full mt-3 flex flex-wrap gap-2">
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
                    <p>Select an item from the collection to inspect its state, links, provenance, and MCP history.</p>
                </div>
            @endif
        </section>
    </div>
</div>
