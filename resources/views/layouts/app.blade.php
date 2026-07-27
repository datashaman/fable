<x-app-shell :title="$title ?? null">
    <flux:main class="!max-w-none">
        {{ $slot }}
    </flux:main>
</x-app-shell>
