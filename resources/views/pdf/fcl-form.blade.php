<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; font-size: 10px; color: #000; padding: 65px 78px; }

        .ref { font-size: 8.5px; text-align: right; margin-bottom: 4px; }
        .title { font-size: 13px; font-weight: bold; text-transform: uppercase; margin-bottom: 3px; }
        .org-name { font-size: 10px; font-weight: bold; margin-bottom: 2px; }
        .org-addr { font-size: 9.5px; line-height: 1.5; color: #222; }
        .divider { border-top: 3px solid #555; margin: 20px 0 8px; }
        .divider-space { margin: 20px 0 8px; }
        table.header-table { width: 100%; border-collapse: collapse; margin-bottom: 0; }
        table.header-table td { vertical-align: top; padding: 0; }

        .section-title { font-weight: bold; font-size: 10px; text-decoration: underline; margin: 13px 0 6px 0px; }

        /* Inline field: label : value underlined */
        table.fields { width: 100%; border-collapse: collapse; margin-bottom: 6px; }
        table.fields td { vertical-align: bottom; padding: 4px 0 0 5px; font-size: 10px; color: solid #555}
        td.lbl { white-space: nowrap; padding-right: 4px; width: 1%; }
        td.val { border-bottom: 1px dotted #999; padding-bottom: 1px; color: #292929; }
        td.gap { width: 14px; }
        td.gap2 { width: 13px; }

        .textarea-label { font-size: 10px; margin-bottom: 4px; margin-top: 6px; margin-left: 5.5px; }
        .textarea-box { width: 97%; margin-bottom: 10px; margin-left: 18px; }
        .dot-line { border-bottom: 1px dotted #999; min-height: 16px; padding-bottom: 2px; margin-bottom: 4px; font-size: 10px; color: #292929; word-wrap: break-word; }

        .checkbox-row { display: flex; flex-wrap: wrap; gap: 10px; margin: 10px 0 6px 10px; font-size: 10px; }
        .cb { display: inline-flex; align-items: center; gap: 3px; }
        .cb-box { width: 10px; height: 10px; border: 1px solid #000; display: inline-block; flex-shrink: 0; }
        .cb-box.checked { background-color: #000; }

        table.reporter { width: 100%; border-collapse: collapse; margin-top: 6px; }
        table.reporter { width: 100%; border-collapse: collapse; margin-top: 6px; }
        table.reporter > tbody > tr > td { padding: 3px 5px; vertical-align: top; width: 50%; font-size: 10px; }
        table.reporter-inner { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        table.reporter-inner td { vertical-align: bottom; padding: 0; font-size: 10px; }
        td.ri-lbl { white-space: nowrap; padding-right: 4px; width: 1%; }
        td.ri-val { border-bottom: 1px dotted #999; }

        .note { font-size: 8px; font-style: italic; margin-top: 12px; border-top: 1px solid #bbb; padding-top: 5px; color: #444; }
        .section3-note { font-size: 8.5px; color: #777; font-style: italic; margin-bottom: 6px; }
    </style>
</head>
<body>

<div class="ref">CLAB/SOP/08/23</div>

<table class="header-table">
    <tr>
        <td>
            <div class="title">
                @if($incidentType === 'accident')
                    FCL Accident Cases Report (Laporan Kemalangan Pekerja Asing)
                @else
                    FCL Non-Accident Report (Laporan Bukan Kemalangan Pekerja Asing)
                @endif
            </div>
            <div class="org-name">CONSTRUCTION LABOUR EXCHANGE CENTRE BERHAD</div>
            <div class="org-addr">
                Level 2, Annexe Block, Menara Milenium,<br>
                No. 8, Jalan Damanlela,<br>
                Pusat Bandar Damansara,<br>
                50490 Kuala Lumpur.<br>
                Careline: 03-20959599 &nbsp;&nbsp; Fax: 03-20959566
            </div>
        </td>
        <td style="width: 90px; text-align: right; vertical-align: top;">
            <img src="{{ public_path('images/logo-clab.png') }}" style="width: 75px; height: auto; padding-top: 10px;" />
        </td>
    </tr>
</table>

<div class="divider"></div>

{{-- SECTION I --}}
<div class="section-title">SECTION I &mdash; WORKER'S DETAIL (BUTIR-BUTIR MENGENAI PEKERJA)</div>

<table class="fields">
    <tr>
        <td class="lbl">a) Name (Nama) :</td>
        <td class="val">{{ $worker['name'] ?? '' }}</td>
        <td class="gap"></td>
        <td class="lbl">b) Sex (Jantina) :</td>
        <td class="val" style="width:80px;">{{ $worker['gender'] ?? '' }}</td>
    </tr>
</table>
<table class="fields">
    <tr>
        <td class="lbl">c) Passport No (Pasport No) :</td>
        <td class="val">{{ $worker['passport_number'] ?? '' }}</td>
        <td class="gap"></td>
        <td class="lbl">d) Work Permit (Permit Kerja) :</td>
        <td class="val" style="width:100px;">{{ $worker['permit_expiry'] ?? '' }}</td>
    </tr>
</table>
<table class="fields">
    <tr>
        <td class="lbl">e) Date of Employment (Tarikh Mula Bekerja) :</td>
        <td class="val">{{ $dateOfEmployment ? \Carbon\Carbon::parse($dateOfEmployment)->format('d/m/Y') : '' }}</td>
        <td class="gap"></td>
        <td class="lbl">f) Nationality (Warganegara) :</td>
        <td class="val" style="width:100px;">{{ $worker['nationality'] ?? '' }}</td>
    </tr>
</table>
<table class="fields">
    <tr>
        <td class="lbl">g) Working Hour (Waktu bekerja) :</td>
        <td class="val" style="width:60px;">{{ $workingHourFrom ? \Carbon\Carbon::parse($workingHourFrom)->format('h:i A') : '' }}</td>
        <td class="lbl" style="padding: 0 6px;">to</td>
        <td class="val" style="width:60px;">{{ $workingHourTo ? \Carbon\Carbon::parse($workingHourTo)->format('h:i A') : '' }}</td>
        <td class="gap2"></td>
        <td style="width:100%;"></td>
        {{-- h) Facilities hidden for now --}}
    </tr>
</table>

<div class="divider-space"></div>

{{-- SECTION II --}}
@if($incidentType === 'accident')
<div class="section-title">SECTION II &mdash; ACCIDENT DETAILS (BUTIR-BUTIR MENGENAI KEMALANGAN)</div>

<table class="fields">
    <tr>
        <td class="lbl">a) Date of Accident (Tarikh kemalangan) :</td>
        <td class="val">{{ $incidentDate ? \Carbon\Carbon::parse($incidentDate)->format('d/m/Y') : '' }}</td>
        <td class="gap"></td>
        <td class="lbl">b) Time of Accident (Masa kemalangan) :</td>
        <td class="val" style="width:100px;">{{ $incidentTime ? \Carbon\Carbon::parse($incidentTime)->format('h:i A') : '' }}</td>
    </tr>
</table>

<div class="textarea-label">c) Location of Accident &amp; Full Address (Lokasi kemalangan &amp; Alamat Penuh) :</div>
<div class="textarea-box">
    <div class="dot-line">{{ $incidentLocation ?? '' }}</div>
    <div class="dot-line"></div>
    <div class="dot-line"></div>
</div>

<div class="textarea-label">d) Description of Accident (Penghuraian mengenai kemalangan yang berlaku) :</div>
<div class="textarea-box">
    <div class="dot-line">{{ $incidentDescription ?? '' }}</div>
    <div class="dot-line"></div>
    <div class="dot-line"></div>
</div>

<div class="textarea-label">e) Type of Injury (Jenis-Jenis Kecederaan) :</div>
<div class="checkbox-row">
    @foreach(['fractured' => 'Fractured (Kepatahan)', 'burn' => 'Burn (Terbakar)', 'death' => 'Death (Kematian)', 'dismemberment' => 'Dismemberment (Terputus)'] as $val => $lbl)
    <div class="cb">
        <div class="cb-box {{ in_array($val, (array)$injuryTypes) ? 'checked' : '' }}"></div>
        {{ $lbl }}
    </div>
    @endforeach
    <div class="cb">
        <div class="cb-box {{ in_array('others', (array)$injuryTypes) ? 'checked' : '' }}"></div>
        Others (Lain-Lain): {{ $injuryTypeOther ?? '' }}
    </div>
</div>

<div class="textarea-label">f) Description of Injury (Penghuraian mengenai kecederaan) :</div>
<div class="textarea-box">
    <div class="dot-line">{{ $injuryDescription ?? '' }}</div>
    <div class="dot-line"></div>
    <div class="dot-line"></div>
</div>

<table class="fields">
    <tr>
        <td class="lbl">g) Hospital / Clinic Name :</td>
        <td class="val">{{ $hospitalName ?? '' }}</td>
    </tr>
</table>

@else
<div class="section-title">SECTION II &mdash; NON-ACCIDENT DETAILS (BUTIR-BUTIR MENGENAI PENYAKIT)</div>

<table class="fields">
    <tr>
        <td class="lbl">a) Type of Disease (Jenis Penyakit) :</td>
        <td class="val">{{ $diseaseType ?? '' }}</td>
        <td class="gap"></td>
        <td class="lbl">b) Historical Disease (Penyakit Sejarah) :</td>
        <td class="val" style="width:80px;">{{ $isHistoricalDisease === '1' ? 'Yes (Ya)' : ($isHistoricalDisease === '0' ? 'No (Tidak)' : '') }}</td>
    </tr>
</table>

<div class="textarea-label">c) Work-related illness? (Adakah penyakit berkaitan pekerjaan) :</div>
<div class="textarea-box">
    <div class="dot-line">@if($isWorkRelated === '1') Yes — {{ $workRelatedDescription ?? '' }} @elseif($isWorkRelated === '0') No (Tidak) @endif</div>
    <div class="dot-line"></div>
</div>

<div class="textarea-label">d) Description of Disease (Penghuraian mengenai penyakit berlaku) :</div>
<div class="textarea-box">
    <div class="dot-line">{{ $incidentDescription ?? '' }}</div>
    <div class="dot-line"></div>
    <div class="dot-line"></div>
</div>

<table class="fields">
    <tr>
        <td class="lbl">e) Hospital / Clinic Name :</td>
        <td class="val">{{ $hospitalName ?? '' }}</td>
    </tr>
</table>
@endif

<div class="divider-space"></div>

{{-- SECTION III --}}
<div class="section-title">SECTION III &mdash; INSURANCE COVERAGE (PERLINDUNGAN INSURAN)</div>
<div class="section3-note">Please complete this section, sign, and return with your submission.</div>

@if($incidentType === 'non_accident')
<table class="fields" style="margin-bottom:8px;">
    <tr>
        <td class="lbl">Does the worker covered by FWHS? :</td>
        <td style="width:60px; padding: 0 8px; vertical-align:bottom;">
            <span style="border:1px solid #000; padding:0 4px;">Yes</span> &nbsp;
            <span style="border:1px solid #000; padding:0 4px;">No</span>
        </td>
        <td class="gap"></td>
        <td class="lbl">Insurance Policy No. (No Polisi) :</td>
        <td class="val"></td>
    </tr>
</table>
@else
<table class="fields" style="margin-bottom:8px;">
    <tr>
        <td class="lbl">Insurance Policy No. (No Polisi) :</td>
        <td class="val"></td>
    </tr>
</table>
@endif

<div style="font-weight:bold; font-size:10px; margin-bottom:4px;">Reported by (Dilaporkan oleh):</div>
<table class="reporter">
    <tr>
        <td>
            <table class="reporter-inner"><tr><td class="ri-lbl">Name (Nama):</td><td class="ri-val">&nbsp;</td></tr></table>
            <table class="reporter-inner"><tr><td class="ri-lbl">Designation (Jawatan):</td><td class="ri-val">&nbsp;</td></tr></table>
            <table class="reporter-inner"><tr><td class="ri-lbl">Date (Tarikh):</td><td class="ri-val">&nbsp;</td></tr></table>
        </td>
        <td>
            <table class="reporter-inner"><tr><td class="ri-lbl">Company Name &amp; Address:</td><td class="ri-val">&nbsp;</td></tr></table>
            <table class="reporter-inner"><tr><td class="ri-lbl">&nbsp;</td><td class="ri-val">&nbsp;</td></tr></table>
            <table class="reporter-inner"><tr><td class="ri-lbl">&nbsp;</td><td class="ri-val">&nbsp;</td></tr></table>
        </td>
    </tr>
</table>

<div class="note">
    (Please enclose with the latest payslip (at least 6 months) and sick leave certificate (if any). Sila sertakan bersama slip pembayaran gaji (sekurang-kurangnya 6 bulan) dan sijil cuti sakit (jika ada))
</div>

</body>
</html>
