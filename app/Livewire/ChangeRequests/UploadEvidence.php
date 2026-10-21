<?php

namespace App\Livewire\ChangeRequests;

use App\Models\ChangeRequest;
use App\Models\Evidence;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.app')]
class UploadEvidence extends Component
{
    use WithFileUploads;

    public ChangeRequest $rfc;
    public string $evidence_type = 'deployment_log';
    public string $description = '';

    /** @var mixed */
    public $file = null;

    protected array $rules = [
        'evidence_type' => 'required|in:uat_signoff,deployment_log,test_results,screenshot,other',
        'description' => 'required|string|min:5|max:500',
        'file' => 'required|file|mimes:pdf,png,jpg,jpeg,zip|max:10240',
    ];

    protected array $messages = [
        'evidence_type.required' => 'Jenis bukti implementasi wajib dipilih.',
        'description.required' => 'Deskripsi bukti implementasi wajib diisi.',
        'description.min' => 'Deskripsi minimal terdiri dari 5 karakter.',
        'description.max' => 'Deskripsi maksimal 500 karakter.',
        'file.required' => 'Berkas bukti wajib diunggah.',
        'file.mimes' => 'Format berkas harus PDF, PNG, JPG, JPEG, atau ZIP.',
        'file.max' => 'Ukuran berkas maksimal 10MB.',
    ];

    public function mount(ChangeRequest $rfc): void
    {
        /** @var User|null $user */
        $user = Auth::user();

        if (! $user || ! $user->can('rfc.assign_release')) {
            abort(403, 'Anda tidak memiliki akses untuk mengunggah bukti.');
        }

        $this->rfc = $rfc->load(['application', 'evidences.uploadedBy']);
    }

    public function saveEvidence(): void
    {
        $this->validate();

        /** @var User $user */
        $user = Auth::user();

        $checksum = hash('sha256', file_get_contents($this->file->getRealPath()));
        $path = $this->file->store('evidences', 'public');

        try {
            DB::transaction(function () use ($path, $checksum, $user) {
                Evidence::create([
                    'change_request_id' => $this->rfc->id,
                    'uploaded_by_id' => $user->id,
                    'evidence_type' => $this->evidence_type,
                    'description' => $this->description,
                    'file_path' => $path,
                    'original_filename' => $this->file->getClientOriginalName(),
                    'mime_type' => $this->file->getMimeType(),
                    'file_size' => $this->file->getSize(),
                    'checksum_sha256' => $checksum,
                ]);
            });
        } catch (\Throwable $e) {
            Storage::disk('public')->delete($path);
            throw $e;
        }

        session()->flash('message', "Bukti implementasi berhasil diunggah.");
        $this->redirect(route('change-requests.show', $this->rfc->id), navigate: true);
    }

    public function render(): View
    {
        return view('livewire.change-requests.upload-evidence');
    }
}
