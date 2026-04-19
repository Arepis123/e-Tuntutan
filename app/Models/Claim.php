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
        'incident_type',
        'status',
        'forwarded_to',
        'incident_date',
        'incident_description',
        'hospital_name',
        'admission_date',
        'discharge_date',
        'submitted_at',
        'approved_at',
        'rejected_at',
        'closed_at',
        'rejection_reason',
        'notes',
    ];

    protected $casts = [
        'incident_date'  => 'date',
        'admission_date' => 'date',
        'discharge_date' => 'date',
        'submitted_at'   => 'date',
        'approved_at'    => 'date',
        'rejected_at'    => 'date',
        'closed_at'      => 'date',
    ];

    public function worker(): BelongsTo
    {
        return $this->belongsTo(Worker::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
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
    }
}
