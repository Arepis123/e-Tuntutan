<?php

namespace App\Livewire\Claims;

use App\Models\Claim;
use App\Models\Worker;
use App\Notifications\ClaimSubmittedNotification;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

#[Layout('components.layouts.app')]
#[Title('Submit New Claim')]
class ClaimCreate extends Component
{
    use WithFileUploads;

    // Step tracking
    public int $step = 1;

    // Step 1: Claim type
    public string $claimType = '';
    public string $claimCategory = '';

    // Step 2: Worker info
    public string $workerType = 'existing';
    public string $passportNumber = '';
    public ?Worker $foundWorker = null;

    // New worker fields
    public string $workerName = '';
    public string $nationality = '';
    public string $dateOfBirth = '';
    public string $employerName = '';
    public string $employerIc = '';
    public string $phone = '';
    public string $address = '';

    // Step 3: Incident details
    public string $incidentDate = '';
    public string $incidentDescription = '';
    public string $hospitalName = '';
    public string $admissionDate = '';
    public string $dischargeDate = '';

    // Step 4: Documents
    public array $uploadedFiles = [];

    protected function rules(): array
    {
        $rules = [
            'claimType'     => 'required|in:fwhs,green_card,perkeso',
            'claimCategory' => 'required|in:hospitalization,death',
            'incidentDate'  => 'required|date',
            'incidentDescription' => 'required|min:10',
        ];

        if ($this->workerType === 'new') {
            $rules['workerName']     = 'required|string|max:255';
            $rules['passportNumber'] = 'required|string|max:50';
            $rules['nationality']    = 'required|string|max:100';
        } else {
            $rules['passportNumber'] = 'required|string|max:50';
        }

        if ($this->claimCategory === 'hospitalization') {
            $rules['hospitalName']  = 'required|string|max:255';
            $rules['admissionDate'] = 'required|date';
        }

        return $rules;
    }

    public function lookupWorker(): void
    {
        $this->validate(['passportNumber' => 'required|string']);
        $this->foundWorker = Worker::where('passport_number', $this->passportNumber)->first();

        if (! $this->foundWorker) {
            $this->addError('passportNumber', 'Worker not found. Please add as a new worker.');
            $this->workerType = 'new';
        }
    }

    public function nextStep(): void
    {
        match ($this->step) {
            1 => $this->validate([
                'claimType'     => 'required|in:fwhs,green_card,perkeso',
                'claimCategory' => 'required|in:hospitalization,death',
            ]),
            2 => $this->validateWorkerStep(),
            3 => $this->validate([
                'incidentDate'        => 'required|date',
                'incidentDescription' => 'required|min:10',
            ]),
            default => null,
        };

        $this->step++;
    }

    protected function validateWorkerStep(): void
    {
        if ($this->workerType === 'existing' && ! $this->foundWorker) {
            $this->addError('passportNumber', 'Please search and confirm the worker first.');
            return;
        }

        if ($this->workerType === 'new') {
            $this->validate([
                'workerName'     => 'required|string|max:255',
                'passportNumber' => 'required|string|max:50|unique:workers,passport_number',
                'nationality'    => 'required|string|max:100',
            ]);
        }
    }

    public function previousStep(): void
    {
        $this->step = max(1, $this->step - 1);
    }

    public function submit(): void
    {
        $this->validate();

        DB::transaction(function () {
            // Create or get worker
            if ($this->workerType === 'new') {
                $worker = Worker::create([
                    'name'            => $this->workerName,
                    'passport_number' => $this->passportNumber,
                    'nationality'     => $this->nationality,
                    'date_of_birth'   => $this->dateOfBirth ?: null,
                    'worker_type'     => 'outsource',
                    'employer_name'   => $this->employerName,
                    'employer_ic'     => $this->employerIc,
                    'phone'           => $this->phone,
                    'address'         => $this->address,
                ]);
            } else {
                $worker = $this->foundWorker;
            }

            $claim = Claim::create([
                'worker_id'           => $worker->id,
                'user_id'             => Auth::id(),
                'claim_type'          => $this->claimType,
                'claim_category'      => $this->claimCategory,
                'status'              => 'open',
                'incident_date'       => $this->incidentDate,
                'incident_description' => $this->incidentDescription,
                'hospital_name'       => $this->hospitalName ?: null,
                'admission_date'      => $this->admissionDate ?: null,
                'discharge_date'      => $this->dischargeDate ?: null,
                'submitted_at'        => now(),
            ]);

            // Store uploaded documents
            foreach ($this->uploadedFiles as $docType => $file) {
                if ($file) {
                    $path = $file->store("claims/{$claim->id}", 'local');
                    $claim->documents()->create([
                        'document_type'     => $docType,
                        'original_filename' => $file->getClientOriginalName(),
                        'stored_filename'   => basename($path),
                        'path'              => $path,
                        'file_size'         => $file->getSize(),
                        'mime_type'         => $file->getMimeType(),
                        'uploaded_by'       => Auth::id(),
                    ]);
                }
            }

            // Notify PICs
            $pics = \App\Models\User::role('pic')->get();
            Notification::send($pics, new ClaimSubmittedNotification($claim));
        });

        session()->flash('success', 'Claim submitted successfully!');
        $this->redirect(route('claims.index'), navigate: true);
    }

    public function render()
    {
        $requiredDocs = $this->getRequiredDocuments();

        return view('livewire.claims.claim-create', compact('requiredDocs'));
    }

    protected function getRequiredDocuments(): array
    {
        if (! $this->claimCategory) {
            return [];
        }

        $docs = match ($this->claimCategory) {
            'hospitalization' => [
                'accident_fcl'    => 'Accident FCL Form',
                'original_receipt' => 'Original Receipt',
                'discharge_note'  => 'Discharge Note',
                'doctor_letter'   => "Doctor's Letter",
            ],
            'death' => [
                'death_cert'   => 'Death Certificate',
                'body_receipt' => 'Body Delivery Receipt',
                'police_report' => 'Police Report',
                'embassy_letter' => 'Embassy Letter',
            ],
            default => [],
        };

        return $docs;
    }
}
