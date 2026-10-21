<?php

namespace App\Livewire\ChangeRequests;

use App\Enums\DecisionStatus;
use App\Enums\RfcStatus;
use App\Exceptions\InvalidRfcStatusTransitionException;
use App\Models\Authority;
use App\Models\ChangeRequest;
use App\Models\Decision;
use App\StateMachines\RfcStatusTransition;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class RecordDecision extends Component
{
    public ChangeRequest $rfc;

    public string $decisionStatus = DecisionStatus::APPROVED->value;

    public ?int $authorityId = null;

    public string $notes = '';

    public function mount(ChangeRequest $rfc): void
    {
        $this->authorize('recordDecision', $rfc);
        $this->rfc = $rfc->load(['submittedBy', 'application.opd', 'riskAssessment']);

        $defaultAuthority = Authority::where('is_active', true)->first();
        if ($defaultAuthority) {
            $this->authorityId = $defaultAuthority->id;
        }
    }

    public function saveDecision(): void
    {
        $this->authorize('recordDecision', $this->rfc);

        $this->validate([
            'decisionStatus' => 'required|in:'.implode(',', DecisionStatus::values()),
            'authorityId' => 'required|exists:authorities,id',
            'notes' => 'required|string|max:1000',
        ]);

        if (! $this->rfc->riskAssessment) {
            $this->addError('decisionStatus', 'Keputusan tidak dapat direkam sebelum analisis risiko dilakukan.');

            return;
        }

        /** @var \App\Models\User $user */
        $user = Auth::user();

        $authority = Authority::where('is_active', true)->findOrFail($this->authorityId);
        $decisionStatus = DecisionStatus::from($this->decisionStatus);

        $targetStatus = match ($decisionStatus) {
            DecisionStatus::APPROVED => RfcStatus::APPROVED,
            DecisionStatus::REJECTED => RfcStatus::REJECTED,
            DecisionStatus::RETURNED_TO_ADMIN => RfcStatus::RETURNED_TO_ADMIN,
        };

        try {
            DB::transaction(function () use ($authority, $decisionStatus, $targetStatus, $user) {
                Decision::create([
                    'change_request_id' => $this->rfc->id,
                    'authority_id' => $authority->id,
                    'recorded_by_id' => $user->id,
                    'snapshot_authority_name' => $authority->name ?? $authority->nama ?? null,
                    'snapshot_authority_nip' => $authority->nip ?? null,
                    'snapshot_authority_position' => $authority->position ?? $authority->jabatan ?? null,
                    'decision_status' => $decisionStatus->value,
                    'decision_date' => now(),
                    'reason' => $this->notes,
                ]);

                RfcStatusTransition::transitionTo($this->rfc, $targetStatus, $user, $this->notes);
            });
        } catch (InvalidRfcStatusTransitionException $exception) {
            $this->addError('status', $exception->getMessage());

            return;
        }

        session()->flash('message', 'Keputusan rapat CAB / ECAB berhasil dicatat.');
        $this->redirectRoute('change-requests.index');
    }

    public function render()
    {
        $authorities = Authority::where('is_active', true)->get();

        return view('livewire.change-requests.record-decision', [
            'authorities' => $authorities,
        ]);
    }
}
