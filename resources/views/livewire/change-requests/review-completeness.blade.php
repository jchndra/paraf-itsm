<div class="space-y-6">
    <!-- Header & Navigation -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-slate-200 pb-4">
        <div>
            <div class="flex items-center space-x-3">
                <a href="{{ route('change-requests.index') }}" class="text-slate-400 hover:text-slate-600 transition"
                    wire:navigate>
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                </a>
                <h1 class="text-2xl font-bold text-slate-900">{{ $rfc->rfc_number }}</h1>

                {{-- Badge Status --}}
                @php
                    $statusVal = is_object($rfc->status) ? $rfc->status->value ?? $rfc->status->name : $rfc->status;
                    $statusClasses = [
                        'draft' => 'bg-slate-100 text-slate-700 border-slate-300',
                        'submitted' => 'bg-blue-100 text-blue-800 border-blue-200',
                        'complete' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                        'approved' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                        'rejected' => 'bg-red-100 text-red-800 border-red-200',
                        'revision_required' => 'bg-amber-100 text-amber-800 border-amber-200',
                        'revision_requested' => 'bg-amber-100 text-amber-800 border-amber-200',
                    ];
                @endphp
                <span
                    class="px-3 py-1 rounded-full text-xs font-semibold border {{ $statusClasses[$statusVal] ?? 'bg-slate-100 text-slate-700 border-slate-200' }}">
                    {{ is_object($rfc->status) && method_exists($rfc->status, 'label') ? $rfc->status->label() : ucfirst(str_replace('_', ' ', $statusVal)) }}
                </span>
            </div>
            <p class="text-sm text-slate-500 mt-1 pl-9">{{ $rfc->title }}</p>
        </div>

        <div class="flex items-center space-x-2 pl-9 sm:pl-0">
            @if (auth()->user()->hasRole(['Change Manager', 'Super Admin']) && in_array($statusVal, ['complete', 'reviewed', 'submitted']))
                <a href="{{ route('change-requests.decision', $rfc->id) }}"
                    class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-medium shadow-sm transition"
                    wire:navigate>
                    Catat Keputusan
                </a>
            @endif

            <a href="{{ route('change-requests.index') }}"
                class="px-4 py-2 border border-slate-300 rounded-lg text-sm font-medium text-slate-700 hover:bg-slate-50 transition"
                wire:navigate>
                Kembali ke Daftar
            </a>
        </div>
    </div>

    {{-- Flash Notifications --}}
    @if (session()->has('message'))
        <div
            class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-lg text-sm flex items-center justify-between">
            <span>{{ session('message') }}</span>
        </div>
    @endif

    @if (session()->has('error'))
        <div
            class="p-4 bg-red-50 border border-red-200 text-red-800 rounded-lg text-sm flex items-center justify-between">
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <!-- Main Content Layout -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Left 2 Columns: RFC Details -->
        <div class="lg:col-span-2 space-y-6">

            <!-- Detail Informasi RFC -->
            <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm space-y-4">
                <h2 class="text-lg font-bold text-slate-800 border-b border-slate-100 pb-3">Ringkasan Permohonan</h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                    <div>
                        <span class="block text-xs font-semibold uppercase tracking-wider text-slate-400">Aplikasi /
                            Sistem</span>
                        <span class="font-medium text-slate-800">{{ $rfc->application->name ?? '-' }}</span>
                        <span class="text-xs text-slate-500 block">Kode: {{ $rfc->application->code ?? '-' }}</span>
                    </div>

                    <div>
                        <span class="block text-xs font-semibold uppercase tracking-wider text-slate-400">OPD
                            Pengaju</span>
                        <span class="font-medium text-slate-800">{{ $rfc->application->opd->name ?? '-' }}</span>
                    </div>

                    <div>
                        <span class="block text-xs font-semibold uppercase tracking-wider text-slate-400">Pemohon
                            (Submitted By)</span>
                        <span class="font-medium text-slate-800">{{ $rfc->submittedBy->name ?? '-' }}</span>
                        <span class="text-xs text-slate-500 block">{{ $rfc->submittedBy->email ?? '-' }}</span>
                    </div>

                    <div>
                        <span class="block text-xs font-semibold uppercase tracking-wider text-slate-400">Target
                            Pelaksanaan</span>
                        <span class="font-medium text-slate-800">
                            {{ $rfc->target_implementation_date ? \Carbon\Carbon::parse($rfc->target_implementation_date)->translatedFormat('d F Y, H:i') : '-' }}
                            WIB
                        </span>
                    </div>
                </div>

                {{-- Status Berkas Fisik --}}
                <div class="pt-3 border-t border-slate-100">
                    <span class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1">Metode
                        Pengajuan & Surat</span>
                    @if ($rfc->is_physical_letter)
                        <div
                            class="inline-flex items-center space-x-2 px-3 py-1 bg-amber-50 text-amber-800 border border-amber-200 rounded-lg text-xs font-medium">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <span>Dengan Surat Fisik / Nota Dinas</span>
                        </div>
                        <p class="text-xs text-slate-600 mt-2">Nomor Surat: <strong
                                class="text-slate-800">{{ $rfc->physical_letter_number ?? 'Belum diisi' }}</strong></p>
                    @else
                        <div
                            class="inline-flex items-center space-x-2 px-3 py-1 bg-slate-50 text-slate-700 border border-slate-200 rounded-lg text-xs font-medium">
                            <span>Pengajuan Digital (Tanpa Surat Fisik)</span>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Analisis Perubahan & Dampak -->
            <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm space-y-4">
                <h2 class="text-lg font-bold text-slate-800 border-b border-slate-100 pb-3">Deskripsi & Dampak Perubahan
                </h2>

                <div>
                    <h3 class="text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1">Alasan Perubahan
                        (Reason for Change)</h3>
                    <p
                        class="text-sm text-slate-700 leading-relaxed bg-slate-50 p-3 rounded-lg border border-slate-100">
                        {{ $rfc->reason_for_change ?? 'Tidak ada alasan khusus yang dicantumkan.' }}
                    </p>
                </div>

                <div>
                    <h3 class="text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1">Estimasi Dampak
                        Sistem (Impact Description)</h3>
                    <p
                        class="text-sm text-slate-700 leading-relaxed bg-slate-50 p-3 rounded-lg border border-slate-100">
                        {{ $rfc->impact_description ?? 'Tidak ada estimasi dampak yang dicantumkan.' }}
                    </p>
                </div>

                @if (!empty($rfc->rejection_note))
                    <div class="pt-2">
                        <h3 class="text-xs font-semibold uppercase tracking-wider text-amber-600 mb-1">Catatan Revisi
                            Terakhir</h3>
                        <p
                            class="text-sm text-amber-900 leading-relaxed bg-amber-50 p-3 rounded-lg border border-amber-200">
                            {{ $rfc->rejection_note }}
                        </p>
                    </div>
                @endif
            </div>

        </div>

        <!-- Right Column: Verification Action Panel -->
        <div class="space-y-6">

            <!-- Panel Khusus Admin / Frontdesk -->
            @if (auth()->user()->can('rfc.review_completeness'))
                <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm space-y-4">
                    <h2 class="text-lg font-bold text-slate-800 border-b border-slate-100 pb-3">Verifikasi Kelengkapan
                        Admin
                    </h2>

                    <p class="text-xs text-slate-600 leading-relaxed">
                        Periksa kesesuaian data, keabsahan dokumen, dan kelengkapan administrasi RFC ini sebelum
                        meneruskannya ke Change Manager.
                    </p>

                    <!-- Input Catatan Perbaikan/Revisi -->
                    <div class="space-y-2">
                        <label for="rejectionNote" class="block text-xs font-medium text-slate-700">
                            Catatan Perbaikan / Alasan Penolakan Berkas
                        </label>
                        <textarea id="rejectionNote" wire:model="rejectionNote" rows="4"
                            placeholder="Tuliskan catatan kekurangan berkas atau instruksi perbaikan jika dokumen tidak lengkap (min. 10 karakter)..."
                            class="w-full text-xs px-3 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 {{ $errors->has('rejectionNote') ? 'border-red-500' : 'border-slate-300' }}"></textarea>

                        @error('rejectionNote')
                            <span class="text-xs text-red-500 block">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Tombol Aksi Admin -->
                    <div class="pt-2 space-y-2">
                        <button type="button" wire:click="markAsComplete" wire:loading.attr="disabled"
                            class="w-full py-2.5 px-4 bg-emerald-600 hover:bg-emerald-700 disabled:opacity-50 text-white font-medium rounded-lg text-sm shadow-sm transition flex items-center justify-center space-x-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 13l4 4L19 7" />
                            </svg>
                            <span>Verifikasi Lengkap & Teruskan</span>
                        </button>

                        <button type="button" wire:click="requestRevision" wire:loading.attr="disabled"
                            class="w-full py-2.5 px-4 bg-amber-600 hover:bg-amber-700 disabled:opacity-50 text-white font-medium rounded-lg text-sm shadow-sm transition flex items-center justify-center space-x-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                            <span>Minta Perbaikan / Revisi Data</span>
                        </button>
                    </div>
                </div>
            @endif

            <!-- Panel Otorisasi Khusus Change Manager -->
            @if (auth()->user()->hasRole(['Change Manager', 'Super Admin']) && in_array($statusVal, ['complete', 'reviewed', 'submitted']))
                <div class="bg-indigo-50 p-6 rounded-xl border border-indigo-200 shadow-sm space-y-3">
                    <h2 class="text-base font-bold text-indigo-900">Otorisasi Keputusan Change Manager</h2>
                    <p class="text-xs text-indigo-700">
                        RFC ini sudah dalam status siap diputuskan. Silakan lakukan pencatatan otorisasi dan
                        pengunggahan berita acara keputusan.
                    </p>
                    <a href="{{ route('change-requests.decision', $rfc->id) }}"
                        class="w-full py-2.5 px-4 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg text-sm shadow-sm transition flex items-center justify-center space-x-2"
                        wire:navigate>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span>Catat Keputusan Otorisasi CAB</span>
                    </a>
                </div>
            @endif

            <!-- Metadata Info -->
            <div class="bg-slate-50 p-4 rounded-xl border border-slate-200 text-xs text-slate-500 space-y-2">
                <div class="flex justify-between">
                    <span>Dibuat Pada:</span>
                    <strong
                        class="text-slate-700">{{ $rfc->created_at ? $rfc->created_at->format('d/m/Y H:i') : '-' }}</strong>
                </div>
                <div class="flex justify-between">
                    <span>Terakhir Diperbarui:</span>
                    <strong
                        class="text-slate-700">{{ $rfc->updated_at ? $rfc->updated_at->format('d/m/Y H:i') : '-' }}</strong>
                </div>
            </div>

        </div>

    </div>
</div>
