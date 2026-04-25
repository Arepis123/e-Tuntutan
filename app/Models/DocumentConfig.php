<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentConfig extends Model
{
    protected $fillable = [
        'claim_type',
        'claim_category',
        'incident_type',
        'document_type',
        'label',
        'is_required',
        'is_downloadable',
        'file_path',
        'sort_order',
    ];

    protected $casts = [
        'is_required'    => 'boolean',
        'is_downloadable' => 'boolean',
    ];

    public static function getFor(string $claimType, string $claimCategory, ?string $incidentType): \Illuminate\Database\Eloquent\Collection
    {
        return static::where('claim_type', $claimType)
            ->where('claim_category', $claimCategory)
            ->where('incident_type', $incidentType)
            ->where('is_required', true)
            ->orderBy('sort_order')
            ->get();
    }
}
