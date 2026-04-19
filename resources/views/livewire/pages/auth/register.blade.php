<?php

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';

    public function register(): void
    {
        $validated = $this->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
        ]);

        $validated['password'] = Hash::make($validated['password']);

        event(new Registered($user = User::create($validated)));

        Auth::login($user);

        $this->redirect(route('dashboard', absolute: false), navigate: true);
    }
}; ?>

<div>
    <flux:heading size="xl" class="mb-1">Create account</flux:heading>
    <flux:subheading class="mb-6">Register to submit and track claims</flux:subheading>

    <form wire:submit="register" class="space-y-5">
        <flux:input
            wire:model="name"
            label="Full Name"
            placeholder="Your full name"
            autofocus
            autocomplete="name"
            required
        />
        @error('name') <p class="text-red-500 text-sm -mt-3">{{ $message }}</p> @enderror

        <flux:input
            wire:model="email"
            label="Email"
            type="email"
            placeholder="you@example.com"
            autocomplete="username"
            required
        />
        @error('email') <p class="text-red-500 text-sm -mt-3">{{ $message }}</p> @enderror

        <flux:input
            wire:model="password"
            label="Password"
            type="password"
            placeholder="Min. 8 characters"
            autocomplete="new-password"
            viewable
            required
        />
        @error('password') <p class="text-red-500 text-sm -mt-3">{{ $message }}</p> @enderror

        <flux:input
            wire:model="password_confirmation"
            label="Confirm Password"
            type="password"
            placeholder="Repeat password"
            autocomplete="new-password"
            viewable
            required
        />

        <flux:button type="submit" variant="primary" class="w-full">
            Create Account
        </flux:button>

        <p class="text-center text-sm text-zinc-500">
            Already have an account?
            <flux:link href="{{ route('login') }}" wire:navigate>Sign in</flux:link>
        </p>
    </form>
</div>
