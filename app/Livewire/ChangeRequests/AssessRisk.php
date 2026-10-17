<?php

namespace App\Livewire\ChangeRequests;

use App\Enums\RfcStatus;
use App\Models\ChangeRequest;
use App\Models\RiskAssessment;
use App\Models\User;
use App\StateMachines\RfcStatusTransition;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class AssessRisk extends Component
{
    public ChangeRequest $rfc;

    public string $impact_level = 'medium';
    public string $probability_level = 'medium';
    public string $overall_risk_level = 'medium';
    public string $mitigation_plan = '';
    public string $rollback_plan = '';
    public string $testing_plan = '';

    protected array $rules = [
        'impact_level' => 'required|in:low,medium,high',
        'probability_level' => 'required|in:low,medium,high',
        'overall_risk_level' => 'required|in:low,medium,high,emergency',
        'mitigation_plan' => 'required|string|min:10',
        'rollback_plan' => 'required|string|min:10',
        'testing_plan' => 'required|string|min:10',
    ];

    protected array $messages = [
        'impact_level.required' => 'Tingkat dampak sistem wajib dipilih.',
        'probability_level.required' => 'Tingkat kemutakhiran/probabilitas risiko wajib dipilih.',
        'overall_risk_level.required' => 'Tingkat risiko keseluruhan wajib ditentukan.',
        'mitigation_plan.required' => 'Rencana mitigasi risiko wajib diisi.',
        'mitigation_plan.min' => 'Rencana mitigasi minimal terdiri dari 10 karakter.',
        'rollback_plan.required' => 'Rencana pengembalian (rollback plan) wajib diisi.',
        'rollback_plan.min' => 'Rencana rollback minimal terdiri dari 10 karakter.',
        'testing_plan.required' => 'Rencana pengujian (testing plan) wajib diisi.',
        'testing_plan.min' => 'Rencana pengujian minimal terdiri dari 10 karakter.',
    ];

    public function mount(ChangeRequest $rfc): void
    {
        /** @var User|null $user */
        $user = Auth::user();

        if (! $user || ! $user->can('rfc.assess_risk')) {
            abort(403, 'Anda tidak memiliki otorisasi untuk melakukan penilaian risiko RFC.');
        }

        if ($rfc->submitted_by_id === $user->id && ! $user->hasRole('Super Admin')) {
            abort(403, 'Aturan SoD: Anda tidak boleh menilai risiko pada RFC yang Anda ajukan sendiri.');
        }

        $this->rfc = $rfc->load(['application.opd', 'submittedBy', 'riskAssessment']);

        if ($this->rfc->riskAssessment) {
            $assessment = $this->rfc->riskAssessment;
            $this->overall_risk_level = $assessment->risk_level ?? 'medium';
            $this->mitigation_plan = $assessment->mitigation_plan ?? '';
            $this->rollback_plan = $assessment->rollback_plan ?? '';
        }
    }

    public function saveAssessment(): void
    {
        $this->validate();

        /** @var User $user */
        $user = Auth::user();

        $scoreMap = [
            'low' => 1,
            'medium' => 3,
            'high' => 5,
            'emergency' => 5,
        ];

        $impactScore = $scoreMap[$this->impact_level] ?? 3;
        $likelihoodScore = $scoreMap[$this->probability_level] ?? 3;
        $criticalityScore = 3;
        $rollbackReadinessScore = 3;
        $totalScore = $impactScore * $likelihoodScore;

        DB::transaction(function () use ($user, $impactScore, $criticalityScore, $rollbackReadinessScore, $totalScore) {
            RiskAssessment::updateOrCreate(
                ['change_request_id' => $this->rfc->id],
                [
                    'assessed_by_id' => $user->id,
                    'system_criticality_score' => $criticalityScore,
                    'impact_scope_score' => $impactScore,
                    'rollback_readiness_score' => $rollbackReadinessScore,
                    'total_score' => $totalScore,
                    'risk_level' => $this->overall_risk_level,
                    'mitigation_plan' => $this->mitigation_plan,
                    'rollback_plan' => $this->rollback_plan,
                ]
            );

            $this->rfc->update([
                'risk_level' => $this->overall_risk_level,
            ]);

            RfcStatusTransition::transitionTo($this->rfc, RfcStatus::Assessed, $user);
        });

        session()->flash('message', "Penilaian risiko untuk RFC {$this->rfc->rfc_number} berhasil disimpan.");
        $this->redirect(route('change-requests.show', $this->rfc->id), navigate: true);
    }

    public function render(): View
    {
        return view('livewire.change-requests.assess-risk');
    }
}
