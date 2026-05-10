<div>
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-3 mb-1">
                <flux:heading size="xl">{{ $claim->claim_number }}</flux:heading>
            </div>
            <div class="flex items-center gap-2">
                <flux:subheading>{{ $claim->getClaimTypeLabel() }} — {{ $claim->claim_category === 'hospitalization' ? 'Hospitalization' : 'Death' }}</flux:subheading>
                @if (auth()->user()->hasRole('employer'))
                <flux:badge color="{{ match($claim->status) { 'open' => 'red', 'in_progress' => 'yellow', 'closed' => 'emerald', default => 'zinc' } }}" size="sm">
                    {{ match($claim->status) { 'open' => 'Open', 'in_progress' => 'In Progress', 'closed' => 'Closed', default => ucfirst($claim->status) } }}
                </flux:badge>
                @endif
            </div>
        </div>

        @if ($claim->insurer_decision === 'rejected' && $claim->user_id === auth()->id() && auth()->user()->hasRole('employer') && $claim->appeal_count === 0 && ! $claim->isEmployerManaged())
        <flux:button :href="route('claims.appeal', $claim)" wire:navigate variant="danger" icon="arrow-path">
            Appeal Claim
        </flux:button>
        @endif

        @if (auth()->user()->hasAnyRole(['admin', 'pic']))
        <flux:dropdown>
            <flux:button
                icon:trailing="chevron-down"
                icon="{{ match($claim->status) { 'open' => 'envelope-open', 'in_progress' => 'clock', 'closed' => 'archive-box', default => 'arrow-path' } }}"
                color="{{ match($claim->status) { 'open' => 'red', 'in_progress' => 'yellow', 'closed' => 'emerald', default => '' } }}"
                variant="primary"
                >
                {{ match($claim->status) { 'open' => 'Open', 'in_progress' => 'In Progress', 'closed' => 'Closed', default => ucfirst($claim->status) } }}
            </flux:button>
            <flux:menu>
                <flux:menu.item icon="envelope-open" wire:click="changeStatus('open')">Open</flux:menu.item>
                <flux:menu.item icon="clock" wire:click="changeStatus('in_progress')">In Progress</flux:menu.item>
                <flux:menu.item icon="archive-box" wire:click="changeStatus('closed')">Closed</flux:menu.item>
            </flux:menu>
        </flux:dropdown>
        @endif
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Main Content --}}
        <div class="lg:col-span-2 space-y-6">

            {{-- Worker Info --}}
            <flux:card class="dark:bg-zinc-900">
                <flux:heading size="lg" class="mb-4">Worker Information</flux:heading>
                <dl class="grid grid-cols-2 sm:grid-cols-3 gap-4 text-sm">
                    <div class="col-span-2 sm:col-span-3">
                        <dt class="text-zinc-500 dark:text-zinc-400">Name</dt>
                        <dd class="font-medium text-zinc-900 dark:text-white mt-1">{{ $claim->worker->name }}</dd>
                    </div>
                    <div>
                        <dt class="text-zinc-500 dark:text-zinc-400">Worker Type</dt>
                        <dd class="mt-1">
                            <flux:badge color="{{ $claim->worker->worker_type === 'outsource' ? 'amber' : 'fuchsia' }}" size="sm">{{ ucfirst($claim->worker->worker_type) }}</flux:badge>
                        </dd>
                    </div>
                    @if ($claim->worker->worker_status)
                    <div>
                        <dt class="text-zinc-500 dark:text-zinc-400">Worker Status</dt>
                        <dd class="font-medium text-zinc-900 dark:text-white mt-1">{{ $claim->worker->worker_status }}</dd>
                    </div>
                    @endif
                    @if ($claim->worker->gender)
                    <div>
                        <dt class="text-zinc-500 dark:text-zinc-400">Gender</dt>
                        <dd class="font-medium text-zinc-900 dark:text-white mt-1">{{ ucfirst(strtolower($claim->worker->gender)) }}</dd>
                    </div>
                    @endif
                    @if ($claim->worker->date_of_birth)
                    <div>
                        <dt class="text-zinc-500 dark:text-zinc-400">Date of Birth</dt>
                        <dd class="font-medium text-zinc-900 dark:text-white mt-1">{{ $claim->worker->date_of_birth->format('d M Y') }}</dd>
                    </div>
                    @endif
                    <div>
                        <dt class="text-zinc-500 dark:text-zinc-400">Nationality</dt>
                        <dd class="font-medium text-zinc-900 dark:text-white mt-1">{{ $claim->worker->nationality }}</dd>
                    </div>
                    <div>
                        <dt class="text-zinc-500 dark:text-zinc-400">Passport No.</dt>
                        <dd class="font-medium text-zinc-900 dark:text-white mt-1">{{ $claim->worker->passport_number }}</dd>
                    </div>
                    <div>
                        <dt class="text-zinc-500 dark:text-zinc-400">Passport Expiry</dt>
                        <dd class="mt-1">
                            @if ($claim->worker->passport_expiry)
                            @php $exp = \Carbon\Carbon::parse($claim->worker->passport_expiry); @endphp
                            <span class="font-medium {{ $exp->isPast() ? 'text-red-600 dark:text-red-400' : 'text-zinc-900 dark:text-white' }}">
                                {{ $exp->format('d M Y') }}
                                @if ($exp->isPast()) <span class="text-xs">(Expired)</span> @endif
                            </span>
                            @else
                            <span class="text-zinc-400">No data</span>
                            @endif
                        </dd>
                    </div>
                    <div>
                        <dt class="text-zinc-500 dark:text-zinc-400">Permit Expiry</dt>
                        <dd class="mt-1">
                            @if ($claim->worker->permit_expiry)
                            @php $exp = \Carbon\Carbon::parse($claim->worker->permit_expiry); @endphp
                            <span class="font-medium {{ $exp->isPast() ? 'text-red-600 dark:text-red-400' : 'text-zinc-900 dark:text-white' }}">
                                {{ $exp->format('d M Y') }}
                                @if ($exp->isPast()) <span class="text-xs">(Expired)</span> @endif
                            </span>
                            @else
                            <span class="text-zinc-400">No data</span>
                            @endif
                        </dd>
                    </div>
                </dl>
            </flux:card>

            {{-- Contractor / Employment Info --}}
            <flux:card class="dark:bg-zinc-900">
                <flux:heading size="lg" class="mb-4">Contractor Information</flux:heading>
                <dl class="grid grid-cols-2 gap-4 text-sm">
                    @if ($claim->worker->employer_name)
                    <div>
                        <dt class="text-zinc-500 dark:text-zinc-400">Contractor / Employer</dt>
                        <dd class="font-medium text-zinc-900 dark:text-white mt-1">{{ $claim->worker->employer_name }}</dd>
                    </div>
                    @endif
                    @if ($claim->worker->employer_address)
                    <div>
                        <dt class="text-zinc-500 dark:text-zinc-400">Contractor Address</dt>
                        <dd class="font-medium text-zinc-900 dark:text-white mt-1">{{ $claim->worker->employer_address }}</dd>
                    </div>
                    @endif
                    @if ($claim->date_of_employment)
                    <div>
                        <dt class="text-zinc-500 dark:text-zinc-400">Date of Employment</dt>
                        <dd class="font-medium text-zinc-900 dark:text-white mt-1">{{ \Carbon\Carbon::parse($claim->date_of_employment)->format('d M Y') }}</dd>
                    </div>
                    @endif
                    @if ($claim->working_hour_from || $claim->working_hour_to)
                    <div>
                        <dt class="text-zinc-500 dark:text-zinc-400">Working Hours</dt>
                        <dd class="font-medium text-zinc-900 dark:text-white mt-1">{{ $claim->working_hour_from ?? '—' }} – {{ $claim->working_hour_to ?? '—' }}</dd>
                    </div>
                    @endif
                    @if ($claim->tin_no)
                    <div>
                        <dt class="text-zinc-500 dark:text-zinc-400">TIN No.</dt>
                        <dd class="font-medium text-zinc-900 dark:text-white mt-1">{{ $claim->tin_no }}</dd>
                    </div>
                    @endif
                    @if ($claim->sst_no)
                    <div>
                        <dt class="text-zinc-500 dark:text-zinc-400">SST No.</dt>
                        <dd class="font-medium text-zinc-900 dark:text-white mt-1">{{ $claim->sst_no }}</dd>
                    </div>
                    @endif
                    @if ($claim->facility_meals !== null || $claim->facility_accommodation !== null || $claim->facility_transportation !== null)
                    <div class="col-span-2">
                        <dt class="text-zinc-500 dark:text-zinc-400 mb-2">Facilities Provided</dt>
                        <dd class="flex flex-wrap gap-2">
                            <flux:badge color="{{ $claim->facility_meals ? 'green' : 'zinc' }}" size="sm">
                                Meals: {{ $claim->facility_meals ? 'Yes' : 'No' }}
                            </flux:badge>
                            <flux:badge color="{{ $claim->facility_accommodation ? 'green' : 'zinc' }}" size="sm">
                                Accommodation: {{ $claim->facility_accommodation ? 'Yes' : 'No' }}
                            </flux:badge>
                            <flux:badge color="{{ $claim->facility_transportation ? 'green' : 'zinc' }}" size="sm">
                                Transportation: {{ $claim->facility_transportation ? 'Yes' : 'No' }}
                            </flux:badge>
                        </dd>
                    </div>
                    @endif
                </dl>
            </flux:card>

            {{-- Incident Details --}}
            <flux:card class="dark:bg-zinc-900">
                <flux:heading size="lg" class="mb-4">Incident Details</flux:heading>
                <dl class="grid grid-cols-2 sm:grid-cols-3 gap-4 text-sm">

                    {{-- Type & Date --}}
                    @if ($claim->incident_type)
                    <div>
                        <dt class="text-zinc-500 dark:text-zinc-400">Incident Type</dt>
                        <dd class="mt-1">
                            <flux:badge color="{{ $claim->incident_type === 'accident' ? 'red' : 'blue' }}" size="sm">
                                {{ $claim->incident_type === 'accident' ? 'Accident' : 'Non-Accident' }}
                            </flux:badge>
                        </dd>
                    </div>
                    @endif
                    <div>
                        <dt class="text-zinc-500 dark:text-zinc-400">{{ $claim->incident_type === 'non_accident' ? 'Date of Illness' : 'Date of Accident' }}</dt>
                        <dd class="font-medium text-zinc-900 dark:text-white mt-1">{{ $claim->incident_date?->format('d M Y') ?? '—' }}</dd>
                    </div>
                    @if ($claim->incident_time)
                    <div>
                        <dt class="text-zinc-500 dark:text-zinc-400">Time of Accident</dt>
                        <dd class="font-medium text-zinc-900 dark:text-white mt-1">{{ $claim->incident_time }}</dd>
                    </div>
                    @endif

                    {{-- Accident-specific --}}
                    @if ($claim->incident_location)
                    <div class="col-span-2 sm:col-span-1">
                        <dt class="text-zinc-500 dark:text-zinc-400">Location of Accident</dt>
                        <dd class="font-medium text-zinc-900 dark:text-white mt-1">{{ $claim->incident_location }}</dd>
                    </div>
                    @endif
                    @if ($claim->injury_types)
                    <div class="col-span-2 sm:col-span-2">
                        <dt class="text-zinc-500 dark:text-zinc-400 mb-1">Type of Injury</dt>
                        <dd class="flex flex-wrap gap-1">
                            @foreach(explode(',', $claim->injury_types) as $injury)
                            <flux:badge size="sm">{{ ucfirst($injury) }}</flux:badge>
                            @endforeach
                            @if ($claim->injury_type_other)
                            <flux:badge size="sm" color="zinc">{{ $claim->injury_type_other }}</flux:badge>
                            @endif
                        </dd>
                    </div>
                    @endif
                    @if ($claim->injury_description)
                    <div class="col-span-2 sm:col-span-3">
                        <dt class="text-zinc-500 dark:text-zinc-400">Description of Injury</dt>
                        <dd class="font-medium text-zinc-900 dark:text-white mt-1">{{ $claim->injury_description }}</dd>
                    </div>
                    @endif

                    {{-- Non-accident-specific --}}
                    @if ($claim->disease_type)
                    <div>
                        <dt class="text-zinc-500 dark:text-zinc-400">Type of Disease</dt>
                        <dd class="font-medium text-zinc-900 dark:text-white mt-1">{{ $claim->disease_type }}</dd>
                    </div>
                    @endif
                    @if ($claim->is_historical_disease !== null)
                    <div>
                        <dt class="text-zinc-500 dark:text-zinc-400">Historical Disease?</dt>
                        <dd class="mt-1">
                            <flux:badge color="{{ $claim->is_historical_disease ? 'amber' : 'zinc' }}" size="sm">{{ $claim->is_historical_disease ? 'Yes' : 'No' }}</flux:badge>
                        </dd>
                    </div>
                    @endif
                    @if ($claim->is_work_related !== null)
                    <div>
                        <dt class="text-zinc-500 dark:text-zinc-400">Work-Related?</dt>
                        <dd class="mt-1">
                            <flux:badge color="{{ $claim->is_work_related ? 'amber' : 'zinc' }}" size="sm">{{ $claim->is_work_related ? 'Yes' : 'No' }}</flux:badge>
                        </dd>
                    </div>
                    @endif
                    @if ($claim->work_related_description)
                    <div class="col-span-2 sm:col-span-3">
                        <dt class="text-zinc-500 dark:text-zinc-400">Work-Related Details</dt>
                        <dd class="font-medium text-zinc-900 dark:text-white mt-1">{{ $claim->work_related_description }}</dd>
                    </div>
                    @endif

                    {{-- Description --}}
                    <div class="col-span-2 sm:col-span-3">
                        <dt class="text-zinc-500 dark:text-zinc-400">{{ $claim->incident_type === 'non_accident' ? 'Description of Disease' : 'Description of Accident' }}</dt>
                        <dd class="font-medium text-zinc-900 dark:text-white mt-1">{{ $claim->incident_description }}</dd>
                    </div>

                    {{-- Hospitalization --}}
                    @if ($claim->hospital_name)
                    <div>
                        <dt class="text-zinc-500 dark:text-zinc-400">Hospital / Clinic</dt>
                        <dd class="font-medium text-zinc-900 dark:text-white mt-1">{{ $claim->hospital_name }}</dd>
                    </div>
                    @endif
                    @if ($claim->admission_date)
                    <div>
                        <dt class="text-zinc-500 dark:text-zinc-400">Admission Date</dt>
                        <dd class="font-medium text-zinc-900 dark:text-white mt-1">{{ $claim->admission_date->format('d M Y') }}</dd>
                    </div>
                    @endif
                    @if ($claim->discharge_date)
                    <div>
                        <dt class="text-zinc-500 dark:text-zinc-400">Discharge Date</dt>
                        <dd class="font-medium text-zinc-900 dark:text-white mt-1">{{ $claim->discharge_date->format('d M Y') }}</dd>
                    </div>
                    @endif

                    {{-- Insurance --}}
                    @if ($claim->insurance_policy_no)
                    <div>
                        <dt class="text-zinc-500 dark:text-zinc-400">Insurance Policy No.</dt>
                        <dd class="font-medium text-zinc-900 dark:text-white mt-1">{{ $claim->insurance_policy_no }}</dd>
                    </div>
                    @endif

                    @if ($claim->forwarded_to)
                    <div>
                        <dt class="text-zinc-500 dark:text-zinc-400">Forwarded To</dt>
                        <dd class="mt-1">
                            <flux:badge size="sm">{{ strtoupper(str_replace('_', ' ', $claim->forwarded_to)) }}</flux:badge>
                        </dd>
                    </div>
                    @endif
                </dl>
            </flux:card>

            {{-- Documents --}}
            @if (auth()->user()->hasAnyRole(['admin', 'pic']))
            <flux:card class="dark:bg-zinc-900">
                <div class="flex items-start justify-between mb-4">
                    <div>
                        <flux:heading size="lg" class="mb-1">Required Documents</flux:heading>
                        <flux:text class="text-sm">Mark each document as received when the copy arrives (physical or digital).</flux:text>
                    </div>
                    @can('claims.approve')
                    <flux:dropdown position="bottom" align="end">
                        <flux:button icon="ellipsis-horizontal" variant="ghost" size="sm" />
                        <flux:menu>
                            <flux:menu.item wire:click="markAllReceived" icon="check-circle">Mark all received</flux:menu.item>
                            <flux:menu.item wire:click="notifyMissingDocuments" icon="envelope">Notify contractor of missing docs</flux:menu.item>
                        </flux:menu>
                    </flux:dropdown>
                    @endcan
                </div>

                @if ($claim->documents->count() > 0)
                @php $allReceived = $claim->documents->every(fn($d) => $d->is_received); @endphp
                @if ($allReceived)
                <flux:accordion transition>
                    <flux:accordion.item>
                        <flux:accordion.heading>
                            <div class="flex items-center gap-2">
                                <flux:icon.check-circle class="w-4 h-4 text-green-500" />
                                <span>All {{ $claim->documents->count() }} documents received</span>
                                <flux:text class="text-xs text-zinc-400 dark:text-zinc-500 ml-1">· Click to view</flux:text>
                            </div>
                        </flux:accordion.heading>
                        <flux:accordion.content>
                            <div class="space-y-3 pt-2">
                                @foreach ($claim->documents as $doc)
                                <div class="flex items-center justify-between p-3 rounded-lg border bg-green-50 dark:bg-green-900/20 border-green-200 dark:border-green-800">
                                    <div class="flex items-center gap-3">
                                        <flux:icon.check-circle class="w-5 h-5 text-green-500 shrink-0" />
                                        <div>
                                            <p class="text-sm font-medium text-zinc-800 dark:text-zinc-200">{{ $doc->getDocumentTypeLabel() }}</p>
                                            <p class="text-xs text-green-600 dark:text-green-400">
                                                Received on {{ $doc->received_at->format('d/m/Y H:i') }}
                                                @if($doc->receiver) by {{ $doc->receiver->name }} @endif
                                            </p>
                                            @if ($doc->original_filename)
                                            <p class="text-xs text-zinc-400 mt-0.5">{{ $doc->original_filename }}</p>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        @if ($doc->path)
                                        <flux:button :href="route('claims.documents.download', [$claim, $doc]) . '?inline=1'" target="_blank" size="sm" variant="ghost" icon="eye" />
                                        <flux:button :href="route('claims.documents.download', [$claim, $doc])" size="sm" variant="ghost" icon="arrow-down-tray" />
                                        @endif
                                        <flux:badge size="sm">Received</flux:badge>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </flux:accordion.content>
                    </flux:accordion.item>
                </flux:accordion>
                @else
                <div class="space-y-3">
                    @foreach ($claim->documents as $doc)
                    <div class="flex items-center justify-between p-3 rounded-lg border
                        {{ $doc->is_received
                            ? 'bg-green-50 dark:bg-green-900/20 border-green-200 dark:border-green-800'
                            : 'bg-zinc-50 dark:bg-zinc-800 border-zinc-200 dark:border-zinc-700' }}">
                        <div class="flex items-center gap-3">
                            @if ($doc->is_received)
                                <flux:icon.check-circle class="w-5 h-5 text-green-500 shrink-0" />
                            @else
                                <flux:icon.clock class="w-5 h-5 text-zinc-400 shrink-0" />
                            @endif
                            <div>
                                <p class="text-sm font-medium text-zinc-800 dark:text-zinc-200">{{ $doc->getDocumentTypeLabel() }}</p>
                                @if ($doc->is_received)
                                <p class="text-xs text-green-600 dark:text-green-400">
                                    Received on {{ $doc->received_at->format('d/m/Y H:i') }}
                                    @if($doc->receiver) by {{ $doc->receiver->name }} @endif
                                </p>
                                @else
                                <p class="text-xs text-zinc-400">
                                    {{ $doc->path ? 'Uploaded — awaiting confirmation' : 'Awaiting document' }}
                                </p>
                                @endif
                                @if ($doc->original_filename)
                                <p class="text-xs text-zinc-400 mt-0.5">{{ $doc->original_filename }}</p>
                                @endif
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            @if ($doc->path)
                            <flux:button :href="route('claims.documents.download', [$claim, $doc]) . '?inline=1'" target="_blank" size="sm" variant="ghost" icon="eye" />
                            <flux:button :href="route('claims.documents.download', [$claim, $doc])" size="sm" variant="ghost" icon="arrow-down-tray" />
                            @endif
                            @if (!$doc->is_received)
                            @can('claims.approve')
                            <flux:button
                                wire:click="markDocumentReceived({{ $doc->id }})"
                                size="sm"
                                variant="ghost"
                                icon="check"
                            >
                                Mark Received
                            </flux:button>
                            @endcan
                            @else
                            <flux:badge size="sm">Received</flux:badge>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
                @endif
                @else
                <p class="text-sm text-zinc-400">No documents tracked yet.</p>
                @endif
            </flux:card>
            @endif

            {{-- Client Document Upload --}}
            @if (auth()->user()->hasRole('employer') && $claim->user_id === auth()->id() && $claim->isEmployerManaged())
            @php
                $requiredDocs   = $docConfigs->where('is_required', true);
                $uploadedDocs   = $claim->documents->filter(fn($d) => $d->path);
                $showClientCard = $requiredDocs->isNotEmpty() || $uploadedDocs->isNotEmpty();
            @endphp
            @if ($showClientCard)
            <flux:card class="dark:bg-zinc-900">
                <flux:heading size="lg" class="mb-1">Documents</flux:heading>
                @if ($claim->isEmployerManaged())
                <flux:text class="text-sm mb-4">Upload digital copies of the required documents for CLAB's records.</flux:text>
                @else
                <flux:text class="text-sm mb-4">Upload the required documents below. CLAB will review and confirm receipt.</flux:text>
                @endif

                {{-- Required documents from config --}}
                @if ($requiredDocs->isNotEmpty())
                <div class="space-y-4">
                    @foreach ($requiredDocs as $doc)
                    @php $uploaded = $claim->documents->firstWhere('document_type', $doc->document_type); @endphp
                    <div class="border border-zinc-200 dark:border-zinc-700 rounded-lg p-4">
                        <div class="flex items-center gap-3 mb-3">
                            @if ($uploaded)
                            <flux:icon.check-circle class="w-5 h-5 text-green-500 shrink-0" />
                            @else
                            <flux:icon.clock class="w-5 h-5 text-zinc-400 shrink-0" />
                            @endif
                            <div>
                                <p class="text-sm font-medium text-zinc-800 dark:text-zinc-200">{{ $doc->label }}</p>
                                @if ($uploaded)
                                <p class="text-xs text-green-600 dark:text-green-400">
                                    {{ $uploaded->original_filename }}
                                    @if ($uploaded->is_received)
                                    &nbsp;·&nbsp;<span class="font-medium">Received by CLAB</span>
                                    @endif
                                </p>
                                @else
                                <p class="text-xs text-zinc-400">Not yet uploaded</p>
                                @endif
                            </div>
                        </div>
                        <flux:error name="clientUploadFiles.{{ $doc->document_type }}" class="mt-2" />
                        <div class="flex items-center gap-2 mt-2">
                            <input
                                type="file"
                                wire:model="clientUploadFiles.{{ $doc->document_type }}"
                                wire:loading.attr="disabled"
                                wire:target="clientUploadFiles.{{ $doc->document_type }}"
                                accept=".pdf,.jpg,.jpeg,.png"
                                class="block flex-1 text-sm text-zinc-700 dark:text-zinc-300
                                       file:mr-3 file:py-1.5 file:px-3 file:rounded file:border-0
                                       file:text-sm file:font-medium file:bg-zinc-100 file:text-zinc-700
                                       dark:file:bg-zinc-800 dark:file:text-zinc-300
                                       hover:file:bg-zinc-200 dark:hover:file:bg-zinc-700
                                       disabled:opacity-50 disabled:cursor-not-allowed"
                            />
                            @if ($uploaded?->path)
                            <flux:button :href="route('claims.documents.download', [$claim, $uploaded]) . '?inline=1'" target="_blank" size="sm" variant="ghost" icon="eye" />
                            <flux:button :href="route('claims.documents.download', [$claim, $uploaded])" size="sm" variant="ghost" icon="arrow-down-tray" />
                            @endif
                        </div>
                        <div wire:loading wire:target="clientUploadFiles.{{ $doc->document_type }}" class="text-xs text-blue-500 mt-1">
                            Uploading, please wait…
                        </div>
                    </div>
                    @endforeach
                </div>
                @endif

                {{-- Uploaded documents not covered by config (always visible) --}}
                @php
                    $configuredTypes = $requiredDocs->pluck('document_type')->toArray();
                    $extraUploads    = $uploadedDocs->filter(fn($d) => ! in_array($d->document_type, $configuredTypes));
                @endphp
                @if ($extraUploads->isNotEmpty())
                <div class="{{ $requiredDocs->isNotEmpty() ? 'mt-4 pt-4 border-t border-zinc-200 dark:border-zinc-700' : '' }}">
                    <p class="text-xs font-semibold uppercase tracking-wide text-zinc-500 mb-3">Uploaded Files</p>
                    <div class="space-y-2">
                        @foreach ($extraUploads as $doc)
                        <div class="flex items-center justify-between gap-3 p-3 rounded-lg bg-zinc-50 dark:bg-zinc-800">
                            <div class="flex items-center gap-2 min-w-0">
                                <flux:icon.document class="w-4 h-4 text-zinc-400 shrink-0" />
                                <div class="min-w-0">
                                    <p class="text-sm font-medium text-zinc-800 dark:text-zinc-200 truncate">{{ $doc->original_filename }}</p>
                                    <p class="text-xs text-zinc-400">{{ $doc->document_type }}</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2 shrink-0">
                                @if ($doc->is_received)
                                <flux:badge color="green" size="sm">Received</flux:badge>
                                @endif
                                <flux:button :href="route('claims.documents.download', [$claim, $doc]) . '?inline=1'" target="_blank" size="sm" variant="ghost" icon="eye" />
                                <flux:button :href="route('claims.documents.download', [$claim, $doc])" size="sm" variant="ghost" icon="arrow-down-tray" />
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- No docs uploaded yet and no config --}}
                @if ($requiredDocs->isEmpty() && $uploadedDocs->isEmpty())
                <p class="text-sm text-zinc-400">No documents uploaded yet.</p>
                @endif
            </flux:card>
            @endif
            @endif

            {{-- Notes --}}
            <flux:card class="dark:bg-zinc-900">
                <flux:heading size="lg" class="mb-4">Notes</flux:heading>

                <div class="space-y-3 mb-4">
                    @forelse ($claim->claimNotes->sortByDesc('created_at') as $note)
                    @if (!$note->is_internal || auth()->user()->hasAnyRole(['admin','pic']) || $note->user_id === auth()->id())
                    <div class="p-3 rounded-lg {{ $note->is_internal ? 'bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800' : 'bg-zinc-50 dark:bg-zinc-800' }}">
                        <div class="flex items-center justify-between mb-1">
                            <span class="text-sm font-medium dark:text-white">{{ $note->user->name }}</span>
                            <div class="flex items-center gap-2">
                                @if ($note->is_internal)
                                <flux:badge color="yellow" size="sm">Internal</flux:badge>
                                @endif
                                <span class="text-xs text-zinc-400">{{ $note->created_at->diffForHumans() }}</span>
                            </div>
                        </div>
                        <p class="text-sm text-zinc-700 dark:text-zinc-300">{{ $note->note }}</p>
                        @if ($note->attachments)
                        <div class="mt-2 flex flex-wrap gap-2">
                            @foreach ($note->attachments as $i => $att)
                            <button
                                wire:click="downloadNoteAttachment({{ $note->id }}, {{ $i }})"
                                class="inline-flex items-center gap-1.5 px-2 py-1 rounded text-xs bg-white dark:bg-zinc-700 border border-zinc-200 dark:border-zinc-600 text-zinc-600 dark:text-zinc-300 hover:bg-zinc-50 dark:hover:bg-zinc-600"
                            >
                                <flux:icon.paper-clip class="w-3 h-3" />
                                {{ $att['name'] }}
                                <span class="text-zinc-400">({{ number_format($att['size'] / 1024, 0) }} KB)</span>
                            </button>
                            @endforeach
                        </div>
                        @endif
                    </div>
                    @endif
                    @empty
                    <p class="text-zinc-400 text-sm">No notes yet.</p>
                    @endforelse
                </div>

                @if (auth()->user()->hasAnyRole(['admin','pic']) || $claim->status !== 'closed')
                <div class="border-t border-zinc-200 dark:border-zinc-700 pt-4 space-y-3">
                    <flux:textarea wire:model="newNote" placeholder="Add a note..." rows="3" />
                    <div>
                        <label class="block text-sm text-zinc-600 dark:text-zinc-400 mb-1">
                            Attachments <span class="text-zinc-400 font-normal">(max 10 files, 3 MB each — PDF, Word, images)</span>
                        </label>
                        <input
                            type="file"
                            wire:model="noteFiles"
                            multiple
                            accept=".pdf,.doc,.docx,.jpg,.jpeg,.png"
                            class="block w-full text-sm text-zinc-700 dark:text-zinc-300
                                   file:mr-3 file:py-1.5 file:px-3 file:rounded file:border-0
                                   file:text-sm file:font-medium file:bg-zinc-100 file:text-zinc-700
                                   dark:file:bg-zinc-800 dark:file:text-zinc-300
                                   hover:file:bg-zinc-200 dark:hover:file:bg-zinc-700"
                        />
                        <div wire:loading wire:target="noteFiles" class="text-xs text-zinc-400 mt-1">Uploading…</div>
                        <flux:error name="noteFiles" />
                        <flux:error name="noteFiles.*" />
                    </div>
                    @if (auth()->user()->hasAnyRole(['admin','pic']))
                    <div class="flex items-center gap-2">
                        <flux:checkbox wire:model="noteIsInternal" id="internal" />
                        <label for="internal" class="text-sm text-zinc-600 dark:text-zinc-400">Internal note only</label>
                    </div>
                    @endif
                    <flux:button wire:click="addNote" size="sm" icon="chat-bubble-left">Add Note</flux:button>
                </div>
                @else
                <div class="border-t border-zinc-200 dark:border-zinc-700 pt-4">
                    <flux:text class="text-sm text-zinc-400">This claim is closed. No further notes can be added.</flux:text>
                </div>
                @endif
            </flux:card>
        </div>

        {{-- Sidebar --}}
        <div class="space-y-6">
            {{-- Status Timeline --}}
            <flux:card class="dark:bg-zinc-900">
                <flux:heading size="lg" class="mb-4">Timeline</flux:heading>
                @php
                    $appealed = $claim->appealed_at;
                    $tsMap = collect([
                        'submitted'        => $claim->created_at,
                        'approved'         => $claim->approved_at,
                        'rejected'         => $claim->rejected_at,
                        'docs_pre'         => ($claim->documents_received_at && (!$appealed || $claim->documents_received_at <= $appealed)) ? $claim->documents_received_at : null,
                        'insurer_sub_pre'  => ($claim->submitted_to_insurer_at && (!$appealed || $claim->submitted_to_insurer_at <= $appealed)) ? $claim->submitted_to_insurer_at : null,
                        'insurer_dec_pre'  => ($claim->insurer_decision && $claim->insurer_decided_at && (!$appealed || $claim->insurer_decided_at <= $appealed)) ? $claim->insurer_decided_at : null,
                        'appealed'         => $appealed,
                        'docs_post'        => ($claim->documents_received_at && $appealed && $claim->documents_received_at > $appealed) ? $claim->documents_received_at : null,
                        'insurer_sub_post' => ($claim->submitted_to_insurer_at && $appealed && $claim->submitted_to_insurer_at > $appealed) ? $claim->submitted_to_insurer_at : null,
                        'insurer_dec_post' => ($claim->insurer_decision && $claim->insurer_decided_at && $appealed && $claim->insurer_decided_at > $appealed) ? $claim->insurer_decided_at : null,
                        'closed'           => $claim->closed_at,
                    ])->filter();
                    $latestTs  = $tsMap->max(fn($v) => $v->timestamp);
                    $latestKey = $tsMap->search(fn($v) => $v->timestamp === $latestTs) ?: 'submitted';
                @endphp
                <flux:timeline>
                    <flux:timeline.item>
                        <flux:timeline.indicator :color="$latestKey === 'submitted' ? 'green' : null">
                            <flux:icon.send-horizontal variant="micro" />
                        </flux:timeline.indicator>
                        <flux:timeline.content>
                            <div class="flex items-center justify-between">
                                <flux:heading>Submitted</flux:heading>
                                <flux:text class="text-xs text-zinc-400">{{ $claim->created_at->format('M j, g:i A') }}</flux:text>
                            </div>
                        </flux:timeline.content>
                    </flux:timeline.item>

                    {{-- PIC approval / rejection happen before documents are received --}}
                    @if ($claim->approved_at)
                    <flux:timeline.item>
                        <flux:timeline.indicator :color="$latestKey === 'approved' ? 'green' : null">
                            <flux:icon.check variant="micro" />
                        </flux:timeline.indicator>
                        <flux:timeline.content>
                            <div class="flex items-center justify-between">
                                <flux:heading>Approved by PIC</flux:heading>
                                <flux:text class="text-xs text-zinc-400">{{ $claim->approved_at->format('M j, g:i A') }}</flux:text>
                            </div>
                        </flux:timeline.content>
                    </flux:timeline.item>
                    @endif

                    @if ($claim->rejected_at)
                    <flux:timeline.item>
                        <flux:timeline.indicator :color="$latestKey === 'rejected' ? 'green' : null">
                            <flux:icon.x-mark variant="micro" />
                        </flux:timeline.indicator>
                        <flux:timeline.content>
                            <div class="flex items-center justify-between">
                                <flux:heading>Rejected by PIC</flux:heading>
                                <flux:text class="text-xs text-zinc-400">{{ $claim->rejected_at->format('M j, g:i A') }}</flux:text>
                            </div>
                            @if ($claim->rejection_reason)
                            <flux:text class="text-xs mt-0.5">{{ $claim->rejection_reason }}</flux:text>
                            @endif
                        </flux:timeline.content>
                    </flux:timeline.item>
                    @endif

                    {{-- Pre-appeal: Documents Received --}}
                    @if ($claim->documents_received_at && (!$appealed || $claim->documents_received_at <= $appealed))
                    <flux:timeline.item>
                        <flux:timeline.indicator :color="$latestKey === 'docs_pre' ? 'green' : null">
                            <flux:icon.check-check variant="micro" />
                        </flux:timeline.indicator>
                        <flux:timeline.content>
                            <div class="flex items-center justify-between">
                                <flux:heading>All Documents Received</flux:heading>
                                <flux:text class="text-xs text-zinc-400">{{ $claim->documents_received_at->format('M j, g:i A') }}</flux:text>
                            </div>
                            {{-- @if ($claim->documentsReceivedBy)
                            <flux:text class="text-xs mt-0.5">Verified by {{ $claim->documentsReceivedBy->name }}</flux:text>
                            @endif --}}
                        </flux:timeline.content>
                    </flux:timeline.item>
                    @endif

                    {{-- Pre-appeal: Submitted to Insurer --}}
                    @if ($claim->submitted_to_insurer_at && (!$appealed || $claim->submitted_to_insurer_at <= $appealed))
                    <flux:timeline.item>
                        <flux:timeline.indicator :color="$latestKey === 'insurer_sub_pre' ? 'green' : null">
                            <flux:icon.send-horizontal variant="micro" />
                        </flux:timeline.indicator>
                        <flux:timeline.content>
                            <div class="flex items-center justify-between">
                                <flux:heading>Submitted to Insurer</flux:heading>
                                <flux:text class="text-xs text-zinc-400">{{ $claim->submitted_to_insurer_at->format('M j, g:i A') }}</flux:text>
                            </div>
                            {{-- @if ($claim->submittedToInsurerBy)
                            <flux:text class="text-xs mt-0.5">By {{ $claim->submittedToInsurerBy->name }}</flux:text>
                            @endif --}}
                        </flux:timeline.content>
                    </flux:timeline.item>
                    @endif

                    {{-- Pre-appeal: Insurer Decision --}}
                    @if ($claim->insurer_decision && (!$appealed || $claim->insurer_decided_at <= $appealed))
                    <flux:timeline.item>
                        <flux:timeline.indicator :color="$latestKey === 'insurer_dec_pre' ? 'green' : null">
                            @if ($claim->insurer_decision === 'approved')
                                <flux:icon.circle-check variant="micro" />
                            @else
                                <flux:icon.x variant="micro" />
                            @endif
                        </flux:timeline.indicator>
                        <flux:timeline.content>
                            <div class="flex items-center justify-between">
                                <flux:heading>Insurer {{ $claim->insurer_decision === 'approved' ? 'Approved' : 'Rejected' }}</flux:heading>
                                <flux:text class="text-xs text-zinc-400">{{ $claim->insurer_decided_at->format('M j, g:i A') }}</flux:text>
                            </div>
                            {{-- @if ($claim->insurerDecidedBy)
                            <flux:text class="text-xs mt-0.5">Recorded by {{ $claim->insurerDecidedBy->name }}</flux:text>
                            @endif --}}
                            @if ($claim->insurer_decision === 'approved' && $claim->payment_channel)
                            <flux:text class="text-xs mt-0.5">
                                Payment channel: <strong>{{ $claim->payment_channel === 'clab' ? 'Transfer to CLAB' : 'Transfer to Contractor' }}</strong>
                            </flux:text>
                            @endif
                            @if ($claim->insurer_decision === 'rejected' && $claim->insurer_rejection_reason)
                            <flux:text class="text-xs mt-0.5 text-red-500">{{ $claim->insurer_rejection_reason }}</flux:text>
                            @endif
                        </flux:timeline.content>
                    </flux:timeline.item>
                    @endif

                    {{-- Appeal marker --}}
                    @if ($appealed)
                    <flux:timeline.item>
                        <flux:timeline.indicator :color="$latestKey === 'appealed' ? 'green' : null">
                            <flux:icon.arrow-path variant="micro" />
                        </flux:timeline.indicator>
                        <flux:timeline.content>
                            <div class="flex items-center justify-between">
                                <flux:heading>Appeal #{{ $claim->appeal_count }} Submitted</flux:heading>
                                <flux:text class="text-xs text-zinc-400">{{ $appealed->format('M j, g:i A') }}</flux:text>
                            </div>
                        </flux:timeline.content>
                    </flux:timeline.item>

                    {{-- Post-appeal: Documents Received --}}
                    @if ($claim->documents_received_at && $claim->documents_received_at > $appealed)
                    <flux:timeline.item>
                        <flux:timeline.indicator :color="$latestKey === 'docs_post' ? 'green' : null">
                            <flux:icon.check-check variant="micro" />
                        </flux:timeline.indicator>
                        <flux:timeline.content>
                            <div class="flex items-center justify-between">
                                <flux:heading>All Documents Received</flux:heading>
                                <flux:text class="text-xs text-zinc-400">{{ $claim->documents_received_at->format('M j, g:i A') }}</flux:text>
                            </div>
                            @if ($claim->documentsReceivedBy)
                            <flux:text class="text-xs mt-0.5">Verified by {{ $claim->documentsReceivedBy->name }}</flux:text>
                            @endif
                        </flux:timeline.content>
                    </flux:timeline.item>
                    @endif

                    {{-- Post-appeal: Submitted to Insurer --}}
                    @if ($claim->submitted_to_insurer_at && $claim->submitted_to_insurer_at > $appealed)
                    <flux:timeline.item>
                        <flux:timeline.indicator :color="$latestKey === 'insurer_sub_post' ? 'green' : null">
                            <flux:icon.send-horizontal variant="micro" />
                        </flux:timeline.indicator>
                        <flux:timeline.content>
                            <div class="flex items-center justify-between">
                                <flux:heading>Resubmitted to Insurer</flux:heading>
                                <flux:text class="text-xs text-zinc-400">{{ $claim->submitted_to_insurer_at->format('M j, g:i A') }}</flux:text>
                            </div>
                            @if ($claim->submittedToInsurerBy)
                            <flux:text class="text-xs mt-0.5">By {{ $claim->submittedToInsurerBy->name }}</flux:text>
                            @endif
                        </flux:timeline.content>
                    </flux:timeline.item>
                    @endif

                    {{-- Post-appeal: Insurer Decision --}}
                    @if ($claim->insurer_decision && $claim->insurer_decided_at > $appealed)
                    <flux:timeline.item>
                        <flux:timeline.indicator :color="$latestKey === 'insurer_dec_post' ? 'green' : null">
                            @if ($claim->insurer_decision === 'approved')
                                <flux:icon.circle-check variant="micro" />
                            @else
                                <flux:icon.x variant="micro" />
                            @endif
                        </flux:timeline.indicator>
                        <flux:timeline.content>
                            <div class="flex items-center justify-between">
                                <flux:heading>Insurer {{ $claim->insurer_decision === 'approved' ? 'Approved' : 'Rejected' }} (Appeal)</flux:heading>
                                <flux:text class="text-xs text-zinc-400">{{ $claim->insurer_decided_at->format('M j, g:i A') }}</flux:text>
                            </div>
                            {{-- @if ($claim->insurerDecidedBy)
                            <flux:text class="text-xs mt-0.5">Recorded by {{ $claim->insurerDecidedBy->name }}</flux:text>
                            @endif --}}
                            @if ($claim->insurer_decision === 'approved' && $claim->payment_channel)
                            <flux:text class="text-xs mt-0.5">
                                Payment channel: <strong>{{ $claim->payment_channel === 'clab' ? 'Transfer to CLAB' : 'Transfer to Contractor' }}</strong>
                            </flux:text>
                            @endif
                            @if ($claim->insurer_decision === 'rejected' && $claim->insurer_rejection_reason)
                            <flux:text class="text-xs mt-0.5 text-red-500">{{ $claim->insurer_rejection_reason }}</flux:text>
                            @endif
                        </flux:timeline.content>
                    </flux:timeline.item>
                    @endif
                    @endif

                    @if ($claim->closed_at)
                    <flux:timeline.item>
                        <flux:timeline.indicator :color="$latestKey === 'closed' ? 'green' : null">
                            <flux:icon.archive-box variant="micro" />
                        </flux:timeline.indicator>
                        <flux:timeline.content>
                            <div class="flex items-center justify-between">
                                <flux:heading>Case Closed</flux:heading>
                                <flux:text class="text-xs text-zinc-400">{{ $claim->closed_at->format('M j, g:i A') }}</flux:text>
                            </div>
                        </flux:timeline.content>
                    </flux:timeline.item>
                    @endif
                </flux:timeline>
            </flux:card>

            @php
                $isEmployerManaged = $claim->isEmployerManaged();
                $insurerLabel = match($claim->claim_type) { 'perkeso' => 'PERKESO', 'green_card' => 'CIDB', default => 'Liberty' };
                $clientHasUploaded = $claim->documents->filter(fn($d) => $d->path)->isNotEmpty();
                // "needs redo" = no value yet, OR value predates last appeal
                $needsSubmit  = ! $claim->submitted_to_insurer_at
                    || ($claim->appealed_at && $claim->appealed_at > $claim->submitted_to_insurer_at);
                // employer-managed appeal: appeal confirmed but no new submission yet — skip submission, go straight to decision
                $employerAppealActive = $isEmployerManaged
                    && $claim->appealed_at
                    && (! $claim->submitted_to_insurer_at || $claim->appealed_at > $claim->submitted_to_insurer_at);
                // "current round submitted" = submitted AFTER last appeal (or no appeal)
                $currentRoundSubmitted = $claim->submitted_to_insurer_at
                    && (! $claim->appealed_at || $claim->submitted_to_insurer_at > $claim->appealed_at);
                $needsDecision = ($currentRoundSubmitted
                    && (! $claim->insurer_decision || $claim->insurer_decided_at < $claim->submitted_to_insurer_at))
                    || ($employerAppealActive
                        && (! $claim->insurer_decision || ($claim->insurer_decided_at && $claim->insurer_decided_at < $claim->appealed_at)));
                // Close prompt only when decision is from the current (post-last-appeal) round
                $decisionIsCurrent = $claim->insurer_decision
                    && (! $claim->appealed_at || $claim->insurer_decided_at > $claim->appealed_at);
            @endphp

            {{-- Insurer Submission Prompt --}}
            @if ($needsSubmit)
                @if ($isEmployerManaged && auth()->id() === $claim->user_id && $clientHasUploaded && ! $employerAppealActive)
                <style>
                    @keyframes blink3 { 0%, 100% { opacity: 1; } 50% { opacity: 0.25; } }
                    .blink-3 { animation: blink3 0.6s ease-in-out 3; }
                </style>
                <flux:card class="blink-3 dark:bg-zinc-900 border border-amber-300 dark:border-amber-700">
                    <div class="flex items-start gap-4">
                        <div class="p-2 rounded-lg bg-amber-50 dark:bg-amber-900/30 shrink-0">
                            <flux:icon.exclamation-triangle class="w-5 h-5 text-amber-500" />
                        </div>
                        <div class="flex-1">
                            <flux:heading size="md" class="mb-1">Action Required: Submit to {{ $insurerLabel }}</flux:heading>
                            <flux:text class="text-sm mb-4">You are required to submit this claim directly to <strong>{{ $insurerLabel }}</strong>. Once submitted, please confirm below so CLAB can track the progress.</flux:text>
                            <flux:button wire:click="openEmployerSubmissionModal" variant="primary" size="sm" icon="paper-airplane">
                                Yes, I have submitted to {{ $insurerLabel }}
                            </flux:button>
                        </div>
                    </div>
                </flux:card>
                @elseif (! $isEmployerManaged && auth()->user()->hasAnyRole(['admin', 'pic']) && $claim->documents_received_at)
                <style>
                    @keyframes blink3 { 0%, 100% { opacity: 1; } 50% { opacity: 0.25; } }
                    .blink-3 { animation: blink3 0.6s ease-in-out 3; }
                </style>
                <flux:card class="blink-3 dark:bg-zinc-900 border border-amber-300 dark:border-amber-700">
                    <div class="flex items-start gap-4">
                        <div class="p-2 rounded-lg bg-amber-50 dark:bg-amber-900/30 shrink-0">
                            <flux:icon.exclamation-triangle class="w-5 h-5 text-amber-500" />
                        </div>
                        <div class="flex-1">
                            <flux:heading size="md" class="mb-1">Pending: Submit to Insurer</flux:heading>
                            <flux:text class="text-sm mb-4">Have you submitted this claim application to the insurance provider ({{ $insurerLabel }})?</flux:text>
                            <flux:button wire:click="openInsurerModal" variant="primary" size="sm" icon="paper-airplane">
                                Yes, I have submitted to {{ $insurerLabel }}
                            </flux:button>
                        </div>
                    </div>
                </flux:card>
                @endif
            @endif

            {{-- Insurer Decision Prompt --}}
            @if ($needsDecision)
                @if ($isEmployerManaged && auth()->id() === $claim->user_id)
                <flux:card class="dark:bg-zinc-900 border border-blue-300 dark:border-blue-700">
                    <div class="flex items-start gap-4">
                        <div class="p-2 rounded-lg bg-blue-50 dark:bg-blue-900/30 shrink-0">
                            <flux:icon.scale variant="outline" class="w-5 h-5 text-blue-500" />
                        </div>
                        <div class="flex-1">
                            <flux:heading size="md" class="mb-1">Update: {{ $insurerLabel }} Decision</flux:heading>
                            <flux:text class="text-sm mb-4">Has <strong>{{ $insurerLabel }}</strong> responded with a decision on your claim application?</flux:text>
                            <div class="flex gap-2">
                                <flux:button wire:click="openInsurerApprovedModal" variant="primary" size="sm" icon="check">
                                    Approved
                                </flux:button>
                                <flux:button wire:click="openInsurerRejectedModal" variant="danger" size="sm" icon="x-mark">
                                    Not Approved
                                </flux:button>
                            </div>
                        </div>
                    </div>
                </flux:card>
                @elseif (! $isEmployerManaged && auth()->user()->hasAnyRole(['admin', 'pic']))
                <flux:card class="dark:bg-zinc-900 border border-blue-300 dark:border-blue-700">
                    <div class="flex items-start gap-4">
                        <div class="p-2 rounded-lg bg-blue-50 dark:bg-blue-900/30 shrink-0">
                            <flux:icon.scale variant="outline" class="w-5 h-5 text-blue-500" />
                        </div>
                        <div class="flex-1">
                            <flux:heading size="md" class="mb-1">Pending: Insurer Decision</flux:heading>
                            <flux:text class="text-sm mb-4">Has {{ $insurerLabel }} responded with a decision on this claim?</flux:text>
                            <div class="flex gap-2">
                                <flux:button wire:click="openInsurerApprovedModal" variant="primary" size="sm" icon="check">
                                    Approved
                                </flux:button>
                                <flux:button wire:click="openInsurerRejectedModal" variant="danger" size="sm" icon="x-mark">
                                    Not Approved
                                </flux:button>
                            </div>
                        </div>
                    </div>
                </flux:card>
                @endif
            @endif

            {{-- Appeal Rejected — Close Prompt --}}
            @if ($claim->appeal_count >= 1 && $decisionIsCurrent && $claim->insurer_decision === 'rejected' && $claim->status !== 'closed')
            @can('claims.close')
            <flux:card class="dark:bg-zinc-900 border border-red-300 dark:border-red-700">
                <div class="flex items-start gap-4">
                    <div class="p-2 rounded-lg bg-red-50 dark:bg-red-900/30 shrink-0">
                        <flux:icon.x-circle class="w-5 h-5 text-red-500" />
                    </div>
                    <div class="flex-1">
                        <flux:heading size="md" class="mb-1">Appeal Rejected — Close Application</flux:heading>
                        <flux:text class="text-sm mb-4">
                            The insurer has rejected this claim after appeal. No further appeal is allowed.
                            Please close the application to finalise the case.
                        </flux:text>
                        <flux:button
                            wire:click="closeClaim"
                            variant="primary"
                            color="emerald"
                            size="sm"
                            icon="archive-box"
                        >
                            Close Application
                        </flux:button>
                    </div>
                </div>
            </flux:card>
            @endcan
            @endif

            {{-- Employer-Managed Appeal Prompt --}}
            @if ($isEmployerManaged && auth()->id() === $claim->user_id && $decisionIsCurrent && $claim->insurer_decision === 'rejected' && $claim->appeal_count === 0 && $claim->status !== 'closed')
            <flux:card class="dark:bg-zinc-900 border border-amber-300 dark:border-amber-700">
                <div class="flex items-start gap-4">
                    <div class="p-2 rounded-lg bg-amber-50 dark:bg-amber-900/30 shrink-0">
                        <flux:icon.arrow-path class="w-5 h-5 text-amber-500" />
                    </div>
                    <div class="flex-1">
                        <flux:heading size="md" class="mb-1">Appeal with {{ $insurerLabel }}?</flux:heading>
                        <flux:text class="text-sm mb-4">Your claim was not approved. You may apply for an appeal directly with <strong>{{ $insurerLabel }}</strong>. Have you already submitted an appeal?</flux:text>
                        <flux:button wire:click="openEmployerAppealModal" variant="primary" size="sm" icon="arrow-path">
                            Yes, I have applied for an appeal
                        </flux:button>
                    </div>
                </div>
            </flux:card>
            @endif

            {{-- Submitted By --}}
            <flux:card class="dark:bg-zinc-900">
                <flux:heading size="lg" class="mb-4">Submitted By</flux:heading>
                <flux:text variant="strong" class="text-sm">{{ $claim->user->name }}</flux:text>
                <flux:text class="text-sm">{{ $claim->user->email }}</flux:text>
                @if ($claim->user->company_name)
                <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ $claim->user->company_name }}</p>
                @endif

                @if ($claim->company_pic_name || $claim->company_pic_phone || $claim->company_pic_email)
                <div class="mt-4 pt-4 border-t border-zinc-200 dark:border-zinc-700">
                    <p class="text-xs font-semibold uppercase tracking-wider text-zinc-400 dark:text-zinc-500 mb-2">Person In Charge</p>
                    @if ($claim->company_pic_name)
                    <flux:text variant="strong" class="text-sm">{{ $claim->company_pic_name }}</flux:text>
                    @endif
                    @if ($claim->company_pic_phone)
                    <flux:text class="text-sm">{{ $claim->company_pic_phone }}</flux:text>
                    @endif
                    @if ($claim->company_pic_email)
                    <flux:text class="text-sm">{{ $claim->company_pic_email }}</flux:text>
                    @endif
                </div>
                @endif
            </flux:card>
        </div>
    </div>

    {{-- Reject Modal --}}
    <flux:modal wire:model="showRejectModal" name="reject-claim">
        <div class="p-6">
            <flux:heading size="lg" class="mb-4">Reject Claim</flux:heading>
            <flux:textarea wire:model="rejectionReason" label="Reason for Rejection" rows="4" required />
            <div class="flex gap-3 mt-4">
                <flux:button wire:click="confirmReject" variant="danger">Confirm Reject</flux:button>
                <flux:button wire:click="$set('showRejectModal', false)" variant="ghost">Cancel</flux:button>
            </div>
        </div>
    </flux:modal>

    {{-- Forward Modal --}}
    <flux:modal wire:model="showForwardModal" name="forward-claim">
        <div class="p-6">
            <flux:heading size="lg" class="mb-4">Forward Claim</flux:heading>
            <flux:select wire:model="forwardTo" label="Forward To" placeholder="Select...">
                <flux:select.option value="perkeso">PERKESO</flux:select.option>
                <flux:select.option value="green_card">Green Card</flux:select.option>
            </flux:select>
            <div class="flex gap-3 mt-4">
                <flux:button wire:click="confirmForward" variant="filled">Confirm</flux:button>
                <flux:button wire:click="$set('showForwardModal', false)" variant="ghost">Cancel</flux:button>
            </div>
        </div>
    </flux:modal>

    {{-- Employer Insurer Submission Modal (Employer-Managed Claims) --}}
    <flux:modal name="employer-insurer-submission" class="max-w-md">
        <div class="p-6 space-y-4">
            <div class="flex items-center gap-3">
                <div class="p-2 rounded-lg bg-blue-50 dark:bg-blue-900/30">
                    <flux:icon.paper-airplane class="w-5 h-5 text-blue-600 dark:text-blue-400" />
                </div>
                <flux:heading size="lg">Submitted to {{ $claim->claim_type === 'perkeso' ? 'PERKESO' : 'Green Card' }}</flux:heading>
            </div>

            <flux:text>You are confirming that you have submitted this claim application directly to <strong>{{ $claim->claim_type === 'perkeso' ? 'PERKESO' : 'Green Card' }}</strong>.</flux:text>
            <flux:text class="text-sm text-zinc-500">CLAB will be notified and will monitor the progress of your application.</flux:text>

            <div class="flex flex-col gap-2 pt-2">
                <flux:button wire:click="confirmSubmittedToInsurer(false)" variant="primary" icon="check" class="w-full">
                    Confirm
                </flux:button>
                <flux:modal.close>
                    <flux:button variant="ghost" class="w-full">Cancel</flux:button>
                </flux:modal.close>
            </div>
        </div>
    </flux:modal>

    {{-- Insurer Submission Modal (PIC/Admin) --}}
    <flux:modal name="insurer-submission" class="max-w-md">
        <div class="p-6 space-y-4">
            <div class="flex items-center gap-3">
                <div class="p-2 rounded-lg bg-blue-50 dark:bg-blue-900/30">
                    <flux:icon.paper-airplane class="w-5 h-5 text-blue-600 dark:text-blue-400" />
                </div>
                <flux:heading size="lg">Submitted to Insurer</flux:heading>
            </div>

            <flux:text>You are confirming that this claim application has been submitted to <strong>{{ $insurerLabel }}</strong>.</flux:text>
            <flux:text>Would you like to send an email notification to the contractor informing them of this update?</flux:text>

            <div class="flex flex-col gap-2 pt-2">
                <flux:button wire:click="confirmSubmittedToInsurer(true)" variant="primary" icon="envelope" class="w-full">
                    Confirm & Notify Contractor
                </flux:button>
                <flux:button wire:click="confirmSubmittedToInsurer(false)" variant="outline" icon="bell-slash" class="w-full">
                    Confirm, No Email
                </flux:button>
                <flux:modal.close>
                    <flux:button variant="ghost" class="w-full">Cancel</flux:button>
                </flux:modal.close>
            </div>
        </div>
    </flux:modal>

    {{-- Insurer Approved Modal --}}
    <flux:modal name="insurer-approved" class="max-w-md">
        <div class="p-6 space-y-4">
            <div class="flex items-center gap-3">
                <div class="p-2 rounded-lg bg-green-50 dark:bg-green-900/30">
                    <flux:icon.check-circle class="w-5 h-5 text-green-600 dark:text-green-400" />
                </div>
                <flux:heading size="lg">{{ $claim->isEmployerManaged() ? $insurerLabel . ' Approved' : 'Insurer Approved' }}</flux:heading>
            </div>

            <flux:text class="text-sm">
                @if ($claim->isEmployerManaged())
                    Please upload the approval letter received from <strong>{{ $insurerLabel }}</strong> and specify the payment channel.
                @else
                    Please upload the approval letter and specify the payment channel as advised by {{ $insurerLabel }}.
                @endif
            </flux:text>

            <flux:field>
                <flux:label>Payment Channel</flux:label>
                <flux:select variant="listbox" wire:model="insurerPaymentChannel" placeholder="Select payment channel...">
                    <flux:select.option value="clab">Transfer to CLAB</flux:select.option>
                    <flux:select.option value="contractor">Transfer to Contractor</flux:select.option>
                </flux:select>
                <flux:error name="insurerPaymentChannel" />
            </flux:field>

            <flux:field>
                <flux:label>Approval Letter (PDF)</flux:label>
                <input
                    type="file"
                    x-on:change="$wire.upload('insurerApprovalLetter', $event.target.files[0])"
                    accept=".pdf"
                    class="block w-full text-sm text-zinc-700 dark:text-zinc-300
                           file:mr-3 file:py-1.5 file:px-3 file:rounded file:border-0
                           file:text-sm file:font-medium file:bg-zinc-100 file:text-zinc-700
                           dark:file:bg-zinc-800 dark:file:text-zinc-300"
                />
                <div wire:loading wire:target="insurerApprovalLetter" class="text-xs text-zinc-400 mt-1">Uploading…</div>
                <flux:error name="insurerApprovalLetter" />
            </flux:field>

            <div class="flex flex-col gap-2 pt-2">
                <flux:button wire:click="confirmInsurerApproved" variant="primary" icon="check" class="w-full">
                    {{ $claim->isEmployerManaged() ? 'Confirm & Notify CLAB' : 'Confirm & Notify Contractor' }}
                </flux:button>
                <flux:modal.close>
                    <flux:button variant="ghost" class="w-full">Cancel</flux:button>
                </flux:modal.close>
            </div>
        </div>
    </flux:modal>

    {{-- Insurer Rejected Modal --}}
    <flux:modal name="insurer-rejected" class="max-w-md">
        <div class="p-6 space-y-4">
            <div class="flex items-center gap-3">
                <div class="p-2 rounded-lg bg-red-50 dark:bg-red-900/30">
                    <flux:icon.x-circle class="w-5 h-5 text-red-600 dark:text-red-400" />
                </div>
                <flux:heading size="lg">{{ $claim->isEmployerManaged() ? $insurerLabel . ' Not Approved' : 'Insurer Not Approved' }}</flux:heading>
            </div>

            <flux:text class="text-sm">
                @if ($claim->isEmployerManaged())
                    Please provide the rejection reason from <strong>{{ $insurerLabel }}</strong>. CLAB admin will be notified.
                @else
                    Please provide the reason for rejection from {{ $insurerLabel }}. The contractor will be notified via email.
                @endif
            </flux:text>

            <flux:field>
                <flux:label>Rejection Reason</flux:label>
                <flux:textarea wire:model="insurerRejectionReason" rows="3" placeholder="e.g. Claim submitted outside policy period..." />
                <flux:error name="insurerRejectionReason" />
            </flux:field>

            <flux:field>
                <flux:label>Attachment <span class="text-zinc-400 font-normal ms-1">(optional)</span></flux:label>
                <input
                    type="file"
                    x-on:change="$wire.upload('insurerRejectionAttachment', $event.target.files[0])"
                    accept=".pdf"
                    class="block w-full text-sm text-zinc-700 dark:text-zinc-300
                           file:mr-3 file:py-1.5 file:px-3 file:rounded file:border-0
                           file:text-sm file:font-medium file:bg-zinc-100 file:text-zinc-700
                           dark:file:bg-zinc-800 dark:file:text-zinc-300"
                />
                <div wire:loading wire:target="insurerRejectionAttachment" class="text-xs text-zinc-400 mt-1">Uploading…</div>
                <flux:error name="insurerRejectionAttachment" />
            </flux:field>

            <div class="flex flex-col gap-2 pt-2">
                <flux:button wire:click="confirmInsurerRejected" variant="danger" class="w-full">
                    {{ $claim->isEmployerManaged() ? 'Confirm & Notify CLAB' : 'Confirm & Notify Contractor' }}
                </flux:button>
                <flux:modal.close>
                    <flux:button variant="ghost" class="w-full">Cancel</flux:button>
                </flux:modal.close>
            </div>
        </div>
    </flux:modal>

    {{-- Employer Appeal Confirmation Modal --}}
    <flux:modal name="employer-appeal" class="max-w-md">
        <div class="p-6 space-y-4">
            <div class="flex items-center gap-3">
                <div class="p-2 rounded-lg bg-amber-50 dark:bg-amber-900/30">
                    <flux:icon.arrow-path class="w-5 h-5 text-amber-500" />
                </div>
                <flux:heading size="lg">Confirm Appeal Submission</flux:heading>
            </div>
            <flux:text>You are confirming that you have submitted an appeal directly to <strong>{{ $insurerLabel }}</strong>. CLAB will be notified and will monitor the progress.</flux:text>
            <div class="flex flex-col gap-2 pt-2">
                <flux:button wire:click="confirmEmployerAppeal" variant="primary" icon="check" class="w-full">
                    Confirm
                </flux:button>
                <flux:modal.close>
                    <flux:button variant="ghost" class="w-full">Cancel</flux:button>
                </flux:modal.close>
            </div>
        </div>
    </flux:modal>

    {{-- Notify Missing Documents Compose Modal --}}
    <flux:modal name="notify-missing-docs" class="max-w-2xl">
        <div class="p-6 space-y-4">
            <flux:heading size="lg">Notify Contractor — Missing Documents</flux:heading>
            <flux:text class="text-sm">Review and edit the email before sending. The email will be sent to <strong>{{ $claim->user?->email }}</strong>.</flux:text>

            <flux:field>
                <flux:label>Subject</flux:label>
                <flux:input wire:model="emailSubject" />
                <flux:error name="emailSubject" />
            </flux:field>

            <flux:field>
                <flux:label>CC <span class="text-zinc-400 font-normal">(comma-separated, optional)</span></flux:label>
                <flux:input wire:model="emailCc" placeholder="e.g. pic@clab.com, manager@clab.com" />
                <flux:error name="emailCc" />
            </flux:field>

            <flux:field>
                <flux:label>Body</flux:label>
                <flux:textarea wire:model="emailBody" rows="12" class="font-mono text-sm" />
                <flux:error name="emailBody" />
            </flux:field>

            <div class="flex justify-end gap-2 pt-2">
                <flux:modal.close>
                    <flux:button variant="ghost">Cancel</flux:button>
                </flux:modal.close>
                <flux:button wire:click="sendMissingDocumentsEmail" variant="primary" icon="paper-airplane">
                    Send Email
                </flux:button>
            </div>
        </div>
    </flux:modal>
</div>
