<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    protected $fillable = [
        'claim_id',
        'amount',
        'beneficiary_name',
        'beneficiary_ic',
        'beneficiary_relationship',
        'bank_name',
        'bank_account_number',
        'payment_date',
        'payment_slip_path',
        'status',
        'notes',
    ];

    protected $casts = [
        'amount'       => 'decimal:2',
        'payment_date' => 'date',
    ];

    public function claim(): BelongsTo
    {
        return $this->belongsTo(Claim::class);
    }
}
