<div>
    <flux:main container class="max-w-xl lg:max-w-3xl !mx-0">

        <flux:heading size="xl">Settings</flux:heading>

        <flux:separator variant="subtle" class="my-8" />

        {{-- Profile --}}
        <div class="flex flex-col lg:flex-row gap-4 lg:gap-6">
            <div class="w-80 shrink-0">
                <flux:heading size="lg">Profile</flux:heading>
                <flux:subheading>Update your personal information and contact details.</flux:subheading>
            </div>
            <div class="flex-1 space-y-6">
                @if (session('profile_success'))
                <div class="rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-700">
                    {{ session('profile_success') }}
                </div>
                @endif

                <flux:input
                    wire:model="name"
                    label="Full Name"
                    placeholder="Your full name"
                    required
                />
                @error('name') <p class="text-red-500 text-sm -mt-4">{{ $message }}</p> @enderror

                <flux:input
                    wire:model="email"
                    label="Email Address"
                    type="email"
                    placeholder="you@example.com"
                    required
                />
                @error('email') <p class="text-red-500 text-sm -mt-4">{{ $message }}</p> @enderror

                <flux:input
                    wire:model="phone"
                    label="Phone Number"
                    placeholder="+60 12-345 6789"
                />
                @error('phone') <p class="text-red-500 text-sm -mt-4">{{ $message }}</p> @enderror

                <flux:input
                    wire:model="company_name"
                    label="Company / Organisation"
                    placeholder="Your company name"
                />
                @error('company_name') <p class="text-red-500 text-sm -mt-4">{{ $message }}</p> @enderror

                <div class="flex justify-end">
                    <flux:button wire:click="saveProfile" variant="primary">Save Profile</flux:button>
                </div>
            </div>
        </div>

        <flux:separator variant="subtle" class="my-8" />

        {{-- Password --}}
        <div class="flex flex-col lg:flex-row gap-4 lg:gap-6">
            <div class="w-80 shrink-0">
                <flux:heading size="lg">Password</flux:heading>
                <flux:subheading>Ensure your account is using a strong password to stay secure.</flux:subheading>
            </div>
            <div class="flex-1 space-y-6">
                @if (session('password_success'))
                <div class="rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-700">
                    {{ session('password_success') }}
                </div>
                @endif

                <flux:input
                    wire:model="current_password"
                    label="Current Password"
                    type="password"
                    placeholder="Enter current password"
                    required
                />
                @error('current_password') <p class="text-red-500 text-sm -mt-4">{{ $message }}</p> @enderror

                <flux:input
                    wire:model="new_password"
                    label="New Password"
                    type="password"
                    placeholder="Min. 8 characters"
                    required
                />
                @error('new_password') <p class="text-red-500 text-sm -mt-4">{{ $message }}</p> @enderror

                <flux:input
                    wire:model="new_password_confirmation"
                    label="Confirm New Password"
                    type="password"
                    placeholder="Repeat new password"
                    required
                />

                <div class="flex justify-end">
                    <flux:button wire:click="savePassword" variant="primary">Update Password</flux:button>
                </div>
            </div>
        </div>

        <flux:separator variant="subtle" class="my-8" />

        {{-- Appearance --}}
        <div class="flex flex-col lg:flex-row gap-4 lg:gap-6 pb-10">
            <div class="w-80 shrink-0">
                <flux:heading size="lg">Appearance</flux:heading>
                <flux:subheading>Choose how the interface looks. Changes apply instantly and persist across sessions.</flux:subheading>
            </div>
            <div class="flex-1" x-data>
                <flux:radio.group variant="cards" :indicator="false" x-model="$flux.appearance" class="flex-col sm:flex-row">
                    <flux:radio value="light" label="Light" icon="sun" description="Always use light theme" />
                    <flux:radio value="dark" label="Dark" icon="moon" description="Always use dark theme" />
                    <flux:radio value="system" label="System" icon="computer-desktop" description="Follow device setting" />
                </flux:radio.group>
            </div>
        </div>

    </flux:main>
</div>
