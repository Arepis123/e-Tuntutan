<?php

use Illuminate\Support\Facades\Password;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public string $email = '';

    public function sendPasswordResetLink(): void
    {
        $this->validate([
            'email' => ['required', 'string', 'email'],
        ]);

        $status = Password::sendResetLink($this->only('email'));

        if ($status != Password::RESET_LINK_SENT) {
            $this->addError('email', __($status));
            return;
        }

        $this->reset('email');
        session()->flash('status', __($status));
    }
}; ?>

<div>
    <flux:heading size="xl" class="mb-1">Forgot password?</flux:heading>
    <flux:subheading class="mb-6">Enter your email and we'll send you a reset link.</flux:subheading>

    @if (session('status'))
        <flux:callout variant="success" icon="check-circle" class="mb-4">
            {{ session('status') }}
        </flux:callout>
    @endif

    <form wire:submit="sendPasswordResetLink" class="space-y-5">
        <flux:input
            wire:model="email"
            label="Email"
            type="email"
            placeholder="you@example.com"
            autofocus
            required
        />
        @error('email') <p class="text-red-500 text-sm -mt-3">{{ $message }}</p> @enderror

        <flux:button type="submit" variant="primary" class="w-full">
            Send Reset Link
        </flux:button>

        <p class="text-center text-sm text-zinc-500">
            <flux:link href="{{ route('login') }}" wire:navigate>Back to sign in</flux:link>
        </p>
    </form>
</div>
