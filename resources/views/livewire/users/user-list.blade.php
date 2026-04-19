<div>
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <flux:text class="text-2xl font-bold mb-2" variant="strong">Users</flux:text>
            <flux:subheading>Manage system users and their roles</flux:subheading>
        </div>
        @hasanyrole('admin|pic')
        <flux:modal.trigger name="create-user">
            <flux:button icon="plus" variant="primary">Add New User</flux:button>
        </flux:modal.trigger>
        @endhasanyrole
    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        <flux:card class="p-5 flex items-center gap-4">
            <div class="p-3 rounded-xl bg-blue-50 dark:bg-blue-900/30">
                <flux:icon.users class="w-6 h-6 text-blue-600 dark:text-blue-400" />
            </div>
            <div>
                <p class="text-sm text-zinc-500 dark:text-zinc-400">Total Users</p>
                <p class="text-2xl font-bold text-zinc-900 dark:text-white">{{ $stats['total'] }}</p>
            </div>
        </flux:card>

        <flux:card class="p-5 flex items-center gap-4">
            <div class="p-3 rounded-xl bg-purple-50 dark:bg-purple-900/30">
                <flux:icon.identification class="w-6 h-6 text-purple-600 dark:text-purple-400" />
            </div>
            <div>
                <p class="text-sm text-zinc-500 dark:text-zinc-400">PICs</p>
                <p class="text-2xl font-bold text-zinc-900 dark:text-white">{{ $stats['pic'] }}</p>
            </div>
        </flux:card>

        <flux:card class="p-5 flex items-center gap-4">
            <div class="p-3 rounded-xl bg-green-50 dark:bg-green-900/30">
                <flux:icon.building-office class="w-6 h-6 text-green-600 dark:text-green-400" />
            </div>
            <div>
                <p class="text-sm text-zinc-500 dark:text-zinc-400">Employers</p>
                <p class="text-2xl font-bold text-zinc-900 dark:text-white">{{ $stats['employer'] }}</p>
            </div>
        </flux:card>
    </div>

    {{-- Table --}}
    <flux:card class="p-4 sm:p-6 dark:bg-zinc-900 rounded-lg">
        <flux:table>
            <flux:table.columns>
                <flux:table.column>No</flux:table.column>
                <flux:table.column sortable :sorted="$sortBy === 'name'" :direction="$sortDirection" wire:click="sort('name')">User</flux:table.column>
                <flux:table.column sortable :sorted="$sortBy === 'role'" :direction="$sortDirection" wire:click="sort('role')">Role</flux:table.column>
                <flux:table.column sortable :sorted="$sortBy === 'company_name'" :direction="$sortDirection" wire:click="sort('company_name')">Company</flux:table.column>
                <flux:table.column>Phone</flux:table.column>
                <flux:table.column sortable :sorted="$sortBy === 'created_at'" :direction="$sortDirection" wire:click="sort('created_at')">Joined</flux:table.column>
                @role('admin')<flux:table.column>Action</flux:table.column>@endrole
            </flux:table.columns>
            <flux:table.rows>
                @forelse ($users as $user)
                <flux:table.row wire:key="user-{{ $user->id }}" class="hover:bg-zinc-50 dark:hover:bg-zinc-700/50 transition-colors cursor-pointer">
                    <flux:table.cell class="text-zinc-500 text-sm">
                        {{ $loop->iteration }}
                    </flux:table.cell>
                    <flux:table.cell>
                        <div class="flex items-center gap-3">
                            <flux:avatar
                                name="{{ $user->name }}"
                                size="sm"
                                color="auto"
                            />
                            <div>
                                <p class="font-medium text-zinc-900 dark:text-white">{{ $user->name }}</p>
                                <p class="text-xs text-zinc-500">{{ $user->email }}</p>
                            </div>
                        </div>
                    </flux:table.cell>
                    <flux:table.cell>
                        @foreach ($user->roles as $role)
                            <flux:badge
                                color="{{ match($role->name) { 'admin' => 'red', 'pic' => 'blue', 'employer' => 'green', default => 'zinc' } }}"
                                size="sm"
                            >
                                {{ ucfirst($role->name) }}
                            </flux:badge>
                        @endforeach
                    </flux:table.cell>
                    <flux:table.cell class="text-zinc-600 dark:text-zinc-400">
                        {{ $user->company_name ?? '' }}
                    </flux:table.cell>
                    <flux:table.cell class="text-zinc-600 dark:text-zinc-400">
                        {{ $user->phone ?? '' }}
                    </flux:table.cell>
                    <flux:table.cell class="text-zinc-500 text-sm">
                        {{ $user->created_at->format('d/m/Y') }}
                    </flux:table.cell>
                    @role('admin')
                    <flux:table.cell>
                        <flux:button icon="pencil" variant="ghost" size="sm" wire:click="openEdit({{ $user->id }})" />
                    </flux:table.cell>
                    @endrole
                </flux:table.row>
                @empty
                <flux:table.row>
                    <flux:table.cell colspan="6" class="text-center py-12 text-zinc-400">
                        <flux:icon.users class="w-12 h-12 mx-auto mb-3 opacity-50" />
                        <p>No users found.</p>
                    </flux:table.cell>
                </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>

        @if ($users->hasPages())
        <div class="px-4 py-4 border-t border-zinc-200 dark:border-zinc-700">
            <flux:pagination :paginator="$users" />
        </div>
        @endif
    </flux:card>

    {{-- Edit User Modal --}}
    <flux:modal name="edit-user" class="md:w-96">
        <form wire:submit="updateUser">
            <div class="space-y-6">
                <flux:heading size="lg">Edit User</flux:heading>

                <flux:input wire:model="editForm.name" label="Full Name" placeholder="Enter full name" required />
                <flux:input wire:model="editForm.email" label="Email" type="email" placeholder="Enter email address" required />
                <flux:input wire:model="editForm.phone" label="Phone" placeholder="e.g. 0123456789" />
                <flux:select wire:model="editForm.role" label="Role" placeholder="Select role" required>
                    <flux:select.option value="admin">Admin</flux:select.option>
                    <flux:select.option value="pic">PIC</flux:select.option>
                </flux:select>
                <flux:input wire:model="editForm.password" label="New Password" type="password" placeholder="Leave blank to keep current" viewable />

                <div class="flex gap-2 justify-end">
                    <flux:modal.close>
                        <flux:button variant="ghost">Cancel</flux:button>
                    </flux:modal.close>
                    <flux:button type="submit" variant="primary">Save Changes</flux:button>
                </div>
            </div>
        </form>
    </flux:modal>

    {{-- Create User Modal --}}
    <flux:modal name="create-user" class="md:w-96">
        <form wire:submit="createUser">
            <div class="space-y-6">
                <flux:heading size="lg">Add New User</flux:heading>

                <flux:input wire:model="form.name" label="Full Name" placeholder="Enter full name" required />
                <flux:input wire:model="form.email" label="Email" type="email" placeholder="Enter email address" required />
                <flux:input wire:model="form.phone" label="Phone" placeholder="e.g. 0123456789" />
                <flux:select wire:model="form.role" label="Role" placeholder="Select role" required>
                    <flux:select.option value="admin">Admin</flux:select.option>
                    <flux:select.option value="pic">PIC</flux:select.option>
                </flux:select>
                <flux:input wire:model="form.password" label="Password" type="password" placeholder="Minimum 8 characters" viewable required />

                <div class="flex gap-2 justify-end">
                    <flux:modal.close>
                        <flux:button variant="ghost">Cancel</flux:button>
                    </flux:modal.close>
                    <flux:button type="submit" variant="primary">Create User</flux:button>
                </div>
            </div>
        </form>
    </flux:modal>
</div>
