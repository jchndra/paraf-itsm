<?php

namespace App\Livewire\ChangeRequests;

use App\Enums\RfcStatus;
use App\Exceptions\InvalidRfcStatusTransitionException;
use App\Models\ChangeRequest;
use App\StateMachines\RfcStatusTransition;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class ReviewCompleteness extends Component
{
    public ChangeRequest $rfc;

    public string $action = RfcStatus::TICKET_CREATED->value;

    public string $note = '';

    public function mount(ChangeRequest $rfc): void
    {
        $this->authorize('reviewCompleteness', $rfc);
        $this->rfc = $rfc;
    }

    public function submitReview(): void
    {
        $this->authorize('reviewCompleteness', $this->rfc);

        $this->validate([
            'action' => 'required|in:'.implode(',', [
                RfcStatus::TICKET_CREATED->value,
                RfcStatus::NEEDS_REVISION->value,
                RfcStatus::REJECTED->value,
            ]),
            'note' => 'required_if:action,'.RfcStatus::NEEDS_REVISION->value.','.RfcStatus::REJECTED->value.'|nullable|string|max:1000',
        ]);

        /** @var \App\Models\User $user */
        $user = Auth::user();

        try {
            DB::transaction(function () use ($user) {
                if ($this->action === RfcStatus::TICKET_CREATED->value) {
                    $this->rfc->update(['rejection_note' => null]);
                    RfcStatusTransition::transitionTo($this->rfc, RfcStatus::TICKET_CREATED, $user);
                } elseif ($this->action === RfcStatus::NEEDS_REVISION->value) {
                    $this->rfc->update(['rejection_note' => $this->note]);
                    RfcStatusTransition::transitionTo($this->rfc, RfcStatus::NEEDS_REVISION, $user, $this->note);
                } else {
                    $this->rfc->update(['rejection_note' => $this->note]);
                    RfcStatusTransition::transitionTo($this->rfc, RfcStatus::REJECTED, $user, $this->note);
                }
            });

            session()->flash(
                'message',
                match ($this->action) {
                    RfcStatus::TICKET_CREATED->value => 'Dokumen RFC dinyatakan lengkap dan diteruskan ke Change Manager.',
                    RfcStatus::NEEDS_REVISION->value => 'Dokumen RFC telah dikembalikan ke Pemohon untuk perbaikan.',
                    default => 'Dokumen RFC telah ditolak.',
                }
            );
        } catch (InvalidRfcStatusTransitionException $exception) {
            $this->addError('status', $exception->getMessage());

            return;
        }

        $this->redirectRoute('change-requests.index');
    }

    public function render()
    {
        return view('livewire.change-requests.review-completeness');
    }
}
