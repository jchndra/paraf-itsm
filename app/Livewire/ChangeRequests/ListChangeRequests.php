<?php

namespace App\Livewire\ChangeRequests;

use App\Enums\RfcStatus;
use App\Models\ChangeRequest;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class ListChangeRequests extends Component
{
    use WithPagination;

    public ?ChangeRequest $selectedRfc = null;

    public string $activeTab = 'informasi';

    public string $search = '';

    public string $statusFilter = '';

    public string $categoryFilter = '';

    public function mount(?ChangeRequest $rfc = null): void
    {
        $this->search = (string) request()->query('q', '');

        if ($rfc && $rfc->exists) {
            $this->authorize('view', $rfc);
            $this->selectedRfc = $rfc->load([
                'submittedBy',
                'application',
                'riskAssessment',
                'decisions',
                'releaseTasks',
                'evidences',
                'comments.user',
            ]);
        }
    }

    public function setTab(string $tab): void
    {
        $this->activeTab = $tab;
    }

    public function render()
    {
        /** @var User $user */
        $user = Auth::user();

        // Jika rute mengakses detail RFC ($selectedRfc terisi)
        if ($this->selectedRfc) {
            return view('livewire.change-requests.show-change-request', [
                'rfc' => $this->selectedRfc,
            ]);
        }

        // 1. Tampilan Dashboard Pemohon (Hanya memiliki role Pemohon)
        if ($user->hasRole('Pemohon') && ! $user->hasRole('Admin') && ! $user->hasRole('Change Manager') && ! $user->hasRole('Super Admin')) {
            $baseQuery = ChangeRequest::query()->where('submitted_by_id', $user->id);

            $stats = [
                'my_rfc' => (clone $baseQuery)->count(),
                'in_process' => (clone $baseQuery)->whereIn('status', RfcStatus::valuesForGroup('in_process'))->count(),
                'needs_revision' => (clone $baseQuery)->whereIn('status', RfcStatus::valuesForGroup('needs_revision'))->count(),
                'approved' => (clone $baseQuery)->whereIn('status', RfcStatus::valuesForGroup('approved'))->count(),
                'rejected' => (clone $baseQuery)->whereIn('status', RfcStatus::valuesForGroup('rejected'))->count(),
            ];

            $rfcs = (clone $baseQuery)
                ->with('riskAssessment')
                ->when($this->search, fn ($q) => $q->where(fn ($sub) => $sub
                    ->where('title', 'like', "%{$this->search}%")
                    ->orWhere('rfc_number', 'like', "%{$this->search}%")))
                ->latest()
                ->paginate(10);

            return view('livewire.change-requests.list-change-requests', [
                'roleView' => 'pemohon',
                'stats' => $stats,
                'rfcs' => $rfcs,
                'notifications' => $user->unreadNotifications()->take(5)->get(),
            ]);
        }

        // 2. Tampilan Dashboard Admin / Frontdesk (Prioritas Admin murni)
        if ($user->hasRole('Admin') && ! $user->hasRole('Change Manager')) {
            $stats = [
                'incoming' => ChangeRequest::whereIn('status', [
                    RfcStatus::SUBMITTED->value,
                    RfcStatus::RETURNED_TO_ADMIN->value,
                ])->count(),
                'needs_revision' => ChangeRequest::whereIn('status', RfcStatus::valuesForGroup('needs_revision'))->count(),
                'forwarded' => ChangeRequest::whereIn('status', [
                    RfcStatus::TICKET_CREATED->value,
                    ...RfcStatus::valuesForGroup('approved'),
                ])->count(),
                'rejected' => ChangeRequest::whereIn('status', RfcStatus::valuesForGroup('rejected'))->count(),
            ];

            $rfcs = ChangeRequest::query()
                ->with('riskAssessment')
                ->when($this->search, fn ($q) => $q->where(fn ($sub) => $sub
                    ->where('title', 'like', "%{$this->search}%")
                    ->orWhere('rfc_number', 'like', "%{$this->search}%")))
                ->when($this->statusFilter, fn ($q) => $q->where('status', $this->statusFilter))
                ->latest()
                ->paginate(10);

            return view('livewire.change-requests.list-change-requests', [
                'roleView' => 'admin',
                'stats' => $stats,
                'rfcs' => $rfcs,
                'notifications' => $user->unreadNotifications()->take(5)->get(),
            ]);
        }

        // 3. Tampilan Dashboard Change Manager / Super Admin
        $stats = [
            'total' => ChangeRequest::count(),
            'pending_approval' => ChangeRequest::whereIn('status', [
                RfcStatus::TICKET_CREATED->value,
            ])->count(),
            'approved' => ChangeRequest::whereIn('status', RfcStatus::valuesForGroup('approved'))->count(),
            'rejected' => ChangeRequest::whereIn('status', RfcStatus::valuesForGroup('rejected'))->count(),
            'emergency' => ChangeRequest::where('change_type', 'emergency')->count(),
        ];

        $rfcs = ChangeRequest::query()
            ->with('riskAssessment')
            ->when($this->search, fn ($q) => $q->where(fn ($sub) => $sub
                ->where('title', 'like', "%{$this->search}%")
                ->orWhere('rfc_number', 'like', "%{$this->search}%")))
            ->when($this->statusFilter, fn ($q) => $q->where('status', $this->statusFilter))
            ->when($this->categoryFilter, fn ($q) => $q->where('change_type', $this->categoryFilter))
            ->latest()
            ->paginate(10);

        return view('livewire.change-requests.list-change-requests', [
            'roleView' => 'change_manager',
            'stats' => $stats,
            'rfcs' => $rfcs,
            'notifications' => $user->unreadNotifications()->take(5)->get(),
        ]);
    }
}
