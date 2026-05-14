<div>
    <div class="mb-6">
        <flux:text class="text-2xl font-bold mb-2" variant="strong">Submit New Claim</flux:text>
        <flux:subheading>Complete the form in 4 steps</flux:subheading>
    </div>

    {{-- Step Indicator --}}
    <div class="mb-8">
        <flux:timeline horizontal>
            <flux:timeline.item status="{{ $step > 1 ? 'complete' : ($step === 1 ? 'current' : 'incomplete') }}">
                <flux:timeline.indicator>
                    <flux:icon.tag variant="micro" />
                </flux:timeline.indicator>
                <flux:timeline.content class="{{ $step === 1 ? '' : 'hidden sm:block' }}">
                    <flux:heading>Claim Type</flux:heading>
                </flux:timeline.content>
            </flux:timeline.item>
            <flux:timeline.item status="{{ $step > 2 ? 'complete' : ($step === 2 ? 'current' : 'incomplete') }}">
                <flux:timeline.indicator>
                    <flux:icon.user variant="micro" />
                </flux:timeline.indicator>
                <flux:timeline.content class="{{ $step === 2 ? '' : 'hidden sm:block' }}">
                    <flux:heading>Worker Info</flux:heading>
                </flux:timeline.content>
            </flux:timeline.item>
            <flux:timeline.item status="{{ $step > 3 ? 'complete' : ($step === 3 ? 'current' : 'incomplete') }}">
                <flux:timeline.indicator>
                    <flux:icon.document-text variant="micro" />
                </flux:timeline.indicator>
                <flux:timeline.content class="{{ $step === 3 ? '' : 'hidden sm:block' }}">
                    <flux:heading>Incident Details</flux:heading>
                </flux:timeline.content>
            </flux:timeline.item>
            <flux:timeline.item status="{{ $step === 4 ? 'current' : ($step > 4 ? 'complete' : 'incomplete') }}">
                <flux:timeline.indicator>
                    <flux:icon.paper-clip variant="micro" />
                </flux:timeline.indicator>
                <flux:timeline.content class="{{ $step === 4 ? '' : 'hidden sm:block' }}">
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
            <flux:radio value="green_card" icon="credit-card" label="Green Card" description="Construction Personnel Registration Card" />
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

        @if ($claimType === 'perkeso')
        <flux:checkbox.group wire:model="perkesoSchemes" label="PERKESO Claim Category" variant="cards" :indicator="false" class="mb-6 max-sm:flex-col">
            <flux:checkbox value="skim_bencana_kerja" icon="shield-check" label="Skim Bencana Kerja" />
            <flux:checkbox value="skim_pengurusan_jenazah" icon="user-circle" label="Skim Pengurusan Jenazah" />
            <flux:checkbox value="skim_luar_waktu_bekerja" icon="clock" label="Skim Luar Waktu Bekerja" />
        </flux:checkbox.group>

        @error('perkesoSchemes') <p class="text-red-500 dark:text-red-400 text-sm mb-4">{{ $message }}</p> @enderror
        @endif

        @endif

        {{-- Step 2: Worker Info --}}
        @if ($step === 2)
        <flux:heading size="lg" class="mb-4">Step 2: Worker Information</flux:heading>

        <div class="mb-2">
            <flux:label class="mb-1">Passport Number</flux:label>
            <div class="flex flex-col sm:flex-row gap-3">
                <flux:input wire:model="passportNumber" placeholder="Enter passport number" class="flex-1 max-w-xs" />
                <flux:button wire:click="lookupWorker" variant="filled" class="w-full sm:w-auto">Search</flux:button>
            </div>
            <flux:error name="passportNumber" />
        </div>

        @if ($workerNotFound)
        <flux:callout variant="danger" icon="x-circle" class="mb-4">
            <flux:callout.heading>Worker not found</flux:callout.heading>
            <flux:callout.text>No worker with passport number <strong>{{ $passportNumber }}</strong> was found in the system.</flux:callout.text>
        </flux:callout>
        @endif

        @if ($foundWorker)
        <flux:callout variant="success" icon="check-circle" class="mb-6">
            <flux:callout.heading>Worker Found</flux:callout.heading>
            <flux:callout.text>
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-x-6 gap-y-3 text-sm mt-2">
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
            </flux:callout.text>
        </flux:callout>

        {{-- Section I fields e, g, h — not available from worker DB --}}
        <p class="text-xs font-semibold uppercase tracking-wider text-zinc-400 dark:text-zinc-500 mb-3">Section I — Additional Employment Details</p>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">

            {{-- Date of Employment --}}
            <flux:date-picker locale="en-GB" wire:model="dateOfEmployment" with-today label="Date of Employment" type="input"/>

            {{-- Working Hours --}}
            <flux:field>
                <flux:label>Working Hours</flux:label>
                <div class="flex items-center gap-2 mt-1">
                    <flux:time-picker wire:model.live="workingHourFrom" class="flex-1" />
                    <span class="text-zinc-400 text-sm shrink-0">to</span>
                    <flux:time-picker wire:model="workingHourTo" class="flex-1" />
                </div>
            </flux:field>

        </div>

        {{-- Facilities Provided (hidden for now) --}}

        <flux:separator class="my-6"/>

        {{-- Company Details --}}
        <p class="text-xs font-semibold uppercase tracking-wider text-zinc-400 dark:text-zinc-500 mb-3">Company Details</p>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
            <flux:input wire:model="tinNo" label="Tax Identification No. (TIN)" placeholder="e.g. C12345678900" />
            <flux:input wire:model="sstNo" label="SST No." placeholder="e.g. W10-1234-12345678" />
        </div>

        <flux:separator class="my-6"/>

        {{-- Company PIC --}}
        <p class="text-xs font-semibold uppercase tracking-wider text-zinc-400 dark:text-zinc-500 mb-3">Person In Charge (Company)</p>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-4">
            <flux:input wire:model="companyPicName" label="Name" placeholder="Full name" />
            <flux:input wire:model="companyPicPhone" label="Phone Number" placeholder="e.g. 0123456789" />
            <flux:input wire:model="companyPicEmail" label="Email" type="email" placeholder="e.g. pic@company.com" />
        </div>
        @endif
        @endif

        {{-- Step 3: Incident Details --}}
        @if ($step === 3)
        <flux:heading size="lg" class="mb-1">Step 3: Incident Details</flux:heading>
        <flux:subheading class="mb-6">Based on FCL Form (CLAB/SOP/08/23)</flux:subheading>

        {{-- Incident Type --}}
        <flux:radio.group wire:model.live="incidentType" label="Incident Type" variant="cards" :indicator="false" class="mb-6 max-sm:flex-col">
            <flux:radio value="accident" icon="bolt" label="Accident" description="Worker was injured due to an accident at the workplace" />
            <flux:radio value="non_accident" icon="heart" label="Non-Accident" description="Worker fell ill or was diagnosed with a disease (not caused by an accident)" />
        </flux:radio.group>
        @error('incidentType') <p class="text-red-500 dark:text-red-400 text-sm -mt-4 mb-4">{{ $message }}</p> @enderror

        @if ($incidentType === 'accident')
        {{-- SECTION II: Accident Details --}}
        <p class="text-xs font-semibold uppercase tracking-wider text-zinc-400 dark:text-zinc-500 mb-3">Section II — Accident Details</p>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
            <flux:date-picker locale="en-GB" wire:model="incidentDate" label="Date of Accident" with-today type="input" required />
            <flux:time-picker wire:model="incidentTime" label="Time of Accident" required />
            <flux:input wire:model="incidentLocation" label="Location of Accident & Full Address" class="sm:col-span-2" required />
            <flux:textarea wire:model="incidentDescription" label="Description of Accident" rows="3" class="sm:col-span-2" required />
        </div>

        <flux:field class="mb-4">
            <flux:label>Type of Injury</flux:label>
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-2 mt-1">
                @foreach(['fractured' => 'Fractured (Kepatahan)', 'burn' => 'Burn (Terbakar)', 'death' => 'Death (Kematian)', 'dismemberment' => 'Dismemberment (Terputus)', 'others' => 'Others (Lain-Lain)'] as $val => $lbl)
                <label class="flex items-center gap-2 px-3 py-2 rounded-lg border border-zinc-200 dark:border-zinc-700 hover:bg-zinc-50 dark:hover:bg-zinc-700/50 cursor-pointer text-sm">
                    <flux:checkbox wire:model.live="injuryTypes" value="{{ $val }}" />
                    {{ $lbl }}
                </label>
                @endforeach
            </div>
            @error('injuryTypes') <flux:error>{{ $message }}</flux:error> @enderror
        </flux:field>

        @if (in_array('others', $injuryTypes))
        <div class="mb-4">
            <flux:input wire:model="injuryTypeOther" label="Specify Other Injury" placeholder="Describe the injury type" />
        </div>
        @endif

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
            <flux:textarea wire:model="injuryDescription" label="Description of Injury" rows="3" class="sm:col-span-2" required />
            <flux:input wire:model="hospitalName" label="Hospital / Clinic Name" class="sm:col-span-2" />
        </div>
        @endif

        @if ($incidentType === 'non_accident')
        {{-- SECTION II: Non-Accident Details --}}
        <p class="text-xs font-semibold uppercase tracking-wider text-zinc-400 dark:text-zinc-500 mb-3">Section II — Non-Accident Details</p>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
            <flux:date-picker locale="en-GB" wire:model="incidentDate" label="Date of Illness" with-today type="input" required />
            <flux:input wire:model="diseaseType" label="Type of Disease" placeholder="e.g. Pneumonia, Fever" required />

            <flux:field>
                <flux:label>Historical Disease?</flux:label>
                <flux:radio.group wire:model="isHistoricalDisease" variant="cards" :indicator="false" class="mt-1">
                    <flux:radio value="1" label="Yes (Ya)" />
                    <flux:radio value="0" label="No (Tidak)" />
                </flux:radio.group>
            </flux:field>

            <flux:field>
                <flux:label>Work-Related Illness?</flux:label>
                <flux:radio.group wire:model.live="isWorkRelated" variant="cards" :indicator="false" class="mt-1">
                    <flux:radio value="1" label="Yes (Ya)" />
                    <flux:radio value="0" label="No (Tidak)" />
                </flux:radio.group>
            </flux:field>

            @if ($isWorkRelated == '1')
            <flux:textarea wire:model="workRelatedDescription" label="Work-Related Details" placeholder="Describe how the illness is work-related" rows="2" class="sm:col-span-2" />
            @endif

            <flux:textarea wire:model="incidentDescription" label="Description of Disease" rows="3" class="sm:col-span-2" required />
            <flux:input wire:model="hospitalName" label="Hospital / Clinic Name" class="sm:col-span-2" />
        </div>
        @endif

        @if ($claimCategory === 'hospitalization' && $incidentType)
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
            <flux:date-picker locale="en-GB" wire:model="admissionDate" label="Admission Date" with-today type="input" required />
            <flux:date-picker locale="en-GB" wire:model="dischargeDate" label="Discharge Date" with-today type="input" />
        </div>
        @endif

        @if ($incidentType)
        {{-- SECTION III: Insurance Coverage --}}        
        <flux:separator class="my-6"/>
        <p class="text-xs font-semibold uppercase tracking-wider text-zinc-400 dark:text-zinc-500 mb-3 mt-2">Section III — Insurance Coverage</p>
        <flux:input wire:model="insurancePolicyNo" label="Insurance Policy No." placeholder="No. Polisi" />
        @endif

        @endif

        {{-- Step 4: Documents --}}
        @if ($step === 4)
        @php
            $isEmployerManaged = in_array($claimType, ['perkeso', 'green_card'])
                && ($foundWorker['worker_type'] ?? '') === 'existing';
            $insurerLabel = match($claimType) { 'perkeso' => 'PERKESO', 'green_card' => 'CIDB', default => 'Liberty' };
        @endphp
        <flux:heading size="lg" class="mb-1">Step 4: Required Documents</flux:heading>
        @if ($isEmployerManaged)
        <flux:text class="mb-6">
            As you will be submitting this claim directly to <strong>{{ $insurerLabel }}</strong>, CLAB does not require the original documents.
            Please send <strong>digital copies</strong> (scanned or photographed) of all documents — including any completed forms — to CLAB for record-keeping.
        </flux:text>
        @else
        <flux:text class="mb-6">Please download, complete, and send the following documents to our office by post or in person.</flux:text>
        @endif

        {{-- Downloadable Forms --}}
        @if ($downloadableDocs->isNotEmpty())
        <p class="text-xs font-semibold uppercase tracking-wide text-zinc-400 dark:text-zinc-500 mb-2">Forms to Download & Complete</p>
        <div class="space-y-2 mb-6">
            @foreach ($downloadableDocs as $doc)
            <flux:card class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 dark:bg-zinc-900 py-4">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 bg-blue-50 dark:bg-blue-900/50 rounded-lg flex items-center justify-center shrink-0">
                        <flux:icon.arrow-down-tray class="w-5 h-5 text-blue-600 dark:text-blue-400" />
                    </div>
                    <div>
                        <p class="font-medium">{{ $doc->label }}</p>
                        @if ($isEmployerManaged)
                        <flux:text size="sm">Download, complete, and send a digital copy to CLAB</flux:text>
                        @elseif ($doc->document_type === 'fwhs_medical_form')
                        <flux:text size="sm">To be completed by the attending doctor — bring this form to the hospital</flux:text>
                        @elseif ($doc->document_type === 'fwhs_checklist')
                        <flux:text size="sm">Download, tick all items, and include with your submission</flux:text>
                        @else
                        <flux:text size="sm">Pre-filled with your details — complete Section III (Reporter) and sign before submitting</flux:text>
                        @endif
                    </div>
                </div>
                @if ($doc->file_path && !in_array($doc->document_type, ['accident_fcl', 'non_accident_fcl']))
                    @if (in_array($doc->id, $downloadedDocIds))
                    <flux:button wire:click="downloadDoc({{ $doc->id }})" size="sm" variant="outline" icon="check-circle" class="w-full sm:w-auto text-green-600 dark:text-green-400">Downloaded</flux:button>
                    @else
                    <flux:button wire:click="downloadDoc({{ $doc->id }})" size="sm" variant="filled" icon="arrow-down-tray" class="w-full sm:w-auto">Download</flux:button>
                    @endif
                @elseif (in_array($doc->document_type, ['accident_fcl', 'non_accident_fcl']))
                    @if ($fclDownloaded)
                    <flux:button wire:click="downloadFcl" size="sm" variant="outline" icon="check-circle" class="w-full sm:w-auto text-green-600 dark:text-green-400">Downloaded</flux:button>
                    @else
                    <flux:button wire:click="downloadFcl" size="sm" variant="filled" icon="arrow-down-tray" class="w-full sm:w-auto">Download</flux:button>
                    @endif
                @else
                <flux:button size="sm" variant="outline" icon="arrow-down-tray" disabled class="w-full sm:w-auto">No File</flux:button>
                @endif
            </flux:card>
            @endforeach
        </div>
        @error('downloads')
        <flux:callout variant="danger" icon="x-circle" class="mb-4">
            <flux:callout.text>{{ $message }}</flux:callout.text>
        </flux:callout>
        @enderror
        @endif

        {{-- Supporting Documents --}}
        @php $submitDocs = $requiredDocs->filter(fn($d) => !$downloadableDocs->contains('id', $d->id)); @endphp
        @if ($submitDocs->isNotEmpty())
        <p class="text-xs font-semibold uppercase tracking-wide text-zinc-400 dark:text-zinc-500 mb-2">
            {{ $isEmployerManaged ? 'Documents to Send as Digital Copy' : 'Original Documents to Submit' }}
        </p>
        <div class="space-y-2 mb-6">
            @foreach ($submitDocs as $doc)
            <flux:card class="flex items-center gap-3 dark:bg-zinc-900 py-4">
                <!-- <flux:icon.document-text class="w-5 h-5 text-zinc-500 dark:text-zinc-400 shrink-0" /> -->
                <div class="w-9 h-9 bg-yellow-100 dark:bg-zinc-800 rounded-lg flex items-center justify-center">
                    <flux:icon.document-text class="w-5 h-5 text-yellow-600 dark:text-yellow-400" />
                </div>
                <div>
                    <p class="font-medium">{{ $doc->label }}</p>
                    <flux:text size="sm">{{ $isEmployerManaged ? 'Send a scanned or digital copy to CLAB' : 'Please submit the original copy' }}</flux:text>
                </div>
            </flux:card>
            @endforeach
        </div>
        @endif

        {{-- Submission Instructions --}}
        @if ($isEmployerManaged)
        <flux:callout icon="envelope" color="lime">
            <flux:callout.heading>Send Digital Copies To CLAB</flux:callout.heading>
            <flux:callout.text>
                Please upload all digital copies via the claim detail page after submission. Once submitted, CLAB will review your claim. Remember to also submit the claim directly to <strong>{{ $insurerLabel }}</strong> and update the status via the claim detail page.
            </flux:callout.text>
        </flux:callout>
        @else
        <flux:callout icon="map-pin" color="lime">
            <flux:callout.heading>Send Completed Documents To</flux:callout.heading>
            <flux:callout.text>
                Construction Labour Exchange Centre Berhad (CLAB)<br>
                Level 2, Annexe Block, Menara Milenium,<br>
                No. 8, Jalan Damanlela,<br>
                Pusat Bandar Damansara,<br>
                50490 Kuala Lumpur.<br><br>
                Once submitted, our admin team will process your claim and mark each document as received upon arrival.
            </flux:callout.text>
        </flux:callout>
        @endif
        @endif

        </div>
    </flux:card>

    {{-- Success Modal --}}
    <flux:modal wire:model="showSuccessModal" class="max-w-md w-full" :dismissible="false">
        <div class="flex flex-col items-center text-center p-2">
            <div class="w-16 h-16 rounded-full bg-green-100 dark:bg-green-900/40 flex items-center justify-center mb-4">
                <flux:icon.check-circle class="w-9 h-9 text-green-600 dark:text-green-400" />
            </div>
            <flux:heading size="lg" class="mb-2">Claim Submitted!</flux:heading>
            <flux:text class="text-zinc-500 dark:text-zinc-400 mb-2">
                Your claim application has been successfully submitted.
            </flux:text>
            <flux:text class="text-zinc-500 dark:text-zinc-400 mb-6">
                A notification email has been sent to our Person-In-Charge (PIC). They will review your application and contact you if additional information is required.
            </flux:text>
            <flux:button wire:click="goToClaims" variant="primary" icon="clipboard-document-list" class="w-full">
                View My Claims
            </flux:button>
        </div>
    </flux:modal>

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
