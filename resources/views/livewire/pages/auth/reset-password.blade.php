<?php

use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    #[Locked]
    public string $token = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';

    public function mount(string $token): void
    {
        $this->token = $token;
        $this->email = request()->string('email');
    }

    public function resetPassword(): void
    {
        $this->validate([
            'token'    => ['required'],
            'email'    => ['required', 'string', 'email'],
            'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
        ]);

        $status = Password::reset(
            $this->only('email', 'password', 'password_confirmation', 'token'),
            function ($user) {
                $user->forceFill([
                    'password'       => Hash::make($this->password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        if ($status != Password::PASSWORD_RESET) {
            $this->addError('email', __($status));
            return;
        }

        Session::flash('status', __($status));
        $this->redirectRoute('login', navigate: true);
    }
}; ?>

<div>
    <flux:heading size="xl" class="mb-1">Reset password</flux:heading>
    <flux:subheading class="mb-6">Choose a new password for your account.</flux:subheading>

    <form wire:submit="resetPassword" class="space-y-5">
        <flux:input
            wire:model="email"
            label="Email"
            type="email"
            autocomplete="username"
            required
        />
        @error('email') <p class="text-red-500 text-sm -mt-3">{{ $message }}</p> @enderror

        <flux:input
            wire:model="password"
            label="New Password"
            type="password"
            placeholder="Min. 8 characters"
            autocomplete="new-password"
            viewable
            required
        />
        @error('password') <p class="text-red-500 text-sm -mt-3">{{ $message }}</p> @enderror

        <flux:input
            wire:model="password_confirmation"
            label="Confirm New Password"
            type="password"
            placeholder="Repeat new password"
            autocomplete="new-password"
            viewable
            required
        />

        <flux:button type="submit" variant="primary" class="w-full">
            Reset Password
        </flux:button>
    </form>
</div>
