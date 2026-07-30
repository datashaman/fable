<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="fable-consent-body">
        <div class="fable-home">
            <header class="fable-home-nav">
                <div class="fable-home-brand" aria-label="{{ config('app.name', 'Fable') }}">
                    <span class="fable-consent-mark" aria-hidden="true">
                        <x-app-logo-icon />
                    </span>
                    <span>{{ config('app.name', 'Fable') }}</span>
                </div>

                <nav class="fable-home-nav-links">
                    @auth
                        <a href="{{ route('dashboard') }}" class="fable-consent-button fable-consent-button-primary" wire:navigate>
                            Dashboard
                        </a>
                    @else
                        @if (Route::has('login'))
                            <a href="{{ route('login') }}" class="fable-text-link" wire:navigate>Log in</a>
                        @endif
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="fable-consent-button fable-consent-button-primary" wire:navigate>
                                Register
                            </a>
                        @endif
                    @endauth
                </nav>
            </header>

            <main class="fable-home-hero">
                <p class="fable-eyebrow">Model Context Protocol server</p>
                <h1 class="fable-display">Build worlds an agent can remember.</h1>
                <p class="fable-page-intro fable-home-lead">
                    Fable lets AI agents collaboratively build and query a serialized narrative world model — entities, relationships, events, claims, beliefs, and stories. It's the kind of continuity bible a writers' room keeps by hand, but structured, revisioned, and queryable. A web UI lets humans browse the same state agents are editing.
                </p>

                <div class="fable-consent-actions fable-home-actions">
                    @auth
                        <a href="{{ route('dashboard') }}" class="fable-consent-button fable-consent-button-primary" wire:navigate>
                            Dashboard
                        </a>
                    @else
                        @if (Route::has('login'))
                            <a href="{{ route('login') }}" class="fable-consent-button fable-consent-button-secondary" wire:navigate>
                                Log in
                            </a>
                        @endif
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="fable-consent-button fable-consent-button-primary" wire:navigate>
                                Register
                            </a>
                        @endif
                    @endauth
                </div>
            </main>

            <section class="fable-home-features" aria-label="How Fable is organized">
                <article class="fable-home-feature">
                    <p class="fable-eyebrow">Milieus</p>
                    <h2 class="fable-section-title">Story worlds, scoped</h2>
                    <p>Every record — entity, event, claim, scene — belongs to a milieu, owned or shared with editors and viewers.</p>
                </article>

                <article class="fable-home-feature">
                    <p class="fable-eyebrow">Revisioned</p>
                    <h2 class="fable-section-title">Nothing overwritten silently</h2>
                    <p>Every record carries a revision counter. Mutations are optimistic-concurrency checked, and every change lands in an append-only audit log.</p>
                </article>

                <article class="fable-home-feature">
                    <p class="fable-eyebrow">Layered</p>
                    <h2 class="fable-section-title">World → canon → knowledge → possibility → narrative</h2>
                    <p>Entities and relationships ground the canon; claims and beliefs model who knows what; scenarios and conflicts explore what could happen; stories and scenes tell what did.</p>
                </article>

                <article class="fable-home-feature">
                    <p class="fable-eyebrow">MCP</p>
                    <h2 class="fable-section-title">Built for agents</h2>
                    <p>Connect any MCP-capable agent locally or over OAuth, and it can save, query, and evolve the world alongside you.</p>
                </article>
            </section>
        </div>
    </body>
</html>
