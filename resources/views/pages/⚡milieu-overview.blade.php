<?php

use App\Livewire\ReadonlyPage;
use App\Models\ChangeSet;
use App\Models\Continuity;
use App\Models\Milieu;
use App\Support\Fable\ContinuityValidator;
use App\Support\Fable\PresentationRegistry;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;

new #[Title('Milieu observatory')] class extends ReadonlyPage {
    public Milieu $milieu;

    protected PresentationRegistry $presentation;
    protected ContinuityValidator $validator;

    public function boot(PresentationRegistry $presentation, ContinuityValidator $validator): void
    {
        $this->presentation = $presentation;
        $this->validator = $validator;
    }

    public function mount(Milieu $milieu): void
    {
        Gate::authorize('view', $milieu);
        $this->milieu = $milieu;
    }

    /** @return array<string, array<string, int>> */
    #[Computed]
    public function strataCounts(): array
    {
        $counts = [];

        foreach ($this->presentation->strata() as $stratum => $types) {
            foreach ($types as $type) {
                $counts[$stratum][$type] = $this->presentation->query($this->milieu, $type)->count();
            }
        }

        return $counts;
    }

    /** @return Collection<int, Continuity> */
    #[Computed]
    public function continuities(): Collection
    {
        return $this->milieu->continuities()->with('parent:id,name')->orderBy('name')->get();
    }

    /** @return array{valid: bool, issue_count: int} */
    #[Computed]
    public function validation(): array
    {
        $results = $this->continuities->map(fn (Continuity $continuity): array => $this->validator->validate($continuity));
        $issueCount = (int) $results->sum('issue_count');

        return ['valid' => $issueCount === 0, 'issue_count' => $issueCount];
    }

    /** @return Collection<int, ChangeSet> */
    #[Computed]
    public function activity(): Collection
    {
        return $this->milieu->changeSets()
            ->with(['user:id,name', 'entries'])
            ->latest()
            ->limit(10)
            ->get();
    }

    /** @param array<string, mixed> $event */
    protected function refreshState(array $event): void
    {
        unset($this->strataCounts, $this->continuities, $this->validation, $this->activity);
    }
}; ?>

<div class="fable-page">
    <header class="fable-milieu-header" @if (($lastChange['milieu_id'] ?? null) === $milieu->id) data-fable-changed @endif>
        <div class="min-w-0">
            <div class="flex flex-wrap items-center gap-2">
                <span class="fable-status">{{ $milieu->status->value }}</span>
                <span class="fable-role">{{ $milieu->roleFor(auth()->user()) }}</span>
                <span class="font-mono text-xs text-fable-muted">rev {{ $milieu->revision }}</span>
            </div>
            <h1 class="fable-display mt-3">{{ $milieu->name }}</h1>
            <p class="fable-page-intro">{{ $milieu->description ?: 'No milieu description has been recorded.' }}</p>
            <div class="mt-4 flex flex-wrap gap-x-5 gap-y-2 text-xs text-fable-tertiary">
                <span><strong class="font-medium text-fable-secondary">Genre</strong> {{ $milieu->genre ?: 'Unclassified' }}</span>
                <span><strong class="font-medium text-fable-secondary">Now</strong> {{ $milieu->current_time ?: 'Unspecified' }}</span>
                <span><strong class="font-medium text-fable-secondary">Scope</strong> {{ $milieu->spatial_scope ?: 'Unspecified' }}</span>
            </div>
        </div>
    </header>

    <section aria-labelledby="strata-title">
        <div class="fable-section-heading">
            <div>
                <p class="fable-eyebrow">Milieu model</p>
                <h2 id="strata-title" class="fable-section-title">State strata</h2>
            </div>
            <div class="flex items-center gap-2">
                <span class="fable-validation {{ $this->validation['valid'] ? 'is-valid' : 'is-invalid' }}">
                    {{ $this->validation['valid'] ? 'Continuities valid' : $this->validation['issue_count'].' validation issues' }}
                </span>
            </div>
        </div>

        <div class="fable-cosmology">
            @foreach ($this->strataCounts as $stratum => $counts)
                <section class="fable-cosmology-row" wire:key="stratum-{{ $stratum }}">
                    <div class="fable-cosmology-index">
                        <span>0{{ $loop->iteration }}</span>
                    </div>
                    <div class="fable-cosmology-copy">
                        <h3>{{ str($stratum)->headline() }}</h3>
                        <p>{{ match ($stratum) {
                            'world' => 'The frame: branches and ontology',
                            'canon' => 'What exists and what happened',
                            'knowledge' => 'What is claimed, believed, and seen',
                            'possibility' => 'What may happen and what drives it',
                            'narrative' => 'How the world is disclosed',
                        } }}</p>
                    </div>
                    <nav class="fable-cosmology-links" aria-label="{{ str($stratum)->headline() }} records">
                        @foreach ($counts as $type => $count)
                            <a href="{{ route('milieus.explore', [$milieu, $type]) }}" wire:navigate>
                                <span>{{ str($type)->replace('_', ' ')->plural()->headline() }}</span>
                                <strong>{{ $count }}</strong>
                            </a>
                        @endforeach
                    </nav>
                </section>
            @endforeach
        </div>
    </section>

    <div class="fable-observatory-layout">
        <section aria-labelledby="continuities-title">
            <div class="fable-section-heading">
                <div>
                    <p class="fable-eyebrow">Branches</p>
                    <h2 id="continuities-title" class="fable-section-title">Continuities</h2>
                </div>
                <a class="fable-text-link" href="{{ route('milieus.explore', [$milieu, 'continuity']) }}" wire:navigate>Explore all</a>
            </div>
            <div class="fable-branch-list">
                @forelse ($this->continuities as $continuity)
                    <a class="fable-branch" href="{{ route('milieus.explore', [$milieu, 'continuity', $continuity->id]) }}" wire:navigate wire:key="continuity-{{ $continuity->id }}">
                        <span class="fable-branch-node" aria-hidden="true"></span>
                        <span class="min-w-0 flex-1">
                            <span class="fable-branch-title">{{ $continuity->name }}</span>
                            <span class="fable-branch-summary">{{ $continuity->description ?: 'No divergence note.' }}</span>
                            @if ($continuity->parent)
                                <span class="fable-branch-origin">Branches from {{ $continuity->parent->name }}</span>
                            @endif
                        </span>
                        <span class="fable-branch-state">
                            {{ $continuity->canonical_status->value }} · rev {{ $continuity->revision }}
                        </span>
                    </a>
                @empty
                    <div class="fable-empty md:col-span-2">No continuities have been defined.</div>
                @endforelse
            </div>
        </section>

        <aside class="fable-margin-ledger" aria-labelledby="milieu-ledger-title">
            <div class="fable-section-heading">
                <div>
                    <p class="fable-eyebrow">Recent</p>
                    <h2 id="milieu-ledger-title" class="fable-section-title">Recent Changes</h2>
                </div>
                <a class="fable-text-link" href="{{ route('milieus.activity', $milieu) }}" wire:navigate>Full history</a>
            </div>
            <x-fable.activity-list :change-sets="$this->activity" compact />
        </aside>
    </div>
</div>
