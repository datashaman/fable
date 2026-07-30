@props([
    'title',
    'description',
])

<div class="flex w-full flex-col text-center">
    <h1 class="fable-auth-title">{{ $title }}</h1>
    <p class="fable-auth-subtitle">{{ $description }}</p>
</div>
