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
        'nationality',
        'date_of_birth',
        'worker_type',
        'employer_name',
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
