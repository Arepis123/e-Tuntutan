<div>
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <flux:heading size="xl">Payments</flux:heading>
            <flux:subheading>Track compensation payments to beneficiaries</flux:subheading>
        </div>
    </div>

    {{-- Filters --}}
    <div class="bg-white dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-700 p-4 mb-4">
        <div class="flex flex-col sm:flex-row gap-3">
            <flux:input
                wire:model.live.debounce.300ms="search"
                placeholder="Search claim no., worker, beneficiary..."
                icon="magnifying-glass"
                class="flex-1"
            />
            <flux:select wire:model.live="statusFilter" placeholder="All Statuses">
                <flux:select.option value="">All Statuses</flux:select.option>
                <flux:select.option value="pending">Pending</flux:select.option>
                <flux:select.option value="paid">Paid</flux:select.option>
                <flux:select.option value="rejected">Rejected</flux:select.option>
            </flux:select>
        </div>
    </div>

    {{-- Table --}}
    <div class="bg-white dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-700">
        <flux:table>
            <flux:table.columns>
                <flux:table.column>Claim No.</flux:table.column>
                <flux:table.column>Worker</flux:table.column>
                <flux:table.column>Beneficiary</flux:table.column>
                <flux:table.column>Amount</flux:table.column>
                <flux:table.column>Bank</flux:table.column>
                <flux:table.column>Status</flux:table.column>
                <flux:table.column>Payment Date</flux:table.column>
                <flux:table.column></flux:table.column>
            </flux:table.columns>
            <flux:table.rows>
                @forelse ($payments as $payment)
                <flux:table.row wire:key="payment-{{ $payment->id }}">
                    <flux:table.cell class="font-mono text-sm font-medium">
                        {{ $payment->claim->claim_number }}
                    </flux:table.cell>
                    <flux:table.cell>
                        <div>
                            <p class="font-medium text-zinc-900 dark:text-white">{{ $payment->claim->worker->name }}</p>
                            <p class="text-xs text-zinc-500">{{ $payment->claim->worker->passport_number }}</p>
                        </div>
                    </flux:table.cell>
                    <flux:table.cell>
                        <div>
                            <p class="font-medium">{{ $payment->beneficiary_name }}</p>
                            <p class="text-xs text-zinc-500">{{ $payment->beneficiary_relationship }}</p>
                        </div>
                    </flux:table.cell>
                    <flux:table.cell class="font-medium">
                        RM {{ number_format($payment->amount, 2) }}
                    </flux:table.cell>
                    <flux:table.cell>
                        <div>
                            <p class="text-sm">{{ $payment->bank_name }}</p>
                            <p class="text-xs text-zinc-500 font-mono">{{ $payment->bank_account_number }}</p>
                        </div>
                    </flux:table.cell>
                    <flux:table.cell>
                        <flux:badge
                            color="{{ match($payment->status) { 'paid' => 'green', 'pending' => 'yellow', 'rejected' => 'red', default => 'zinc' } }}"
                            size="sm"
                        >
                            {{ ucfirst($payment->status) }}
                        </flux:badge>
                    </flux:table.cell>
                    <flux:table.cell class="text-zinc-500 text-sm">
                        {{ $payment->payment_date?->format('d/m/Y') ?? '—' }}
                    </flux:table.cell>
                    <flux:table.cell>
                        <flux:button href="{{ route('claims.show', $payment->claim) }}" variant="ghost" size="sm" wire:navigate>
                            View Claim
                        </flux:button>
                    </flux:table.cell>
                </flux:table.row>
                @empty
                <flux:table.row>
                    <flux:table.cell colspan="8" class="text-center py-12 text-zinc-400">
                        <flux:icon.banknotes class="w-12 h-12 mx-auto mb-3 opacity-50" />
                        <p>No payments found.</p>
                    </flux:table.cell>
                </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>

        @if ($payments->hasPages())
        <div class="px-6 py-4 border-t border-zinc-200 dark:border-zinc-700">
            {{ $payments->links() }}
        </div>
        @endif
    </div>
</div>
