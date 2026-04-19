<?php

use App\Livewire\Actions\Logout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public function sendVerification(): void
    {
        if (Auth::user()->hasVerifiedEmail()) {
            $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);
            return;
        }

        Auth::user()->sendEmailVerificationNotification();
        Session::flash('status', 'verification-link-sent');
    }

    public function logout(Logout $logout): void
    {
        $logout();
        $this->redirect('/', navigate: true);
    }
}; ?>

<div>
    <flux:heading size="xl" class="mb-1">Verify your email</flux:heading>
    <flux:subheading class="mb-6">
        Thanks for signing up! Please verify your email address by clicking the link we sent you.
    </flux:subheading>

    @if (session('status') === 'verification-link-sent')
        <flux:callout variant="success" icon="check-circle" class="mb-4">
            A new verification link has been sent to your email address.
        </flux:callout>
    @endif

    <div class="space-y-3">
        <flux:button wire:click="sendVerification" variant="primary" class="w-full">
            Resend Verification Email
        </flux:button>

        <flux:button wire:click="logout" variant="ghost" class="w-full">
            Log Out
        </flux:button>
    </div>
</div>
