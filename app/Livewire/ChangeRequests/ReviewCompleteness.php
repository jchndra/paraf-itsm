<?php

namespace App\Livewire\ChangeRequests;

use App\Enums\RfcStatus;
use App\Models\ChangeRequest;
use App\Models\User;
use App\Notifications\RfcStatusChangedNotification;
use App\StateMachines\RfcStatusTransition;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class ReviewCompleteness extends Component
{
    public ChangeRequest $rfc;
    public string $rejectionNote = '';

    public function mount(ChangeRequest $rfc): void
    {
        /** @var User $user */
        $user = Auth::user();

        if (! $user->can('rfc.review_completeness')) {
            abort(403, 'Hanya Admin/Frontdesk yang dapat melakukan verifikasi kelengkapan.');
        }

        $this->rfc = $rfc->load(['application.opd', 'submittedBy', 'createdBy']);
    }

    public function markAsComplete(): void
    {
        /** @var User $user */
        $user = Auth::user();

        $prevStatus = $this->rfc->status;

        RfcStatusTransition::transitionTo($this->rfc, RfcStatus::Complete, $user);

        if ($this->rfc->submittedBy) {
            $this->rfc->submittedBy->notify(new RfcStatusChangedNotification(
                $this->rfc,
                $prevStatus,
                RfcStatus::Complete->value ?? (string) RfcStatus::Complete,
                'Dokumen dan data RFC dinyatakan lengkap oleh Admin.'
            ));
        }

        session()->flash('message', "RFC {$this->rfc->rfc_number} berhasil diverifikasi LENGKAP dan diteruskan ke Change Manager.");
        $this->redirect(route('change-requests.show', $this->rfc->id), navigate: true);
    }

    public function requestRevision(): void
    {
        $this->validate([
            'rejectionNote' => 'required|string|min:10',
        ], [
            'rejectionNote.required' => 'Catatan kekurangan berkas/revisi wajib diisi.',
            'rejectionNote.min' => 'Catatan revisi minimal terdiri dari 10 karakter.',
        ]);

        /** @var User $user */
        $user = Auth::user();

        $prevStatus = $this->rfc->status;

        $this->rfc->rejection_note = $this->rejectionNote;
        $this->rfc->save();

        RfcStatusTransition::transitionTo($this->rfc, RfcStatus::RevisionRequired, $user);

        if ($this->rfc->submittedBy) {
            $this->rfc->submittedBy->notify(new RfcStatusChangedNotification(
                $this->rfc,
                $prevStatus,
                RfcStatus::RevisionRequired->value ?? (string) RfcStatus::RevisionRequired,
                $this->rejectionNote
            ));
        }

        session()->flash('message', "Permintaan perbaikan data untuk RFC {$this->rfc->rfc_number} telah dikirimkan ke pemohon.");
        $this->redirect(route('change-requests.show', $this->rfc->id), navigate: true);
    }

    public function render(): View
    {
        return view('livewire.change-requests.review-completeness');
    }
}
