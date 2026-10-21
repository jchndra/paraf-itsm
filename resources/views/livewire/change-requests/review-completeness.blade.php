<div class="max-w-4xl mx-auto space-y-6">
    {{-- Top Bar --}}
    <div class="flex items-center justify-between">
        <a href="{{ route('change-requests.index') }}"
            class="inline-flex items-center text-xs font-semibold text-slate-600 hover:text-blue-900 transition">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Kembali ke Dashboard
        </a>
        <span class="text-xs text-slate-500 font-medium">Pemeriksaan Administrasi (Admin / Frontdesk)</span>
    </div>

    {{-- Detail Singkat Dokumen --}}
    <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 pb-4">
            <div>
                <span
                    class="text-xs font-bold text-blue-900 bg-blue-50 px-2.5 py-1 rounded-md">{{ $rfc->rfc_number }}</span>
                <h2 class="text-lg font-bold text-slate-800 mt-2">{{ $rfc->title }}</h2>
            </div>
            <x-status-badge :status="$rfc->status" />
        </div>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-xs">
            <div>
                <p class="text-slate-400">Pemohon</p>
                <p class="font-semibold text-slate-800 mt-0.5">{{ $rfc->submittedBy?->name ?? '-' }}</p>
            </div>
            <div>
                <p class="text-slate-400">Aplikasi</p>
                <p class="font-semibold text-slate-800 mt-0.5">{{ $rfc->application?->name ?? '-' }}</p>
            </div>
            <div>
                <p class="text-slate-400">OPD / Instansi</p>
                <p class="font-semibold text-slate-800 mt-0.5">{{ $rfc->opd?->name ?? '-' }}</p>
            </div>
            <div>
                <p class="text-slate-400">Tanggal Pengajuan</p>
                <p class="font-semibold text-slate-800 mt-0.5">{{ $rfc->created_at?->format('d M Y, H:i') }} WIB</p>
            </div>
        </div>

        <div class="pt-2 border-t border-slate-100 text-xs">
            <p class="text-slate-400 font-medium">Alasan / Deskripsi Perubahan</p>
            <p class="text-slate-700 mt-1 leading-relaxed">
                {{ $rfc->reason_for_change ?? ($rfc->description ?? 'Tidak ada deskripsi.') }}</p>
        </div>
    </div>

    {{-- Form Verifikasi Admin --}}
    <form wire:submit="submitReview" class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm space-y-6">
        <div>
            <h3 class="text-sm font-bold text-slate-800">Hasil Verifikasi Administrasi</h3>
            <p class="text-xs text-slate-500">Tentukan kelengkapan berkas fisik / digital dan informasi pendukung usulan
                RFC</p>
        </div>

        <div class="space-y-3">
            <label
                class="flex items-center p-3.5 border rounded-xl cursor-pointer transition hover:bg-slate-50 {{ $action === \App\Enums\RfcStatus::TICKET_CREATED->value ? 'border-blue-500 bg-blue-50/30' : 'border-slate-200' }}">
                <input type="radio" wire:model.live="action"
                    value="{{ \App\Enums\RfcStatus::TICKET_CREATED->value }}"
                    class="text-blue-600 focus:ring-blue-500">
                <div class="ml-3">
                    <span class="block text-xs font-bold text-slate-800">Lengkap & Teruskan ke Change Manager</span>
                    <span class="block text-[11px] text-slate-500">Dokumen lengkap, status menjadi sedang ditinjau oleh
                        Change Manager.</span>
                </div>
            </label>

            <label
                class="flex items-center p-3.5 border rounded-xl cursor-pointer transition hover:bg-slate-50 {{ $action === \App\Enums\RfcStatus::NEEDS_REVISION->value ? 'border-amber-500 bg-amber-50/30' : 'border-slate-200' }}">
                <input type="radio" wire:model.live="action"
                    value="{{ \App\Enums\RfcStatus::NEEDS_REVISION->value }}"
                    class="text-amber-600 focus:ring-amber-500">
                <div class="ml-3">
                    <span class="block text-xs font-bold text-amber-900">Perlu Revisi (Kembalikan ke Pemohon)</span>
                    <span class="block text-[11px] text-slate-500">Informasi belum lengkap, meminta Pemohon melakukan
                        perbaikan dokumen.</span>
                </div>
            </label>

            <label
                class="flex items-center p-3.5 border rounded-xl cursor-pointer transition hover:bg-slate-50 {{ $action === \App\Enums\RfcStatus::REJECTED->value ? 'border-rose-500 bg-rose-50/30' : 'border-slate-200' }}">
                <input type="radio" wire:model.live="action"
                    value="{{ \App\Enums\RfcStatus::REJECTED->value }}"
                    class="text-rose-600 focus:ring-rose-500">
                <div class="ml-3">
                    <span class="block text-xs font-bold text-rose-900">Tolak Permohonan</span>
                    <span class="block text-[11px] text-slate-500">Usulan tidak memenuhi ketentuan Manajemen Perubahan
                        Diskominfostandi.</span>
                </div>
            </label>
        </div>

        @error('status')
            <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-xs font-medium text-rose-700">
                {{ $message }}</div>
        @enderror

        @if ($action !== \App\Enums\RfcStatus::TICKET_CREATED->value)
            <div class="space-y-2">
                <label class="block text-xs font-bold text-slate-700">Catatan Masukan / Alasan <span
                        class="text-rose-500">*</span></label>
                <textarea wire:model="note" rows="3" placeholder="Tuliskan alasan pengembalian revisi atau penolakan..."
                    class="w-full text-xs p-3 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:outline-none"></textarea>
                @error('note')
                    <span class="text-xs text-rose-600 font-medium">{{ $message }}</span>
                @enderror
            </div>
        @endif

        <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
            <a href="{{ route('change-requests.index') }}"
                class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-xl transition">
                Batal
            </a>
            <button type="submit"
                class="px-5 py-2 bg-blue-900 hover:bg-blue-800 text-white text-xs font-semibold rounded-xl shadow transition">
                Simpan & Teruskan
            </button>
        </div>
    </form>
</div>
