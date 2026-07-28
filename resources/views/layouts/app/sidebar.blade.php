<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-vellum text-fable-primary dark:bg-carbon">
        @php
            $routeMilieu = request()->route('milieu');
            $activeMilieu = $routeMilieu instanceof \App\Models\Milieu ? $routeMilieu : null;
            $activeRecordType = request()->route('recordType');
            $strataNavigation = [
                'World' => ['continuity' => 'Continuities', 'ontology_type' => 'Ontology'],
                'Canon' => ['entity' => 'Entities', 'relationship' => 'Relationships', 'event' => 'Events', 'rule' => 'Rules'],
                'Knowledge' => ['claim' => 'Claims', 'belief' => 'Beliefs', 'perspective' => 'Perspectives'],
                'Possibility' => ['scenario' => 'Scenarios', 'goal' => 'Goals', 'conflict' => 'Conflicts'],
                'Narrative' => ['story' => 'Stories', 'scene' => 'Scenes', 'disclosure' => 'Disclosures', 'saga' => 'Sagas'],
            ];
        @endphp

        <flux:sidebar sticky collapsible="mobile" class="fable-sidebar">
            <flux:sidebar.header>
                <x-app-logo :sidebar="true" href="{{ route('dashboard') }}" wire:navigate />
                <flux:sidebar.collapse class="lg:hidden" />
            </flux:sidebar.header>

            <flux:sidebar.nav class="gap-3">
                <flux:sidebar.group heading="Workspace" class="grid">
                    <flux:sidebar.item :href="route('dashboard')" :current="request()->routeIs('dashboard')" wire:navigate>
                        World shelf
                    </flux:sidebar.item>
                </flux:sidebar.group>

                @if ($navigationMilieus->isNotEmpty())
                    <div class="px-2">
                        <flux:dropdown position="bottom" align="start">
                            <flux:button variant="ghost" class="w-full !justify-between" icon-trailing="chevrons-up-down">
                                <span class="truncate">{{ $activeMilieu?->name ?? 'Choose a world' }}</span>
                            </flux:button>
                            <flux:menu class="min-w-64">
                                @foreach ($navigationMilieus as $navigationMilieu)
                                    <flux:menu.item :href="route('milieus.show', $navigationMilieu)" wire:navigate wire:key="nav-milieu-{{ $navigationMilieu->id }}">
                                        <div class="min-w-0">
                                            <div class="truncate font-medium">{{ $navigationMilieu->name }}</div>
                                            <div class="text-xs text-fable-muted">
                                                {{ $navigationMilieu->owner_id === auth()->id() ? 'Owner' : ($navigationMilieu->memberships->first()?->role?->value ?? 'Viewer') }}
                                            </div>
                                        </div>
                                    </flux:menu.item>
                                @endforeach
                            </flux:menu>
                        </flux:dropdown>
                    </div>
                @endif

                @if ($activeMilieu)
                    <flux:sidebar.group heading="Observatory" class="grid">
                        <flux:sidebar.item :href="route('milieus.show', $activeMilieu)" :current="request()->routeIs('milieus.show')" wire:navigate>
                            Overview
                        </flux:sidebar.item>
                    </flux:sidebar.group>

                    @foreach ($strataNavigation as $stratum => $items)
                        <flux:sidebar.group :heading="$stratum" class="grid fable-nav-stratum fable-nav-{{ str($stratum)->lower() }}">
                            @foreach ($items as $type => $label)
                                <flux:sidebar.item
                                    :href="route('milieus.explore', [$activeMilieu, $type])"
                                    :current="request()->routeIs('milieus.explore') && $activeRecordType === $type"
                                    wire:navigate
                                >
                                    {{ $label }}
                                </flux:sidebar.item>
                            @endforeach
                        </flux:sidebar.group>
                    @endforeach

                    <flux:sidebar.group heading="Provenance" class="grid">
                        <flux:sidebar.item :href="route('milieus.activity', $activeMilieu)" :current="request()->routeIs('milieus.activity')" wire:navigate>
                            Recent Changes
                        </flux:sidebar.item>
                    </flux:sidebar.group>
                @endif
            </flux:sidebar.nav>

            <flux:spacer />

            <div class="px-3 pb-2">
                <div class="flex items-center justify-end border-t border-fable-soft pt-3">
                    <x-fable.connection-status />
                </div>
            </div>

            <x-desktop-user-menu class="hidden lg:block" :name="auth()->user()->name" />
        </flux:sidebar>

        <!-- Mobile User Menu -->
        <flux:header class="border-b border-fable-soft bg-vellum/95 lg:hidden dark:bg-carbon/95">
            <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

            <flux:spacer />

            <flux:dropdown position="top" align="end">
                <flux:profile
                    :initials="auth()->user()->initials()"
                    icon-trailing="chevron-down"
                />

                <flux:menu>
                    <flux:menu.radio.group>
                        <div class="p-0 text-sm font-normal">
                            <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                                <flux:avatar
                                    :name="auth()->user()->name"
                                    :initials="auth()->user()->initials()"
                                />

                                <div class="grid flex-1 text-start text-sm leading-tight">
                                    <flux:heading class="truncate">{{ auth()->user()->name }}</flux:heading>
                                    <flux:text class="truncate">{{ auth()->user()->email }}</flux:text>
                                </div>
                            </div>
                        </div>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <flux:menu.radio.group>
                        <flux:menu.item :href="route('profile.edit')" icon="cog" wire:navigate>
                            {{ __('Settings') }}
                        </flux:menu.item>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <flux:menu.item
                            as="button"
                            type="submit"
                            icon="arrow-right-start-on-rectangle"
                            class="w-full cursor-pointer"
                            data-test="logout-button"
                        >
                            {{ __('Log out') }}
                        </flux:menu.item>
                    </form>
                </flux:menu>
            </flux:dropdown>
        </flux:header>

        {{ $slot }}

        @persist('toast')
            <flux:toast.group>
                <flux:toast />
            </flux:toast.group>
        @endpersist

        @fluxScripts
    </body>
</html>
