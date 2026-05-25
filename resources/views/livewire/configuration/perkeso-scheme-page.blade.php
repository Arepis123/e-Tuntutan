<div>
    <div class="flex items-center justify-between mb-6">
        <div>
            <flux:text class="text-2xl font-bold mb-2" variant="strong">PERKESO Claim Categories</flux:text>
            <flux:subheading>Categories shown to employers when submitting a PERKESO claim.</flux:subheading>
        </div>
        <flux:button wire:click="openAddScheme" variant="primary" icon="plus">
            Add Category
        </flux:button>
    </div>

    <flux:card class="dark:bg-zinc-900">
        @if ($schemes->isEmpty())
            <flux:text class="text-sm text-zinc-400">No categories configured. Add one above.</flux:text>
        @else
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-zinc-200 dark:border-zinc-700 text-left">
                    <th class="pb-3 font-medium text-zinc-500 dark:text-zinc-400">#</th>
                    <th class="pb-3 font-medium text-zinc-500 dark:text-zinc-400">Label</th>
                    <th class="pb-3 font-medium text-zinc-500 dark:text-zinc-400">Value (key)</th>
                    <th class="pb-3 font-medium text-zinc-500 dark:text-zinc-400 text-center w-28">Status</th>
                    <th class="pb-3 font-medium text-zinc-500 dark:text-zinc-400 text-center w-24">Active</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                @foreach ($schemes as $scheme)
                <tr wire:key="scheme-{{ $scheme->id }}" class="{{ $scheme->is_active ? '' : 'opacity-50' }}">
                    <td class="py-3 text-zinc-400 w-8">{{ $loop->iteration }}</td>
                    <td class="py-3 font-medium text-zinc-800 dark:text-zinc-200">{{ $scheme->label }}</td>
                    <td class="py-3 font-mono text-xs text-zinc-400">{{ $scheme->value }}</td>
                    <td class="py-3 text-center">
                        <flux:badge size="sm" :color="$scheme->is_active ? 'green' : 'zinc'">
                            {{ $scheme->is_active ? 'Active' : 'Inactive' }}
                        </flux:badge>
                    </td>
                    <td class="py-3 text-center">
                        <flux:switch
                            wire:click="toggleScheme({{ $scheme->id }})"
                            :checked="$scheme->is_active"
                        />
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif
    </flux:card>

    {{-- Add Category Modal --}}
    <flux:modal name="add-perkeso-scheme" class="max-w-md">
        <div class="p-6 space-y-4">
            <flux:heading size="lg">Add PERKESO Category</flux:heading>

            <flux:field>
                <flux:label>Label</flux:label>
                <flux:input wire:model="newSchemeLabel" placeholder="e.g. Skim Bencana Kerja" />
                <flux:error name="newSchemeLabel" />
            </flux:field>

            <flux:field>
                <flux:label>Value <span class="ms-1 text-zinc-400 font-normal">(lowercase, underscores only)</span></flux:label>
                <flux:input wire:model="newSchemeValue" placeholder="e.g. skim_bencana_kerja" />
                <flux:error name="newSchemeValue" />
            </flux:field>

            <div class="flex justify-end gap-2 pt-2">
                <flux:modal.close>
                    <flux:button variant="ghost">Cancel</flux:button>
                </flux:modal.close>
                <flux:button wire:click="saveScheme" variant="primary" icon="plus">
                    Add
                </flux:button>
            </div>
        </div>
    </flux:modal>
</div>
