<div>
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-3 mb-1">
                <flux:heading size="xl">{{ $claim->claim_number }}</flux:heading>
                <flux:badge color="{{ $claim->status_color }}" size="sm">
                    {{ match($claim->status) {
                        'open' => 'Open',
                        'in_progress' => 'In Progress',
                        'closed' => 'Closed',
                        default => $claim->status
                    } }}
                </flux:badge>
            </div>
            <flux:subheading>{{ $claim->getClaimTypeLabel() }} — {{ $claim->claim_category === 'hospitalization' ? 'Hospitalization' : 'Death' }}</flux:subheading>
        </div>

        @if ($claim->insurer_decision === 'rejected' && $claim->user_id === auth()->id() && auth()->user()->hasRole('employer'))
        <flux:button :href="route('claims.appeal', $claim)" wire:navigate variant="danger" icon="arrow-path">
            Appeal Claim
        </flux:button>
        @endif

        @if (auth()->user()->hasAnyRole(['admin', 'pic']))
        <flux:dropdown>
            <flux:button
                icon:trailing="chevron-down"
                icon="{{ match($claim->status) { 'open' => 'fire', 'in_progress' => 'clock', 'closed' => 'check-circle', default => 'arrow-path' } }}"
                variant="{{ match($claim->status) { 'open' => 'danger', 'in_progress' => 'primary', 'closed' => 'filled', default => 'outline' } }}"
            >
                {{ match($claim->status) { 'open' => 'Open', 'in_progress' => 'In Progress', 'closed' => 'Closed', default => ucfirst($claim->status) } }}
            </flux:button>
            <flux:menu>
                <flux:menu.item icon="fire" wire:click="changeStatus('open')">Open</flux:menu.item>
                <flux:menu.item icon="clock" wire:click="changeStatus('in_progress')">In Progress</flux:menu.item>
                <flux:menu.item icon="check-circle" wire:click="changeStatus('closed')">Closed</flux:menu.item>
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
            <flux:card class="dark:bg-zinc-900">
                <flux:heading size="lg" class="mb-1">Required Documents</flux:heading>
                <flux:text class="mb-4 text-sm">Mark each document as received when the physical copy arrives.</flux:text>

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
                                        </div>
                                    </div>
                                    <flux:badge  size="sm">Received</flux:badge>
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
                                <p class="text-xs text-zinc-400">Awaiting physical document</p>
                                @endif
                            </div>
                        </div>
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
                        <flux:badge  size="sm">Received</flux:badge>
                        @endif
                    </div>
                    @endforeach
                </div>
                @endif
                @else
                <p class="text-sm text-zinc-400">No documents tracked yet.</p>
                @endif
            </flux:card>

            {{-- Notes --}}
            <flux:card class="dark:bg-zinc-900">
                <flux:heading size="lg" class="mb-4">Notes</flux:heading>

                <div class="space-y-3 mb-4">
                    @forelse ($claim->claimNotes->sortByDesc('created_at') as $note)
                    @if (!$note->is_internal || auth()->user()->hasAnyRole(['admin','pic']))
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
                    </div>
                    @endif
                    @empty
                    <p class="text-zinc-400 text-sm">No notes yet.</p>
                    @endforelse
                </div>

                <div class="border-t border-zinc-200 dark:border-zinc-700 pt-4">
                    <flux:textarea wire:model="newNote" placeholder="Add a note..." rows="3" class="mb-3" />
                    @if (auth()->user()->hasAnyRole(['admin','pic']))
                    <div class="flex items-center gap-2 mb-3">
                        <flux:checkbox wire:model="noteIsInternal" id="internal" />
                        <label for="internal" class="text-sm text-zinc-600 dark:text-zinc-400">Internal note only</label>
                    </div>
                    @endif
                    <flux:button wire:click="addNote" size="sm" icon="chat-bubble-left">Add Note</flux:button>
                </div>
            </flux:card>
        </div>

        {{-- Sidebar --}}
        <div class="space-y-6">
            {{-- Status Timeline --}}
            <flux:card class="dark:bg-zinc-900">
                <flux:heading size="lg" class="mb-4">Timeline</flux:heading>
                <flux:timeline>
                    <flux:timeline.item>
                        <flux:timeline.indicator>
                            <flux:icon.send-horizontal variant="micro" />
                        </flux:timeline.indicator>
                        <flux:timeline.content>
                            <div class="flex items-center justify-between">
                                <flux:heading>Submitted</flux:heading>
                                <flux:text class="text-xs text-zinc-400">{{ $claim->created_at->format('M j, g:i A') }}</flux:text>
                            </div>
                        </flux:timeline.content>
                    </flux:timeline.item>

                    @if ($claim->documents_received_at)
                    <flux:timeline.item>
                        <flux:timeline.indicator>
                            <flux:icon.file-check variant="micro" />
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

                    @if ($claim->submitted_to_insurer_at)
                    <flux:timeline.item>
                        <flux:timeline.indicator>
                            <flux:icon.send-horizontal variant="micro" />
                        </flux:timeline.indicator>
                        <flux:timeline.content>
                            <div class="flex items-center justify-between">
                                <flux:heading>Submitted to Insurer</flux:heading>
                                <flux:text class="text-xs text-zinc-400">{{ $claim->submitted_to_insurer_at->format('M j, g:i A') }}</flux:text>
                            </div>
                            @if ($claim->submittedToInsurerBy)
                            <flux:text class="text-xs mt-0.5">By {{ $claim->submittedToInsurerBy->name }}</flux:text>
                            @endif
                        </flux:timeline.content>
                    </flux:timeline.item>
                    @endif

                    @if ($claim->insurer_decision)
                    <flux:timeline.item>
                        <flux:timeline.indicator>
                            @if ($claim->insurer_decision === 'approved')
                                <flux:icon.check variant="micro" />
                            @else
                                <flux:icon.x variant="micro" />
                            @endif
                        </flux:timeline.indicator>
                        <flux:timeline.content>
                            <div class="flex items-center justify-between">
                                <flux:heading>Insurer {{ $claim->insurer_decision === 'approved' ? 'Approved' : 'Rejected' }}</flux:heading>
                                <flux:text class="text-xs text-zinc-400">{{ $claim->insurer_decided_at->format('M j, g:i A') }}</flux:text>
                            </div>
                            @if ($claim->insurerDecidedBy)
                            <flux:text class="text-xs mt-0.5">Recorded by {{ $claim->insurerDecidedBy->name }}</flux:text>
                            @endif
                            @if ($claim->insurer_decision === 'rejected' && $claim->insurer_rejection_reason)
                            <flux:text class="text-xs mt-0.5 text-red-500">{{ $claim->insurer_rejection_reason }}</flux:text>
                            @endif
                        </flux:timeline.content>
                    </flux:timeline.item>
                    @endif

                    @if ($claim->appealed_at)
                    <flux:timeline.item>
                        <flux:timeline.indicator>
                            <flux:icon.arrow-path variant="micro" />
                        </flux:timeline.indicator>
                        <flux:timeline.content>
                            <div class="flex items-center justify-between">
                                <flux:heading>Appeal #{{ $claim->appeal_count }} Submitted</flux:heading>
                                <flux:text class="text-xs text-zinc-400">{{ $claim->appealed_at->format('M j, g:i A') }}</flux:text>
                            </div>
                        </flux:timeline.content>
                    </flux:timeline.item>
                    @endif

                    @if ($claim->approved_at)
                    <flux:timeline.item>
                        <flux:timeline.indicator>
                            <flux:icon.check variant="micro" />
                        </flux:timeline.indicator>
                        <flux:timeline.content>
                            <div class="flex items-center justify-between">
                                <flux:heading>Approved</flux:heading>
                                <flux:text class="text-xs text-zinc-400">{{ $claim->approved_at->format('M j, g:i A') }}</flux:text>
                            </div>
                        </flux:timeline.content>
                    </flux:timeline.item>
                    @endif

                    @if ($claim->rejected_at)
                    <flux:timeline.item>
                        <flux:timeline.indicator>
                            <flux:icon.x-mark variant="micro" />
                        </flux:timeline.indicator>
                        <flux:timeline.content>
                            <div class="flex items-center justify-between">
                                <flux:heading>Rejected</flux:heading>
                                <flux:text class="text-xs text-zinc-400">{{ $claim->rejected_at->format('M j, g:i A') }}</flux:text>
                            </div>
                            @if ($claim->rejection_reason)
                            <flux:text class="text-xs mt-0.5">{{ $claim->rejection_reason }}</flux:text>
                            @endif
                        </flux:timeline.content>
                    </flux:timeline.item>
                    @endif

                    @if ($claim->closed_at)
                    <flux:timeline.item>
                        <flux:timeline.indicator >
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

            {{-- Insurer Submission Prompt --}}
            @if ($claim->documents_received_at && ! $claim->submitted_to_insurer_at)
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
                        <flux:text class="text-sm mb-4">Have you submitted this claim application to the insurance provider (Liberty)?</flux:text>
                        <flux:button wire:click="openInsurerModal" variant="primary" size="sm" icon="paper-airplane">
                            Yes, I have submitted to Liberty
                        </flux:button>
                    </div>
                </div>
            </flux:card>
            @endif

            {{-- Insurer Decision Prompt --}}
            @if ($claim->submitted_to_insurer_at && ! $claim->insurer_decision)
            <flux:card class="dark:bg-zinc-900 border border-blue-300 dark:border-blue-700">
                <div class="flex items-start gap-4">
                    <div class="p-2 rounded-lg bg-blue-50 dark:bg-blue-900/30 shrink-0">
                        <flux:icon.scale variant="outline" class="w-5 h-5 text-blue-500" />
                    </div>
                    <div class="flex-1">
                        <flux:heading size="md" class="mb-1">Pending: Insurer Decision</flux:heading>
                        <flux:text class="text-sm mb-4">Has Liberty Insurance responded with a decision on this claim?</flux:text>
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

            {{-- Submitted By --}}
            <flux:card class="dark:bg-zinc-900">
                <flux:heading size="lg" class="mb-4">Submitted By</flux:heading>
                <p class="font-medium dark:text-white">{{ $claim->user->name }}</p>
                <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ $claim->user->email }}</p>
                @if ($claim->user->company_name)
                <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ $claim->user->company_name }}</p>
                @endif

                @if ($claim->company_pic_name || $claim->company_pic_phone || $claim->company_pic_email)
                <div class="mt-4 pt-4 border-t border-zinc-200 dark:border-zinc-700">
                    <p class="text-xs font-semibold uppercase tracking-wider text-zinc-400 dark:text-zinc-500 mb-2">Person In Charge</p>
                    @if ($claim->company_pic_name)
                    <p class="font-medium dark:text-white text-sm">{{ $claim->company_pic_name }}</p>
                    @endif
                    @if ($claim->company_pic_phone)
                    <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ $claim->company_pic_phone }}</p>
                    @endif
                    @if ($claim->company_pic_email)
                    <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ $claim->company_pic_email }}</p>
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

    {{-- Insurer Submission Modal --}}
    <flux:modal name="insurer-submission" class="max-w-md">
        <div class="p-6 space-y-4">
            <div class="flex items-center gap-3">
                <div class="p-2 rounded-lg bg-blue-50 dark:bg-blue-900/30">
                    <flux:icon.paper-airplane class="w-5 h-5 text-blue-600 dark:text-blue-400" />
                </div>
                <flux:heading size="lg">Submitted to Insurer</flux:heading>
            </div>

            <flux:text>You are confirming that this claim application has been submitted to <strong>Liberty Insurance</strong>.</flux:text>
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
                <flux:heading size="lg">Insurer Approved</flux:heading>
            </div>

            <flux:text class="text-sm">Please upload the approval letter from Liberty Insurance as supporting proof. The contractor will be notified via email.</flux:text>

            <flux:field>
                <flux:label>Approval Letter (PDF)</flux:label>
                <input
                    type="file"
                    wire:model="insurerApprovalLetter"
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
                    Confirm & Notify Contractor
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
                <flux:heading size="lg">Insurer Not Approved</flux:heading>
            </div>

            <flux:text class="text-sm">Please provide the reason for rejection from Liberty Insurance. The contractor will be notified via email.</flux:text>

            <flux:field>
                <flux:label>Rejection Reason</flux:label>
                <flux:textarea wire:model="insurerRejectionReason" rows="3" placeholder="e.g. Claim submitted outside policy period..." />
                <flux:error name="insurerRejectionReason" />
            </flux:field>

            <flux:field>
                <flux:label>Attachment <span class="text-zinc-400 font-normal">(optional)</span></flux:label>
                <input
                    type="file"
                    wire:model="insurerRejectionAttachment"
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
                <flux:button wire:click="confirmInsurerRejected" variant="danger" icon="x-mark" class="w-full">
                    Confirm & Notify Contractor
                </flux:button>
                <flux:modal.close>
                    <flux:button variant="ghost" class="w-full">Cancel</flux:button>
                </flux:modal.close>
            </div>
        </div>
    </flux:modal>
</div>
