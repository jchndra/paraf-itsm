<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ReleaseTeamMember extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'nip_or_nik',
        'role_title',
        'phone_number',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function releaseTasks(): HasMany
    {
        return $this->hasMany(ReleaseTask::class);
    }
}
