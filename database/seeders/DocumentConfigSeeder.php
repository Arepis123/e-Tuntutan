<?php

namespace Database\Seeders;

use App\Models\DocumentConfig;
use Illuminate\Database\Seeder;

class DocumentConfigSeeder extends Seeder
{
    public function run(): void
    {
        DocumentConfig::truncate();

        $rows = [];

        $base = ['file_path' => null, 'created_at' => now(), 'updated_at' => now()];

        foreach (['fwhs', 'green_card', 'perkeso'] as $type) {
            $rows[] = $base + ['claim_type' => $type, 'claim_category' => 'hospitalization', 'incident_type' => 'accident',     'document_type' => 'accident_fcl',    'label' => 'Accident FCL Form',     'is_required' => true,  'is_downloadable' => true,  'sort_order' => 1];
            $rows[] = $base + ['claim_type' => $type, 'claim_category' => 'hospitalization', 'incident_type' => 'accident',     'document_type' => 'original_receipt', 'label' => 'Original Receipt',      'is_required' => true,  'is_downloadable' => false, 'sort_order' => 2];
            $rows[] = $base + ['claim_type' => $type, 'claim_category' => 'hospitalization', 'incident_type' => 'accident',     'document_type' => 'discharge_note',   'label' => 'Discharge Note',        'is_required' => true,  'is_downloadable' => false, 'sort_order' => 3];
            $rows[] = $base + ['claim_type' => $type, 'claim_category' => 'hospitalization', 'incident_type' => 'accident',     'document_type' => 'doctor_letter',    'label' => "Doctor's Letter",       'is_required' => true,  'is_downloadable' => false, 'sort_order' => 4];

            $rows[] = $base + ['claim_type' => $type, 'claim_category' => 'hospitalization', 'incident_type' => 'non_accident', 'document_type' => 'non_accident_fcl', 'label' => 'Non-Accident FCL Form', 'is_required' => true,  'is_downloadable' => true,  'sort_order' => 1];
            $rows[] = $base + ['claim_type' => $type, 'claim_category' => 'hospitalization', 'incident_type' => 'non_accident', 'document_type' => 'original_receipt', 'label' => 'Original Receipt',      'is_required' => true,  'is_downloadable' => false, 'sort_order' => 2];
            $rows[] = $base + ['claim_type' => $type, 'claim_category' => 'hospitalization', 'incident_type' => 'non_accident', 'document_type' => 'discharge_note',   'label' => 'Discharge Note',        'is_required' => true,  'is_downloadable' => false, 'sort_order' => 3];
            $rows[] = $base + ['claim_type' => $type, 'claim_category' => 'hospitalization', 'incident_type' => 'non_accident', 'document_type' => 'doctor_letter',    'label' => "Doctor's Letter",       'is_required' => true,  'is_downloadable' => false, 'sort_order' => 4];
        }

        foreach (['accident', 'non_accident'] as $incident) {
            $rows[] = ['file_path' => 'attachments/FWHS REIMBURSEMENT MEDICAL FORM.pdf', 'claim_type' => 'fwhs', 'claim_category' => 'hospitalization', 'incident_type' => $incident, 'document_type' => 'fwhs_medical_form', 'label' => 'FWHS Reimbursement Medical Form',  'is_required' => true,  'is_downloadable' => true, 'sort_order' => 5] + $base;
            $rows[] = ['file_path' => 'attachments/CHECKLIST FWHS.pdf',                  'claim_type' => 'fwhs', 'claim_category' => 'hospitalization', 'incident_type' => $incident, 'document_type' => 'fwhs_checklist',    'label' => 'Checklist Tuntutan Insuran FWHS', 'is_required' => false, 'is_downloadable' => true, 'sort_order' => 6] + $base;
        }

        foreach (['green_card', 'perkeso'] as $type) {
            foreach (['accident', 'non_accident'] as $incident) {
                $fclType  = $incident === 'accident' ? 'accident_fcl'     : 'non_accident_fcl';
                $fclLabel = $incident === 'accident' ? 'Accident FCL Form' : 'Non-Accident FCL Form';
                $rows[] = $base + ['claim_type' => $type, 'claim_category' => 'death', 'incident_type' => $incident, 'document_type' => $fclType,         'label' => $fclLabel,               'is_required' => true, 'is_downloadable' => true,  'sort_order' => 1];
                $rows[] = $base + ['claim_type' => $type, 'claim_category' => 'death', 'incident_type' => $incident, 'document_type' => 'death_cert',     'label' => 'Death Certificate',     'is_required' => true, 'is_downloadable' => false, 'sort_order' => 2];
                $rows[] = $base + ['claim_type' => $type, 'claim_category' => 'death', 'incident_type' => $incident, 'document_type' => 'body_receipt',   'label' => 'Body Delivery Receipt', 'is_required' => true, 'is_downloadable' => false, 'sort_order' => 3];
                $rows[] = $base + ['claim_type' => $type, 'claim_category' => 'death', 'incident_type' => $incident, 'document_type' => 'police_report',  'label' => 'Police Report',         'is_required' => true, 'is_downloadable' => false, 'sort_order' => 4];
                $rows[] = $base + ['claim_type' => $type, 'claim_category' => 'death', 'incident_type' => $incident, 'document_type' => 'embassy_letter', 'label' => 'Embassy Letter',        'is_required' => true, 'is_downloadable' => false, 'sort_order' => 5];
            }
        }

        DocumentConfig::insert($rows);
    }
}
