<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Claim extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'claim_number',
        'worker_id',
        'user_id',
        'claim_type',
        'claim_category',
        'perkeso_schemes',
        'incident_type',
        'status',
        'forwarded_to',
        'incident_date',
        'incident_description',
        'hospital_name',
        'admission_date',
        'discharge_date',
        'submitted_at',
        'in_progress_at',
        'documents_received_at',
        'documents_received_by',
        'submitted_to_insurer_at',
        'submitted_to_insurer_by',
        'insurer_decision',
        'insurer_decided_at',
        'insurer_decided_by',
        'insurer_approval_letter',
        'payment_channel',
        'insurer_rejection_reason',
        'insurer_rejection_attachment',
        'appealed_at',
        'appeal_count',
        'approved_at',
        'rejected_at',
        'closed_at',
        'rejection_reason',
        'notes',
        'tin_no',
        'sst_no',
        'company_pic_name',
        'company_pic_phone',
        'company_pic_email',
        'date_of_employment',
        'working_hour_from',
        'working_hour_to',
        'facility_meals',
        'facility_accommodation',
        'facility_transportation',
        'incident_time',
        'incident_location',
        'injury_types',
        'injury_type_other',
        'injury_description',
        'disease_type',
        'is_historical_disease',
        'is_work_related',
        'work_related_description',
        'insurance_policy_no',
    ];

    protected $casts = [
        'perkeso_schemes' => 'array',
        'incident_date'  => 'date',
        'admission_date' => 'date',
        'discharge_date' => 'date',
        'submitted_at'            => 'datetime',
        'in_progress_at'          => 'datetime',
        'documents_received_at'   => 'datetime',
        'submitted_to_insurer_at' => 'datetime',
        'insurer_decided_at'      => 'datetime',
        'appealed_at'             => 'datetime',
        'approved_at'             => 'datetime',
        'rejected_at'             => 'datetime',
        'closed_at'               => 'datetime',
    ];

    public function worker(): BelongsTo
    {
        return $this->belongsTo(Worker::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function documentsReceivedBy(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'documents_received_by');
    }

    public function submittedToInsurerBy(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_to_insurer_by');
    }

    public function insurerDecidedBy(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'insurer_decided_by');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(ClaimDocument::class);
    }

    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class);
    }

    public function claimNotes(): HasMany
    {
        return $this->hasMany(ClaimNote::class);
    }

    public function emailLogs(): HasMany
    {
        return $this->hasMany(ClaimEmailLog::class)->orderBy('sent_at', 'desc');
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'open'        => 'red',
            'in_progress' => 'yellow',
            'closed'      => 'green',
            default       => 'zinc',
        };
    }

    public function getClaimTypeLabel(): string
    {
        return match ($this->claim_type) {
            'fwhs'       => 'Insurance (FWHS)',
            'green_card' => 'Green Card',
            'perkeso'    => 'PERKESO',
            default      => $this->claim_type,
        };
    }

    public function isEmployerManaged(): bool
    {
        return in_array($this->claim_type, ['perkeso', 'green_card'])
            && $this->worker?->worker_type === 'existing';
    }

    protected static function booted(): void
    {
        static::creating(function (Claim $claim) {
            $claim->claim_number = 'CLM-' . date('Y') . '-' . str_pad(
                static::whereYear('created_at', date('Y'))->count() + 1,
                4,
                '0',
                STR_PAD_LEFT
            );
        });

        static::updating(function (Claim $claim) {
            if ($claim->isDirty('status') && $claim->status === 'in_progress' && ! $claim->in_progress_at) {
                $claim->in_progress_at = now();
            }
        });
    }
}
