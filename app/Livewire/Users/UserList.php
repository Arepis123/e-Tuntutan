<?php

namespace App\Livewire\Users;

use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Illuminate\Support\Facades\Hash;

#[Layout('components.layouts.app')]
#[Title('Users')]
class UserList extends Component
{
    use WithPagination;

    #[Url(as: 'q')]
    public string $search = '';

    #[Url]
    public string $roleFilter = '';

    #[Url]
    public string $sortBy = 'created_at';

    #[Url]
    public string $sortDirection = 'desc';

    public array $form = [
        'name'     => '',
        'email'    => '',
        'phone'    => '',
        'role'     => '',
        'password' => '',
    ];

    public ?int $editingUserId = null;

    public array $editForm = [
        'name'                 => '',
        'email'                => '',
        'phone'                => '',
        'role'                 => '',
        'password'             => '',
        'notify_on_submission' => true,
    ];

    public function updatedSearch(): void { $this->resetPage(); }
    public function updatedRoleFilter(): void { $this->resetPage(); }

    public function sort(string $column): void
    {
        if ($this->sortBy === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $column;
            $this->sortDirection = 'asc';
        }
        $this->resetPage();
    }

    public function createUser(): void
    {
        $this->validate([
            'form.name'     => 'required|string|max:255',
            'form.email'    => 'required|email|unique:users,email',
            'form.phone'    => 'nullable|string|max:20',
            'form.role'     => 'required|in:admin,pic',
            'form.password' => 'required|string|min:8',
        ]);

        $user = User::create([
            'name'     => $this->form['name'],
            'email'    => $this->form['email'],
            'phone'    => $this->form['phone'],
            'password' => Hash::make($this->form['password']),
        ]);

        $user->assignRole($this->form['role']);

        $this->form = ['name' => '', 'email' => '', 'phone' => '', 'role' => '', 'password' => ''];
        $this->modal('create-user')->close();
        $this->dispatch('notify', message: 'User created successfully.');
    }

    public function openEdit(int $userId): void
    {
        $user = User::with('roles')->findOrFail($userId);

        $this->editingUserId = $userId;
        $this->editForm = [
            'name'                 => $user->name,
            'email'                => $user->email,
            'phone'                => $user->phone ?? '',
            'role'                 => $user->roles->first()?->name ?? '',
            'password'             => '',
            'notify_on_submission' => (bool) $user->notify_on_submission,
        ];

        $this->modal('edit-user')->show();
    }

    public function updateUser(): void
    {
        $this->validate([
            'editForm.name'     => 'required|string|max:255',
            'editForm.email'    => 'required|email|unique:users,email,' . $this->editingUserId,
            'editForm.phone'    => 'nullable|string|max:20',
            'editForm.role'     => 'required|in:admin,pic',
            'editForm.password' => 'nullable|string|min:8',
        ]);

        $user = User::findOrFail($this->editingUserId);

        $user->update([
            'name'                 => $this->editForm['name'],
            'email'                => $this->editForm['email'],
            'phone'                => $this->editForm['phone'],
            'notify_on_submission' => $this->editForm['notify_on_submission'],
            ...($this->editForm['password'] ? ['password' => Hash::make($this->editForm['password'])] : []),
        ]);

        $user->syncRoles([$this->editForm['role']]);

        $this->editingUserId = null;
        $this->editForm = ['name' => '', 'email' => '', 'phone' => '', 'role' => '', 'password' => '', 'notify_on_submission' => true];
        $this->modal('edit-user')->close();
        $this->dispatch('notify', message: 'User updated successfully.');
    }

    public function render()
    {
        $stats = [
            'total'    => User::count(),
            'pic'      => User::role('pic')->count(),
            'employer' => User::role('employer')->count(),
        ];

        $users = User::with('roles')
            ->when($this->search, function ($q) {
                $q->where(function ($q) {
                    $q->where('name', 'like', "%{$this->search}%")
                      ->orWhere('email', 'like', "%{$this->search}%")
                      ->orWhere('company_name', 'like', "%{$this->search}%");
                });
            })
            ->when($this->roleFilter, fn ($q) => $q->role($this->roleFilter))
            ->when($this->sortBy === 'role', function ($q) {
                $q->join('model_has_roles', 'users.id', '=', 'model_has_roles.model_id')
                  ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
                  ->orderBy('roles.name', $this->sortDirection)
                  ->select('users.*');
            }, function ($q) {
                $q->orderBy($this->sortBy, $this->sortDirection);
            })
            ->paginate(15);

        return view('livewire.users.user-list', compact('users', 'stats'));
    }
}
