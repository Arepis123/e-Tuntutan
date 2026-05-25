<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>e-Tuntutan CLAB</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('images/favicon.svg') }}">
    <link rel="icon" type="image/png" sizes="96x96" href="{{ asset('images/favicon-96x96.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/apple-touch-icon.png') }}">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600&display=swap" rel="stylesheet" />
    @fluxAppearance
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-zinc-50 dark:bg-zinc-800 antialiased">

<flux:sidebar sticky collapsible class="bg-white dark:bg-zinc-900 border-r border-zinc-200 dark:border-zinc-700">

    <flux:sidebar.header>
        <flux:sidebar.brand
            href="{{ route('dashboard') }}"
            logo="{{ asset('images/logo-clab.png') }}"
            logo:dark="{{ asset('images/logo-clab.png') }}"
            name="e-Tuntutan CLAB"
        />
        <flux:sidebar.collapse class="in-data-flux-sidebar-on-desktop:not-in-data-flux-sidebar-collapsed-desktop:-mr-2" />
    </flux:sidebar.header>

    <flux:sidebar.nav>
        <flux:sidebar.item
            icon="squares-2x2"
            href="{{ route('dashboard') }}"
            :current="request()->routeIs('dashboard')"
            wire:navigate
        >
            Dashboard
        </flux:sidebar.item>

        @can('claims.view')
        <flux:sidebar.item
            icon="document-text"
            href="{{ route('claims.index') }}"
            :current="request()->routeIs('claims.*') && !request()->routeIs('claims.create')"
            wire:navigate
        >
            Claims
        </flux:sidebar.item>
        @endcan

        @can('claims.create')
        <flux:sidebar.item
            icon="plus-circle"
            href="{{ route('claims.create') }}"
            :current="request()->routeIs('claims.create')"
            wire:navigate
        >
            Submit Claim
        </flux:sidebar.item>
        @endcan


        @can('users.manage')
        <flux:sidebar.item
            icon="users"
            href="{{ route('users.index') }}"
            :current="request()->routeIs('users.*')"
            wire:navigate
        >
            Users
        </flux:sidebar.item>
        @endcan

        @can('configuration.view')
        <flux:sidebar.group
            expandable
            heading="Configuration"
            icon="cog"
            class="grid"
            :expanded="request()->routeIs('configuration.*')"
        >
            <flux:sidebar.item
                href="{{ route('configuration.documents') }}"
                :current="request()->routeIs('configuration.documents')"
                wire:navigate
            >
                Documents
            </flux:sidebar.item>
            <flux:sidebar.item
                href="{{ route('configuration.perkeso-schemes') }}"
                :current="request()->routeIs('configuration.perkeso-schemes')"
                wire:navigate
            >
                PERKESO Categories
            </flux:sidebar.item>
        </flux:sidebar.group>
        @endcan

        @role('admin')
        <flux:sidebar.item
            icon="finger-print"
            href="{{ route('roles.index') }}"
            :current="request()->routeIs('roles.*')"
            wire:navigate
        >
            Roles
        </flux:sidebar.item>
        @endrole
    </flux:sidebar.nav>

    <flux:sidebar.spacer />

    @php
        $displayName = auth()->user()->company_name ?: auth()->user()->name;
    @endphp
    <flux:dropdown position="top" align="start" class="max-lg:hidden">
        <flux:sidebar.profile
            :name="$displayName"
            :initials="strtoupper(substr($displayName, 0, 2))"
        />
        <flux:menu>
            <flux:menu.radio.group>
                <div class="p-0 text-sm font-normal">
                    <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                        <flux:avatar name="{{ auth()->user() ? preg_replace('/\s+(BIN|BINTI|BT)\b.*/i', '', auth()->user()->name) : 'N/A' }}" />
                        <div class="grid flex-1 text-start text-sm leading-tight">
                            <span class="truncate font-semibold">{{ auth()->user()->name ? preg_replace('/\s+(BIN|BINTI|BT)\b.*/i', '', auth()->user()->name) : 'N/A' }}</span>
                            <span class="truncate text-xs">{{ auth()->user()->email }}</span>
                        </div>
                    </div>
                </div>
            </flux:menu.radio.group>
            <flux:menu.separator />
            <flux:menu.item icon="cog" href="{{ route('settings') }}">Settings</flux:menu.item>
            <flux:menu.separator />
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <flux:menu.item icon="arrow-right-start-on-rectangle" type="submit" variant="danger">
                    Log Out
                </flux:menu.item>
            </form>
        </flux:menu>
    </flux:dropdown>

</flux:sidebar>

{{-- Mobile header --}}
<flux:header class="lg:hidden">
    <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />
    <flux:spacer />
    <flux:dropdown position="bottom" align="end">
        <flux:profile
            :name="Str::limit($displayName, 20)"
            :initials="strtoupper(substr($displayName, 0, 2))"
        />
        <flux:menu>
            <flux:menu.radio.group>
                <div class="p-0 text-sm font-normal">
                    <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                        <flux:avatar name="{{ auth()->user() ? preg_replace('/\s+(BIN|BINTI|BT)\b.*/i', '', auth()->user()->name) : 'N/A' }}" />
                        <div class="grid flex-1 text-start text-sm leading-tight">
                            <span class="truncate font-semibold">{{ auth()->user()->name ? preg_replace('/\s+(BIN|BINTI|BT)\b.*/i', '', auth()->user()->name) : 'N/A' }}</span>
                            <span class="truncate text-xs">{{ auth()->user()->email }}</span>
                        </div>
                    </div>
                </div>
            </flux:menu.radio.group>
            <flux:menu.separator />
            <flux:menu.item icon="cog" href="{{ route('settings') }}">Settings</flux:menu.item>
            <flux:menu.separator />
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <flux:menu.item icon="arrow-right-start-on-rectangle" type="submit" variant="danger">
                    Log Out
                </flux:menu.item>
            </form>
        </flux:menu>
    </flux:dropdown>
</flux:header>

<flux:main>
    {{ $slot }}
</flux:main>

@fluxScripts
</body>
</html>
