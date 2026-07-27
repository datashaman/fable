<?php

use App\Livewire\ReadonlyPage;
use App\Models\ChangeSet;
use App\Models\Milieu;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\WithPagination;

new #[Title('MCP activity')] class extends ReadonlyPage {
    use WithPagination;

    public Milieu $milieu;

    #[Url(as: 'q', except: '')]
    public string $search = '';

    #[Url(except: '')]
    public string $action = '';

    public function mount(Milieu $milieu): void
    {
        Gate::authorize('view', $milieu);
        $this->milieu = $milieu;
    }

    public function updatedSearch(): void
    {
        $this->resetPage('activity');
    }

    public function updatedAction(): void
    {
        $this->resetPage('activity');
    }

    /** @return LengthAwarePaginator<int, ChangeSet> */
    #[Computed]
    public function changes(): LengthAwarePaginator
    {
        return $this->milieu->changeSets()
            ->when($this->search !== '', fn (Builder $query) => $query->where(fn (Builder $query) => $query
                ->where('summary', 'like', "%{$this->search}%")
                ->orWhere('tool_name', 'like', "%{$this->search}%")))
            ->when($this->action !== '', fn (Builder $query) => $query->whereHas(
                'entries',
                fn (Builder $query) => $query->where('action', $this->action),
            ))
            ->with(['user:id,name', 'entries'])
            ->latest()
            ->paginate(30, pageName: 'activity');
    }

    /** @param array<string, mixed> $event */
    protected function refreshState(array $event): void
    {
        unset($this->changes);
    }
}; ?>

<div class="fable-page max-w-5xl">
    <header class="fable-page-header">
        <div>
            <div class="flex items-center gap-2 text-xs text-fable-tertiary">
                <a href="{{ route('milieus.show', $milieu) }}" wire:navigate class="hover:text-brass">{{ $milieu->name }}</a>
                <span>/</span>
                <span>Activity</span>
            </div>
            <h1 class="fable-display mt-2">MCP ledger</h1>
            <p class="fable-page-intro">The durable record of every agent-authored change to this world.</p>
        </div>
        <x-fable.connection-status />
    </header>

    <section class="fable-ledger-page" aria-label="MCP change history">
        <div class="grid gap-3 sm:grid-cols-[minmax(0,1fr)_12rem]">
            <flux:input wire:model.live.debounce.300ms="search" icon="magnifying-glass" placeholder="Search summaries or tools…" clearable />
            <flux:select wire:model.live="action" aria-label="Filter by action">
                <flux:select.option value="">Every action</flux:select.option>
                <flux:select.option value="created">Created</flux:select.option>
                <flux:select.option value="updated">Updated</flux:select.option>
                <flux:select.option value="removed">Removed</flux:select.option>
                <flux:select.option value="applied">Applied</flux:select.option>
            </flux:select>
        </div>

        <x-fable.activity-list class="mt-8" :change-sets="$this->changes" />

        @if ($this->changes->hasPages())
            <div class="mt-5 border-t border-fable-soft pt-4">
                <flux:pagination :paginator="$this->changes" />
            </div>
        @endif
    </section>
</div>
