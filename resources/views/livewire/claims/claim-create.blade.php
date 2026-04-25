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
        <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-4 mb-6">
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

        {{-- Section I fields e, g, h — not available from worker DB --}}
        <p class="text-xs font-semibold uppercase tracking-wider text-zinc-400 dark:text-zinc-500 mb-3">Section I — Additional Employment Details</p>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">

            {{-- Date of Employment --}}
            <flux:date-picker locale="en-GB" wire:model="dateOfEmployment" with-today label="Date of Employment" type="input"/>

            {{-- Working Hours --}}
            <flux:field>
                <flux:label>Working Hours</flux:label>
                <div class="flex items-center gap-2 mt-1">
                    <flux:time-picker wire:model="workingHourFrom" class="flex-1" />
                    <span class="text-zinc-400 text-sm shrink-0">to</span>
                    <flux:time-picker wire:model="workingHourTo" class="flex-1" />
                </div>
            </flux:field>

        </div>

        {{-- Facilities Provided --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-4">
            @foreach(['facilityMeals' => 'Meals', 'facilityAccommodation' => 'Accommodation', 'facilityTransportation' => 'Transportation'] as $field => $label)
            <flux:select wire:model="{{ $field }}" variant="listbox" label="{{ $label }}" placeholder="Select...">
                <flux:select.option value="1">Yes</flux:select.option>
                <flux:select.option value="0">No</flux:select.option>
            </flux:select>
            @endforeach
        </div>

        {{-- Company Details --}}
        <p class="text-xs font-semibold uppercase tracking-wider text-zinc-400 dark:text-zinc-500 mb-3">Company Details</p>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
            <flux:input wire:model="tinNo" label="Tax Identification No. (TIN)" placeholder="e.g. C12345678900" />
            <flux:input wire:model="sstNo" label="SST No." placeholder="e.g. W10-1234-12345678" />
        </div>

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
        <p class="text-xs font-semibold uppercase tracking-wider text-zinc-400 dark:text-zinc-500 mb-3 mt-2">Section III — Insurance Coverage</p>
        <flux:input wire:model="insurancePolicyNo" label="Insurance Policy No." placeholder="No. Polisi" />
        @endif

        @endif

        {{-- Step 4: Documents --}}
        @if ($step === 4)
        <flux:heading size="lg" class="mb-1">Step 4: Required Documents</flux:heading>
        <flux:text class="mb-6">Please download, complete, and send the following documents to our office by post or in person.</flux:text>

        {{-- Downloadable Forms --}}
        @if ($downloadableDocs->isNotEmpty())
        <p class="text-xs font-semibold uppercase tracking-wide text-zinc-400 dark:text-zinc-500 mb-2">Form to Download & Complete</p>
        <div class="space-y-2 mb-6">
            @foreach ($downloadableDocs as $doc)
            <div class="flex items-center justify-between border border-blue-200 dark:border-blue-800 bg-blue-50 dark:bg-blue-900/20 rounded-lg p-4">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 bg-blue-100 dark:bg-blue-900/50 rounded-lg flex items-center justify-center">
                        <flux:icon.arrow-down-tray class="w-5 h-5 text-blue-600 dark:text-blue-400" />
                    </div>
                    <div>
                        <p class="font-medium text-zinc-800 dark:text-zinc-200">{{ $doc->label }}</p>
                        @if ($doc->document_type === 'fwhs_medical_form')
                        <p class="text-xs text-zinc-500 dark:text-zinc-400">To be completed by the attending doctor — bring this form to the hospital</p>
                        @elseif ($doc->document_type === 'fwhs_checklist')
                        <p class="text-xs text-zinc-500 dark:text-zinc-400">Download, tick all items, and include with your submission</p>
                        @else
                        <p class="text-xs text-zinc-500 dark:text-zinc-400">Pre-filled with your details — complete Section III (Reporter) and sign before submitting</p>
                        @endif
                    </div>
                </div>
                @if ($doc->file_path && !in_array($doc->document_type, ['accident_fcl', 'non_accident_fcl']))
                <a href="{{ str_starts_with($doc->file_path, 'documents/') ? asset('storage/' . $doc->file_path) : asset($doc->file_path) }}" target="_blank" download>
                    <flux:button size="sm" variant="filled" icon="arrow-down-tray">Download</flux:button>
                </a>
                @elseif (in_array($doc->document_type, ['accident_fcl', 'non_accident_fcl']))
                <form method="POST" action="{{ route('claims.fcl.download') }}" target="_blank">
                    @csrf
                    <input type="hidden" name="incidentType" value="{{ $incidentType }}">
                    <input type="hidden" name="incidentDate" value="{{ $incidentDate }}">
                    <input type="hidden" name="incidentTime" value="{{ $incidentTime }}">
                    <input type="hidden" name="incidentLocation" value="{{ $incidentLocation }}">
                    <input type="hidden" name="incidentDescription" value="{{ $incidentDescription }}">
                    @foreach($injuryTypes as $it)
                    <input type="hidden" name="injuryTypes[]" value="{{ $it }}">
                    @endforeach
                    <input type="hidden" name="injuryTypeOther" value="{{ $injuryTypeOther }}">
                    <input type="hidden" name="injuryDescription" value="{{ $injuryDescription }}">
                    <input type="hidden" name="diseaseType" value="{{ $diseaseType }}">
                    <input type="hidden" name="isHistoricalDisease" value="{{ $isHistoricalDisease }}">
                    <input type="hidden" name="isWorkRelated" value="{{ $isWorkRelated }}">
                    <input type="hidden" name="workRelatedDescription" value="{{ $workRelatedDescription }}">
                    <input type="hidden" name="hospitalName" value="{{ $hospitalName }}">
                    <input type="hidden" name="dateOfEmployment" value="{{ $dateOfEmployment }}">
                    <input type="hidden" name="workingHourFrom" value="{{ $workingHourFrom }}">
                    <input type="hidden" name="workingHourTo" value="{{ $workingHourTo }}">
                    <input type="hidden" name="facilityMeals" value="{{ $facilityMeals }}">
                    <input type="hidden" name="facilityAccommodation" value="{{ $facilityAccommodation }}">
                    <input type="hidden" name="facilityTransportation" value="{{ $facilityTransportation }}">
                    @foreach($foundWorker as $key => $val)
                    <input type="hidden" name="worker[{{ $key }}]" value="{{ $val }}">
                    @endforeach
                    <flux:button type="submit" size="sm" variant="filled" icon="arrow-down-tray">Download</flux:button>
                </form>
                @else
                <flux:button size="sm" variant="outline" icon="arrow-down-tray" disabled>No File</flux:button>
                @endif
            </div>
            @endforeach
        </div>
        @endif

        {{-- Original Supporting Documents --}}
        @php $submitDocs = $requiredDocs->filter(fn($d) => !$downloadableDocs->contains('id', $d->id)); @endphp
        @if ($submitDocs->isNotEmpty())
        <p class="text-xs font-semibold uppercase tracking-wide text-zinc-400 dark:text-zinc-500 mb-2">Original Documents to Submit</p>
        <div class="space-y-2 mb-6">
            @foreach ($submitDocs as $doc)
            <div class="flex items-center gap-3 border border-zinc-200 dark:border-zinc-700 rounded-lg p-4">
                <div class="w-9 h-9 bg-zinc-100 dark:bg-zinc-800 rounded-lg flex items-center justify-center">
                    <flux:icon.document-text class="w-5 h-5 text-zinc-500 dark:text-zinc-400" />
                </div>
                <div>
                    <p class="font-medium text-zinc-800 dark:text-zinc-200">{{ $doc->label }}</p>
                    <p class="text-xs text-zinc-500 dark:text-zinc-400">Please submit the original copy</p>
                </div>
            </div>
            @endforeach
        </div>
        @endif

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
