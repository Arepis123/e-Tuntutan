<div>
    <div class="mb-6">
        <flux:text class="text-2xl font-bold mb-2" variant="strong">Appeal Claim</flux:text>
        <flux:subheading>Review and update your claim details, then resubmit for reconsideration.</flux:subheading>
    </div>

    {{-- Rejection Reason Card --}}
    @if ($claim->insurer_rejection_reason)
    <flux:card class="dark:bg-zinc-900 border border-red-300 dark:border-red-700 mb-6">
        <div class="flex items-start gap-3">
            <flux:icon.x-circle class="w-5 h-5 text-red-500 shrink-0 mt-0.5" />
            <div>
                <p class="font-semibold text-red-700 dark:text-red-400 mb-1">Rejection Reason from Insurer</p>
                <p class="text-sm text-red-600 dark:text-red-400">{{ $claim->insurer_rejection_reason }}</p>
            </div>
        </div>
    </flux:card>
    @endif

    {{-- Step Indicator --}}
    <div class="mb-8">
        <flux:timeline horizontal>
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
        <div wire:key="step-{{ $step }}">

        {{-- Step 2: Worker Info (read-only) --}}
        @if ($step === 2)
        <flux:heading size="lg" class="mb-4">Step 1: Worker Information</flux:heading>

        @if ($foundWorker)
        <div class="bg-zinc-50 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-lg p-4 mb-6">
            <div class="flex items-center gap-3 mb-3">
                <flux:icon.user-circle class="w-5 h-5 text-zinc-500 dark:text-zinc-400" />
                <p class="font-semibold text-zinc-800 dark:text-zinc-200">Worker Details</p>
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

        {{-- Section I fields --}}
        <p class="text-xs font-semibold uppercase tracking-wider text-zinc-400 dark:text-zinc-500 mb-3">Section I — Additional Employment Details</p>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
            <flux:date-picker locale="en-GB" wire:model="dateOfEmployment" with-today label="Date of Employment" type="input"/>
            <flux:field>
                <flux:label>Working Hours</flux:label>
                <div class="flex items-center gap-2 mt-1">
                    <flux:time-picker wire:model="workingHourFrom" class="flex-1" />
                    <span class="text-zinc-400 text-sm shrink-0">to</span>
                    <flux:time-picker wire:model="workingHourTo" class="flex-1" />
                </div>
            </flux:field>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-4">
            @foreach(['facilityMeals' => 'Meals', 'facilityAccommodation' => 'Accommodation', 'facilityTransportation' => 'Transportation'] as $field => $label)
            <flux:select wire:model="{{ $field }}" variant="listbox" label="{{ $label }}" placeholder="Select...">
                <flux:select.option value="1">Yes</flux:select.option>
                <flux:select.option value="0">No</flux:select.option>
            </flux:select>
            @endforeach
        </div>

        <p class="text-xs font-semibold uppercase tracking-wider text-zinc-400 dark:text-zinc-500 mb-3">Company Details</p>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
            <flux:input wire:model="tinNo" label="Tax Identification No. (TIN)" placeholder="e.g. C12345678900" />
            <flux:input wire:model="sstNo" label="SST No." placeholder="e.g. W10-1234-12345678" />
        </div>

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
        <flux:heading size="lg" class="mb-1">Step 2: Incident Details</flux:heading>
        <flux:subheading class="mb-6">Based on FCL Form (CLAB/SOP/08/23)</flux:subheading>

        <flux:radio.group wire:model.live="incidentType" label="Incident Type" variant="cards" :indicator="false" class="mb-6 max-sm:flex-col">
            <flux:radio value="accident" icon="bolt" label="Accident" description="Worker was injured due to an accident at the workplace" />
            <flux:radio value="non_accident" icon="heart" label="Non-Accident" description="Worker fell ill or was diagnosed with a disease (not caused by an accident)" />
        </flux:radio.group>
        @error('incidentType') <p class="text-red-500 dark:text-red-400 text-sm -mt-4 mb-4">{{ $message }}</p> @enderror

        @if ($incidentType === 'accident')
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

        @if ($claim->claim_category === 'hospitalization' && $incidentType)
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
            <flux:date-picker locale="en-GB" wire:model="admissionDate" label="Admission Date" with-today type="input" required />
            <flux:date-picker locale="en-GB" wire:model="dischargeDate" label="Discharge Date" with-today type="input" />
        </div>
        @endif

        @if ($incidentType)
        <p class="text-xs font-semibold uppercase tracking-wider text-zinc-400 dark:text-zinc-500 mb-3 mt-2">Section III — Insurance Coverage</p>
        <flux:input wire:model="insurancePolicyNo" label="Insurance Policy No." placeholder="No. Polisi" />
        @endif
        @endif

        {{-- Step 4: Documents --}}
        @if ($step === 4)
        <flux:heading size="lg" class="mb-1">Step 3: Supporting Documents</flux:heading>
        <flux:text class="mb-6">Upload any updated documents to support your appeal. Previously submitted documents remain unless replaced.</flux:text>

        @if ($downloadableDocs->isNotEmpty())
        <p class="text-xs font-semibold uppercase tracking-wide text-zinc-400 dark:text-zinc-500 mb-2">Forms to Download & Complete</p>
        <div class="space-y-2 mb-6">
            @foreach ($downloadableDocs as $doc)
            <div class="flex items-center justify-between border border-blue-200 dark:border-blue-800 bg-blue-50 dark:bg-blue-900/20 rounded-lg p-4">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 bg-blue-100 dark:bg-blue-900/50 rounded-lg flex items-center justify-center">
                        <flux:icon.arrow-down-tray class="w-5 h-5 text-blue-600 dark:text-blue-400" />
                    </div>
                    <p class="font-medium text-zinc-800 dark:text-zinc-200">{{ $doc->label }}</p>
                </div>
                @if ($doc->file_path)
                <flux:button wire:click="downloadDoc({{ $doc->id }})" size="sm" variant="filled" icon="arrow-down-tray">Download</flux:button>
                @else
                <flux:button size="sm" variant="outline" icon="arrow-down-tray" disabled>No File</flux:button>
                @endif
            </div>
            @endforeach
        </div>
        @endif

        @if ($requiredDocs->isNotEmpty())
        <p class="text-xs font-semibold uppercase tracking-wide text-zinc-400 dark:text-zinc-500 mb-2">Upload Documents</p>
        <div class="space-y-4 mb-6">
            @foreach ($requiredDocs as $doc)
            @php $existing = $claim->documents->firstWhere('document_type', $doc->document_type); @endphp
            <div class="border border-zinc-200 dark:border-zinc-700 rounded-lg p-4">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-9 h-9 bg-zinc-100 dark:bg-zinc-800 rounded-lg flex items-center justify-center">
                        <flux:icon.document-text class="w-5 h-5 text-zinc-500 dark:text-zinc-400" />
                    </div>
                    <div>
                        <p class="font-medium text-zinc-800 dark:text-zinc-200">{{ $doc->label }}</p>
                        @if ($existing?->file_path)
                        <p class="text-xs text-green-600 dark:text-green-400">Previously submitted — upload to replace</p>
                        @else
                        <p class="text-xs text-zinc-400">No file uploaded yet</p>
                        @endif
                    </div>
                </div>
                <input
                    type="file"
                    wire:model="uploadedFiles.{{ $doc->document_type }}"
                    accept=".pdf,.jpg,.jpeg,.png"
                    class="block w-full text-sm text-zinc-700 dark:text-zinc-300
                           file:mr-3 file:py-1.5 file:px-3 file:rounded file:border-0
                           file:text-sm file:font-medium file:bg-zinc-100 file:text-zinc-700
                           dark:file:bg-zinc-800 dark:file:text-zinc-300"
                />
                <div wire:loading wire:target="uploadedFiles.{{ $doc->document_type }}" class="text-xs text-zinc-400 mt-1">Uploading…</div>
            </div>
            @endforeach
        </div>
        @endif

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
        @endif

        </div>
    </flux:card>

    {{-- Navigation Buttons --}}
    <div class="flex justify-between mt-6">
        @if ($step > 2)
        <flux:button wire:click="previousStep" variant="filled">
            Previous
        </flux:button>
        @else
        <flux:button :href="route('claims.show', $claim)" wire:navigate variant="ghost" icon="arrow-left">
            Back to Claim
        </flux:button>
        @endif

        @if ($step < 4)
        <flux:button wire:click="nextStep" variant="primary">
            Next
        </flux:button>
        @else
        <flux:button wire:click="submit" variant="primary" icon="paper-airplane">
            Resubmit Appeal
        </flux:button>
        @endif
    </div>
</div>
