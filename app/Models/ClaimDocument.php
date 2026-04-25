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
        'is_received',
        'received_at',
        'received_by',
    ];

    protected $casts = [
        'is_received' => 'boolean',
        'received_at' => 'datetime',
    ];

    public function claim(): BelongsTo
    {
        return $this->belongsTo(Claim::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    public function getUrlAttribute(): string
    {
        return Storage::disk($this->disk)->url($this->path);
    }

    public function getDocumentTypeLabel(): string
    {
        return DocumentConfig::where('document_type', $this->document_type)->value('label')
            ?? ucwords(str_replace('_', ' ', $this->document_type));
    }
}
