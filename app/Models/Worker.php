<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Worker extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'passport_number',
        'passport_expiry',
        'permit_expiry',
        'nationality',
        'date_of_birth',
        'gender',
        'worker_type',
        'worker_status',
        'employer_name',
        'employer_address',
        'employer_ic',
        'phone',
        'address',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
    ];

    public function claims(): HasMany
    {
        return $this->hasMany(Claim::class);
    }
}
