<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'e-Tuntutan' }} — CLAB</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600&display=swap" rel="stylesheet" />
    @fluxAppearance
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-white dark:bg-zinc-800 antialiased">

<flux:sidebar sticky collapsible class="bg-zinc-50 dark:bg-zinc-900 border-r border-zinc-200 dark:border-zinc-700">

    <flux:sidebar.header>
        <flux:sidebar.brand href="{{ route('dashboard') }}" name="e-Tuntutan CLAB" />
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

        @can('payments.view')
        <flux:sidebar.item
            icon="banknotes"
            href="{{ route('payments.index') }}"
            :current="request()->routeIs('payments.*')"
            wire:navigate
        >
            Payments
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

        @role('admin')
        <flux:sidebar.item
            icon="cog-6-tooth"
            href="{{ route('configuration.index') }}"
            :current="request()->routeIs('configuration.*')"
            wire:navigate
        >
            Configuration
        </flux:sidebar.item>

        <flux:sidebar.item
            icon="shield-check"
            href="{{ route('roles.index') }}"
            :current="request()->routeIs('roles.*')"
            wire:navigate
        >
            Roles
        </flux:sidebar.item>
        @endrole
    </flux:sidebar.nav>

    <flux:sidebar.spacer />

    <flux:sidebar.nav>
        <flux:sidebar.item
            icon="cog-6-tooth"
            href="{{ route('settings') }}"
            :current="request()->routeIs('settings')"
            wire:navigate
        >
            Settings
        </flux:sidebar.item>
    </flux:sidebar.nav>

    @php
        $displayName = auth()->user()->company_name ?: auth()->user()->name;
    @endphp
    <flux:dropdown position="top" align="start" class="max-lg:hidden">
        <flux:sidebar.profile
            :name="$displayName"
            :initials="strtoupper(substr($displayName, 0, 2))"
        />
        <flux:menu>
            <flux:menu.item icon="user">My Profile</flux:menu.item>
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
            <flux:menu.item icon="user">My Profile</flux:menu.item>
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
