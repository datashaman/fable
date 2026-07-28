@props(['changeSets', 'compact' => false])
@inject('presentation', 'App\Support\Fable\PresentationRegistry')

<div {{ $attributes->class(['fable-ledger', 'fable-ledger-compact' => $compact]) }}>
    @forelse ($changeSets as $changeSet)
        <article class="fable-ledger-entry" wire:key="change-set-{{ $changeSet->id }}">
            <div class="fable-ledger-line" aria-hidden="true"></div>
            <div class="min-w-0 flex-1">
                <div class="fable-ledger-heading">
                    <span class="fable-tool-name">{{ $changeSet->tool_name }}</span>
                    <time class="font-mono text-[0.6875rem] text-fable-muted" datetime="{{ $changeSet->created_at?->toIso8601String() }}">
                        {{ $changeSet->created_at?->diffForHumans() }}
                    </time>
                </div>

                @if ($changeSet->milieu ?? null)
                    <span class="fable-ledger-milieu">{{ $changeSet->milieu->name }}</span>
                @endif

                <div class="fable-ledger-actions">
                    @foreach ($changeSet->entries as $entry)
                        @php
                            $entryUrl = match (true) {
                                $entry->record_type === 'milieu' => route('milieus.show', $changeSet->milieu_id),
                                filled($entry->record_id) => route('milieus.explore', [$changeSet->milieu_id, $entry->record_type, $entry->record_id]),
                                default => null,
                            };
                            $entryTitle = $presentation->changeEntryTitle($changeSet->milieu, $entry);
                            $entryAction = str($entry->action)->replace('_', ' ')->lower();
                        @endphp
                        @if ($entryUrl)
                            <a class="fable-ledger-action" href="{{ $entryUrl }}" wire:navigate>
                                <span>{{ $entryTitle }}</span> {{ $entryAction }}
                            </a>
                        @else
                            <span class="fable-ledger-action">
                                <span>{{ $entryTitle }}</span> {{ $entryAction }}
                            </span>
                        @endif
                    @endforeach
                </div>

                @if ($changeSet->entries->isEmpty())
                    <p class="mt-2 text-sm leading-5 text-fable-secondary">{{ $changeSet->summary ?: 'State changed' }}</p>
                @endif

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
            <p>No changes have been recorded yet.</p>
        </div>
    @endforelse
</div>
