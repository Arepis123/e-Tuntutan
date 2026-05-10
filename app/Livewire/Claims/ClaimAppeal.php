<?php

namespace App\Livewire\Claims;

use App\Models\Claim;
use App\Models\Worker;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

#[Layout('components.layouts.app')]
#[Title('Appeal Claim')]
class ClaimAppeal extends Component
{
    use WithFileUploads;

    public Claim $claim;

    public int $step = 2; // skip step 1 — claim type cannot change

    // Step 2: Worker info (read-only, pre-filled)
    public ?array $foundWorker = null;

    // Step 2: Employment fields
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

    // Step 3: Incident details
    public string $incidentType = '';
    public string $incidentDate = '';
    public string $incidentDescription = '';
    public string $hospitalName = '';
    public string $admissionDate = '';
    public string $dischargeDate = '';
    public string $insurancePolicyNo = '';
    public string $incidentTime = '';
    public string $incidentLocation = '';
    public array  $injuryTypes = [];
    public string $injuryTypeOther = '';
    public string $injuryDescription = '';
    public string $diseaseType = '';
    public string $isHistoricalDisease = '';
    public string $isWorkRelated = '';
    public string $workRelatedDescription = '';

    // Step 4: Documents
    public array $uploadedFiles = [];

    public function mount(Claim $claim): void
    {
        // Only employer who owns this claim can appeal, and only if insurer rejected
        abort_unless(
            $claim->user_id === Auth::id() && $claim->insurer_decision === 'rejected',
            403
        );

        $this->claim = $claim;

        // Pre-fill worker
        $worker = $claim->worker;
        $this->foundWorker = [
            'id'                 => $worker->id,
            'name'               => $worker->name,
            'passport_number'    => $worker->passport_number,
            'nationality'        => $worker->nationality,
            'date_of_birth'      => $worker->date_of_birth,
            'gender'             => $worker->gender ?? null,
            'passport_expiry'    => $worker->passport_expiry ?? 'NO DATA FOUND',
            'permit_expiry'      => $worker->permit_expiry ?? 'NO DATA FOUND',
            'phone'              => $worker->phone,
            'address'            => $worker->address,
            'contractor_name'    => $worker->employer_name,
            'contractor_address' => $worker->employer_address,
            'worker_type'        => $worker->worker_type,
        ];

        // Pre-fill incident fields
        $this->incidentType         = $claim->incident_type ?? '';
        $this->incidentDate         = $claim->incident_date?->format('Y-m-d') ?? '';
        $this->incidentDescription  = $claim->incident_description ?? '';
        $this->hospitalName         = $claim->hospital_name ?? '';
        $this->admissionDate        = $claim->admission_date?->format('Y-m-d') ?? '';
        $this->dischargeDate        = $claim->discharge_date?->format('Y-m-d') ?? '';
        $this->insurancePolicyNo    = $claim->insurance_policy_no ?? '';
        $this->incidentTime         = $claim->incident_time ?? '';
        $this->incidentLocation     = $claim->incident_location ?? '';
        $this->injuryTypes          = $claim->injury_types ? explode(',', $claim->injury_types) : [];
        $this->injuryTypeOther      = $claim->injury_type_other ?? '';
        $this->injuryDescription    = $claim->injury_description ?? '';
        $this->diseaseType          = $claim->disease_type ?? '';
        $this->isHistoricalDisease  = $claim->is_historical_disease !== null ? (string)(int)$claim->is_historical_disease : '';
        $this->isWorkRelated        = $claim->is_work_related !== null ? (string)(int)$claim->is_work_related : '';
        $this->workRelatedDescription = $claim->work_related_description ?? '';

        // Pre-fill employment fields
        $this->dateOfEmployment      = $claim->date_of_employment ?? '';
        $this->workingHourFrom       = $claim->working_hour_from ?? '';
        $this->workingHourTo         = $claim->working_hour_to ?? '';
        $this->facilityMeals         = $claim->facility_meals !== null ? (string)(int)$claim->facility_meals : '';
        $this->facilityAccommodation = $claim->facility_accommodation !== null ? (string)(int)$claim->facility_accommodation : '';
        $this->facilityTransportation = $claim->facility_transportation !== null ? (string)(int)$claim->facility_transportation : '';
        $this->tinNo                 = $claim->tin_no ?? '';
        $this->sstNo                 = $claim->sst_no ?? '';
        $this->companyPicName        = $claim->company_pic_name ?? '';
        $this->companyPicPhone       = $claim->company_pic_phone ?? '';
        $this->companyPicEmail       = $claim->company_pic_email ?? '';
    }

    protected function step3Rules(): array
    {
        $rules = [
            'incidentType'        => 'required|in:accident,non_accident',
            'incidentDate'        => 'required|date',
            'incidentDescription' => 'required|min:10',
        ];

        if ($this->incidentType === 'accident') {
            $rules['incidentTime']       = 'required';
            $rules['incidentLocation']   = 'required|string';
            $rules['injuryTypes']        = 'required|array|min:1';
            $rules['injuryDescription']  = 'required|min:5';
        }

        if ($this->incidentType === 'non_accident') {
            $rules['diseaseType']         = 'required|string';
            $rules['isHistoricalDisease'] = 'required|in:0,1';
            $rules['isWorkRelated']       = 'required|in:0,1';
        }

        if ($this->claim->claim_category === 'hospitalization') {
            $rules['admissionDate'] = 'required|date';
        }

        return $rules;
    }

    public function nextStep(): void
    {
        if ($this->step === 3) {
            $this->validate($this->step3Rules());
        }

        $this->step++;
    }

    public function previousStep(): void
    {
        $this->step = max(2, $this->step - 1);
    }

    public function submit(): void
    {
        $this->validate([
            'incidentDate'        => 'required|date',
            'incidentDescription' => 'required|min:10',
        ]);

        DB::transaction(function () {
            // Update worker
            $this->claim->worker->update([
                'phone'            => $this->foundWorker['phone'],
                'address'          => $this->foundWorker['address'],
                'employer_name'    => $this->foundWorker['contractor_name'],
                'employer_address' => $this->foundWorker['contractor_address'],
            ]);

            // Update claim fields and reset insurer decision
            $this->claim->update([
                'incident_type'           => $this->incidentType,
                'incident_date'           => $this->incidentDate,
                'incident_time'           => $this->incidentTime ?: null,
                'incident_location'       => $this->incidentLocation ?: null,
                'incident_description'    => $this->incidentDescription,
                'injury_types'            => $this->injuryTypes ? implode(',', $this->injuryTypes) : null,
                'injury_type_other'       => $this->injuryTypeOther ?: null,
                'injury_description'      => $this->injuryDescription ?: null,
                'disease_type'            => $this->diseaseType ?: null,
                'is_historical_disease'   => $this->isHistoricalDisease !== '' ? (bool) $this->isHistoricalDisease : null,
                'is_work_related'         => $this->isWorkRelated !== '' ? (bool) $this->isWorkRelated : null,
                'work_related_description' => $this->workRelatedDescription ?: null,
                'hospital_name'           => $this->hospitalName ?: null,
                'admission_date'          => $this->admissionDate ?: null,
                'discharge_date'          => $this->dischargeDate ?: null,
                'insurance_policy_no'     => $this->insurancePolicyNo ?: null,
                'tin_no'                  => $this->tinNo ?: null,
                'sst_no'                  => $this->sstNo ?: null,
                'company_pic_name'        => $this->companyPicName ?: null,
                'company_pic_phone'       => $this->companyPicPhone ?: null,
                'company_pic_email'       => $this->companyPicEmail ?: null,
                'date_of_employment'      => $this->dateOfEmployment ?: null,
                'working_hour_from'       => $this->workingHourFrom ?: null,
                'working_hour_to'         => $this->workingHourTo ?: null,
                'facility_meals'          => $this->facilityMeals !== '' ? (bool) $this->facilityMeals : null,
                'facility_accommodation'  => $this->facilityAccommodation !== '' ? (bool) $this->facilityAccommodation : null,
                'facility_transportation' => $this->facilityTransportation !== '' ? (bool) $this->facilityTransportation : null,
                // Mark as appealed — historical timestamps preserved intentionally for timeline
                'appealed_at'   => now(),
                'appeal_count'  => $this->claim->appeal_count + 1,
                'submitted_at'  => now(),
                'status'        => 'open',
            ]);

            // Re-upload documents
            foreach ($this->uploadedFiles as $docType => $file) {
                if (! $file) continue;

                $path = $file->store('claim-documents', 'public');
                $doc  = $this->claim->documents()->where('document_type', $docType)->first();

                if ($doc) {
                    $doc->update(['file_path' => $path, 'is_received' => false, 'received_at' => null]);
                } else {
                    $this->claim->documents()->create([
                        'document_type' => $docType,
                        'file_path'     => $path,
                        'is_received'   => false,
                    ]);
                }
            }

            // Notify PICs
            $pics = \App\Models\User::role('pic')->where('notify_on_submission', true)->get();
            Notification::send($pics, new \App\Notifications\ClaimSubmittedNotification($this->claim));
        });

        $this->redirect(route('claims.show', $this->claim), navigate: true);
    }

    protected function getDocumentConfigs(): \Illuminate\Support\Collection
    {
        if (! $this->claim->claim_type || ! $this->claim->claim_category) {
            return collect();
        }

        $incidentType = $this->claim->claim_category === 'death' ? null : ($this->incidentType ?: null);

        return \App\Models\DocumentConfig::where('claim_type', $this->claim->claim_type)
            ->where('claim_category', $this->claim->claim_category)
            ->where('incident_type', $incidentType)
            ->orderBy('sort_order')
            ->get();
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
        ];

        $pdf      = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.fcl-form', $data)->setPaper('a4');
        $filename = $this->incidentType === 'accident' ? 'FCL_Accident_Form.pdf' : 'FCL_Non_Accident_Form.pdf';

        return response()->streamDownload(fn () => print($pdf->output()), $filename, [
            'Content-Type' => 'application/pdf',
        ]);
    }

    public function downloadDoc(int $id): mixed
    {
        $doc = \App\Models\DocumentConfig::findOrFail($id);

        if (! $doc->file_path) return null;

        $path     = public_path($doc->file_path);
        $filename = \Illuminate\Support\Str::slug($doc->label) . '.pdf';

        return response()->streamDownload(function () use ($path) {
            readfile($path);
        }, $filename, ['Content-Type' => 'application/pdf']);
    }

    public function render()
    {
        $configs          = $this->getDocumentConfigs();
        $requiredDocs     = $configs->where('is_required', true);
        $downloadableDocs = $configs->where('is_downloadable', true);

        return view('livewire.claims.claim-appeal', compact('requiredDocs', 'downloadableDocs'));
    }
}
