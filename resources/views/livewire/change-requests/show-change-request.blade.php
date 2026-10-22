<div class="space-y-6">

    @php
        $isStaff = auth()->user()?->can('rfc.view_all') ?? false;
    @endphp

    <!-- Header & Tombol Kembali -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-3">
                <a href="{{ auth()->user()?->isStaff() ? route('change-requests.index') : route('requester.dashboard') }}"
                    class="p-2 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-full transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                </a>
                <h1 class="text-2xl font-bold text-slate-900">{{ $rfc->rfc_number ?? 'Draft RFC' }}</h1>

                @php
                    $status = \App\Enums\RfcStatus::fromValue($rfc->status);
                @endphp
                <x-status-badge :status="$status ?? $rfc->status" />
            </div>
            <p class="text-sm text-slate-500 mt-1 ml-12">{{ $rfc->title }}</p>
        </div>

        {{-- Tombol "Edit / Revisi RFC" disembunyikan sementara karena fitur edit RFC belum tersedia. --}}
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
        <!-- Kolom Kiri: Informasi Utama -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Detail Informasi -->
            <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-6">
                <h3 class="text-lg font-bold text-slate-800 mb-4 border-b pb-2">Informasi Permintaan Perubahan</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-y-4 gap-x-6 text-sm">
                    <div>
                        <span class="block text-slate-500 text-xs uppercase tracking-wider font-semibold mb-1">Judul</span>
                        <p class="text-slate-800 font-medium">{{ $rfc->title }}</p>
                    </div>
                    <div>
                        <span class="block text-slate-500 text-xs uppercase tracking-wider font-semibold mb-1">Sistem / Aplikasi Target</span>
                        <p class="text-slate-800 font-medium">{{ $rfc->application?->name }}</p>
                    </div>
                    <div>
                        <span class="block text-slate-500 text-xs uppercase tracking-wider font-semibold mb-1">Target Tanggal Pelaksanaan</span>
                        <p class="text-slate-800 font-medium">
                            {{ $rfc->target_implementation_date?->copy()->locale('id')->translatedFormat('d M Y') }}
                        </p>
                    </div>
                    <div>
                        <span class="block text-slate-500 text-xs uppercase tracking-wider font-semibold mb-1">Tanggal Diajukan</span>
                        <p class="text-slate-800 font-medium">
                            {{ $rfc->created_at?->copy()->locale('id')->translatedFormat('d M Y, H:i') }}
                        </p>
                    </div>
                    <div>
                        <span class="block text-slate-500 text-xs uppercase tracking-wider font-semibold mb-1">Nota Dinas</span>
                        @if ($rfc->is_physical_letter)
                            <p class="text-slate-800 font-medium">
                                Ya
                                @if (filled($rfc->physical_letter_number))
                                    <span class="block text-slate-600">{{ $rfc->physical_letter_number }}</span>
                                @endif
                            </p>
                        @else
                            <p class="text-slate-800 font-medium">Tidak</p>
                        @endif
                    </div>
                    @if ($isStaff)
                        <div>
                            <span class="block text-slate-500 text-xs uppercase tracking-wider font-semibold mb-1">Nama Pemohon</span>
                            <p class="text-slate-800 font-medium">{{ $rfc->submittedBy?->name }}</p>
                        </div>
                        @if ($rfc->submittedBy?->opd)
                            <div>
                                <span class="block text-slate-500 text-xs uppercase tracking-wider font-semibold mb-1">Unit Kerja</span>
                                <p class="text-slate-800 font-medium">{{ $rfc->submittedBy->opd->name }}</p>
                            </div>
                        @endif
                    @endif
                    <div class="md:col-span-2">
                        <span class="block text-slate-500 text-xs uppercase tracking-wider font-semibold mb-1">Alasan Perubahan</span>
                        <p class="text-slate-800 whitespace-pre-wrap">{{ $rfc->reason_for_change }}</p>
                    </div>
                    <div class="md:col-span-2">
                        <span class="block text-slate-500 text-xs uppercase tracking-wider font-semibold mb-1">Estimasi Dampak</span>
                        <p class="text-slate-800 whitespace-pre-wrap">{{ $rfc->impact_description }}</p>
                    </div>
                </div>
            </div>

            <!-- Ditetapkan Petugas -->
            @php
                $officerCategory = $rfc->category ?? null;
                $officerPriority = $rfc->priority ?? null;
                $officerChangeType = $rfc->change_type ?: $rfc->riskAssessment?->classification;
            @endphp
            @if (filled($officerCategory) || filled($officerPriority) || filled($officerChangeType))
                <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-6">
                    <h3 class="text-lg font-bold text-slate-800 mb-4 border-b pb-2">Ditetapkan Petugas</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-y-4 gap-x-6 text-sm">
                        @if (filled($officerCategory))
                            <div>
                                <span
                                    class="block text-slate-500 text-xs uppercase tracking-wider font-semibold mb-1">Kategori</span>
                                <p class="text-slate-800 font-medium">{{ $officerCategory }}</p>
                            </div>
                        @endif
                        @if (filled($officerPriority))
                            <div>
                                <span
                                    class="block text-slate-500 text-xs uppercase tracking-wider font-semibold mb-1">Prioritas</span>
                                <p class="text-slate-800 font-medium">{{ $officerPriority }}</p>
                            </div>
                        @endif
                        @if (filled($officerChangeType))
                            <div>
                                <span class="block text-slate-500 text-xs uppercase tracking-wider font-semibold mb-1">Jenis Perubahan</span>
                                <p class="text-slate-800 font-medium">{{ $officerChangeType }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            <!-- Alasan Penolakan / Permintaan Revisi -->
            @php
                $isRejected = $status === \App\Enums\RfcStatus::REJECTED;
                $needsRevision = $status === \App\Enums\RfcStatus::NEEDS_REVISION;
                $finalRejection = $rfc->decisions
                    ->where('decision_status', \App\Enums\DecisionStatus::REJECTED->value)
                    ->last();
                $rejectionReason = $isRejected ? ($rfc->rejection_note ?: $finalRejection?->reason) : null;
                $revisionReason = $needsRevision ? $rfc->rejection_note : null;
            @endphp
            @if (filled($rejectionReason))
                <div class="bg-rose-50 rounded-xl border border-rose-100 p-6">
                    <h3 class="text-lg font-bold text-rose-800 mb-2">Alasan Penolakan</h3>
                    <p class="text-sm text-rose-900 whitespace-pre-wrap">{{ $rejectionReason }}</p>
                </div>
            @endif
            @if (filled($revisionReason))
                <div class="bg-amber-50 rounded-xl border border-amber-100 p-6">
                    <h3 class="text-lg font-bold text-amber-800 mb-2">Alasan Permintaan Revisi</h3>
                    <p class="text-sm text-amber-900 whitespace-pre-wrap">{{ $revisionReason }}</p>
                </div>
            @endif

            <!-- Panel Keputusan -->
            @if ($rfc->riskAssessment || $rfc->decisions->isNotEmpty())
                <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-6">
                    <h3 class="text-lg font-bold text-slate-800 mb-4 border-b pb-2">Hasil Evaluasi & Keputusan</h3>

                    @if ($rfc->riskAssessment)
                        <div class="mb-6">
                            <h4 class="text-sm font-bold text-slate-700 mb-3">Penilaian Risiko (Change Manager)</h4>
                            <div class="flex flex-wrap gap-4">
                                <div class="p-3 bg-slate-50 rounded-lg border border-slate-100 flex-1 min-w-[120px]">
                                    <span class="block text-xs text-slate-500 mb-1">Tingkat Risiko</span>
                                    <span
                                        class="font-bold text-slate-800">{{ $rfc->riskAssessment->risk_level }}</span>
                                </div>
                                <div class="p-3 bg-slate-50 rounded-lg border border-slate-100 flex-1 min-w-[120px]">
                                    <span class="block text-xs text-slate-500 mb-1">Dampak</span>
                                    <span
                                        class="font-bold text-slate-800">{{ $rfc->riskAssessment->impact_level }}</span>
                                </div>
                                <div class="p-3 bg-slate-50 rounded-lg border border-slate-100 flex-1 min-w-[120px]">
                                    <span class="block text-xs text-slate-500 mb-1">Klasifikasi</span>
                                    <span
                                        class="font-bold text-blue-700">{{ $rfc->riskAssessment->classification }}</span>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if ($rfc->decisions->isNotEmpty())
                        <div>
                            <h4 class="text-sm font-bold text-slate-700 mb-3">Keputusan Otorisasi (CAB/ECAB)</h4>
                            @foreach ($rfc->decisions as $decision)
                                <div
                                    class="p-4 rounded-lg border {{ $decision->decision_status === \App\Enums\DecisionStatus::APPROVED->value ? 'bg-green-50 border-green-100' : 'bg-red-50 border-red-100' }}">
                                    <div class="flex justify-between items-start mb-2">
                                        <div>
                                            <span
                                                class="font-bold text-slate-800">{{ $decision->snapshot_authority_name ?? 'Otoritas' }}</span>
                                            <span
                                                class="block text-xs text-slate-500">{{ $decision->snapshot_authority_position }}</span>
                                        </div>
                                        <span
                                            class="text-xs font-bold px-2 py-1 rounded {{ $decision->decision_status === \App\Enums\DecisionStatus::APPROVED->value ? 'bg-green-200 text-green-800' : 'bg-red-200 text-red-800' }}">
                                            {{ \App\Enums\DecisionStatus::labelFor($decision->decision_status) }}
                                        </span>
                                    </div>
                                    <p class="text-sm text-slate-700 mt-2"><span
                                            class="font-semibold">Catatan/Alasan:</span>
                                        @if (!$isStaff && $decision->decision_status === \App\Enums\DecisionStatus::RETURNED_TO_ADMIN->value)
                                            <span class="italic text-slate-500">Catatan internal tidak ditampilkan.</span>
                                        @else
                                            {{ $decision->reason ?? '-' }}
                                        @endif
                                    </p>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endif
        </div>

        <!-- Kolom Kanan: Timeline Riwayat -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-6">
            <h3 class="text-lg font-bold text-slate-800 mb-6 flex items-center gap-2">
                <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                Riwayat Proses
            </h3>

            <div
                class="relative pl-6 space-y-8 before:absolute before:inset-0 before:ml-[11px] before:-translate-x-px before:h-full before:w-0.5 before:bg-slate-200">

                <!-- Timeline 1: Pengajuan -->
                <div class="relative flex items-start group">
                    <div
                        class="absolute left-[-29px] flex items-center justify-center w-6 h-6 rounded-full bg-green-500 text-white shadow-sm ring-4 ring-white">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7">
                            </path>
                        </svg>
                    </div>
                    <div class="flex-1">
                        <h4 class="text-sm font-bold text-slate-800">Dokumen Dibuat</h4>
                        <p class="text-xs text-slate-500 mt-0.5">{{ $rfc->created_at->format('d M Y, H:i') }}</p>
                    </div>
                </div>

                <!-- Timeline 2: Evaluasi Risiko -->
                @php $hasRisk = !is_null($rfc->riskAssessment); @endphp
                <div class="relative flex items-start group">
                    <div
                        class="absolute left-[-29px] flex items-center justify-center w-6 h-6 rounded-full {{ $hasRisk ? 'bg-green-500 text-white' : 'bg-slate-200 text-slate-400' }} shadow-sm ring-4 ring-white">
                        @if ($hasRisk)
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                    d="M5 13l4 4L19 7"></path>
                            </svg>
                        @endif
                    </div>
                    <div class="flex-1">
                        <h4 class="text-sm font-bold {{ $hasRisk ? 'text-slate-800' : 'text-slate-400' }}">Analisis
                            Change Manager</h4>
                        @if ($hasRisk)
                            <p class="text-xs text-slate-500 mt-0.5">
                                {{ $rfc->riskAssessment->created_at->format('d M Y, H:i') }}</p>
                        @else
                            <p class="text-xs text-slate-400 mt-0.5">Menunggu proses evaluasi</p>
                        @endif
                    </div>
                </div>

                <!-- Timeline 3: Keputusan CAB -->
                @php
                    $hasDecision = $rfc->decisions->isNotEmpty();
                    $decisionData = $hasDecision ? $rfc->decisions->last() : null;
                    $isRejected = $decisionData && $decisionData->decision_status === \App\Enums\DecisionStatus::REJECTED->value;
                @endphp
                <div class="relative flex items-start group">
                    <div
                        class="absolute left-[-29px] flex items-center justify-center w-6 h-6 rounded-full {{ $hasDecision ? ($isRejected ? 'bg-red-500' : 'bg-green-500') : 'bg-slate-200' }} text-white shadow-sm ring-4 ring-white">
                        @if ($hasDecision)
                            @if ($isRejected)
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                        d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            @else
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                        d="M5 13l4 4L19 7"></path>
                                </svg>
                            @endif
                        @endif
                    </div>
                    <div class="flex-1">
                        <h4 class="text-sm font-bold {{ $hasDecision ? 'text-slate-800' : 'text-slate-400' }}">
                            Persetujuan Otoritas</h4>
                        @if ($hasDecision)
                            <p class="text-xs text-slate-500 mt-0.5">
                                {{ $decisionData->created_at->format('d M Y, H:i') }}</p>
                            <p
                                class="text-xs font-semibold {{ $isRejected ? 'text-red-600' : 'text-green-600' }} mt-1">
                                {{ $isRejected ? 'Ditolak' : 'Disetujui' }}</p>
                        @else
                            <p class="text-xs text-slate-400 mt-0.5">Menunggu persetujuan</p>
                        @endif
                    </div>
                </div>

                <!-- Timeline 4: Implementasi -->
                @php
                    $isImplemented = in_array($status, [
                        \App\Enums\RfcStatus::IMPLEMENTED,
                        \App\Enums\RfcStatus::CLOSED,
                    ], true);
                    $isFailed = $status === \App\Enums\RfcStatus::FAILED;
                    $implementationDone = $isImplemented || $isFailed;
                @endphp
                <div class="relative flex items-start group">
                    <div
                        class="absolute left-[-29px] flex items-center justify-center w-6 h-6 rounded-full {{ $isFailed ? 'bg-red-600 text-white' : ($implementationDone ? 'bg-green-500 text-white' : 'bg-slate-200') }} shadow-sm ring-4 ring-white">
                        @if ($implementationDone)
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                    d="{{ $isFailed ? 'M6 18 18 6M6 6l12 12' : 'M5 13l4 4L19 7' }}"></path>
                            </svg>
                        @endif
                    </div>
                    <div class="flex-1">
                        <h4 class="text-sm font-bold {{ $implementationDone ? 'text-slate-800' : 'text-slate-400' }}">
                            Implementasi Perubahan</h4>
                        <p class="text-xs text-slate-500 mt-0.5">
                            {{ $isFailed ? 'Implementasi gagal dilaksanakan' : ($isImplemented ? 'Perubahan telah dieksekusi di lapangan' : 'Menunggu pelaksanaan teknis') }}
                        </p>
                    </div>
                </div>

                <!-- Timeline 5: Penutupan (Closed) -->
                @php
                    $isClosed = $status === \App\Enums\RfcStatus::CLOSED;
                @endphp
                <div class="relative flex items-start group">
                    <div
                        class="absolute left-[-29px] flex items-center justify-center w-6 h-6 rounded-full {{ $isClosed ? 'bg-slate-800 text-white' : 'bg-slate-200' }} shadow-sm ring-4 ring-white">
                        @if ($isClosed)
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                    d="M5 13l4 4L19 7"></path>
                            </svg>
                        @endif
                    </div>
                    <div class="flex-1">
                        <h4 class="text-sm font-bold {{ $isClosed ? 'text-slate-800' : 'text-slate-400' }}">RFC
                            Ditutup</h4>
                        <p class="text-xs text-slate-500 mt-0.5">
                            {{ $isClosed ? 'Siklus manajemen perubahan selesai' : 'Menunggu review pasca-implementasi' }}
                        </p>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
