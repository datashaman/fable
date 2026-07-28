<?php

use App\Livewire\ReadonlyPage;
use App\Models\ChangeSet;
use App\Models\Milieu;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;

new #[Title('Milieu shelf')] class extends ReadonlyPage {
    #[Url(as: 'q', except: '')]
    public string $search = '';

    /** @return Collection<int, Milieu> */
    #[Computed]
    public function milieus(): Collection
    {
        /** @var User $user */
        $user = Auth::user();

        return Milieu::query()
            ->where(fn (Builder $query) => $query
                ->whereBelongsTo($user, 'owner')
                ->orWhereHas('memberships', fn (Builder $query) => $query->whereBelongsTo($user)))
            ->when(filled($this->search), fn (Builder $query) => $query->where(function (Builder $query): void {
                $search = str($this->search)->squish()->toString();
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('genre', 'like', "%{$search}%");
            }))
            ->with([
                'memberships' => fn ($query) => $query->whereBelongsTo($user),
                'latestChangeSet',
            ])
            ->withCount([
                'continuities',
                'ontologyTypes',
                'entities',
                'relationships',
                'events',
                'rules',
                'claims',
                'beliefs',
                'perspectives',
                'scenarios',
                'goals',
                'conflicts',
                'stories',
                'sagas',
            ])
            ->selectSub(
                fn ($query) => $query
                    ->from('scenes')
                    ->join('stories', 'stories.id', '=', 'scenes.story_id')
                    ->whereColumn('stories.milieu_id', 'milieus.id')
                    ->selectRaw('count(*)'),
                'scenes_count',
            )
            ->selectSub(
                fn ($query) => $query
                    ->from('disclosures')
                    ->whereColumn('disclosures.milieu_id', 'milieus.id')
                    ->selectRaw('count(*)'),
                'disclosures_count',
            )
            ->orderByDesc(
                ChangeSet::query()
                    ->select('created_at')
                    ->whereColumn('change_sets.milieu_id', 'milieus.id')
                    ->latest()
                    ->limit(1),
            )
            ->orderBy('name')
            ->get();
    }

    #[Computed]
    public function changesToday(): int
    {
        return ChangeSet::query()
            ->whereIn('milieu_id', $this->milieus->modelKeys())
            ->whereDate('created_at', today())
            ->count();
    }

    /** @return Collection<int, ChangeSet> */
    #[Computed]
    public function activity(): Collection
    {
        return ChangeSet::query()
            ->whereIn('milieu_id', $this->milieus->modelKeys())
            ->with(['milieu:id,name', 'user:id,name', 'entries'])
            ->latest()
            ->limit(4)
            ->get();
    }

    /** @param array<string, mixed> $event */
    protected function refreshState(array $event): void
    {
        unset($this->milieus, $this->activity, $this->changesToday);
    }
}; ?>

<div class="fable-page">
    <div class="fable-observatory-bar">
        <flux:input wire:model.live.debounce.300ms="search" icon="magnifying-glass" placeholder="Search milieus…" clearable />
        <span class="fable-observatory-stat"><strong>{{ $this->changesToday }}</strong> changes today</span>
    </div>

    <header class="fable-page-header">
        <div>
            <p class="fable-eyebrow">Archive</p>
            <h1 class="fable-display">Your milieus</h1>
            <p class="fable-page-intro">Explore the worlds, histories, and possibilities gathered in each milieu.</p>
        </div>
    </header>

    <div class="fable-observatory-layout">
        <section class="min-w-0" aria-labelledby="milieu-shelf-title">
            <div class="fable-section-heading">
                <div>
                    <p class="fable-eyebrow">Milieus</p>
                    <h2 id="milieu-shelf-title" class="fable-section-title">Milieu index</h2>
                </div>
                <span class="font-mono text-xs text-fable-muted">{{ $this->milieus->count() }} · by last change</span>
            </div>

            <div class="fable-milieu-index">
                @forelse ($this->milieus as $milieu)
                    @php
                        $role = $milieu->owner_id === auth()->id()
                            ? 'owner'
                            : ($milieu->memberships->first()?->role?->value ?? 'viewer');
                    @endphp
                    <article
                        class="fable-milieu-folio group"
                        wire:key="milieu-{{ $milieu->id }}"
                        @if (($lastChange['milieu_id'] ?? null) === $milieu->id) data-fable-changed @endif
                    >
                        <span class="fable-folio-index">#{{ $milieu->id }}</span>

                        <div class="min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="fable-status">{{ $milieu->status->value }}</span>
                                <span class="fable-role">{{ $role }}</span>
                                <span class="fable-folio-mobile-recency">
                                    {{ $milieu->latestChangeSet?->created_at?->diffForHumans() ?? 'Never changed' }}
                                </span>
                            </div>
                            <a href="{{ route('milieus.show', $milieu) }}" wire:navigate>
                                <h3 class="fable-folio-title">{{ $milieu->name }}</h3>
                            </a>
                            <p class="fable-folio-summary">{{ $milieu->description ?: 'An undescribed milieu awaiting observation.' }}</p>
                            <div class="fable-folio-meta">
                                <span>{{ $milieu->genre ?: 'Genre unclassified' }}</span>
                                <span class="font-mono">revision {{ $milieu->revision }}</span>
                            </div>
                            <nav class="fable-folio-collections" aria-label="{{ $milieu->name }} collections">
                                <a href="{{ route('milieus.explore', [$milieu, 'entity']) }}" wire:navigate>Entities <strong>{{ $milieu->entities_count }}</strong></a>
                                <a href="{{ route('milieus.explore', [$milieu, 'relationship']) }}" wire:navigate>Relationships <strong>{{ $milieu->relationships_count }}</strong></a>
                                <a href="{{ route('milieus.explore', [$milieu, 'claim']) }}" wire:navigate>Claims <strong>{{ $milieu->claims_count }}</strong></a>
                                <a href="{{ route('milieus.explore', [$milieu, 'story']) }}" wire:navigate>Stories <strong>{{ $milieu->stories_count }}</strong></a>
                            </nav>
                        </div>

                        <div class="fable-folio-notes" aria-label="Record counts by milieu stratum">
                            <span class="fable-folio-recency">
                                {{ $milieu->latestChangeSet?->created_at?->diffForHumans() ?? 'Never changed' }}
                            </span>
                            <span><strong>{{ $milieu->entities_count + $milieu->relationships_count + $milieu->events_count + $milieu->rules_count }}</strong> canon</span>
                            <span><strong>{{ $milieu->claims_count + $milieu->beliefs_count + $milieu->perspectives_count }}</strong> knowledge</span>
                            <span><strong>{{ $milieu->scenarios_count + $milieu->goals_count + $milieu->conflicts_count }}</strong> possibility</span>
                            <span><strong>{{ $milieu->stories_count + $milieu->scenes_count + $milieu->disclosures_count + $milieu->sagas_count }}</strong> narrative</span>
                        </div>
                    </article>
                @empty
                    <div class="fable-empty">
                        <x-app-logo-icon class="size-10 text-brass" />
                        <h3 class="font-serif text-xl text-fable-primary">The shelf is empty</h3>
                        <p>Create or share a milieu through the Fable MCP server and it will appear here in realtime.</p>
                    </div>
                @endforelse
            </div>
        </section>

        <aside class="fable-margin-ledger" aria-labelledby="live-ledger-title">
            <div class="fable-section-heading">
                <div>
                    <p class="fable-eyebrow">Realtime</p>
                    <h2 id="live-ledger-title" class="fable-section-title">Recent Changes</h2>
                </div>
            </div>
            <x-fable.activity-list :change-sets="$this->activity" compact />
        </aside>
    </div>

    @if ($latestChange = $this->activity->first())
        @php
            $latestEntry = $latestChange->entries->first();
            $latestChangeUrl = match (true) {
                $latestEntry?->record_type === 'milieu' => route('milieus.show', $latestChange->milieu_id),
                filled($latestEntry?->record_id) => route('milieus.explore', [$latestChange->milieu_id, $latestEntry->record_type, $latestEntry->record_id]),
                default => route('milieus.show', $latestChange->milieu_id),
            };
        @endphp
        <a class="fable-mobile-activity" href="{{ $latestChangeUrl }}" wire:navigate>
            <span class="fable-mobile-activity-dot" aria-hidden="true"></span>
            <span>{{ $latestChange->summary ?: 'State changed' }} · {{ $latestChange->created_at?->diffForHumans() }}</span>
            <strong>{{ $this->changesToday }} today <span aria-hidden="true">↑</span></strong>
        </a>
    @endif
</div>
