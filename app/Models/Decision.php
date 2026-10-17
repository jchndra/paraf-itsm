<?php

namespace App\Models;

use BadMethodCallException;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Decision extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'change_request_id',
        'authority_id',
        'meeting_id',
        'recorded_by_id',
        'snapshot_authority_name',
        'snapshot_authority_nip',
        'snapshot_authority_position',
        'decision_status',
        'decision_date',
        'reason',
        'is_replacement',
        'replaces_decision_id',
    ];

    protected function casts(): array
    {
        return [
            'decision_date' => 'datetime',
            'is_replacement' => 'boolean',
            'created_at' => 'datetime',
        ];
    }

    /**
     * Penegakan Aturan Append-Only
     */
    protected static function booted(): void
    {
        static::updating(function () {
            throw new BadMethodCallException('Tabel decisions bersifat append-only. Perubahan record tidak diizinkan.');
        });

        static::deleting(function () {
            throw new BadMethodCallException('Tabel decisions bersifat append-only. Penghapusan record tidak diizinkan.');
        });
    }

    public function changeRequest(): BelongsTo
    {
        return $this->belongsTo(ChangeRequest::class);
    }

    public function authority(): BelongsTo
    {
        return $this->belongsTo(Authority::class);
    }

    public function meeting(): BelongsTo
    {
        return $this->belongsTo(Meeting::class);
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by_id');
    }
}
