<?php

namespace App\Livewire\Settings;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Layout('components.layouts.app')]
#[Title('Settings')]
class UserSettings extends Component
{
    // Profile
    public string $name = '';
    public string $email = '';
    public string $phone = '';
    public string $company_name = '';

    // Password
    public string $current_password = '';
    public string $new_password = '';
    public string $new_password_confirmation = '';

    public function mount(): void
    {
        $user = Auth::user();
        $this->name         = $user->name;
        $this->email        = $user->email;
        $this->phone        = $user->phone ?? '';
        $this->company_name = $user->company_name ?? '';
    }

    public function saveProfile(): void
    {
        $this->validate([
            'name'         => 'required|string|max:255',
            'email'        => 'required|email|max:255|unique:users,email,' . Auth::id(),
            'phone'        => 'nullable|string|max:20',
            'company_name' => 'nullable|string|max:255',
        ]);

        Auth::user()->update([
            'name'         => $this->name,
            'email'        => $this->email,
            'phone'        => $this->phone ?: null,
            'company_name' => $this->company_name ?: null,
        ]);

        session()->flash('profile_success', 'Profile updated successfully.');
    }

    public function savePassword(): void
    {
        $this->validate([
            'current_password' => 'required',
            'new_password'     => ['required', 'confirmed', Password::min(8)],
        ]);

        if (! Hash::check($this->current_password, Auth::user()->password)) {
            $this->addError('current_password', 'The current password is incorrect.');
            return;
        }

        Auth::user()->update([
            'password' => Hash::make($this->new_password),
        ]);

        $this->current_password          = '';
        $this->new_password              = '';
        $this->new_password_confirmation = '';

        session()->flash('password_success', 'Password updated successfully.');
    }

    public function render()
    {
        return view('livewire.settings.user-settings');
    }
}
