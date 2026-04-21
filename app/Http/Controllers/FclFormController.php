<?php

namespace App\Http\Controllers;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class FclFormController extends Controller
{
    public function preview(Request $request)
    {
        $type = $request->query('type', 'accident');

        $data = [
            'incidentType'        => $type,
            'worker'              => [
                'name'            => 'Ahmad Bin Ibrahim',
                'gender'          => 'Male',
                'passport_number' => 'A12345678',
                'permit_expiry'   => '31/12/2026',
                'nationality'     => 'Bangladesh',
            ],
            'dateOfEmployment'    => '2023-06-01',
            'workingHourFrom'     => '08:00',
            'workingHourTo'       => '17:00',
            'facilityMeals'       => '1',
            'facilityAccommodation' => '1',
            'facilityTransportation' => '0',

            // Accident fields
            'incidentDate'        => '2026-04-15',
            'incidentTime'        => '10:30',
            'incidentLocation'    => 'Construction Site, Jalan Ampang, 50450 Kuala Lumpur',
            'incidentDescription' => 'Worker fell from scaffolding on Level 3 while carrying materials. Worker fell from scaffolding on Level 3 while carrying materials. Worker fell from scaffolding on Level 3 while carrying materials. Worker fell from scaffolding on Level 3 while carrying materials.',
            'injuryTypes'         => ['fractured'],
            'injuryTypeOther'     => '',
            'injuryDescription'   => 'Fractured left arm (radius). Worker was taken to hospital immediately.',
            'hospitalName'        => 'Hospital Kuala Lumpur',

            // Non-accident fields
            'diseaseType'         => 'Respiratory Illness',
            'isHistoricalDisease' => '0',
            'isWorkRelated'       => '1',
            'workRelatedDescription' => 'Prolonged exposure to dust and chemicals at the worksite.',
        ];

        $pdf = Pdf::loadView('pdf.fcl-form', $data)->setPaper('a4');

        return $pdf->stream($type === 'accident' ? 'FCL_Accident_Preview.pdf' : 'FCL_Non_Accident_Preview.pdf');
    }

    public function download(Request $request)
    {
        $data = $request->validate([
            'incidentType'           => 'required|in:accident,non_accident',
            'worker'                 => 'required|array',
            'incidentDate'           => 'nullable|string',
            'incidentTime'           => 'nullable|string',
            'incidentLocation'       => 'nullable|string',
            'incidentDescription'    => 'nullable|string',
            'injuryTypes'            => 'nullable|array',
            'injuryTypeOther'        => 'nullable|string',
            'injuryDescription'      => 'nullable|string',
            'diseaseType'            => 'nullable|string',
            'isHistoricalDisease'    => 'nullable|string',
            'isWorkRelated'          => 'nullable|string',
            'workRelatedDescription' => 'nullable|string',
            'hospitalName'           => 'nullable|string',
            'dateOfEmployment'       => 'nullable|string',
            'workingHourFrom'        => 'nullable|string',
            'workingHourTo'          => 'nullable|string',
            'facilityMeals'          => 'nullable|string',
            'facilityAccommodation'  => 'nullable|string',
            'facilityTransportation' => 'nullable|string',
        ]);

        $pdf = Pdf::loadView('pdf.fcl-form', $data)->setPaper('a4');

        $filename = $data['incidentType'] === 'accident'
            ? 'FCL_Accident_Form.pdf'
            : 'FCL_Non_Accident_Form.pdf';

        return $pdf->download($filename);
    }
}
