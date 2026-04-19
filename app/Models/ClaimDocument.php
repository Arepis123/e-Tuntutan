<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class ClaimDocument extends Model
{
    protected $fillable = [
        'claim_id',
        'document_type',
        'original_filename',
        'stored_filename',
        'disk',
        'path',
        'file_size',
        'mime_type',
        'uploaded_by',
    ];

    public function claim(): BelongsTo
    {
        return $this->belongsTo(Claim::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function getUrlAttribute(): string
    {
        return Storage::disk($this->disk)->url($this->path);
    }

    public function getDocumentTypeLabel(): string
    {
        return match ($this->document_type) {
            'accident_fcl'    => 'Accident FCL Form',
            'original_receipt' => 'Original Receipt',
            'discharge_note'  => 'Discharge Note',
            'doctor_letter'   => "Doctor's Letter",
            'death_cert'      => 'Death Certificate',
            'body_receipt'    => 'Body Delivery Receipt',
            'police_report'   => 'Police Report',
            'embassy_letter'  => 'Embassy Letter',
            'approval_letter' => 'Approval Letter',
            'payment_slip'    => 'Payment Slip',
            'passport_copy'   => 'Passport Copy',
            'beneficiary_bank' => 'Beneficiary Bank Details',
            'beneficiary_info' => 'Beneficiary Information',
            default           => $this->document_type,
        };
    }
}
