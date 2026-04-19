<div>
    <div class="mb-6">
        <flux:heading size="xl">Submit New Claim</flux:heading>
        <flux:subheading>Complete the form in 4 steps</flux:subheading>
    </div>

    {{-- Step Indicator --}}
    <div class="mb-8">
        <flux:timeline horizontal>
            <flux:timeline.item status="{{ $step > 1 ? 'complete' : ($step === 1 ? 'current' : 'incomplete') }}">
                <flux:timeline.indicator>
                    <flux:icon.tag variant="micro" />
                </flux:timeline.indicator>
                <flux:timeline.content>
                    <flux:heading>Claim Type</flux:heading>
                </flux:timeline.content>
            </flux:timeline.item>
            <flux:timeline.item status="{{ $step > 2 ? 'complete' : ($step === 2 ? 'current' : 'incomplete') }}">
                <flux:timeline.indicator>
                    <flux:icon.user variant="micro" />
                </flux:timeline.indicator>
                <flux:timeline.content>
                    <flux:heading>Worker Info</flux:heading>
                </flux:timeline.content>
            </flux:timeline.item>
            <flux:timeline.item status="{{ $step > 3 ? 'complete' : ($step === 3 ? 'current' : 'incomplete') }}">
                <flux:timeline.indicator>
                    <flux:icon.document-text variant="micro" />
                </flux:timeline.indicator>
                <flux:timeline.content>
                    <flux:heading>Incident Details</flux:heading>
                </flux:timeline.content>
            </flux:timeline.item>
            <flux:timeline.item status="{{ $step === 4 ? 'current' : ($step > 4 ? 'complete' : 'incomplete') }}">
                <flux:timeline.indicator>
                    <flux:icon.paper-clip variant="micro" />
                </flux:timeline.indicator>
                <flux:timeline.content>
                    <flux:heading>Documents</flux:heading>
                </flux:timeline.content>
            </flux:timeline.item>
        </flux:timeline>
    </div>

    <div class="bg-white rounded-xl border border-zinc-200 p-6">

        {{-- Step 1: Claim Type --}}
        @if ($step === 1)
        <flux:heading size="lg" class="mb-4">Step 1: Select Claim Type</flux:heading>

        <flux:radio.group wire:model="claimType" label="Claim Type" variant="cards" :indicator="false" class="mb-6 max-sm:flex-col">
            <flux:radio value="fwhs" icon="building-office-2" label="Insurance (FWHS)" description="Foreign Worker Hospitalization Scheme" />
            <flux:radio value="green_card" icon="credit-card" label="Green Card" description="Construction industry insurance" />
            <flux:radio value="perkeso" icon="shield-check" label="PERKESO" description="Social Security (SOCSO)" />
        </flux:radio.group>

        @error('claimType') <p class="text-red-500 text-sm mb-4">{{ $message }}</p> @enderror

        <flux:radio.group wire:model="claimCategory" label="Claim Category" variant="cards" :indicator="false" class="mb-6 max-sm:flex-col">
            <flux:radio value="hospitalization" icon="building-office" label="Hospitalization" />
            <flux:radio value="death" icon="heart" label="Death" />
        </flux:radio.group>

        @error('claimCategory') <p class="text-red-500 text-sm mb-4">{{ $message }}</p> @enderror
        @endif

        {{-- Step 2: Worker Info --}}
        @if ($step === 2)
        <flux:heading size="lg" class="mb-4">Step 2: Worker Information</flux:heading>

        <flux:radio.group wire:model.live="workerType" label="Worker Type" variant="cards" :indicator="false" class="mb-4 max-sm:flex-col">
            <flux:radio value="existing" icon="magnifying-glass" label="Existing Worker" description="Search by passport number" />
            <flux:radio value="new" icon="user-plus" label="New Worker" description="Enter worker details manually" />
        </flux:radio.group>

        @if ($workerType === 'existing')
        <div class="flex gap-3 mb-4">
            <flux:input wire:model="passportNumber" placeholder="Passport Number" class="flex-1" />
            <flux:button wire:click="lookupWorker" variant="filled">Search</flux:button>
        </div>
        @error('passportNumber') <p class="text-red-500 text-sm mb-2">{{ $message }}</p> @enderror

        @if ($foundWorker)
        <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-4">
            <p class="font-semibold text-green-800">{{ $foundWorker->name }}</p>
            <p class="text-sm text-green-600">{{ $foundWorker->passport_number }} — {{ $foundWorker->nationality }}</p>
        </div>
        @endif
        @endif

        @if ($workerType === 'new')
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <flux:input wire:model="workerName" label="Full Name" required />
            <flux:input wire:model="passportNumber" label="Passport Number" required />
            <flux:input wire:model="nationality" label="Nationality" required />
            <flux:input wire:model="dateOfBirth" label="Date of Birth" type="date" />
            <flux:input wire:model="employerName" label="Employer Name" />
            <flux:input wire:model="employerIc" label="Employer IC" />
            <flux:input wire:model="phone" label="Phone Number" />
            <flux:input wire:model="address" label="Address" />
        </div>
        @endif
        @endif

        {{-- Step 3: Incident Details --}}
        @if ($step === 3)
        <flux:heading size="lg" class="mb-4">Step 3: Incident Details</flux:heading>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <flux:input wire:model="incidentDate" label="Incident Date" type="date" required />

            @if ($claimCategory === 'hospitalization')
            <flux:input wire:model="hospitalName" label="Hospital Name" required />
            <flux:input wire:model="admissionDate" label="Admission Date" type="date" required />
            <flux:input wire:model="dischargeDate" label="Discharge Date" type="date" />
            @endif
        </div>

        <div class="mt-4">
            <flux:textarea wire:model="incidentDescription" label="Incident Description" rows="4" required />
        </div>
        @endif

        {{-- Step 4: Documents --}}
        @if ($step === 4)
        <flux:heading size="lg" class="mb-4">Step 4: Upload Documents</flux:heading>

        @if (count($requiredDocs) > 0)
        <div class="space-y-4">
            @foreach ($requiredDocs as $docType => $docLabel)
            <div class="border border-zinc-200 rounded-lg p-4">
                <p class="font-medium text-zinc-700 mb-2">{{ $docLabel }}</p>
                <flux:input type="file" wire:model="uploadedFiles.{{ $docType }}" accept=".pdf,.jpg,.jpeg,.png" />
                @error("uploadedFiles.{$docType}") <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
            </div>
            @endforeach
        </div>
        @endif

        <p class="text-sm text-zinc-400 mt-4">Accepted formats: PDF, JPG, PNG. Maximum size: 10MB per file.</p>
        @endif

    </div>

    {{-- Navigation Buttons --}}
    <div class="flex justify-between mt-6">
        <flux:button
            wire:click="previousStep"
            variant="ghost"
            :disabled="$step === 1"
        >
            Previous
        </flux:button>

        @if ($step < 4)
        <flux:button wire:click="nextStep" variant="filled">
            Next
        </flux:button>
        @else
        <flux:button wire:click="submit" variant="filled" icon="paper-airplane">
            Submit Claim
        </flux:button>
        @endif
    </div>
</div>
