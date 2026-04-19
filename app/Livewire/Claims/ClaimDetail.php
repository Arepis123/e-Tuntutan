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

    public function markDocumentReceived(int $documentId): void
    {
        $doc = $this->claim->documents()->findOrFail($documentId);

        $doc->update([
            'is_received' => true,
            'received_at' => now(),
            'received_by' => Auth::id(),
        ]);

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
