<?php

namespace App\Livewire\Claims;

use App\Models\Claim;
use App\Models\PerkesoScheme;
use App\Models\Worker;
use App\Notifications\ClaimReceivedByEmployerNotification;
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
    public array  $perkesoSchemes = [];

    public function updatedClaimType(): void
    {
        if ($this->claimType === 'fwhs' && $this->claimCategory === 'death') {
            $this->claimCategory = '';
        }
        if ($this->claimType !== 'perkeso') {
            $this->perkesoSchemes = [];
        }
    }

    public function updatedWorkingHourFrom(?string $value): void
    {
        if (! $value) return;

        try {
            $this->workingHourTo = \Carbon\Carbon::createFromFormat('H:i', $value)
                ->addHours(9)
                ->format('H:i');
        } catch (\Exception) {
            // invalid time format — leave workingHourTo unchanged
        }
    }

    // Step 2: Worker info
    public string $passportNumber = '';
    public ?array $foundWorker = null;
    public bool $workerNotFound = false;

    // Step 2: Section I fields e, g, h (not in worker DB)
    public string $dateOfEmployment = '';
    public string $workingHourFrom = '';
    public string $workingHourTo = '';
    public string $facilityMeals = '';
    public string $facilityAccommodation = '';
    public string $facilityTransportation = '';
    public string $tinNo = '';
    public string $sstNo = '';
    public string $companyPicName = '';
    public string $companyPicPhone = '';
    public string $companyPicEmail = '';

    // Step 3: Incident details — common
    public string $incidentType = '';
    public string $incidentDate = '';
    public string $incidentDescription = '';
    public string $hospitalName = '';
    public string $admissionDate = '';
    public string $dischargeDate = '';
    public string $insurancePolicyNo = '';

    // Step 3: Accident-specific (FCL Accident Form Section II)
    public string $incidentTime = '';
    public string $incidentLocation = '';
    public array  $injuryTypes = [];
    public string $injuryTypeOther = '';
    public string $injuryDescription = '';

    // Step 3: Non-accident-specific (FCL Non-Accident Form Section II)
    public string $diseaseType = '';
    public string $isHistoricalDisease = '';
    public string $isWorkRelated = '';
    public string $workRelatedDescription = '';

    // Step 4: Documents
    public array $uploadedFiles = [];
    public array $downloadedDocIds = [];
    public bool $fclDownloaded = false;

    // Success modal
    public bool $showSuccessModal = false;

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

    protected function step3Rules(): array
    {
        $rules = [
            'incidentType'        => 'required|in:accident,non_accident',
            'incidentDate'        => 'required|date',
            'incidentDescription' => 'required|min:10',
            'hospitalName'        => 'required|string|max:255',
        ];

        if ($this->incidentType === 'accident') {
            $rules['incidentTime']      = 'required';
            $rules['incidentLocation']  = 'required|string';
            $rules['injuryTypes']       = 'required|array|min:1';
            $rules['injuryDescription'] = 'required|min:5';

            if (in_array('others', $this->injuryTypes)) {
                $rules['injuryTypeOther'] = 'required|string|max:255';
            }
        }

        if ($this->incidentType === 'non_accident') {
            $rules['diseaseType']         = 'required|string';
            $rules['isHistoricalDisease'] = 'required|in:0,1';
            $rules['isWorkRelated']       = 'required|in:0,1';

            if ($this->isWorkRelated === '1') {
                $rules['workRelatedDescription'] = 'required|string';
            }
        }

        if ($this->claimCategory === 'hospitalization') {
            $rules['admissionDate'] = 'required|date';
            $rules['dischargeDate'] = 'required|date';
        }

        return $rules;
    }

    public function lookupWorker(): void
    {
        $this->validate(['passportNumber' => 'required|string']);

        $this->foundWorker = null;
        $this->workerNotFound = false;

        $user = Auth::user();

        $query = \DB::connection('worker_db')
            ->table('workers')
            ->leftJoin('mst_countries', 'workers.wkr_country', '=', 'mst_countries.cty_id')
            ->leftJoin('contractors', 'workers.wkr_currentemp', '=', 'contractors.ctr_clab_no')
            ->leftJoin('mst_states', 'contractors.ctr_state', '=', 'mst_states.state_id')
            ->leftJoin('mst_wkr_availability', 'workers.wkr_transtatus', '=', 'mst_wkr_availability.avlab_id')
            ->select(
                'workers.*',
                'mst_countries.cty_desc as country_name',
                'contractors.ctr_comp_name as contractor_name',
                'contractors.ctr_addr1 as contractor_addr1',
                'contractors.ctr_addr2 as contractor_addr2',
                'contractors.ctr_addr3 as contractor_addr3',
                'contractors.ctr_pcode as contractor_pcode',
                'mst_states.state_name as contractor_state',
                'mst_wkr_availability.avlab_desc as worker_status'
            )
            ->where('wkr_passno', $this->passportNumber);

        // Employers can only look up their own workers
        if ($user->hasRole('employer') && $user->username) {
            $query->where('workers.wkr_currentemp', $user->username);
        }

        $worker = $query->first();

        if (! $worker) {
            $this->workerNotFound = true;
            return;
        }

        if ((int) $worker->wkr_status !== 1) {
            $this->addError('passportNumber', 'This worker is inactive and is not eligible to make a claim.');
            return;
        }

        $permitExp = $worker->wkr_permitexp ?? null;
        $invalidDates = ['0000-00-00', '1970-01-01', null, ''];
        if (in_array($permitExp, $invalidDates) || $permitExp <= now()->toDateString()) {
            $this->addError('passportNumber', 'This worker\'s work permit has expired or is invalid. Claims can only be submitted for workers with a valid permit.');
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
            'contractor_address' => (function () use ($worker) {
                $trim = fn($v) => trim($v ?? '', " \t\n\r,");
                $lines = array_filter([$trim($worker->contractor_addr1), $trim($worker->contractor_addr2)]);
                $addr3    = $trim($worker->contractor_addr3);
                $pcode    = $trim($worker->contractor_pcode);
                $addr3Pcode = implode(' ', array_filter([$addr3, $pcode]));
                if ($addr3Pcode) $lines[] = $addr3Pcode;
                $state = $trim($worker->contractor_state);
                if ($state) $lines[] = $state;
                return implode(', ', $lines);
            })(),
            'worker_type'         => $isOutsource ? 'outsource' : 'existing',
            'worker_status'       => $worker->worker_status ?? null,
        ];
    }

    public function nextStep(): void
    {
        if ($this->step === 2) {
            if (! $this->validateWorkerStep()) return;
        } else {
            match ($this->step) {
                1 => $this->validate(array_merge([
                    'claimType'     => 'required|in:fwhs,green_card,perkeso',
                    'claimCategory' => [
                        'required',
                        $this->claimType === 'fwhs' ? 'in:hospitalization' : 'in:hospitalization,death',
                    ],
                ], ($this->claimType === 'perkeso' && PerkesoScheme::active()->exists()) ? [
                    'perkesoSchemes' => 'required|array|min:1',
                ] : [])),
                3 => $this->validate($this->step3Rules()),
                default => null,
            };
        }

        $this->direction = 'forward';
        $this->step++;
    }

    protected function validateWorkerStep(): bool
    {
        if (! $this->foundWorker) {
            $this->addError('passportNumber', 'Please search and confirm the worker before proceeding.');
            return false;
        }

        $this->validate([
            'dateOfEmployment' => 'required|date',
            'workingHourFrom'  => 'required',
            'workingHourTo'    => 'required',
            'tinNo'            => 'required|string|max:50',
            'sstNo'            => 'required|string|max:50',
            'companyPicName'   => 'required|string|max:255',
            'companyPicPhone'  => 'required|string|max:20',
            'companyPicEmail'  => 'required|email|max:255',
        ]);

        return true;
    }

    public function previousStep(): void
    {
        $this->direction = 'backward';
        $this->step = max(1, $this->step - 1);
    }

    protected function validateDownloads(): bool
    {
        $downloadableDocs = $this->getDocumentConfigs()->where('is_downloadable', true);

        foreach ($downloadableDocs as $doc) {
            $isFcl = in_array($doc->document_type, ['accident_fcl', 'non_accident_fcl']);

            if ($isFcl && ! $this->fclDownloaded) {
                $this->addError('downloads', 'Please download all required forms before submitting.');
                return false;
            }

            if (! $isFcl && $doc->file_path && ! in_array($doc->id, $this->downloadedDocIds)) {
                $this->addError('downloads', 'Please download all required forms before submitting.');
                return false;
            }
        }

        return true;
    }

    public function submit(): void
    {
        if (! $this->validateDownloads()) return;

        $this->validate(array_merge([
            'claimType'           => 'required|in:fwhs,green_card,perkeso',
            'claimCategory'       => 'required|in:hospitalization,death',
            'incidentDate'        => 'required|date',
            'incidentDescription' => 'required|min:10',
            'hospitalName'        => 'required|string|max:255',
            'dateOfEmployment'    => 'required|date',
            'workingHourFrom'     => 'required',
            'workingHourTo'       => 'required',
            'tinNo'               => 'required|string|max:50',
            'sstNo'               => 'required|string|max:50',
            'companyPicName'      => 'required|string|max:255',
            'companyPicPhone'     => 'required|string|max:20',
            'companyPicEmail'     => 'required|email|max:255',
        ], $this->step3Rules()));

        $claim = DB::transaction(function () {
            // Upsert worker into local DB from found worker data
            $worker = Worker::updateOrCreate(
                ['passport_number' => $this->foundWorker['passport_number']],
                [
                    'name'            => $this->foundWorker['name'],
                    'nationality'     => $this->foundWorker['nationality'],
                    'date_of_birth'   => $this->foundWorker['date_of_birth'],
                    'gender'          => $this->foundWorker['gender'],
                    'passport_expiry' => $this->foundWorker['passport_expiry'] !== 'NO DATA FOUND' ? $this->foundWorker['passport_expiry'] : null,
                    'permit_expiry'   => $this->foundWorker['permit_expiry'] !== 'NO DATA FOUND' ? $this->foundWorker['permit_expiry'] : null,
                    'worker_type'     => $this->foundWorker['worker_type'],
                    'worker_status'   => $this->foundWorker['worker_status'],
                    'phone'           => $this->foundWorker['phone'],
                    'address'         => $this->foundWorker['address'],
                    'employer_name'    => $this->foundWorker['contractor_name'],
                    'employer_address' => $this->foundWorker['contractor_address'],
                ]
            );

            $claim = Claim::create([
                'worker_id'              => $worker->id,
                'user_id'                => Auth::id(),
                'claim_type'             => $this->claimType,
                'claim_category'         => $this->claimCategory,
                'perkeso_schemes'        => $this->claimType === 'perkeso' ? $this->perkesoSchemes : null,
                'incident_type'          => $this->incidentType,
                'status'                 => 'open',
                'incident_date'          => $this->incidentDate,
                'incident_time'          => $this->incidentTime ?: null,
                'incident_location'      => $this->incidentLocation ?: null,
                'incident_description'   => $this->incidentDescription,
                'injury_types'           => $this->injuryTypes ? implode(',', $this->injuryTypes) : null,
                'injury_type_other'      => $this->injuryTypeOther ?: null,
                'injury_description'     => $this->injuryDescription ?: null,
                'disease_type'           => $this->diseaseType ?: null,
                'is_historical_disease'  => $this->isHistoricalDisease !== '' ? (bool) $this->isHistoricalDisease : null,
                'is_work_related'        => $this->isWorkRelated !== '' ? (bool) $this->isWorkRelated : null,
                'work_related_description' => $this->workRelatedDescription ?: null,
                'hospital_name'          => $this->hospitalName ?: null,
                'admission_date'         => $this->admissionDate ?: null,
                'discharge_date'         => $this->dischargeDate ?: null,
                'insurance_policy_no'    => $this->insurancePolicyNo ?: null,
                'tin_no'                 => $this->tinNo ?: null,
                'sst_no'                 => $this->sstNo ?: null,
                'company_pic_name'       => $this->companyPicName ?: null,
                'company_pic_phone'      => $this->companyPicPhone ?: null,
                'company_pic_email'      => $this->companyPicEmail ?: null,
                'date_of_employment'     => $this->dateOfEmployment ?: null,
                'working_hour_from'      => $this->workingHourFrom ?: null,
                'working_hour_to'        => $this->workingHourTo ?: null,
                'facility_meals'         => $this->facilityMeals !== '' ? (bool) $this->facilityMeals : null,
                'facility_accommodation' => $this->facilityAccommodation !== '' ? (bool) $this->facilityAccommodation : null,
                'facility_transportation' => $this->facilityTransportation !== '' ? (bool) $this->facilityTransportation : null,
                'submitted_at'           => now(),
            ]);

            // Pre-create document records for tracking (only is_required docs, skip download-only)
            foreach ($this->getDocumentConfigs()->where('is_required', true) as $config) {
                $claim->documents()->create([
                    'document_type' => $config->document_type,
                    'is_received'   => false,
                ]);
            }

            return $claim;
        });

        // Notify PICs outside the transaction so email failures don't rollback the claim
        $pics = \App\Models\User::role('pic')->where('notify_on_submission', true)->get();
        Notification::send($pics, new ClaimSubmittedNotification($claim));

        // Notify the client's PIC with both the submitted alert and a confirmation receipt
        if ($claim->company_pic_email) {
            Notification::route('mail', $claim->company_pic_email)
                ->notify(new ClaimSubmittedNotification($claim));

            Notification::route('mail', $claim->company_pic_email)
                ->notify(new ClaimReceivedByEmployerNotification($claim));
        }

        $this->showSuccessModal = true;
    }

    public function goToClaims(): mixed
    {
        return $this->redirect(route('claims.index'), navigate: true);
    }

    public function downloadFcl(): mixed
    {
        $data = [
            'incidentType'           => $this->incidentType,
            'worker'                 => $this->foundWorker,
            'incidentDate'           => $this->incidentDate,
            'incidentTime'           => $this->incidentTime,
            'incidentLocation'       => $this->incidentLocation,
            'incidentDescription'    => $this->incidentDescription,
            'injuryTypes'            => $this->injuryTypes,
            'injuryTypeOther'        => $this->injuryTypeOther,
            'injuryDescription'      => $this->injuryDescription,
            'diseaseType'            => $this->diseaseType,
            'isHistoricalDisease'    => $this->isHistoricalDisease,
            'isWorkRelated'          => $this->isWorkRelated,
            'workRelatedDescription' => $this->workRelatedDescription,
            'hospitalName'           => $this->hospitalName,
            'dateOfEmployment'       => $this->dateOfEmployment,
            'workingHourFrom'        => $this->workingHourFrom,
            'workingHourTo'          => $this->workingHourTo,
            'facilityMeals'          => $this->facilityMeals,
            'facilityAccommodation'  => $this->facilityAccommodation,
            'facilityTransportation' => $this->facilityTransportation,
            'insurancePolicyNo'      => $this->insurancePolicyNo,
        ];

        $pdf      = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.fcl-form', $data)->setPaper('a4');
        $filename = $this->incidentType === 'accident' ? 'FCL_Accident_Form.pdf' : 'FCL_Non_Accident_Form.pdf';

        $this->fclDownloaded = true;

        return response()->streamDownload(fn () => print($pdf->output()), $filename, [
            'Content-Type' => 'application/pdf',
        ]);
    }

    public function downloadDoc(int $id): mixed
    {
        $doc = \App\Models\DocumentConfig::findOrFail($id);

        if (! $doc->file_path) {
            return null;
        }

        $disk = str_starts_with($doc->file_path, 'documents/') ? 'public' : 'local';
        $path = $disk === 'local'
            ? public_path($doc->file_path)
            : \Illuminate\Support\Facades\Storage::disk('public')->path($doc->file_path);

        $filename = \Illuminate\Support\Str::slug($doc->label) . '.pdf';

        $this->downloadedDocIds = array_unique([...$this->downloadedDocIds, $id]);

        return response()->streamDownload(function () use ($path) {
            readfile($path);
        }, $filename, ['Content-Type' => 'application/pdf']);
    }

    public function render()
    {
        $configs          = $this->getDocumentConfigs();
        $requiredDocs     = $configs->where('is_required', true);
        $downloadableDocs = $configs->where('is_downloadable', true);
        $perkesoSchemeOptions = PerkesoScheme::active()->get();

        return view('livewire.claims.claim-create', compact('requiredDocs', 'downloadableDocs', 'perkesoSchemeOptions'));
    }

    protected function getDocumentConfigs(): \Illuminate\Support\Collection
    {
        if (! $this->claimType || ! $this->claimCategory || ! $this->incidentType) {
            return collect();
        }

        return \App\Models\DocumentConfig::where('claim_type', $this->claimType)
            ->where('claim_category', $this->claimCategory)
            ->where('incident_type', $this->incidentType)
            ->orderBy('sort_order')
            ->get();
    }
}
