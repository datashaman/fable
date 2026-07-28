@props(['status'])

@php
    $canonicalStatus = $status instanceof \App\Enums\CanonicalStatus
        ? $status
        : \App\Enums\CanonicalStatus::from((string) $status);
    [$icon, $color] = match ($canonicalStatus) {
        \App\Enums\CanonicalStatus::Canonical => ['check-circle', 'text-ledger-green'],
        \App\Enums\CanonicalStatus::Proposed => ['clock', 'text-brass'],
        \App\Enums\CanonicalStatus::Disputed => ['exclamation-circle', 'text-archive-red'],
        \App\Enums\CanonicalStatus::Obsolete => ['minus-circle', 'text-fable-muted'],
    };
    $label = str($canonicalStatus->value)->headline()->toString();
@endphp

<span {{ $attributes->class(['inline-flex size-4 shrink-0 items-center justify-center', $color]) }}>
    <flux:tooltip :content="$label">
        <span
            class="inline-flex size-full items-center justify-center"
            role="img"
            aria-label="Status: {{ $label }}"
            data-fable-canonical-status="{{ $canonicalStatus->value }}"
            data-icon="{{ $icon }}"
            tabindex="0"
        >
            <flux:icon :name="$icon" variant="mini" class="size-full" aria-hidden="true" />
        </span>
    </flux:tooltip>
</span>
