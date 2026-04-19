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
            <div class="bg-white rounded-xl border border-zinc-200 p-6">
                <flux:heading size="lg" class="mb-4">Worker Information</flux:heading>
                <dl class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <dt class="text-zinc-500">Name</dt>
                        <dd class="font-medium text-zinc-900 mt-1">{{ $claim->worker->name }}</dd>
                    </div>
                    <div>
                        <dt class="text-zinc-500">Passport No.</dt>
                        <dd class="font-medium font-mono mt-1">{{ $claim->worker->passport_number }}</dd>
                    </div>
                    <div>
                        <dt class="text-zinc-500">Nationality</dt>
                        <dd class="mt-1">{{ $claim->worker->nationality }}</dd>
                    </div>
                    <div>
                        <dt class="text-zinc-500">Worker Type</dt>
                        <dd class="mt-1">
                            <flux:badge color="zinc" size="sm">{{ ucfirst($claim->worker->worker_type) }}</flux:badge>
                        </dd>
                    </div>
                </dl>
            </div>

            {{-- Incident Details --}}
            <div class="bg-white rounded-xl border border-zinc-200 p-6">
                <flux:heading size="lg" class="mb-4">Incident Details</flux:heading>
                <dl class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <dt class="text-zinc-500">Incident Date</dt>
                        <dd class="mt-1">{{ $claim->incident_date?->format('d/m/Y') }}</dd>
                    </div>
                    @if ($claim->hospital_name)
                    <div>
                        <dt class="text-zinc-500">Hospital Name</dt>
                        <dd class="mt-1">{{ $claim->hospital_name }}</dd>
                    </div>
                    <div>
                        <dt class="text-zinc-500">Admission Date</dt>
                        <dd class="mt-1">{{ $claim->admission_date?->format('d/m/Y') }}</dd>
                    </div>
                    <div>
                        <dt class="text-zinc-500">Discharge Date</dt>
                        <dd class="mt-1">{{ $claim->discharge_date?->format('d/m/Y') ?? '—' }}</dd>
                    </div>
                    @endif
                    <div class="col-span-2">
                        <dt class="text-zinc-500">Description</dt>
                        <dd class="mt-1">{{ $claim->incident_description }}</dd>
                    </div>
                    @if ($claim->forwarded_to)
                    <div>
                        <dt class="text-zinc-500">Forwarded To</dt>
                        <dd class="mt-1">
                            <flux:badge color="blue" size="sm">{{ strtoupper(str_replace('_', ' ', $claim->forwarded_to)) }}</flux:badge>
                        </dd>
                    </div>
                    @endif
                </dl>
            </div>

            {{-- Documents --}}
            <div class="bg-white rounded-xl border border-zinc-200 p-6">
                <flux:heading size="lg" class="mb-4">Documents</flux:heading>

                @if ($claim->documents->count() > 0)
                <div class="space-y-2 mb-4">
                    @foreach ($claim->documents as $doc)
                    <div class="flex items-center justify-between p-3 bg-zinc-50 rounded-lg">
                        <div class="flex items-center gap-3">
                            <flux:icon.document class="w-5 h-5 text-zinc-400" />
                            <div>
                                <p class="text-sm font-medium">{{ $doc->getDocumentTypeLabel() }}</p>
                                <p class="text-xs text-zinc-400">{{ $doc->original_filename }}</p>
                            </div>
                        </div>
                        <flux:button size="sm" variant="ghost" icon="arrow-down-tray" href="#">Download</flux:button>
                    </div>
                    @endforeach
                </div>
                @endif

                @can('documents.upload')
                <div class="border-t border-zinc-200 pt-4">
                    <flux:heading size="sm" class="mb-3">Add Document</flux:heading>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <flux:select wire:model="newDocumentType" placeholder="Select Document Type">
                            <flux:select.option value="approval_letter">Approval Letter</flux:select.option>
                            <flux:select.option value="payment_slip">Payment Slip</flux:select.option>
                            <flux:select.option value="passport_copy">Passport Copy</flux:select.option>
                            <flux:select.option value="beneficiary_bank">Beneficiary Bank Details</flux:select.option>
                            <flux:select.option value="beneficiary_info">Beneficiary Information</flux:select.option>
                            <flux:select.option value="accident_fcl">Accident FCL Form</flux:select.option>
                            <flux:select.option value="police_report">Police Report</flux:select.option>
                        </flux:select>
                        <flux:input type="file" wire:model="newDocument" accept=".pdf,.jpg,.jpeg,.png" />
                    </div>
                    <flux:button wire:click="uploadDocument" size="sm" class="mt-3" icon="arrow-up-tray">
                        Upload
                    </flux:button>
                </div>
                @endcan
            </div>

            {{-- Notes --}}
            <div class="bg-white rounded-xl border border-zinc-200 p-6">
                <flux:heading size="lg" class="mb-4">Notes</flux:heading>

                <div class="space-y-3 mb-4">
                    @forelse ($claim->claimNotes->sortByDesc('created_at') as $note)
                    @if (!$note->is_internal || auth()->user()->hasAnyRole(['admin','pic']))
                    <div class="p-3 rounded-lg {{ $note->is_internal ? 'bg-yellow-50 border border-yellow-200' : 'bg-zinc-50' }}">
                        <div class="flex items-center justify-between mb-1">
                            <span class="text-sm font-medium">{{ $note->user->name }}</span>
                            <div class="flex items-center gap-2">
                                @if ($note->is_internal)
                                <flux:badge color="yellow" size="sm">Internal</flux:badge>
                                @endif
                                <span class="text-xs text-zinc-400">{{ $note->created_at->diffForHumans() }}</span>
                            </div>
                        </div>
                        <p class="text-sm text-zinc-700">{{ $note->note }}</p>
                    </div>
                    @endif
                    @empty
                    <p class="text-zinc-400 text-sm">No notes yet.</p>
                    @endforelse
                </div>

                <div class="border-t border-zinc-200 pt-4">
                    <flux:textarea wire:model="newNote" placeholder="Add a note..." rows="3" class="mb-3" />
                    @if (auth()->user()->hasAnyRole(['admin','pic']))
                    <div class="flex items-center gap-2 mb-3">
                        <flux:checkbox wire:model="noteIsInternal" id="internal" />
                        <label for="internal" class="text-sm text-zinc-600">Internal note only</label>
                    </div>
                    @endif
                    <flux:button wire:click="addNote" size="sm" icon="chat-bubble-left">Add Note</flux:button>
                </div>
            </div>
        </div>

        {{-- Sidebar --}}
        <div class="space-y-6">
            {{-- Status Timeline --}}
            <div class="bg-white rounded-xl border border-zinc-200 p-6">
                <flux:heading size="lg" class="mb-4">Timeline</flux:heading>
                <ol class="relative border-l border-zinc-200 space-y-4 ml-3">
                    <li class="ml-4">
                        <div class="absolute w-3 h-3 bg-zinc-200 rounded-full -left-1.5 border border-white"></div>
                        <p class="text-sm font-medium">Submitted</p>
                        <p class="text-xs text-zinc-400">{{ $claim->submitted_at?->format('d/m/Y') ?? $claim->created_at->format('d/m/Y') }}</p>
                    </li>
                    @if ($claim->approved_at)
                    <li class="ml-4">
                        <div class="absolute w-3 h-3 bg-green-400 rounded-full -left-1.5 border border-white"></div>
                        <p class="text-sm font-medium">Approved</p>
                        <p class="text-xs text-zinc-400">{{ $claim->approved_at->format('d/m/Y') }}</p>
                    </li>
                    @endif
                    @if ($claim->rejected_at)
                    <li class="ml-4">
                        <div class="absolute w-3 h-3 bg-red-400 rounded-full -left-1.5 border border-white"></div>
                        <p class="text-sm font-medium">Rejected</p>
                        <p class="text-xs text-zinc-400">{{ $claim->rejected_at->format('d/m/Y') }}</p>
                    </li>
                    @endif
                    @if ($claim->closed_at)
                    <li class="ml-4">
                        <div class="absolute w-3 h-3 bg-blue-400 rounded-full -left-1.5 border border-white"></div>
                        <p class="text-sm font-medium">Case Closed</p>
                        <p class="text-xs text-zinc-400">{{ $claim->closed_at->format('d/m/Y') }}</p>
                    </li>
                    @endif
                </ol>
            </div>

            {{-- Submitted By --}}
            <div class="bg-white rounded-xl border border-zinc-200 p-6">
                <flux:heading size="lg" class="mb-4">Submitted By</flux:heading>
                <p class="font-medium">{{ $claim->user->name }}</p>
                <p class="text-sm text-zinc-500">{{ $claim->user->email }}</p>
                @if ($claim->user->company_name)
                <p class="text-sm text-zinc-500">{{ $claim->user->company_name }}</p>
                @endif
            </div>
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
