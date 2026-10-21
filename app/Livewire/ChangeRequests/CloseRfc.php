<?php

namespace App\Livewire\ChangeRequests;

use App\Enums\RfcStatus;
use App\Exceptions\InvalidRfcStatusTransitionException;
use App\Models\ChangeRequest;
use App\Models\User;
use App\StateMachines\RfcStatusTransition;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class CloseRfc extends Component
{
    public ChangeRequest $rfc;

    public string $closure_notes = '';

    protected array $rules = [
        'closure_notes' => 'required|string|min:10|max:1000',
    ];

    protected array $messages = [
        'closure_notes.required' => 'Catatan penutupan tiket wajib diisi.',
        'closure_notes.min' => 'Catatan penutupan minimal 10 karakter.',
        'closure_notes.max' => 'Catatan penutupan maksimal 1000 karakter.',
    ];

    public function mount(ChangeRequest $rfc): void
    {
        /** @var User|null $user */
        $user = Auth::user();

        if (! $user || ! $user->can('rfc.close')) {
            // Bypass otorisasi penutupan tiket jika akun Super Admin
            if (! $user?->hasRole('Super Admin')) {
                abort(403, 'Anda tidak memiliki otorisasi untuk menutup tiket RFC ini.');
            }
        }

        $this->rfc = $rfc;
    }

    public function closeTicket(): void
    {
        $this->validate();

        /** @var User $user */
        $user = Auth::user();

        try {
            DB::transaction(function () use ($user) {
                RfcStatusTransition::transitionTo($this->rfc, RfcStatus::CLOSED, $user, $this->closure_notes);
                $this->rfc->update(['closed_at' => now()]);
            });
        } catch (InvalidRfcStatusTransitionException $exception) {
            $this->addError('status', $exception->getMessage());

            return;
        }

        session()->flash('message', "Tiket RFC {$this->rfc->rfc_number} resmi ditutup (Closed).");
        $this->redirect(route('change-requests.show', $this->rfc->id), navigate: true);
    }

    public function render(): View
    {
        return view('livewire.change-requests.close-rfc');
    }
}
