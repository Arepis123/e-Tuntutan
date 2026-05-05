<?php

use App\Livewire\Forms\LoginForm;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public LoginForm $form;

    public function login(): void
    {
        $this->validate();

        $this->form->authenticate();

        Session::regenerate();

        $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);
    }
}; ?>

<div>
    <flux:heading size="xl" class="mb-1">Sign in</flux:heading>
    <flux:subheading class="mb-6">Enter your credentials to access e-Tuntutan</flux:subheading>

    @if (session('status'))
        <flux:callout variant="success" icon="check-circle" class="mb-4">
            {{ session('status') }}
        </flux:callout>
    @endif

    <form wire:submit="login" class="space-y-5">
        <flux:input
            wire:model="form.username"
            label="Username"
            type="text"
            placeholder="Enter your username"
            autofocus
            autocomplete="username"
            required
        />

        <flux:input
            wire:model="form.password"
            label="Password"
            type="password"
            placeholder="Enter your password"
            autocomplete="current-password"
            viewable
            required
        />

        <div class="flex items-center justify-center">
            <flux:text class="text-center">Please use your credentials from the e-CLAB Portal to log in.</flux:text>
        </div>

        <!-- <div class="flex items-center">
            <flux:checkbox wire:model="form.remember" label="Remember me" />
        </div> -->

        <flux:button type="submit" variant="primary" class="w-full">
            Sign in
        </flux:button>
    </form>
</div>
