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

    <flux:card class="dark:bg-zinc-900 overflow-hidden">
        <div
            wire:key="step-{{ $step }}"
            x-data="{ show: false }"
            x-init="$nextTick(() => show = true)"
            x-show="show"
            @if ($direction === 'forward')
            x-transition:enter="transition duration-500 ease-out"
            x-transition:enter-start="opacity-0 translate-x-8"
            x-transition:enter-end="opacity-100 translate-x-0"
            @else
            x-transition:enter="transition duration-500 ease-out"
            x-transition:enter-start="opacity-0 -translate-x-8"
            x-transition:enter-end="opacity-100 translate-x-0"
            @endif
        >

        {{-- Step 1: Claim Type --}}
        @if ($step === 1)
        <flux:heading size="lg" class="mb-4">Step 1: Select Claim Type</flux:heading>

        <flux:radio.group wire:model.live="claimType" label="Claim Type" variant="cards" :indicator="false" class="mb-6 max-sm:flex-col">
            <flux:radio value="fwhs" icon="building-office-2" label="Insurance (FWHS)" description="Foreign Worker Hospitalization Scheme" />
            <flux:radio value="green_card" icon="credit-card" label="Green Card" description="Construction industry insurance" />
            <flux:radio value="perkeso" icon="shield-check" label="PERKESO" description="Social Security (SOCSO)" />
        </flux:radio.group>

        @error('claimType') <p class="text-red-500 dark:text-red-400 text-sm mb-4">{{ $message }}</p> @enderror

        <flux:radio.group wire:model="claimCategory" label="Claim Category" variant="cards" :indicator="false" class="mb-6 max-sm:flex-col">
            <flux:radio value="hospitalization" icon="building-office" label="Hospitalization" />
            @if ($claimType !== 'fwhs')
            <flux:radio value="death" icon="heart" label="Death" />
            @endif
        </flux:radio.group>

        @error('claimCategory') <p class="text-red-500 dark:text-red-400 text-sm mb-4">{{ $message }}</p> @enderror
        @endif

        {{-- Step 2: Worker Info --}}
        @if ($step === 2)
        <flux:heading size="lg" class="mb-4">Step 2: Worker Information</flux:heading>

        <div class="flex gap-3 mb-2">
            <flux:input wire:model="passportNumber" placeholder="Enter passport number" label="Passport Number" class="flex-1" />
            <flux:button wire:click="lookupWorker" variant="filled" class="self-end">Search</flux:button>
        </div>
        @error('passportNumber') <p class="text-red-500 dark:text-red-400 text-sm mb-3">{{ $message }}</p> @enderror

        @if ($workerNotFound)
        <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4 mb-4">
            <p class="font-semibold text-red-700 dark:text-red-400">Worker not found</p>
            <p class="text-sm text-red-600 dark:text-red-400">No worker with passport number <strong>{{ $passportNumber }}</strong> was found in the system.</p>
        </div>
        @endif

        @if ($foundWorker)
        <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-4 mb-4">
            <div class="flex items-center gap-3 mb-3">
                <flux:icon.check-circle class="w-5 h-5 text-green-600 dark:text-green-400" />
                <p class="font-semibold text-green-800 dark:text-green-300">Worker Found</p>
            </div>
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-x-6 gap-y-3 text-sm">
                <div>
                    <p class="text-zinc-500 dark:text-zinc-400">Name</p>
                    <p class="font-medium text-zinc-800 dark:text-zinc-200">{{ $foundWorker['name'] ?: '—' }}</p>
                </div>
                <div>
                    <p class="text-zinc-500 dark:text-zinc-400">Gender</p>
                    <p class="font-medium text-zinc-800 dark:text-zinc-200">{{ $foundWorker['gender'] ?: '—' }}</p>
                </div>
                <div>
                    <p class="text-zinc-500 dark:text-zinc-400">Date of Birth</p>
                    <p class="font-medium text-zinc-800 dark:text-zinc-200">{{ $foundWorker['date_of_birth'] ?: '—' }}</p>
                </div>
                <div>
                    <p class="text-zinc-500 dark:text-zinc-400">Passport No.</p>
                    <p class="font-medium text-zinc-800 dark:text-zinc-200">{{ $foundWorker['passport_number'] ?: '—' }}</p>
                </div>
                <div>
                    <p class="text-zinc-500 dark:text-zinc-400">Passport Expiry</p>
                    <p class="font-medium text-zinc-800 dark:text-zinc-200">{{ $foundWorker['passport_expiry'] ?: '—' }}</p>
                </div>
                <div>
                    <p class="text-zinc-500 dark:text-zinc-400">Permit Expiry</p>
                    <p class="font-medium text-zinc-800 dark:text-zinc-200">{{ $foundWorker['permit_expiry'] ?: '—' }}</p>
                </div>
                <div>
                    <p class="text-zinc-500 dark:text-zinc-400">Nationality</p>
                    <p class="font-medium text-zinc-800 dark:text-zinc-200">{{ $foundWorker['nationality'] ?: '—' }}</p>
                </div>
                <div>
                    <p class="text-zinc-500 dark:text-zinc-400">Contractor</p>
                    <p class="font-medium text-zinc-800 dark:text-zinc-200">{{ $foundWorker['contractor_name'] ?: '—' }}</p>
                </div>
                <div>
                    <p class="text-zinc-500 dark:text-zinc-400">Contractor Address</p>
                    <p class="font-medium text-zinc-800 dark:text-zinc-200">{{ $foundWorker['contractor_address'] ?: '—' }}</p>
                </div>
            </div>
        </div>
        @endif
        @endif

        {{-- Step 3: Incident Details --}}
        @if ($step === 3)
        <flux:heading size="lg" class="mb-4">Step 3: Incident Details</flux:heading>

        <flux:radio.group wire:model.live="incidentType" label="Incident Type" variant="cards" :indicator="false" class="mb-6 max-sm:flex-col">
            <flux:radio value="accident" icon="ambulance" label="Accident" description="Work-related accident or injury" />
            <flux:radio value="non_accident" icon="heart-crack" label="Non-Accident" description="Illness, disease, or other medical condition" />
        </flux:radio.group>
        @error('incidentType') <p class="text-red-500 dark:text-red-400 text-sm -mt-4 mb-4">{{ $message }}</p> @enderror

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
        <flux:heading size="lg" class="mb-1">Step 4: Required Documents</flux:heading>
        <flux:text class="mb-6">Please download, complete, and send the following documents to our office by post or in person.</flux:text>

        {{-- Downloadable Form --}}
        @php $downloadableDocs = ['accident_fcl', 'non_accident_fcl']; @endphp
        <p class="text-xs font-semibold uppercase tracking-wide text-zinc-400 dark:text-zinc-500 mb-2">Form to Download & Complete</p>
        <div class="space-y-2 mb-6">
            @foreach ($requiredDocs as $docType => $docLabel)
            @if (in_array($docType, $downloadableDocs))
            <div class="flex items-center justify-between border border-blue-200 dark:border-blue-800 bg-blue-50 dark:bg-blue-900/20 rounded-lg p-4">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 bg-blue-100 dark:bg-blue-900/50 rounded-lg flex items-center justify-center">
                        <flux:icon.arrow-down-tray class="w-5 h-5 text-blue-600 dark:text-blue-400" />
                    </div>
                    <div>
                        <p class="font-medium text-zinc-800 dark:text-zinc-200">{{ $docLabel }}</p>
                        <p class="text-xs text-zinc-500 dark:text-zinc-400">Download, fill in, and include with your submission</p>
                    </div>
                </div>
                <flux:button size="sm" variant="filled" icon="arrow-down-tray">Download</flux:button>
            </div>
            @endif
            @endforeach
        </div>

        {{-- Original Supporting Documents --}}
        <p class="text-xs font-semibold uppercase tracking-wide text-zinc-400 dark:text-zinc-500 mb-2">Original Documents to Submit</p>
        <div class="space-y-2 mb-6">
            @foreach ($requiredDocs as $docType => $docLabel)
            @if (!in_array($docType, $downloadableDocs))
            <div class="flex items-center gap-3 border border-zinc-200 dark:border-zinc-700 rounded-lg p-4">
                <div class="w-9 h-9 bg-zinc-100 dark:bg-zinc-800 rounded-lg flex items-center justify-center">
                    <flux:icon.document-text class="w-5 h-5 text-zinc-500 dark:text-zinc-400" />
                </div>
                <div>
                    <p class="font-medium text-zinc-800 dark:text-zinc-200">{{ $docLabel }}</p>
                    <p class="text-xs text-zinc-500 dark:text-zinc-400">Please submit the original copy</p>
                </div>
            </div>
            @endif
            @endforeach
        </div>

        {{-- Postal Address --}}
        <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-lg p-4">
            <div class="flex items-start gap-3">
                <flux:icon.map-pin class="w-5 h-5 text-amber-600 dark:text-amber-400 mt-0.5 shrink-0" />
                <div>
                    <p class="font-semibold text-amber-800 dark:text-amber-300 mb-1">Send Completed Documents To:</p>
                    <p class="text-sm text-amber-700 dark:text-amber-400 leading-relaxed">
                        Construction Labour Exchange Centre Berhad (CLAB)<br>
                        Level 2, Annexe Block, Menara Milenium,<br>
                        No. 8, Jalan Damanlela,<br>
                        Pusat Bandar Damansara,<br>
                        50490 Kuala Lumpur.
                    </p>
                </div>
            </div>
        </div>

        <flux:text class="mt-4 text-sm text-zinc-500 dark:text-zinc-400">
            Once submitted, our admin team will process your claim and mark each document as received upon arrival.
        </flux:text>
        @endif

        </div>
    </flux:card>

    {{-- Navigation Buttons --}}
    <div class="flex justify-between mt-6">
        @if ($step > 1)
        <flux:button wire:click="previousStep" variant="filled">
            Previous
        </flux:button>
        @else
        <div></div>
        @endif

        @if ($step < 4)
        <flux:button wire:click="nextStep" variant="primary">
            Next
        </flux:button>
        @else
        <flux:button wire:click="submit" variant="primary" icon="paper-airplane">
            Submit Claim
        </flux:button>
        @endif
    </div>
</div>
