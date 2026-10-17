<?php

namespace App\Livewire\ChangeRequests;

use App\Enums\RfcStatus;
use App\Models\ChangeRequest;
use App\Models\RiskAssessment;
use App\Models\User;
use App\StateMachines\RfcStatusTransition;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

class AssessRisk extends Component
{
    public ChangeRequest $rfc;

    // Skor Numerik
    public int $systemCriticalityScore = 3;
    public int $impactScopeScore = 3;
    public int $rollbackReadinessScore = 2;

    // Tingkat Risiko (Low, Medium, High, Critical)
    public string $riskLevel = 'medium';
    public string $mitigationPlan = '';
    public string $rollbackPlan = '';

    protected function rules(): array
    {
        return [
            'systemCriticalityScore' => 'required|integer|min:1|max:5',
            'impactScopeScore' => 'required|integer|min:1|max:5',
            'rollbackReadinessScore' => 'required|integer|min:1|max:5',
            'riskLevel' => 'required|in:low,medium,high,critical',
            'mitigationPlan' => 'required|string|min:10',
            'rollbackPlan' => 'required|string|min:10',
        ];
    }

    public function mount(ChangeRequest $rfc): void
    {
        /** @var User $user */
        $user = Auth::user();

        if (! $user->can('rfc.assess_risk')) {
            abort(403, 'Anda tidak memiliki hak akses untuk melakukan analisis risiko.');
        }

        $this->rfc = $rfc;

        if ($rfc->riskAssessment) {
            $this->systemCriticalityScore = $rfc->riskAssessment->system_criticality_score ?? 3;
            $this->impactScopeScore = $rfc->riskAssessment->impact_scope_score ?? 3;
            $this->rollbackReadinessScore = $rfc->riskAssessment->rollback_readiness_score ?? 2;
            $this->riskLevel = $rfc->riskAssessment->risk_level ?? 'medium';
            $this->mitigationPlan = $rfc->riskAssessment->mitigation_plan ?? '';
            $this->rollbackPlan = $rfc->riskAssessment->rollback_plan ?? '';
        }
    }

    public function saveRiskAssessment(): void
    {
        $this->validate();

        /** @var User $user */
        $user = Auth::user();

        $totalScore = $this->systemCriticalityScore + $this->impactScopeScore + $this->rollbackReadinessScore;

        RiskAssessment::updateOrCreate(
            ['change_request_id' => $this->rfc->id],
            [
                'assessed_by_id' => $user->id,
                'system_criticality_score' => $this->systemCriticalityScore,
                'impact_scope_score' => $this->impactScopeScore,
                'rollback_readiness_score' => $this->rollbackReadinessScore,
                'total_score' => $totalScore,
                'risk_level' => $this->riskLevel,
                'mitigation_plan' => $this->mitigationPlan,
                'rollback_plan' => $this->rollbackPlan,
            ]
        );

        if ($this->rfc->status === RfcStatus::Complete->value) {
            RfcStatusTransition::transitionTo($this->rfc, RfcStatus::Assessed, $user);
        }

        session()->flash('message', 'Analisis risiko berhasil disimpan.');
        $this->redirect(route('change-requests.show', $this->rfc->id));
    }

    #[Layout('layouts.app')]
    public function render(): View
    {
        return view('livewire.change-requests.assess-risk');
    }
}
