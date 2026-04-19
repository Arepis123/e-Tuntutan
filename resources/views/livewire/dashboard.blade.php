<div>
    <div class="mb-6">
        <flux:heading size="xl">Dashboard</flux:heading>
        <flux:subheading>Welcome back, {{ auth()->user()->name }}</flux:subheading>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4 mb-8">
        <flux:card class="flex items-center justify-between dark:bg-zinc-900">
            <div>
                <flux:text class="text-sm">Total Claims</flux:text>
                <p class="text-3xl font-bold text-zinc-900 dark:text-white mt-1">{{ $stats['total'] }}</p>
            </div>
            <div class="w-12 h-12 bg-zinc-100 dark:bg-zinc-700 rounded-lg flex items-center justify-center">
                <flux:icon.document-text class="w-6 h-6 text-zinc-600 dark:text-zinc-300" />
            </div>
        </flux:card>

        <flux:card class="flex items-center justify-between  dark:bg-zinc-900">
            <div>
                <flux:text class="text-sm !text-red-500 dark:!text-red-400">Open</flux:text>
                <p class="text-3xl font-bold text-red-600 dark:text-red-400 mt-1">{{ $stats['open'] }}</p>
            </div>
            <div class="w-12 h-12 bg-red-50 dark:bg-red-900/30 rounded-lg flex items-center justify-center">
                <flux:icon.exclamation-circle class="w-6 h-6 text-red-500 dark:text-red-400" />
            </div>
        </flux:card>

        <flux:card class="flex items-center justify-between  dark:bg-zinc-900">
            <div>
                <flux:text class="text-sm !text-yellow-600 dark:!text-yellow-400">In Progress</flux:text>
                <p class="text-3xl font-bold text-yellow-600 dark:text-yellow-400 mt-1">{{ $stats['in_progress'] }}</p>
            </div>
            <div class="w-12 h-12 bg-yellow-50 dark:bg-yellow-900/30 rounded-lg flex items-center justify-center">
                <flux:icon.arrow-path class="w-6 h-6 text-yellow-600 dark:text-yellow-400" />
            </div>
        </flux:card>

        <flux:card class="flex items-center justify-between dark:bg-zinc-900">
            <div>
                <flux:text class="text-sm !text-green-600 dark:!text-green-400">Closed</flux:text>
                <p class="text-3xl font-bold text-green-600 dark:text-green-400 mt-1">{{ $stats['closed'] }}</p>
            </div>
            <div class="w-12 h-12 bg-green-50 dark:bg-green-900/30 rounded-lg flex items-center justify-center">
                <flux:icon.check-circle class="w-6 h-6 text-green-600 dark:text-green-400" />
            </div>
        </flux:card>
    </div>

    {{-- Recent Claims --}}
    <flux:card class="p-4 dark:bg-zinc-900">
        <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-700 flex items-center justify-between">
            <flux:heading size="lg">Recent Claims</flux:heading>
            <flux:button href="{{ route('claims.index') }}" variant="ghost" size="sm" wire:navigate>
                View All
            </flux:button>
        </div>

        <flux:table>
            <flux:table.columns>
                <flux:table.column>Claim No.</flux:table.column>
                <flux:table.column>Worker</flux:table.column>
                <flux:table.column>Type</flux:table.column>
                <flux:table.column>Status</flux:table.column>
                <flux:table.column>Date</flux:table.column>
                <flux:table.column></flux:table.column>
            </flux:table.columns>
            <flux:table.rows>
                @forelse ($recentClaims as $claim)
                <flux:table.row>
                    <flux:table.cell class="font-mono text-sm">{{ $claim->claim_number }}</flux:table.cell>
                    <flux:table.cell>{{ $claim->worker->name }}</flux:table.cell>
                    <flux:table.cell>{{ $claim->getClaimTypeLabel() }}</flux:table.cell>
                    <flux:table.cell>
                        <flux:badge color="{{ $claim->status_color }}" size="sm">
                            {{ ucfirst(str_replace('_', ' ', $claim->status)) }}
                        </flux:badge>
                    </flux:table.cell>
                    <flux:table.cell class="text-zinc-500 dark:text-zinc-400 text-sm">{{ $claim->created_at->format('d/m/Y') }}</flux:table.cell>
                    <flux:table.cell>
                        <flux:button href="{{ route('claims.show', $claim) }}" variant="ghost" size="sm" wire:navigate>
                            View
                        </flux:button>
                    </flux:table.cell>
                </flux:table.row>
                @empty
                <flux:table.row>
                    <flux:table.cell colspan="6" class="text-center text-zinc-400 dark:text-zinc-500 py-8">
                        No claims yet.
                    </flux:table.cell>
                </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>
    </flux:card>
</div>
