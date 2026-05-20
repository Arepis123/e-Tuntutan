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
    public array $noteFiles = [];
    public string $rejectionReason = '';
    public string $forwardTo = '';
    public bool $showRejectModal = false;
    public bool $showForwardModal = false;

    // Insurer decision
    public $insurerApprovalLetter = null;
    public string $insurerPaymentChannel = '';
    public string $insurerRejectionReason = '';
    public $insurerRejectionAttachment = null;

    // Missing docs email compose
    public string $emailSubject = '';
    public string $emailBody    = '';
    public string $emailCc      = '';

    public $newDocument;
    public string $newDocumentType = '';
    public array $clientUploadFiles = [];

    public function mount(Claim $claim): void
    {
        $this->claim = $claim;
    }

    public function addNote(): void
    {
        $this->validate([
            'newNote'    => 'required|min:3',
            'noteFiles'  => 'nullable|array|max:10',
            'noteFiles.*' => 'file|max:3072|mimes:pdf,doc,docx,jpg,jpeg,png',
        ]);

        $attachments = [];
        foreach ($this->noteFiles as $file) {
            $name = $file->getClientOriginalName();
            $mime = $file->getMimeType();
            $path = $file->store("claims/{$this->claim->id}/notes", 'local');
            $attachments[] = [
                'path' => $path,
                'name' => $name,
                'size' => \Illuminate\Support\Facades\Storage::disk('local')->size($path),
                'mime' => $mime,
            ];
        }

        $this->claim->claimNotes()->create([
            'user_id'     => Auth::id(),
            'note'        => $this->newNote,
            'is_internal' => $this->noteIsInternal,
            'attachments' => $attachments ?: null,
        ]);

        if (! $this->noteIsInternal) {
            $author = Auth::user();

            if ($author->hasAnyRole(['admin', 'pic'])) {
                // Notify the client (employer) via company PIC email
                if ($this->claim->user) {
                    $this->claim->user->notify(new \App\Notifications\ClaimNoteNotification($this->claim, $author));
                }
            } else {
                // Notify all PICs
                $pics = \App\Models\User::role('pic')->get();
                Notification::send($pics, new \App\Notifications\ClaimNoteNotification($this->claim, $author));
            }
        }

        $this->reset('newNote', 'noteFiles');
        $this->claim->refresh();
    }

    public function downloadNoteAttachment(int $noteId, int $index): mixed
    {
        $note = $this->claim->claimNotes()->findOrFail($noteId);
        $attachment = $note->attachments[$index] ?? null;

        abort_if(! $attachment, 404);

        return \Illuminate\Support\Facades\Storage::disk('local')
            ->download($attachment['path'], $attachment['name']);
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

    protected function authorizeInsurerStep(): void
    {
        $this->claim->loadMissing('worker');

        if ($this->claim->isEmployerManaged()) {
            abort_unless(Auth::id() === $this->claim->user_id, 403);
        } else {
            $this->authorize('claims.approve');
        }
    }

    public function openInsurerApprovedModal(): void
    {
        $this->insurerApprovalLetter  = null;
        $this->insurerPaymentChannel  = '';
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
        $this->authorizeInsurerStep();

        $this->validate([
            'insurerApprovalLetter' => 'required|file|mimes:pdf|max:10240',
            'insurerPaymentChannel' => 'required|in:clab,contractor',
        ]);

        $path = $this->insurerApprovalLetter->store('insurer-letters', 'public');

        $this->claim->update([
            'insurer_decision'        => 'approved',
            'insurer_decided_at'      => now(),
            'insurer_decided_by'      => Auth::id(),
            'insurer_approval_letter' => $path,
            'payment_channel'         => $this->insurerPaymentChannel,
        ]);

        if ($this->claim->isEmployerManaged()) {
            $this->notifyStatusChange();
        } elseif ($this->claim->user) {
            $this->claim->user->notify(new \App\Notifications\InsurerDecisionNotification($this->claim));
        }

        $this->claim->refresh();
        $this->modal('insurer-approved')->close();
        $this->dispatch('notify', message: $this->claim->isEmployerManaged()
            ? 'Decision recorded. Admin has been notified.'
            : 'Decision recorded and contractor notified.'
        );
    }

    public function confirmInsurerRejected(): void
    {
        $this->authorizeInsurerStep();

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

        if ($this->claim->isEmployerManaged()) {
            $this->notifyStatusChange();
        } elseif ($this->claim->user) {
            $this->claim->user->notify(new \App\Notifications\InsurerDecisionNotification($this->claim));
        }

        $this->claim->refresh();
        $this->modal('insurer-rejected')->close();
        $this->dispatch('notify', message: $this->claim->isEmployerManaged()
            ? 'Decision recorded. Admin has been notified.'
            : 'Decision recorded and contractor notified.'
        );
    }

    public function openInsurerModal(): void
    {
        $this->authorize('claims.approve');
        $this->modal('insurer-submission')->show();
    }

    public function openEmployerSubmissionModal(): void
    {
        $this->claim->loadMissing('worker');
        abort_unless(Auth::id() === $this->claim->user_id && $this->claim->isEmployerManaged(), 403);
        $this->modal('employer-insurer-submission')->show();
    }

    public function openEmployerAppealModal(): void
    {
        $this->claim->loadMissing('worker');
        abort_unless(Auth::id() === $this->claim->user_id && $this->claim->isEmployerManaged(), 403);
        $this->modal('employer-appeal')->show();
    }

    public function confirmEmployerAppeal(): void
    {
        $this->claim->loadMissing('worker');
        abort_unless(Auth::id() === $this->claim->user_id && $this->claim->isEmployerManaged(), 403);
        abort_unless($this->claim->insurer_decision === 'rejected' && $this->claim->appeal_count === 0, 403);

        $this->claim->update([
            'appealed_at'                   => now(),
            'appeal_count'                  => $this->claim->appeal_count + 1,
            'status'                        => 'open',
            'pre_appeal_insurer_decision'   => $this->claim->insurer_decision,
            'pre_appeal_insurer_decided_at' => $this->claim->insurer_decided_at,
        ]);

        $this->notifyStatusChange();
        $this->claim->refresh();
        $this->modal('employer-appeal')->close();
        $this->dispatch('notify', message: 'Appeal recorded. Please submit to the insurer when ready.');
    }

    public function markAppealLetterReceived(): void
    {
        abort_unless(Auth::user()->hasAnyRole(['admin', 'pic']), 403);
        abort_unless($this->claim->appealed_at && ! $this->claim->appeal_letter_received_at, 403);

        $this->claim->update([
            'appeal_letter_received_at' => now(),
            'appeal_letter_received_by' => Auth::id(),
        ]);

        $this->claim->refresh();
        $this->dispatch('notify', message: 'Appeal letter marked as received.');
    }

    public function confirmSubmittedToInsurer(bool $notifyContractor = false): void
    {
        $this->authorizeInsurerStep();

        $this->claim->update([
            'submitted_to_insurer_at' => now(),
            'submitted_to_insurer_by' => Auth::id(),
        ]);

        if ($notifyContractor && ! $this->claim->isEmployerManaged() && $this->claim->user) {
            $this->claim->user->notify(
                new \App\Notifications\ClaimSubmittedToInsurerNotification($this->claim)
            );
        }

        $this->claim->refresh();
        $this->modal('insurer-submission')->close();
        $this->modal('employer-insurer-submission')->close();
        $this->dispatch('notify', message: $notifyContractor
            ? 'Recorded and notification sent to contractor.'
            : 'Recorded.'
        );
    }

    public function changeStatus(string $status): void
    {
        abort_unless(Auth::user()->hasAnyRole(['admin', 'pic']), 403);

        $this->claim->update(['status' => $status]);
        $this->claim->refresh();
    }

    public function markAllReceived(): void
    {
        $this->authorize('claims.approve');

        $now = now();

        $this->claim->documents()
            ->where('is_received', false)
            ->update([
                'is_received' => true,
                'received_at' => $now,
                'received_by' => Auth::id(),
            ]);

        $isNewRound = $this->claim->appealed_at && $this->claim->documents_received_at
            && $this->claim->appealed_at > $this->claim->documents_received_at;

        if (! $this->claim->documents_received_at || $isNewRound) {
            $this->claim->update([
                'documents_received_at' => $now,
                'documents_received_by' => Auth::id(),
            ]);
        }

        $this->claim->refresh();
        $this->dispatch('notify', message: 'All documents marked as received.');
    }

    public function notifyMissingDocuments(): void
    {
        $this->authorize('claims.approve');

        $missing = $this->claim->documents()->where('is_received', false)->get();

        if ($missing->isEmpty()) {
            $this->dispatch('notify', message: 'All documents are already received.');
            return;
        }

        $docList = $missing->map(fn($d) => '& ' . $d->getDocumentTypeLabel())->join("\n");
        $claim   = $this->claim;

        $this->emailSubject = "[e-Tuntutan] Action Required — Missing Documents: {$claim->claim_number}";
        $this->emailCc      = '';
        $this->emailBody    = implode("\n\n", [
            "Dear {$claim->user?->name},",
            "We have reviewed your claim and found that the following required documents have not yet been received:",
            "Claim No.: {$claim->claim_number}\nWorker: {$claim->worker->name} ({$claim->worker->passport_number})",
            "Missing Documents:\n{$docList}",
            "Please submit the above documents at your earliest convenience to avoid delays in processing your claim.",
        ]);

        $this->modal('notify-missing-docs')->show();
    }

    public function sendMissingDocumentsEmail(): void
    {
        $this->authorize('claims.approve');

        $this->validate([
            'emailSubject' => 'required|string|max:255',
            'emailBody'    => 'required|string',
            'emailCc'      => 'nullable|string',
        ]);

        $ccEmails = array_values(array_filter(array_map('trim', explode(',', $this->emailCc))));

        foreach ($ccEmails as $email) {
            if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $this->addError('emailCc', "Invalid email address: {$email}");
                return;
            }
        }

        if ($this->claim->company_pic_email) {
            \Illuminate\Support\Facades\Notification::route('mail', $this->claim->company_pic_email)
                ->notify(new \App\Notifications\MissingDocumentsNotification(
                    $this->claim,
                    $this->emailSubject,
                    $this->emailBody,
                    $ccEmails,
                ));
        }

        $this->modal('notify-missing-docs')->close();
        $this->dispatch('notify', message: 'Contractor notified of missing documents.');
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

        $isNewRound = $this->claim->appealed_at && $this->claim->documents_received_at
            && $this->claim->appealed_at > $this->claim->documents_received_at;

        if ($allReceived && (! $this->claim->documents_received_at || $isNewRound)) {
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

    protected function getDocumentConfigs(): \Illuminate\Support\Collection
    {
        if (! $this->claim->claim_type || ! $this->claim->claim_category) {
            return collect();
        }

        $incidentType = $this->claim->claim_category === 'death' ? null : ($this->claim->incident_type ?: null);

        return \App\Models\DocumentConfig::where('claim_type', $this->claim->claim_type)
            ->where('claim_category', $this->claim->claim_category)
            ->where('incident_type', $incidentType)
            ->orderBy('sort_order')
            ->get();
    }

    public function updatedClientUploadFiles($value, string $key): void
    {
        if ($value) {
            $this->uploadClientDoc($key);
        }
    }

    public function uploadClientDoc(string $docType): void
    {
        abort_unless(Auth::id() === $this->claim->user_id, 403);

        $file = $this->clientUploadFiles[$docType] ?? null;

        if (! $file) {
            $this->addError("clientUploadFiles.{$docType}", 'Please select a file to upload.');
            return;
        }

        $this->validate([
            "clientUploadFiles.{$docType}" => 'required|file|max:10240|mimes:pdf,jpg,jpeg,png',
        ]);

        // Capture metadata before store() moves the temp file (after move getSize() throws)
        $originalName = $file->getClientOriginalName();
        $mimeType     = $file->getMimeType();
        $fileSize     = $file->getSize();

        $path = $file->store("claims/{$this->claim->id}/documents", 'local');

        if (! $path) {
            $this->addError("clientUploadFiles.{$docType}", 'File could not be saved. Please try again.');
            return;
        }

        $existing = $this->claim->documents()->where('document_type', $docType)->first();

        if ($existing) {
            $existing->update([
                'original_filename' => $originalName,
                'stored_filename'   => basename($path),
                'disk'              => 'local',
                'path'              => $path,
                'file_size'         => $fileSize,
                'mime_type'         => $mimeType,
                'uploaded_by'       => Auth::id(),
                'is_received'       => false,
                'received_at'       => null,
                'received_by'       => null,
            ]);
        } else {
            $this->claim->documents()->create([
                'document_type'     => $docType,
                'original_filename' => $originalName,
                'stored_filename'   => basename($path),
                'disk'              => 'local',
                'path'              => $path,
                'file_size'         => $fileSize,
                'mime_type'         => $mimeType,
                'uploaded_by'       => Auth::id(),
            ]);
        }

        if ($this->claim->status === 'open') {
            $this->claim->update(['status' => 'in_progress']);
        }

        $this->reset('clientUploadFiles');
        $this->claim->load('documents');
        $this->dispatch('notify', message: 'Document uploaded successfully.');
    }

    protected function notifyStatusChange(): void
    {
        $pics = \App\Models\User::role('pic')->get();
        Notification::send($pics, new ClaimStatusChangedNotification($this->claim));
    }

    public function render()
    {
        $this->claim->load(['worker', 'user', 'documents', 'claimNotes.user', 'payment']);
        $docConfigs = $this->getDocumentConfigs();

        return view('livewire.claims.claim-detail', compact('docConfigs'));
    }
}
