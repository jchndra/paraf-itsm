<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RiskAssessment extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function changeRequest()
    {
        return $this->belongsTo(ChangeRequest::class);
    }

    public function assessedBy()
    {
        return $this->belongsTo(User::class, 'assessed_by_id');
    }
}
