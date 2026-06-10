<?php

namespace App\Livewire\Roles;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

#[Layout('components.layouts.app')]
#[Title('Roles & Permissions')]
class RoleList extends Component
{
    public string $selectedRole = '';
    public array $rolePermissions = [];

    public function mount(): void
    {
        $first = Role::first();
        if ($first) {
            $this->selectRole($first->name);
        }
    }

    public function selectRole(string $roleName): void
    {
        $this->selectedRole = $roleName;
        $role = Role::findByName($roleName);
        $this->rolePermissions = $role->permissions->pluck('name')->toArray();
    }

    public function savePermissions(): void
    {
        $role = Role::findByName($this->selectedRole);
        $role->syncPermissions($this->rolePermissions);

        $this->dispatch('notify', message: 'Permissions updated for ' . ucfirst($this->selectedRole) . '.');
    }

    public function render()
    {
        $roles = Role::all();

        $hidden = ['claims.forward'];

        $permissions = Permission::all()
            ->reject(fn ($p) => in_array($p->name, $hidden) || str_starts_with($p->name, 'payments.'))
            ->groupBy(fn ($p) => ucfirst(explode('.', $p->name)[0]));

        return view('livewire.roles.role-list', compact('roles', 'permissions'));
    }
}
