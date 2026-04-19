<div>
    <div class="mb-6">
        <flux:text class="text-2xl font-bold mb-2" variant="strong">Roles & Permissions</flux:text>
        <flux:subheading>Manage permissions assigned to each role</flux:subheading>
    </div>

    <div class="flex flex-col lg:flex-row gap-6">

        {{-- Role selector --}}
        <div class="w-full lg:w-56 shrink-0">
            <flux:card class="p-2 space-y-1">
                @foreach ($roles as $role)
                <button
                    wire:click="selectRole('{{ $role->name }}')"
                    class="w-full text-left px-3 py-2 rounded-lg text-sm font-medium transition-colors
                        {{ $selectedRole === $role->name
                            ? 'bg-zinc-900 text-white dark:bg-white dark:text-zinc-900'
                            : 'text-zinc-700 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-700' }}"
                >
                    {{ ucfirst($role->name) }}
                </button>
                @endforeach
            </flux:card>
        </div>

        {{-- Permissions panel --}}
        <div class="flex-1">
            @if ($selectedRole)
            <flux:card class="p-6">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <flux:heading size="lg">{{ ucfirst($selectedRole) }}</flux:heading>
                        <flux:text class="text-sm text-zinc-500 dark:text-zinc-400 mt-0.5">
                            {{ count($rolePermissions) }} permission(s) assigned
                        </flux:text>
                    </div>
                    <flux:button wire:click="savePermissions" variant="primary" icon="check">
                        Save Changes
                    </flux:button>
                </div>

                <div class="space-y-6">
                    @foreach ($permissions as $group => $groupPermissions)
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-zinc-400 dark:text-zinc-500 mb-3">
                            {{ $group }}
                        </p>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                            @foreach ($groupPermissions as $permission)
                            <label class="flex items-center gap-3 px-3 py-2 rounded-lg border border-zinc-200 dark:border-zinc-700 hover:bg-zinc-50 dark:hover:bg-zinc-700/50 cursor-pointer transition-colors">
                                <flux:checkbox
                                    wire:model="rolePermissions"
                                    value="{{ $permission->name }}"
                                />
                                <span class="text-sm text-zinc-700 dark:text-zinc-300">
                                    {{ str($permission->name)->after('.')->replace('_', ' ')->title() }}
                                </span>
                            </label>
                            @endforeach
                        </div>
                    </div>
                    @endforeach
                </div>
            </flux:card>
            @endif
        </div>

    </div>
</div>
