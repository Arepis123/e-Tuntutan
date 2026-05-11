<div>
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <flux:text class="text-2xl font-bold mb-2" variant="strong">My Claims</flux:text>
            <flux:subheading>Track and manage your submitted insurance claims</flux:subheading>
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
                <flux:table.column>Claim No.</flux:table.column>
                <flux:table.column>Worker</flux:table.column>
                <flux:table.column>Type</flux:table.column>
                <flux:table.column>Category</flux:table.column>
                <flux:table.column>Status</flux:table.column>
                <flux:table.column>Progress</flux:table.column>
                <flux:table.column>Submitted</flux:table.column>
                <flux:table.column></flux:table.column>
            </flux:table.columns>
            <flux:table.rows>
                @forelse ($claims as $claim)
                <flux:table.row wire:key="claim-{{ $claim->id }}" class="hover:bg-zinc-50 dark:hover:bg-zinc-700/50 transition-colors cursor-pointer">
                    <flux:table.cell class="font-mono text-sm font-medium text-zinc-900 dark:text-white">
                        {{ $claim->claim_number }}
                    </flux:table.cell>
                    <flux:table.cell>
                        <div>
                            <p class="font-medium text-zinc-900 dark:text-white">{{ $claim->worker->name }}</p>
                            <p class="text-xs text-zinc-500 dark:text-zinc-400">{{ $claim->worker->passport_number }}</p>
                        </div>
                    </flux:table.cell>
                    <flux:table.cell>
                        <flux:badge color="{{ match($claim->claim_type) { 'fwhs' => 'zink', 'green_card' => 'zink', 'perkeso' => 'zink', default => 'zinc' } }}" size="sm"
                            icon="{{ match($claim->claim_type) { 'fwhs' => 'building-office-2', 'green_card' => 'credit-card', 'perkeso' => 'shield-check', default => 'tag' } }}">
                            {{ $claim->getClaimTypeLabel() }}
                        </flux:badge>
                    </flux:table.cell>
                    <flux:table.cell class="">
                        {{ $claim->claim_category === 'hospitalization' ? 'Hospitalization' : 'Death' }}
                    </flux:table.cell>
                    @php
                        $hasUploaded        = $claim->documents->filter(fn($d) => $d->path)->isNotEmpty();
                        $appealed           = $claim->appealed_at;
                        $isEmgd             = $claim->isEmployerManaged();
                        $iLabel             = match($claim->claim_type) { 'perkeso' => 'PERKESO', 'green_card' => 'CIDB', default => 'Liberty' };
                        $curSubmitted       = $claim->submitted_to_insurer_at
                                                && (! $appealed || $claim->submitted_to_insurer_at > $appealed);
                        $empAppealActive    = $isEmgd && $appealed
                                                && (! $claim->submitted_to_insurer_at || $appealed > $claim->submitted_to_insurer_at);
                        $decisionIsCurrent  = $claim->insurer_decision
                                                && (! $appealed || $claim->insurer_decided_at > $appealed);

                        $sub = match(true) {
                            $claim->status === 'closed'
                                => ['label' => '—',                  'class' => ''],
                            $decisionIsCurrent && $claim->insurer_decision === 'approved'
                                => ['label' => 'Approved',           'class' => ''],
                            $decisionIsCurrent && $claim->insurer_decision === 'rejected' && $claim->appeal_count >= 1
                                => ['label' => 'Final Rejection',    'class' => ''],
                            $decisionIsCurrent && $claim->insurer_decision === 'rejected'
                                => ['label' => 'Rejected',           'class' => ''],
                            (bool) $curSubmitted
                                => ['label' => 'With ' . $iLabel,   'class' => ''],
                            $empAppealActive || ($appealed && ! $isEmgd)
                                => ['label' => 'Appealed',           'class' => ''],
                            $isEmgd && $hasUploaded
                                => ['label' => 'Action Required',    'class' => ''],
                            $hasUploaded
                                => ['label' => 'Pending CLAB',       'class' => ''],
                            default
                                => ['label' => 'Upload Documents',   'class' => ''],
                        };
                    @endphp
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
                        <span class="text-sm {{ $sub['class'] }}">{{ $sub['label'] }}</span>
                    </flux:table.cell>
                    <flux:table.cell class="">
                        {{ $claim->submitted_at?->format('d/m/Y') ?? $claim->created_at->format('d/m/Y') }}
                    </flux:table.cell>
                    <flux:table.cell>
                        <flux:button href="{{ route('claims.show', $claim) }}" variant="filled" size="sm" wire:navigate>
                            View
                        </flux:button>
                    </flux:table.cell>
                </flux:table.row>
                @empty
                <flux:table.row>
                    <flux:table.cell colspan="8" class="text-center py-12 text-zinc-400">
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
