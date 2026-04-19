<div>
    <div class="mb-6">
        <flux:heading size="xl">Dashboard</flux:heading>
        <flux:subheading>Welcome back, {{ auth()->user()->name }}</flux:subheading>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4 mb-8">
        <div class="bg-white rounded-xl border border-zinc-200 p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-zinc-500">Total Claims</p>
                    <p class="text-3xl font-bold text-zinc-900 mt-1">{{ $stats['total'] }}</p>
                </div>
                <div class="w-12 h-12 bg-zinc-100 rounded-lg flex items-center justify-center">
                    <flux:icon.document-text class="w-6 h-6 text-zinc-600" />
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-red-200 p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-red-500">Open</p>
                    <p class="text-3xl font-bold text-red-600 mt-1">{{ $stats['open'] }}</p>
                </div>
                <div class="w-12 h-12 bg-red-50 rounded-lg flex items-center justify-center">
                    <flux:icon.exclamation-circle class="w-6 h-6 text-red-500" />
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-yellow-200 p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-yellow-600">In Progress</p>
                    <p class="text-3xl font-bold text-yellow-600 mt-1">{{ $stats['in_progress'] }}</p>
                </div>
                <div class="w-12 h-12 bg-yellow-50 rounded-lg flex items-center justify-center">
                    <flux:icon.arrow-path class="w-6 h-6 text-yellow-600" />
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-green-200 p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-green-600">Closed</p>
                    <p class="text-3xl font-bold text-green-600 mt-1">{{ $stats['closed'] }}</p>
                </div>
                <div class="w-12 h-12 bg-green-50 rounded-lg flex items-center justify-center">
                    <flux:icon.check-circle class="w-6 h-6 text-green-600" />
                </div>
            </div>
        </div>
    </div>

    {{-- Recent Claims --}}
    <div class="bg-white rounded-xl border border-zinc-200">
        <div class="px-6 py-4 border-b border-zinc-200 flex items-center justify-between">
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
                    <flux:table.cell class="text-zinc-500 text-sm">{{ $claim->created_at->format('d/m/Y') }}</flux:table.cell>
                    <flux:table.cell>
                        <flux:button href="{{ route('claims.show', $claim) }}" variant="ghost" size="sm" wire:navigate>
                            View
                        </flux:button>
                    </flux:table.cell>
                </flux:table.row>
                @empty
                <flux:table.row>
                    <flux:table.cell colspan="6" class="text-center text-zinc-400 py-8">
                        No claims yet.
                    </flux:table.cell>
                </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>
    </div>
</div>
