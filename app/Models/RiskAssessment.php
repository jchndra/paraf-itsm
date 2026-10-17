<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RiskAssessment extends Model
{
    protected $fillable = [
        'change_request_id',
        'assessed_by_id',
        'system_criticality_score',
        'impact_scope_score',
        'impact_score',
        'likelihood_score',
        'rollback_readiness_score',
        'total_score',
        'risk_level',
        'impact_level',
        'probability_level',
        'overall_risk_level',
        'mitigation_plan',
        'rollback_plan',
        'testing_plan',
    ];

    public function changeRequest(): BelongsTo
    {
        return $this->belongsTo(ChangeRequest::class);
    }

    public function assessedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assessed_by_id');
    }
}
