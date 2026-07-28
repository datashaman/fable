@props([
    'sidebar' => false,
])

@if($sidebar)
    <flux:sidebar.brand :name="config('app.name', 'Laravel')" {{ $attributes }}>
        <x-slot name="logo" class="flex aspect-square !size-7 items-center justify-center rounded-sm border border-brass/35 bg-brass/10 text-brass">
            <x-app-logo-icon class="size-5" />
        </x-slot>
    </flux:sidebar.brand>
@else
    <flux:brand :name="config('app.name', 'Laravel')" {{ $attributes }}>
        <x-slot name="logo" class="flex aspect-square size-8 items-center justify-center rounded-md border border-brass/35 bg-brass/10 text-brass">
            <x-app-logo-icon class="size-6" />
        </x-slot>
    </flux:brand>
@endif
