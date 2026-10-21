<div class="max-w-4xl mx-auto space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between border-b border-slate-200 pb-4">
        <div>
            <div class="flex items-center space-x-3">
                <a href="{{ route('change-requests.show', $rfc->id) }}"
                    class="text-slate-400 hover:text-slate-600 transition" wire:navigate>
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                </a>
                <h1 class="text-2xl font-bold text-slate-900">Unggah Bukti Implementasi Rilis</h1>
            </div>
            <p class="text-sm text-slate-500 mt-1 pl-9">Dokumentasi Hasil Pengujian, Log Deployment, dan UAT untuk
                {{ $rfc->rfc_number }}</p>
        </div>
        <a href="{{ route('change-requests.show', $rfc->id) }}"
            class="px-4 py-2 border border-slate-300 rounded-lg text-sm font-medium text-slate-700 hover:bg-slate-50 transition"
            wire:navigate>
            Batal
        </a>
    </div>

    <!-- Form Container -->
    <form wire:submit.prevent="saveEvidence"
        class="bg-white p-6 sm:p-8 rounded-xl border border-slate-200 shadow-sm space-y-6">

        <!-- Jenis Bukti -->
        <div>
            <label for="evidence_type" class="block text-sm font-semibold text-slate-700 mb-1">Jenis Bukti Implementasi
                *</label>
            <select id="evidence_type" wire:model="evidence_type"
                class="w-full text-sm px-3.5 py-2.5 border rounded-lg border-slate-300 focus:ring-2 focus:ring-indigo-500">
                <option value="uat_signoff">Berita Acara / Sign-off UAT</option>
                <option value="deployment_log">Log Server / Log Deployment</option>
                <option value="test_results">Laporan Hasil Pengujian (Pest/Manual)</option>
                <option value="screenshot">Tangkapan Layar (Screenshot Feature)</option>
                <option value="other">Dokumen Pendukung Lainnya</option>
            </select>
            @error('evidence_type')
                <span class="text-xs text-red-500 block mt-1">{{ $message }}</span>
            @enderror
        </div>

        <!-- Deskripsi Bukti -->
        <div>
            <label for="description" class="block text-sm font-semibold text-slate-700 mb-1">Deskripsi & Catatan
                Implementasi *</label>
            <textarea id="description" wire:model="description" rows="3"
                placeholder="Jelaskan ringkasan perubahan yang telah berhasil dipasang di server production..."
                class="w-full text-sm px-3.5 py-2.5 border rounded-lg border-slate-300 focus:ring-2 focus:ring-indigo-500"></textarea>
            @error('description')
                <span class="text-xs text-red-500 block mt-1">{{ $message }}</span>
            @enderror
        </div>

        <!-- Unggah File -->
        <div>
            <label for="file" class="block text-sm font-semibold text-slate-700 mb-1">Berkas Bukti (PDF, Gambar,
                atau ZIP - Maks. 10MB) *</label>
            <input type="file" id="file" wire:model="file" accept=".pdf,.png,.jpg,.jpeg,.zip"
                class="w-full text-sm px-3.5 py-2 border rounded-lg border-slate-300 focus:ring-2 focus:ring-indigo-500 file:mr-4 file:py-1.5 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700">
            <div wire:loading wire:target="file" class="text-xs text-indigo-600 mt-1">Mengunggah file...</div>
            @error('file')
                <span class="text-xs text-red-500 block mt-1">{{ $message }}</span>
            @enderror
        </div>

        <!-- Submit Button -->
        <div class="flex justify-end pt-4 border-t border-slate-100">
            <button type="submit" wire:loading.attr="disabled"
                class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 text-white font-medium rounded-lg text-sm shadow-sm transition">
                Simpan Bukti Implementasi
            </button>
        </div>
    </form>
</div>
