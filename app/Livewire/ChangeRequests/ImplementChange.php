<?php

namespace App\Livewire\ChangeRequests;

use App\Enums\RfcStatus;
use App\Models\ChangeRequest;
use App\Models\ImplementationEvidence;
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
class ImplementChange extends Component
{
    use WithFileUploads;

    public ChangeRequest $rfc;

    public string $execution_result = 'success'; // success | failed
    public ?string $actual_start_time = null;
    public ?string $actual_end_time = null;
    public string $implementation_notes = '';
    public string $testing_notes = '';
    public string $rollback_notes = '';

    // Bukti Implementasi / Testing
    /** @var mixed */
    public $evidence_file;
    public string $evidence_description = '';

    protected function rules(): array
    {
        $rules = [
            'execution_result' => 'required|in:success,failed',
            'actual_start_time' => 'required|date',
            'actual_end_time' => 'required|date|after_or_equal:actual_start_time',
            'implementation_notes' => 'required|string|min:10',
            'testing_notes' => 'required|string|min:10',
            'evidence_file' => 'required|file|mimes:pdf,png,jpg,jpeg,zip|max:10240',
            'evidence_description' => 'required|string|max:255',
        ];

        if ($this->execution_result === 'failed') {
            $rules['rollback_notes'] = 'required|string|min:10';
        }

        return $rules;
    }

    protected array $messages = [
        'execution_result.required' => 'Hasil pelaksanaan wajib ditentukan.',
        'actual_start_time.required' => 'Waktu mulai pelaksanaan wajib diisi.',
        'actual_end_time.required' => 'Waktu selesai pelaksanaan wajib diisi.',
        'actual_end_time.after_or_equal' => 'Waktu selesai tidak boleh lebih awal dari waktu mulai.',
        'implementation_notes.required' => 'Catatan pelaksanaan wajib diisi.',
        'implementation_notes.min' => 'Catatan pelaksanaan minimal 10 karakter.',
        'testing_notes.required' => 'Catatan pengujian wajib diisi.',
        'testing_notes.min' => 'Catatan pengujian minimal 10 karakter.',
        'rollback_notes.required' => 'Catatan tindakan rollback wajib diisi jika implementasi gagal.',
        'rollback_notes.min' => 'Catatan rollback minimal 10 karakter.',
        'evidence_file.required' => 'Bukti pengujian/implementasi wajib diunggah.',
        'evidence_file.mimes' => 'Bukti berkas harus berformat PDF, PNG, JPG, JPEG, atau ZIP.',
        'evidence_file.max' => 'Ukuran berkas maksimal 10MB.',
        'evidence_description.required' => 'Deskripsi bukti wajib diisi.',
    ];

    public function mount(ChangeRequest $rfc): void
    {
        /** @var User|null $user */
        $user = Auth::user();

        if (! $user || ! $user->can('rfc.implement')) {
            abort(403, 'Anda tidak memiliki otorisasi untuk mencatat hasil implementasi RFC.');
        }

        $this->rfc = $rfc->load(['application.opd', 'submittedBy']);
        $this->actual_start_time = now()->format('Y-m-d\TH:i');
        $this->actual_end_time = now()->addHours(1)->format('Y-m-d\TH:i');
    }

    public function saveImplementation(): void
    {
        $this->validate();

        /** @var User $user */
        $user = Auth::user();

        $path = $this->evidence_file->store('implementation_evidences', 'public');
        $checksum = hash('sha256', file_get_contents($this->evidence_file->getRealPath()));

        try {
            DB::transaction(function () use ($user, $path, $checksum) {
                // Simpan Bukti Implementasi ke Database
                ImplementationEvidence::create([
                    'change_request_id' => $this->rfc->id,
                    'uploaded_by_id' => $user->id,
                    'title' => $this->evidence_description,
                    'file_path' => $path,
                    'original_filename' => $this->evidence_file->getClientOriginalName(),
                    'mime_type' => $this->evidence_file->getMimeType(),
                    'file_size' => $this->evidence_file->getSize(),
                    'checksum_sha256' => $checksum,
                ]);

                // Update data waktu & catatan di RFC
                $this->rfc->update([
                    'actual_start_time' => $this->actual_start_time,
                    'actual_end_time' => $this->actual_end_time,
                    'implementation_notes' => $this->implementation_notes,
                    'testing_notes' => $this->testing_notes,
                    'rollback_notes' => $this->execution_result === 'failed' ? $this->rollback_notes : null,
                ]);

                // Transisi Status RFC sesuai Enum Project (Successful / RolledBack)
                if ($this->execution_result === 'success') {
                    RfcStatusTransition::transitionTo($this->rfc, RfcStatus::Successful, $user);
                } else {
                    RfcStatusTransition::transitionTo($this->rfc, RfcStatus::RolledBack, $user);
                }
            });
        } catch (\Throwable $e) {
            Storage::disk('public')->delete($path);
            throw $e;
        }

        $message = $this->execution_result === 'success'
            ? "Implementasi RFC {$this->rfc->rfc_number} berhasil dicatat dan diselesaikan."
            : "Implementasi RFC {$this->rfc->rfc_number} dicatat gagal / mengalami rollback.";

        session()->flash('message', $message);
        $this->redirect(route('change-requests.show', $this->rfc->id), navigate: true);
    }

    public function render(): View
    {
        return view('livewire.change-requests.implement-change');
    }
}
