@props(['value', 'field' => null])

@if (is_array($value))
    @if ($value === [])
        <span class="text-fable-muted">Empty</span>
    @elseif (! array_is_list($value))
        <dl class="fable-map">
            @foreach ($value as $key => $item)
                <div class="fable-map-row">
                    <dt>{{ str((string) $key)->headline() }}</dt>
                    <dd><x-fable.value :value="$item" /></dd>
                </div>
            @endforeach
        </dl>
    @elseif (collect($value)->every(fn (mixed $item): bool => is_scalar($item) || $item === null))
        @if ($field === 'tags')
            <ul class="fable-tag-list" aria-label="Tags">
                @foreach ($value as $item)
                    <li class="fable-tag" wire:key="tag-{{ $loop->index }}-{{ md5((string) $item) }}">{{ $item }}</li>
                @endforeach
            </ul>
        @else
            <ul class="fable-value-list">
                @foreach ($value as $item)
                    <li class="fable-value-list-item" wire:key="value-{{ $field ?? 'list' }}-{{ $loop->index }}-{{ md5((string) $item) }}">
                        <x-fable.value :value="$item" />
                    </li>
                @endforeach
            </ul>
        @endif
    @else
        <pre class="fable-code-block">{{ json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) }}</pre>
    @endif
@elseif (is_bool($value))
    <span>{{ $value ? 'Yes' : 'No' }}</span>
@elseif ($value === null || $value === '')
    <span class="text-fable-muted">Not set</span>
@else
    <span class="whitespace-pre-wrap">{{ $value }}</span>
@endif
