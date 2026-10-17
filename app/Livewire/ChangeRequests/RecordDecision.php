<?php

namespace App\Livewire\ChangeRequests;

use App\Enums\RfcStatus;
use App\Models\AuthorizationDocument;
use App\Models\Authority;
use App\Models\ChangeRequest;
use App\Models\Decision;
use App\Models\User;
use App\StateMachines\RfcStatusTransition;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.app')]
class RecordDecision extends Component
{
    use WithFileUploads;

    public ChangeRequest $rfc;
    public ?int $authorityId = null;
    public string $decisionStatus = 'approved';
    public string $documentType = 'cab_minutes';
    public string $notes = '';

    // File Otorisasi
    public $documentFile;
    public string $documentNumber = '';

    protected function rules(): array
    {
        return [
            'authorityId' => 'required|exists:authorities,id',
            'decisionStatus' => 'required|in:approved,rejected,revision_requested',
            'documentType' => 'required|in:cab_minutes,ecab_approval,delegation_letter,emergency_confirmation',
            'notes' => 'required|string|min:5',
            'documentNumber' => 'required|string|max:100',
            'documentFile' => 'required|file|mimes:pdf|max:10240',
        ];
    }

    protected array $messages = [
        'authorityId.required' => 'Silakan pilih pejabat berwenang yang menyetujui.',
        'decisionStatus.required' => 'Keputusan otorisasi wajib dipilih.',
        'documentType.required' => 'Jenis dokumen otorisasi wajib dipilih.',
        'notes.required' => 'Catatan keputusan wajib diisi.',
        'notes.min' => 'Catatan keputusan minimal 5 karakter.',
        'documentNumber.required' => 'Nomor dokumen/surat otorisasi wajib diisi.',
        'documentFile.required' => 'Dokumen otorisasi berformat PDF wajib diunggah.',
        'documentFile.mimes' => 'Dokumen otorisasi harus berupa berkas PDF.',
        'documentFile.max' => 'Ukuran berkas dokumen maksimal 10MB.',
    ];

    public function mount(ChangeRequest $rfc): void
    {
        /** @var User|null $user */
        $user = Auth::user();

        if (! $user || ! $user->can('rfc.record_decision')) {
            abort(403, 'Anda tidak memiliki otoritas untuk mencatat keputusan RFC.');
        }

        // Aturan Pemisahan Tugas (Segregation of Duties) - Pengecualian untuk Super Admin saat testing
        if ($rfc->submitted_by_id === $user->id && ! $user->hasRole('Super Admin')) {
            abort(403, 'Aturan SoD: Anda tidak boleh memutuskan RFC yang Anda ajukan sendiri.');
        }

        $this->rfc = $rfc->load(['application.opd', 'submittedBy']);
    }

    public function saveDecision(): void
    {
        $this->validate();

        /** @var User $user */
        $user = Auth::user();

        $authority = Authority::where('is_active', true)->findOrFail($this->authorityId);

        [$decisionStatus, $targetStatus] = match ($this->decisionStatus) {
            'approved' => ['approved', RfcStatus::Approved],
            'rejected' => ['rejected', RfcStatus::Rejected],
            'revision_requested' => ['revision_required', RfcStatus::RevisionRequested],
        };

        $checksum = hash('sha256', file_get_contents($this->documentFile->getRealPath()));
        $path = $this->documentFile->store('authorization_docs', 'public');

        try {
            DB::transaction(function () use ($authority, $decisionStatus, $targetStatus, $checksum, $path, $user) {
                $decision = Decision::create([
                    'change_request_id' => $this->rfc->id,
                    'authority_id' => $authority->id,
                    'recorded_by_id' => $user->id,
                    'snapshot_authority_name' => $authority->name,
                    'snapshot_authority_nip' => $authority->nip,
                    'snapshot_authority_position' => $authority->position,
                    'decision_status' => $decisionStatus,
                    'decision_date' => now(),
                    'reason' => "No. Dokumen: {$this->documentNumber} | Catatan: {$this->notes}",
                ]);

                AuthorizationDocument::create([
                    'change_request_id' => $this->rfc->id,
                    'decision_id' => $decision->id,
                    'uploaded_by_id' => $user->id,
                    'document_type' => $this->documentType,
                    'file_path' => $path,
                    'original_filename' => $this->documentFile->getClientOriginalName(),
                    'mime_type' => $this->documentFile->getMimeType(),
                    'file_size' => $this->documentFile->getSize(),
                    'checksum_sha256' => $checksum,
                ]);

                RfcStatusTransition::transitionTo($this->rfc, $targetStatus, $user);
            });
        } catch (\Throwable $e) {
            Storage::disk('public')->delete($path);
            throw $e;
        }

        session()->flash('message', "Keputusan berhasil dicatat. Status RFC diperbarui menjadi: " . (method_exists($targetStatus, 'label') ? $targetStatus->label() : $targetStatus->value));
        $this->redirect(route('change-requests.show', $this->rfc->id), navigate: true);
    }

    public function render(): View
    {
        return view('livewire.change-requests.record-decision', [
            'authorities' => Authority::where('is_active', true)->get(),
        ]);
    }
}
