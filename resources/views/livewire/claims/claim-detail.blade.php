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

        {{-- Action Buttons --}}
        <div class="flex gap-2 flex-wrap">
            @can('claims.approve')
            @if ($claim->status === 'open')
            <flux:button wire:click="approve" variant="filled" size="sm" icon="check">Approve</flux:button>
            <flux:button wire:click="$set('showRejectModal', true)" variant="danger" size="sm" icon="x-mark">Reject</flux:button>
            <flux:button wire:click="$set('showForwardModal', true)" variant="ghost" size="sm" icon="arrow-right">Forward</flux:button>
            @endif
            @endcan

            @can('claims.close')
            @if ($claim->status === 'in_progress')
            <flux:button wire:click="closeClaim" variant="filled" size="sm" icon="archive-box">Close Case</flux:button>
            @endif
            @endcan
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Main Content --}}
        <div class="lg:col-span-2 space-y-6">

            {{-- Worker Info --}}
            <flux:card class="dark:bg-zinc-900">
                <flux:heading size="lg" class="mb-4">Worker Information</flux:heading>
                <dl class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <dt class="text-zinc-500 dark:text-zinc-400">Name</dt>
                        <dd class="font-medium text-zinc-900 dark:text-white mt-1">{{ $claim->worker->name }}</dd>
                    </div>
                    <div>
                        <dt class="text-zinc-500 dark:text-zinc-400">Passport No.</dt>
                        <dd class="font-medium text-zinc-900 dark:text-white mt-1">{{ $claim->worker->passport_number }}</dd>
                    </div>
                    <div>
                        <dt class="text-zinc-500 dark:text-zinc-400">Nationality</dt>
                        <dd class="mt-1 dark:text-zinc-300">{{ $claim->worker->nationality }}</dd>
                    </div>
                    <div>
                        <dt class="text-zinc-500 dark:text-zinc-400">Worker Type</dt>
                        <dd class="mt-1">
                            <flux:badge color="{{ $claim->worker->worker_type === 'outsource' ? 'amber' : 'fuchsia' }}" size="sm">{{ ucfirst($claim->worker->worker_type) }}</flux:badge>
                        </dd>
                    </div>
                </dl>
            </flux:card>

            {{-- Incident Details --}}
            <flux:card class="dark:bg-zinc-900">
                <flux:heading size="lg" class="mb-4">Incident Details</flux:heading>
                <dl class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <dt class="text-zinc-500 dark:text-zinc-400">Incident Date</dt>
                        <dd class="mt-1 dark:text-zinc-300">{{ $claim->incident_date?->format('d/m/Y') }}</dd>
                    </div>
                    @if ($claim->hospital_name)
                    <div>
                        <dt class="text-zinc-500 dark:text-zinc-400">Hospital Name</dt>
                        <dd class="mt-1 dark:text-zinc-300">{{ $claim->hospital_name }}</dd>
                    </div>
                    <div>
                        <dt class="text-zinc-500 dark:text-zinc-400">Admission Date</dt>
                        <dd class="mt-1 dark:text-zinc-300">{{ $claim->admission_date?->format('d/m/Y') }}</dd>
                    </div>
                    <div>
                        <dt class="text-zinc-500 dark:text-zinc-400">Discharge Date</dt>
                        <dd class="mt-1 dark:text-zinc-300">{{ $claim->discharge_date?->format('d/m/Y') ?? '—' }}</dd>
                    </div>
                    @endif
                    <div class="col-span-2">
                        <dt class="text-zinc-500 dark:text-zinc-400">Description</dt>
                        <dd class="mt-1 dark:text-zinc-300">{{ $claim->incident_description }}</dd>
                    </div>
                    @if ($claim->forwarded_to)
                    <div>
                        <dt class="text-zinc-500 dark:text-zinc-400">Forwarded To</dt>
                        <dd class="mt-1">
                            <flux:badge color="blue" size="sm">{{ strtoupper(str_replace('_', ' ', $claim->forwarded_to)) }}</flux:badge>
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
                                    <flux:badge color="green" size="sm">Received</flux:badge>
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
                        <flux:badge color="green" size="sm">Received</flux:badge>
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
                            <flux:icon.paper-airplane variant="micro" />
                        </flux:timeline.indicator>
                        <flux:timeline.content>
                            <flux:heading>Submitted <flux:text inline>· {{ $claim->submitted_at?->format('d/m/Y') ?? $claim->created_at->format('d/m/Y') }}</flux:text></flux:heading>
                        </flux:timeline.content>
                    </flux:timeline.item>

                    @if ($claim->approved_at)
                    <flux:timeline.item>
                        <flux:timeline.indicator color="green">
                            <flux:icon.check variant="micro" />
                        </flux:timeline.indicator>
                        <flux:timeline.content>
                            <flux:heading>Approved <flux:text inline>· {{ $claim->approved_at->format('d/m/Y') }}</flux:text></flux:heading>
                        </flux:timeline.content>
                    </flux:timeline.item>
                    @endif

                    @if ($claim->rejected_at)
                    <flux:timeline.item>
                        <flux:timeline.indicator color="red">
                            <flux:icon.x-mark variant="micro" />
                        </flux:timeline.indicator>
                        <flux:timeline.content>
                            <flux:heading>Rejected <flux:text inline>· {{ $claim->rejected_at->format('d/m/Y') }}</flux:text></flux:heading>
                            @if ($claim->rejection_reason)
                            <flux:text class="text-xs mt-0.5">{{ $claim->rejection_reason }}</flux:text>
                            @endif
                        </flux:timeline.content>
                    </flux:timeline.item>
                    @endif

                    @if ($claim->closed_at)
                    <flux:timeline.item>
                        <flux:timeline.indicator color="blue">
                            <flux:icon.archive-box variant="micro" />
                        </flux:timeline.indicator>
                        <flux:timeline.content>
                            <flux:heading>Case Closed <flux:text inline>· {{ $claim->closed_at->format('d/m/Y') }}</flux:text></flux:heading>
                        </flux:timeline.content>
                    </flux:timeline.item>
                    @endif
                </flux:timeline>
            </flux:card>

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
</div>
