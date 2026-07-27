@props(['changeSets', 'compact' => false])

<div {{ $attributes->class(['fable-ledger', 'fable-ledger-compact' => $compact]) }}>
    @forelse ($changeSets as $changeSet)
        <article class="fable-ledger-entry" wire:key="change-set-{{ $changeSet->id }}">
            <div class="fable-ledger-line" aria-hidden="true"></div>
            <div class="min-w-0 flex-1">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <div class="flex min-w-0 items-center gap-2">
                        <span class="fable-tool-name">{{ $changeSet->tool_name }}</span>
                        @if ($changeSet->milieu ?? null)
                            <span class="truncate text-xs text-fable-tertiary">{{ $changeSet->milieu->name }}</span>
                        @endif
                    </div>
                    <time class="font-mono text-[0.6875rem] text-fable-muted" datetime="{{ $changeSet->created_at?->toIso8601String() }}">
                        {{ $changeSet->created_at?->diffForHumans() }}
                    </time>
                </div>
                <p class="mt-1 text-sm leading-5 text-fable-secondary">{{ $changeSet->summary ?: 'State changed' }}</p>
                <div class="mt-2 flex flex-wrap gap-1.5">
                    @foreach ($changeSet->entries as $entry)
                        <span class="fable-change-chip">
                            {{ str($entry->record_type)->headline() }} #{{ $entry->record_id ?? '—' }} · {{ $entry->action }}
                        </span>
                    @endforeach
                </div>
                @unless ($compact)
                    <div class="mt-2 flex items-center justify-between gap-2 text-xs text-fable-muted">
                        <span>{{ $changeSet->user?->name ?? 'System' }}</span>
                        <span class="font-mono">{{ $changeSet->id }}</span>
                    </div>
                @endunless
            </div>
        </article>
    @empty
        <div @class(['fable-ledger-empty', 'is-compact' => $compact])>
            <p>No MCP changes have been recorded yet.</p>
        </div>
    @endforelse
</div>
