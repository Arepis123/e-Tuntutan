<?php

namespace App\Livewire\Claims;

use App\Models\Claim;
use App\Models\ClaimNote;
use App\Notifications\ClaimStatusChangedNotification;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;

#[Layout('components.layouts.app')]
#[Title('Butiran Tuntutan')]
class ClaimDetail extends Component
{
    use WithFileUploads;

    public Claim $claim;

    public string $newNote = '';
    public bool $noteIsInternal = true;
    public string $rejectionReason = '';
    public string $forwardTo = '';
    public bool $showRejectModal = false;
    public bool $showForwardModal = false;

    // Insurer decision
    public $insurerApprovalLetter = null;
    public string $insurerRejectionReason = '';
    public $insurerRejectionAttachment = null;

    public $newDocument;
    public string $newDocumentType = '';

    public function mount(Claim $claim): void
    {
        $this->claim = $claim;
    }

    public function addNote(): void
    {
        $this->validate([
            'newNote' => 'required|min:3',
        ]);

        $this->claim->claimNotes()->create([
            'user_id'     => Auth::id(),
            'note'        => $this->newNote,
            'is_internal' => $this->noteIsInternal,
        ]);

        $this->reset('newNote');
        $this->claim->refresh();
    }

    public function approve(): void
    {
        $this->authorize('claims.approve');

        $this->claim->update([
            'status'      => 'in_progress',
            'approved_at' => now(),
        ]);

        $this->notifyStatusChange();
        $this->claim->refresh();
    }

    public function confirmReject(): void
    {
        $this->validate(['rejectionReason' => 'required|min:5']);

        $this->claim->update([
            'status'           => 'open',
            'rejected_at'      => now(),
            'rejection_reason' => $this->rejectionReason,
        ]);

        $this->showRejectModal = false;
        $this->reset('rejectionReason');
        $this->notifyStatusChange();
        $this->claim->refresh();
    }

    public function confirmForward(): void
    {
        $this->validate(['forwardTo' => 'required|in:perkeso,green_card']);

        $this->claim->update([
            'forwarded_to' => $this->forwardTo,
            'status'       => 'in_progress',
        ]);

        $this->showForwardModal = false;
        $this->reset('forwardTo');
        $this->notifyStatusChange();
        $this->claim->refresh();
    }

    public function closeClaim(): void
    {
        $this->authorize('claims.close');

        $this->claim->update([
            'status'    => 'closed',
            'closed_at' => now(),
        ]);

        $this->notifyStatusChange();
        $this->claim->refresh();
    }

    public function openInsurerApprovedModal(): void
    {
        $this->insurerApprovalLetter = null;
        $this->modal('insurer-approved')->show();
    }

    public function openInsurerRejectedModal(): void
    {
        $this->insurerRejectionReason = '';
        $this->insurerRejectionAttachment = null;
        $this->modal('insurer-rejected')->show();
    }

    public function confirmInsurerApproved(): void
    {
        $this->validate([
            'insurerApprovalLetter' => 'required|file|mimes:pdf|max:10240',
        ]);

        $path = $this->insurerApprovalLetter->store('insurer-letters', 'public');

        $this->claim->update([
            'insurer_decision'        => 'approved',
            'insurer_decided_at'      => now(),
            'insurer_decided_by'      => Auth::id(),
            'insurer_approval_letter' => $path,
        ]);

        if ($this->claim->user) {
            $this->claim->user->notify(new \App\Notifications\InsurerDecisionNotification($this->claim));
        }

        $this->claim->refresh();
        $this->modal('insurer-approved')->close();
        $this->dispatch('notify', message: 'Decision recorded and contractor notified.');
    }

    public function confirmInsurerRejected(): void
    {
        $this->validate([
            'insurerRejectionReason'    => 'required|string|min:5',
            'insurerRejectionAttachment' => 'nullable|file|mimes:pdf|max:10240',
        ]);

        $updates = [
            'insurer_decision'         => 'rejected',
            'insurer_decided_at'       => now(),
            'insurer_decided_by'       => Auth::id(),
            'insurer_rejection_reason' => $this->insurerRejectionReason,
        ];

        if ($this->insurerRejectionAttachment) {
            $updates['insurer_rejection_attachment'] = $this->insurerRejectionAttachment->store('insurer-letters', 'public');
        }

        $this->claim->update($updates);

        if ($this->claim->user) {
            $this->claim->user->notify(new \App\Notifications\InsurerDecisionNotification($this->claim));
        }

        $this->claim->refresh();
        $this->modal('insurer-rejected')->close();
        $this->dispatch('notify', message: 'Decision recorded and contractor notified.');
    }

    public function openInsurerModal(): void
    {
        $this->modal('insurer-submission')->show();
    }

    public function confirmSubmittedToInsurer(bool $notifyContractor): void
    {
        $this->claim->update([
            'submitted_to_insurer_at' => now(),
            'submitted_to_insurer_by' => Auth::id(),
        ]);

        if ($notifyContractor && $this->claim->user) {
            $this->claim->user->notify(
                new \App\Notifications\ClaimSubmittedToInsurerNotification($this->claim)
            );
        }

        $this->claim->refresh();
        $this->modal('insurer-submission')->close();
        $this->dispatch('notify', message: $notifyContractor
            ? 'Recorded and notification sent to contractor.'
            : 'Recorded. No notification sent.'
        );
    }

    public function changeStatus(string $status): void
    {
        abort_unless(Auth::user()->hasAnyRole(['admin', 'pic']), 403);

        $this->claim->update(['status' => $status]);
        $this->claim->refresh();
    }

    public function markDocumentReceived(int $documentId): void
    {
        $doc = $this->claim->documents()->findOrFail($documentId);

        $doc->update([
            'is_received' => true,
            'received_at' => now(),
            'received_by' => Auth::id(),
        ]);

        if ($this->claim->status === 'open') {
            $this->claim->update(['status' => 'in_progress']);
        }

        $allReceived = $this->claim->documents()->where('is_received', false)->doesntExist();

        if ($allReceived && ! $this->claim->documents_received_at) {
            $this->claim->update([
                'documents_received_at' => now(),
                'documents_received_by' => Auth::id(),
            ]);
        }

        $this->claim->refresh();
    }

    public function uploadDocument(): void
    {
        $this->validate([
            'newDocument'     => 'required|file|max:10240',
            'newDocumentType' => 'required|string',
        ]);

        $path = $this->newDocument->store("claims/{$this->claim->id}", 'local');

        $this->claim->documents()->create([
            'document_type'     => $this->newDocumentType,
            'original_filename' => $this->newDocument->getClientOriginalName(),
            'stored_filename'   => basename($path),
            'path'              => $path,
            'file_size'         => $this->newDocument->getSize(),
            'mime_type'         => $this->newDocument->getMimeType(),
            'uploaded_by'       => Auth::id(),
        ]);

        $this->reset('newDocument', 'newDocumentType');
        $this->claim->refresh();
    }

    protected function notifyStatusChange(): void
    {
        $pics = \App\Models\User::role('pic')->get();
        Notification::send($pics, new ClaimStatusChangedNotification($this->claim));
    }

    public function render()
    {
        $this->claim->load(['worker', 'user', 'documents', 'claimNotes.user', 'payment']);

        return view('livewire.claims.claim-detail');
    }
}
