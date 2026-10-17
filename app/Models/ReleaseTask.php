<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReleaseTask extends Model
{
    use HasFactory;

    protected $fillable = [
        'change_request_id',
        'release_team_member_id',
        'assigned_by_id',
        'instruction',
        'scheduled_start',
        'scheduled_end',
        'execution_status',
        'executed_at',
        'execution_notes',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_start' => 'datetime',
            'scheduled_end' => 'datetime',
            'executed_at' => 'datetime',
        ];
    }

    public function changeRequest(): BelongsTo
    {
        return $this->belongsTo(ChangeRequest::class);
    }

    public function releaseTeamMember(): BelongsTo
    {
        return $this->belongsTo(ReleaseTeamMember::class);
    }

    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by_id');
    }
}
