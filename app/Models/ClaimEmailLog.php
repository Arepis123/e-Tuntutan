<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClaimEmailLog extends Model
{
    protected $fillable = [
        'claim_id',
        'to_email',
        'to_name',
        'subject',
        'event',
        'sent_at',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
    ];

    public function claim(): BelongsTo
    {
        return $this->belongsTo(Claim::class);
    }

    public static function record(Claim $claim, string $toEmail, ?string $toName, string $subject, string $event): void
    {
        static::create([
            'claim_id' => $claim->id,
            'to_email' => $toEmail,
            'to_name'  => $toName,
            'subject'  => $subject,
            'event'    => $event,
            'sent_at'  => now(),
        ]);
    }
}
