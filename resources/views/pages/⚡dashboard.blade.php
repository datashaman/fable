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

new #[Title('World shelf')] class extends ReadonlyPage {
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
            ->with([
                'memberships' => fn ($query) => $query->whereBelongsTo($user),
                'latestChangeSet',
            ])
            ->withCount(['entities', 'events', 'claims', 'stories'])
            ->orderBy('name')
            ->get();
    }

    /** @return Collection<int, ChangeSet> */
    #[Computed]
    public function activity(): Collection
    {
        return ChangeSet::query()
            ->whereIn('milieu_id', $this->milieus->modelKeys())
            ->with(['milieu:id,name', 'user:id,name', 'entries'])
            ->latest()
            ->limit(14)
            ->get();
    }

    /** @param array<string, mixed> $event */
    protected function refreshState(array $event): void
    {
        unset($this->milieus, $this->activity);
    }
}; ?>

<div class="fable-page">
    <header class="fable-page-header">
        <div>
            <p class="fable-eyebrow">Workspace observatory</p>
            <h1 class="fable-display">Your worlds</h1>
            <p class="fable-page-intro">Inspect the state your agents shape through MCP. This workspace never writes to the world.</p>
        </div>
    </header>

    <div class="fable-observatory-layout">
        <section class="min-w-0" aria-labelledby="world-shelf-title">
            <div class="fable-section-heading">
                <div>
                    <p class="fable-eyebrow">Milieus</p>
                    <h2 id="world-shelf-title" class="fable-section-title">World index</h2>
                </div>
                <span class="font-mono text-xs text-fable-muted">{{ $this->milieus->count() }} accessible</span>
            </div>

            <div class="fable-world-index">
                @forelse ($this->milieus as $milieu)
                    @php
                        $role = $milieu->owner_id === auth()->id()
                            ? 'owner'
                            : ($milieu->memberships->first()?->role?->value ?? 'viewer');
                    @endphp
                    <a
                        href="{{ route('milieus.show', $milieu) }}"
                        class="fable-world-folio group"
                        wire:navigate
                        wire:key="milieu-{{ $milieu->id }}"
                        @if (($lastChange['milieu_id'] ?? null) === $milieu->id) data-fable-changed @endif
                    >
                        <span class="fable-folio-index">{{ str($loop->iteration)->padLeft(2, '0') }}</span>

                        <div class="min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="fable-status">{{ $milieu->status->value }}</span>
                                <span class="fable-role">{{ $role }}</span>
                            </div>
                            <h3 class="fable-folio-title">{{ $milieu->name }}</h3>
                            <p class="fable-folio-summary">{{ $milieu->description ?: 'An undescribed world awaiting observation.' }}</p>
                            <div class="fable-folio-meta">
                                <span>{{ $milieu->genre ?: 'Genre unclassified' }}</span>
                                <span class="font-mono">revision {{ $milieu->revision }}</span>
                            </div>
                        </div>

                        <div class="fable-folio-notes" aria-label="Record counts by world stratum">
                            <span><strong>{{ $milieu->entities_count + $milieu->events_count }}</strong> canon</span>
                            <span><strong>{{ $milieu->claims_count }}</strong> knowledge</span>
                            <span><strong>{{ $milieu->stories_count }}</strong> narrative</span>
                        </div>
                    </a>
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
</div>
