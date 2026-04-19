<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public string $password = '';

    public function confirmPassword(): void
    {
        $this->validate([
            'password' => ['required', 'string'],
        ]);

        if (! Auth::guard('web')->validate([
            'email'    => Auth::user()->email,
            'password' => $this->password,
        ])) {
            throw ValidationException::withMessages([
                'password' => __('auth.password'),
            ]);
        }

        session(['auth.password_confirmed_at' => time()]);

        $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);
    }
}; ?>

<div>
    <flux:heading size="xl" class="mb-1">Confirm password</flux:heading>
    <flux:subheading class="mb-6">
        This is a secure area. Please confirm your password before continuing.
    </flux:subheading>

    <form wire:submit="confirmPassword" class="space-y-5">
        <flux:input
            wire:model="password"
            label="Password"
            type="password"
            placeholder="••••••••"
            autocomplete="current-password"
            viewable
            autofocus
            required
        />
        @error('password') <p class="text-red-500 text-sm -mt-3">{{ $message }}</p> @enderror

        <flux:button type="submit" variant="primary" class="w-full">
            Confirm
        </flux:button>
    </form>
</div>
