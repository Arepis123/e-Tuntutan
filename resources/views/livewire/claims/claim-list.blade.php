<div>
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <flux:text class="text-2xl font-bold mb-2" variant="strong">Claims</flux:text>
            <flux:subheading>Manage all foreign worker insurance claims</flux:subheading>
        </div>
        @can('claims.create')
        <flux:button href="{{ route('claims.create') }}" icon="plus" variant="primary" wire:navigate>
            New Claim
        </flux:button>
        @endcan
    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
        <flux:card class="p-5 flex items-center gap-4 dark:bg-zinc-900">
            <div class="hidden sm:block p-3 rounded-xl bg-blue-50 dark:bg-blue-900/30">
                <flux:icon.document-text class="w-6 h-6 text-blue-600 dark:text-blue-400" />
            </div>
            <div>
                <p class="text-sm text-zinc-500 dark:text-zinc-400">Total</p>
                <p class="text-2xl font-bold text-zinc-900 dark:text-white">{{ $stats['total'] }}</p>
            </div>
        </flux:card>

        <flux:card class="p-5 flex items-center gap-4 dark:bg-zinc-900">
            <div class="hidden sm:block p-3 rounded-xl bg-red-50 dark:bg-red-900/30">
                <flux:icon.exclamation-circle class="w-6 h-6 text-red-500 dark:text-red-400" />
            </div>
            <div>
                <p class="text-sm text-zinc-500 dark:text-zinc-400">Open</p>
                <p class="text-2xl font-bold text-zinc-900 dark:text-white">{{ $stats['open'] }}</p>
            </div>
        </flux:card>

        <flux:card class="p-5 flex items-center gap-4 dark:bg-zinc-900">
            <div class="hidden sm:block p-3 rounded-xl bg-yellow-50 dark:bg-yellow-900/30">
                <flux:icon.arrow-path class="w-6 h-6 text-yellow-500 dark:text-yellow-400" />
            </div>
            <div>
                <p class="text-sm text-zinc-500 dark:text-zinc-400">In Progress</p>
                <p class="text-2xl font-bold text-zinc-900 dark:text-white">{{ $stats['in_progress'] }}</p>
            </div>
        </flux:card>

        <flux:card class="p-5 flex items-center gap-4 dark:bg-zinc-900">
            <div class="hidden sm:block p-3 rounded-xl bg-green-50 dark:bg-green-900/30">
                <flux:icon.check-circle class="w-6 h-6 text-green-600 dark:text-green-400" />
            </div>
            <div>
                <p class="text-sm text-zinc-500 dark:text-zinc-400">Closed</p>
                <p class="text-2xl font-bold text-zinc-900 dark:text-white">{{ $stats['closed'] }}</p>
            </div>
        </flux:card>
    </div>

    {{-- Table --}}
    <flux:card class="p-4 sm:p-6 dark:bg-zinc-900 rounded-lg">
        {{-- Filters --}}
        <div class="flex flex-col sm:flex-row gap-3 mb-4">
            <flux:input
                wire:model.live.debounce.300ms="search"
                placeholder="Search claim no., worker name, passport..."
                icon="magnifying-glass"
                class="flex-1"
            />
            <flux:select wire:model.live="statusFilter" variant="listbox" placeholder="All Statuses" class="sm:w-44">
                <flux:select.option value="">All Statuses</flux:select.option>
                <flux:select.option value="open">Open</flux:select.option>
                <flux:select.option value="in_progress">In Progress</flux:select.option>
                <flux:select.option value="closed">Closed</flux:select.option>
            </flux:select>
            <flux:select wire:model.live="typeFilter" variant="listbox" placeholder="All Types" class="sm:w-44">
                <flux:select.option value="">All Types</flux:select.option>
                <flux:select.option value="fwhs">Insurance (FWHS)</flux:select.option>
                <flux:select.option value="green_card">Green Card</flux:select.option>
                <flux:select.option value="perkeso">PERKESO</flux:select.option>
            </flux:select>
        </div>

        <flux:table>
            <flux:table.columns>
                <flux:table.column sortable :sorted="$sortBy === 'claim_number'" :direction="$sortDirection" wire:click="sort('claim_number')">Claim No.</flux:table.column>
                <flux:table.column sortable :sorted="$sortBy === 'employer_name'" :direction="$sortDirection" wire:click="sort('employer_name')">Company Name</flux:table.column>
                <flux:table.column sortable :sorted="$sortBy === 'worker_name'" :direction="$sortDirection" wire:click="sort('worker_name')">Worker</flux:table.column>
                <flux:table.column sortable :sorted="$sortBy === 'worker_type'" :direction="$sortDirection" wire:click="sort('worker_type')">Worker Type</flux:table.column>
                <flux:table.column sortable :sorted="$sortBy === 'claim_type'" :direction="$sortDirection" wire:click="sort('claim_type')">Type</flux:table.column>
                <flux:table.column sortable :sorted="$sortBy === 'claim_category'" :direction="$sortDirection" wire:click="sort('claim_category')">Category</flux:table.column>
                <flux:table.column sortable :sorted="$sortBy === 'status'" :direction="$sortDirection" wire:click="sort('status')">Status</flux:table.column>
                <flux:table.column sortable :sorted="$sortBy === 'in_progress_at'" :direction="$sortDirection" wire:click="sort('in_progress_at')">Days In Progress</flux:table.column>
                <flux:table.column sortable :sorted="$sortBy === 'created_at'" :direction="$sortDirection" wire:click="sort('created_at')">Submitted</flux:table.column>
            </flux:table.columns>
            <flux:table.rows>
                @forelse ($claims as $claim)
                <flux:table.row wire:key="claim-{{ $claim->id }}" class="hover:bg-zinc-50 dark:hover:bg-zinc-700/50 transition-colors cursor-pointer" x-on:click="Livewire.navigate('{{ route('claims.show', $claim) }}')"  >
                    <flux:table.cell class="font-mono text-sm font-medium text-zinc-900 dark:text-white">
                        {{ $claim->claim_number }}
                    </flux:table.cell>
                    <flux:table.cell class="font-medium text-zinc-900 dark:text-white uppercase truncate max-w-[8rem] sm:max-w-[10rem] lg:max-w-[12rem]">                      
                        {{ $claim->worker->employer_name ?? '—' }}                        
                    </flux:table.cell>
                    <flux:table.cell>
                        <p class="font-medium text-zinc-900 dark:text-white">{{ $claim->worker->name }}</p>
                        <p class="text-xs text-zinc-500 dark:text-zinc-400">{{ $claim->worker->passport_number }}</p>
                    </flux:table.cell>
                    <flux:table.cell>
                            {{ $claim->worker->worker_type === 'outsource' ? 'Outsource' : 'Existing' }}
                    </flux:table.cell>
                    <flux:table.cell>
                        <flux:badge color="{{ match($claim->claim_type) { 'fwhs' => 'zinc', 'green_card' => 'zinc', 'perkeso' => 'zinc', default => 'zinc' } }}" size="sm"
                            icon="{{ match($claim->claim_type) { 'fwhs' => 'building-office-2', 'green_card' => 'credit-card', 'perkeso' => 'shield-check', default => 'tag' } }}">
                            {{ $claim->getClaimTypeLabel() }}
                        </flux:badge>
                    </flux:table.cell>
                    <flux:table.cell>
                        {{ $claim->claim_category === 'hospitalization' ? 'Hospitalization' : 'Death' }}
                    </flux:table.cell>
                    <flux:table.cell>
                        <flux:badge color="{{ $claim->status_color }}" size="sm">
                            {{ match($claim->status) {
                                'open'        => 'Open',
                                'in_progress' => 'In Progress',
                                'closed'      => 'Closed',
                                default       => $claim->status
                            } }}
                        </flux:badge>
                    </flux:table.cell>
                    <flux:table.cell>
                        @if ($claim->in_progress_at)
                            @php
                                $until = $claim->status === 'closed' ? $claim->closed_at : now();
                                $days  = (int) $claim->in_progress_at->diffInDays($until);
                            @endphp
                            <flux:badge color="{{ $days >= 60 ? 'red' : ($days >= 30 ? 'yellow' : 'green') }}" size="sm">
                                {{ $days }} {{ Str::plural('day', $days) }}
                            </flux:badge>
                        @else
                            <span class="text-zinc-400 text-sm">—</span>
                        @endif
                    </flux:table.cell>
                    <flux:table.cell>
                        {{ $claim->submitted_at?->format('d/m/Y') ?? $claim->created_at->format('d/m/Y') }}
                    </flux:table.cell>
                </flux:table.row>
                @empty
                <flux:table.row>
                    <flux:table.cell colspan="9" class="text-center py-12 text-zinc-400">
                        <flux:icon.document-magnifying-glass class="w-12 h-12 mx-auto mb-3 opacity-50" />
                        <p>No claims found.</p>
                    </flux:table.cell>
                </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>

        @if ($claims->hasPages())
        <div class="px-4 py-4 border-t border-zinc-200 dark:border-zinc-700">
            <flux:pagination :paginator="$claims" />
        </div>
        @endif
    </flux:card>
</div>
