<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ImplementationEvidence extends Model
{
    protected $fillable = [
        'change_request_id',
        'uploaded_by_id',
        'title',
        'file_path',
        'original_filename',
        'mime_type',
        'file_size',
        'checksum_sha256',
    ];

    public function changeRequest(): BelongsTo
    {
        return $this->belongsTo(ChangeRequest::class);
    }

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by_id');
    }
}
