<div>
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <flux:heading size="xl">Claims</flux:heading>
            <flux:subheading>Manage all foreign worker insurance claims</flux:subheading>
        </div>
        @can('claims.create')
        <flux:button href="{{ route('claims.create') }}" icon="plus" wire:navigate>
            New Claim
        </flux:button>
        @endcan
    </div>

    {{-- Filters --}}
    <div class="bg-white rounded-xl border border-zinc-200 p-4 mb-4">
        <div class="flex flex-col sm:flex-row gap-3">
            <flux:input
                wire:model.live.debounce.300ms="search"
                placeholder="Search claim no., worker name, passport..."
                icon="magnifying-glass"
                class="flex-1"
            />
            <flux:select wire:model.live="statusFilter" placeholder="All Statuses">
                <flux:select.option value="">All Statuses</flux:select.option>
                <flux:select.option value="open">Open</flux:select.option>
                <flux:select.option value="in_progress">In Progress</flux:select.option>
                <flux:select.option value="closed">Closed</flux:select.option>
            </flux:select>
            <flux:select wire:model.live="typeFilter" placeholder="All Types">
                <flux:select.option value="">All Types</flux:select.option>
                <flux:select.option value="fwhs">Insurance (FWHS)</flux:select.option>
                <flux:select.option value="green_card">Green Card</flux:select.option>
                <flux:select.option value="perkeso">PERKESO</flux:select.option>
            </flux:select>
        </div>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-xl border border-zinc-200">
        <flux:table>
            <flux:table.columns>
                <flux:table.column sortable>Claim No.</flux:table.column>
                <flux:table.column>Worker</flux:table.column>
                <flux:table.column>Claim Type</flux:table.column>
                <flux:table.column>Category</flux:table.column>
                <flux:table.column>Status</flux:table.column>
                <flux:table.column>Submitted</flux:table.column>
                <flux:table.column></flux:table.column>
            </flux:table.columns>
            <flux:table.rows>
                @forelse ($claims as $claim)
                <flux:table.row wire:key="claim-{{ $claim->id }}">
                    <flux:table.cell class="font-mono text-sm font-medium">
                        {{ $claim->claim_number }}
                    </flux:table.cell>
                    <flux:table.cell>
                        <div>
                            <p class="font-medium text-zinc-900">{{ $claim->worker->name }}</p>
                            <p class="text-xs text-zinc-500">{{ $claim->worker->passport_number }}</p>
                        </div>
                    </flux:table.cell>
                    <flux:table.cell>{{ $claim->getClaimTypeLabel() }}</flux:table.cell>
                    <flux:table.cell>
                        <flux:badge color="zinc" size="sm">
                            {{ $claim->claim_category === 'hospitalization' ? 'Hospitalization' : 'Death' }}
                        </flux:badge>
                    </flux:table.cell>
                    <flux:table.cell>
                        <flux:badge color="{{ $claim->status_color }}" size="sm">
                            {{ match($claim->status) {
                                'open' => 'Open',
                                'in_progress' => 'In Progress',
                                'closed' => 'Closed',
                                default => $claim->status
                            } }}
                        </flux:badge>
                    </flux:table.cell>
                    <flux:table.cell class="text-zinc-500 text-sm">
                        {{ $claim->submitted_at?->format('d/m/Y') ?? $claim->created_at->format('d/m/Y') }}
                    </flux:table.cell>
                    <flux:table.cell>
                        <flux:button href="{{ route('claims.show', $claim) }}" variant="ghost" size="sm" wire:navigate>
                            View
                        </flux:button>
                    </flux:table.cell>
                </flux:table.row>
                @empty
                <flux:table.row>
                    <flux:table.cell colspan="7" class="text-center py-12 text-zinc-400">
                        <flux:icon.document-magnifying-glass class="w-12 h-12 mx-auto mb-3 opacity-50" />
                        <p>No claims found.</p>
                    </flux:table.cell>
                </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>

        @if ($claims->hasPages())
        <div class="px-6 py-4 border-t border-zinc-200">
            {{ $claims->links() }}
        </div>
        @endif
    </div>
</div>
