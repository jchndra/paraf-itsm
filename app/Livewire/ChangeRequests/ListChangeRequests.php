<?php

namespace App\Livewire\ChangeRequests;

use App\Models\Application;
use App\Models\ChangeRequest;
use App\Models\Opd;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class ListChangeRequests extends Component
{
    use WithPagination;

    public string $search = '';
    public string $statusFilter = '';
    public string $opdFilter = '';
    public string $changeTypeFilter = '';

    protected $queryString = ['search', 'statusFilter', 'opdFilter', 'changeTypeFilter'];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function render(): View
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $query = ChangeRequest::with(['application.opd', 'submittedBy'])
            ->latest();

        // Pemohon HANYA melihat RFC miliknya sendiri
        if (! $user->can('rfc.view_all')) {
            $query->where('submitted_by_id', $user->id);
        }

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('rfc_number', 'like', "%{$this->search}%")
                    ->orWhere('title', 'like', "%{$this->search}%");
            });
        }

        if ($this->statusFilter) {
            $query->where('status', $this->statusFilter);
        }

        if ($this->changeTypeFilter) {
            $query->where('change_type', $this->changeTypeFilter);
        }

        if ($this->opdFilter) {
            $query->whereHas('application', function ($q) {
                $q->where('opd_id', $this->opdFilter);
            });
        }

        return view('livewire.change-requests.list-change-requests', [
            'changeRequests' => $query->paginate(15),
            'opds' => Opd::where('is_active', true)->get(),
        ])->layout('layouts.app');
    }
}
