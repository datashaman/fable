<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="fable-consent-body antialiased">
        <div class="fable-consent-shell">
            <div class="fable-auth-shell">
                <a href="{{ route('home') }}" class="fable-auth-brand" wire:navigate>
                    <span class="fable-consent-mark" aria-hidden="true">
                        <x-app-logo-icon />
                    </span>
                    <span class="sr-only">{{ config('app.name', 'Laravel') }}</span>
                </a>
                <div class="fable-auth-card">
                    {{ $slot }}
                </div>
            </div>
        </div>

        @persist('toast')
            <flux:toast.group>
                <flux:toast />
            </flux:toast.group>
        @endpersist

        @fluxScripts
    </body>
</html>
