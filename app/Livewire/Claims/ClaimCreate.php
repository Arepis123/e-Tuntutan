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
    public string $direction = 'forward';

    // Step 1: Claim type
    public string $claimType = '';
    public string $claimCategory = '';

    public function updatedClaimType(): void
    {
        if ($this->claimType === 'fwhs' && $this->claimCategory === 'death') {
            $this->claimCategory = '';
        }
    }

    // Step 2: Worker info
    public string $passportNumber = '';
    public ?array $foundWorker = null;
    public bool $workerNotFound = false;

    // Step 3: Incident details
    public string $incidentType = '';
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
            'claimType'           => 'required|in:fwhs,green_card,perkeso',
            'claimCategory'       => 'required|in:hospitalization,death',
            'passportNumber'      => 'required|string|max:50',
            'incidentDate'        => 'required|date',
            'incidentDescription' => 'required|min:10',
        ];

        if ($this->claimCategory === 'hospitalization') {
            $rules['hospitalName']  = 'required|string|max:255';
            $rules['admissionDate'] = 'required|date';
        }

        return $rules;
    }

    public function lookupWorker(): void
    {
        $this->validate(['passportNumber' => 'required|string']);

        $this->foundWorker = null;
        $this->workerNotFound = false;

        $worker = \DB::connection('worker_db')
            ->table('workers')
            ->leftJoin('mst_countries', 'workers.wkr_country', '=', 'mst_countries.cty_id')
            ->leftJoin('contractors', 'workers.wkr_currentemp', '=', 'contractors.ctr_clab_no')
            ->leftJoin('mst_states', 'contractors.ctr_state', '=', 'mst_states.state_id')
            ->select(
                'workers.*',
                'mst_countries.cty_desc as country_name',
                'contractors.ctr_comp_name as contractor_name',
                'contractors.ctr_addr1 as contractor_addr1',
                'contractors.ctr_addr2 as contractor_addr2',
                'contractors.ctr_addr3 as contractor_addr3',
                'contractors.ctr_pcode as contractor_pcode',
                'mst_states.state_name as contractor_state'
            )
            ->where('wkr_passno', $this->passportNumber)
            ->first();

        if (! $worker) {
            $this->workerNotFound = true;
            return;
        }

        $isOutsource = \DB::connection('worker_db')
            ->table('contract_worker')
            ->where('con_wkr_id', $worker->wkr_id)
            ->exists();

        $this->foundWorker = [
            'id'                  => $worker->wkr_id,
            'name'                => $worker->wkr_name ?? '',
            'passport_number'     => $worker->wkr_passno,
            'passport_expiry'     => ($worker->wkr_passexp && !in_array($worker->wkr_passexp, ['0000-00-00', '1970-01-01'])) ? $worker->wkr_passexp : 'NO DATA FOUND',
            'nationality'         => $worker->country_name ?? '',
            'date_of_birth'       => $worker->wkr_dob ?? null,
            'gender'              => match((string)($worker->wkr_gender ?? '')) {
                '1' => 'MALE',
                '2' => 'FEMALE',
                default => null,
            },
            'permit_expiry'       => ($worker->wkr_permitexp && !in_array($worker->wkr_permitexp, ['0000-00-00', '1970-01-01'])) ? $worker->wkr_permitexp : 'NO DATA FOUND',
            'phone'               => $worker->wkr_tel ?? null,
            'address'             => $worker->wkr_address1 ?? null,
            'contractor_name'    => $worker->contractor_name ?? null,
            'contractor_address' => implode(', ', array_filter(array_map(
                fn($part) => trim($part ?? '', " \t\n\r,"),
                [
                    $worker->contractor_addr1 ?? null,
                    $worker->contractor_addr2 ?? null,
                    $worker->contractor_addr3 ?? null,
                    $worker->contractor_pcode ?? null,
                    $worker->contractor_state ?? null,
                ]
            ))),
            'worker_type'         => $isOutsource ? 'outsource' : 'normal',
        ];
    }

    public function nextStep(): void
    {
        if ($this->step === 2) {
            if (! $this->validateWorkerStep()) return;
        } else {
            match ($this->step) {
                1 => $this->validate([
                    'claimType'     => 'required|in:fwhs,green_card,perkeso',
                    'claimCategory' => [
                        'required',
                        $this->claimType === 'fwhs' ? 'in:hospitalization' : 'in:hospitalization,death',
                    ],
                ]),
                3 => $this->validate([
                    'incidentType'        => 'required|in:accident,non_accident',
                    'incidentDate'        => 'required|date',
                    'incidentDescription' => 'required|min:10',
                ]),
                default => null,
            };
        }

        $this->direction = 'forward';
        $this->step++;
    }

    protected function validateWorkerStep(): bool
    {
        $this->validate(['passportNumber' => 'required|string']);

        if (! $this->foundWorker) {
            $this->addError('passportNumber', 'Please search and confirm the worker before proceeding.');
            return false;
        }

        return true;
    }

    public function previousStep(): void
    {
        $this->direction = 'backward';
        $this->step = max(1, $this->step - 1);
    }

    public function submit(): void
    {
        $this->validate([
            'claimType'           => 'required|in:fwhs,green_card,perkeso',
            'claimCategory'       => 'required|in:hospitalization,death',
            'incidentDate'        => 'required|date',
            'incidentDescription' => 'required|min:10',
        ]);

        DB::transaction(function () {
            // Upsert worker into local DB from found worker data
            $worker = Worker::updateOrCreate(
                ['passport_number' => $this->foundWorker['passport_number']],
                [
                    'name'          => $this->foundWorker['name'],
                    'nationality'   => $this->foundWorker['nationality'],
                    'date_of_birth' => $this->foundWorker['date_of_birth'],
                    'worker_type'   => $this->foundWorker['worker_type'],
                    'phone'         => $this->foundWorker['phone'],
                    'address'       => $this->foundWorker['address'],
                    'employer_name'    => $this->foundWorker['contractor_name'],
                    'employer_address' => $this->foundWorker['contractor_address'],
                ]
            );

            $claim = Claim::create([
                'worker_id'           => $worker->id,
                'user_id'             => Auth::id(),
                'claim_type'          => $this->claimType,
                'claim_category'      => $this->claimCategory,
                'incident_type'       => $this->incidentType,
                'status'              => 'open',
                'incident_date'       => $this->incidentDate,
                'incident_description' => $this->incidentDescription,
                'hospital_name'       => $this->hospitalName ?? null,
                'admission_date'      => $this->admissionDate ?? null,
                'discharge_date'      => $this->dischargeDate ?? null,
                'submitted_at'        => now(),
            ]);

            // Pre-create document records for tracking physical receipt
            foreach (array_keys($this->getRequiredDocuments()) as $docType) {
                $claim->documents()->create([
                    'document_type' => $docType,
                    'is_received'   => false,
                ]);
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

        if ($this->claimCategory === 'hospitalization') {
            if ($this->incidentType === 'accident') {
                return [
                    'accident_fcl'    => 'Accident FCL Form',
                    'original_receipt' => 'Original Receipt',
                    'discharge_note'  => 'Discharge Note',
                    'doctor_letter'   => "Doctor's Letter",
                ];
            }
            if ($this->incidentType === 'non_accident') {
                return [
                    'non_accident_fcl' => 'Non-Accident FCL Form',
                    'original_receipt' => 'Original Receipt',
                    'discharge_note'   => 'Discharge Note',
                    'doctor_letter'    => "Doctor's Letter",
                ];
            }
            return [];
        }

        return match ($this->claimCategory) {
            'death' => [
                'death_cert'    => 'Death Certificate',
                'body_receipt'  => 'Body Delivery Receipt',
                'police_report' => 'Police Report',
                'embassy_letter' => 'Embassy Letter',
            ],
            default => [],
        };
    }
}
