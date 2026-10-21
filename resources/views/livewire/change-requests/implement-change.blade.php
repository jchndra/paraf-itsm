<div class="mx-auto max-w-4xl space-y-6">
    <div class="flex items-center justify-between border-b border-slate-200 pb-4">
        <div>
            <div class="flex items-center space-x-3">
                <a href="{{ route('change-requests.show', $rfc->id) }}"
                    class="text-slate-400 transition hover:text-slate-600" wire:navigate>
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                </a>
                <h1 class="text-2xl font-bold text-slate-900">Catat Hasil Implementasi</h1>
            </div>
            <p class="mt-1 pl-9 text-sm text-slate-500">Pelaksanaan perubahan untuk {{ $rfc->rfc_number }}</p>
        </div>
        <x-status-badge :status="$rfc->status" />
    </div>

    @error('status')
        <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-xs font-medium text-rose-700">
            {{ $message }}</div>
    @enderror

    <form wire:submit="saveImplementation"
        class="space-y-6 rounded-xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
            <div>
                <label for="execution_result" class="mb-1 block text-sm font-semibold text-slate-700">Hasil
                    Pelaksanaan *</label>
                <select id="execution_result" wire:model.live="execution_result"
                    class="w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500">
                    <option value="success">Berhasil</option>
                    <option value="failed">Gagal / Rollback</option>
                </select>
                @error('execution_result')
                    <span class="mt-1 block text-xs text-rose-500">{{ $message }}</span>
                @enderror
            </div>

            <div>
                <label for="evidence_description" class="mb-1 block text-sm font-semibold text-slate-700">Judul
                    Bukti *</label>
                <input id="evidence_description" type="text" wire:model="evidence_description"
                    class="w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500">
                @error('evidence_description')
                    <span class="mt-1 block text-xs text-rose-500">{{ $message }}</span>
                @enderror
            </div>

            <div>
                <label for="actual_start_time" class="mb-1 block text-sm font-semibold text-slate-700">Waktu Mulai
                    *</label>
                <input id="actual_start_time" type="datetime-local" wire:model="actual_start_time"
                    class="w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500">
                @error('actual_start_time')
                    <span class="mt-1 block text-xs text-rose-500">{{ $message }}</span>
                @enderror
            </div>

            <div>
                <label for="actual_end_time" class="mb-1 block text-sm font-semibold text-slate-700">Waktu Selesai
                    *</label>
                <input id="actual_end_time" type="datetime-local" wire:model="actual_end_time"
                    class="w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500">
                @error('actual_end_time')
                    <span class="mt-1 block text-xs text-rose-500">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <div>
            <label for="implementation_notes" class="mb-1 block text-sm font-semibold text-slate-700">Catatan
                Pelaksanaan *</label>
            <textarea id="implementation_notes" wire:model="implementation_notes" rows="3"
                class="w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500"></textarea>
            @error('implementation_notes')
                <span class="mt-1 block text-xs text-rose-500">{{ $message }}</span>
            @enderror
        </div>

        <div>
            <label for="testing_notes" class="mb-1 block text-sm font-semibold text-slate-700">Catatan Pengujian
                *</label>
            <textarea id="testing_notes" wire:model="testing_notes" rows="3"
                class="w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500"></textarea>
            @error('testing_notes')
                <span class="mt-1 block text-xs text-rose-500">{{ $message }}</span>
            @enderror
        </div>

        @if ($execution_result === 'failed')
            <div>
                <label for="rollback_notes" class="mb-1 block text-sm font-semibold text-slate-700">Catatan Rollback
                    *</label>
                <textarea id="rollback_notes" wire:model="rollback_notes" rows="3"
                    class="w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500"></textarea>
                @error('rollback_notes')
                    <span class="mt-1 block text-xs text-rose-500">{{ $message }}</span>
                @enderror
            </div>
        @endif

        <div>
            <label for="evidence_file" class="mb-1 block text-sm font-semibold text-slate-700">Berkas Bukti (PDF,
                Gambar, atau ZIP - Maks. 10MB) *</label>
            <input id="evidence_file" type="file" wire:model="evidence_file" accept=".pdf,.png,.jpg,.jpeg,.zip"
                class="w-full rounded-lg border border-slate-300 px-3.5 py-2 text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500">
            <div wire:loading wire:target="evidence_file" class="mt-1 text-xs text-indigo-600">Mengunggah file...</div>
            @error('evidence_file')
                <span class="mt-1 block text-xs text-rose-500">{{ $message }}</span>
            @enderror
        </div>

        <div class="flex justify-end border-t border-slate-100 pt-4">
            <button type="submit" wire:loading.attr="disabled"
                class="rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-medium text-white shadow-sm transition hover:bg-indigo-700 disabled:opacity-50">
                Simpan Hasil Implementasi
            </button>
        </div>
    </form>
</div>
