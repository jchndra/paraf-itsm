<div class="space-y-6">

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
                        <span class="block text-slate-500 text-xs uppercase tracking-wider font-semibold mb-1">Tujuan
                            Perubahan</span>
                        <p class="text-slate-800 font-medium">{{ $rfc->purpose ?? '-' }}</p>
                    </div>
                    <div>
                        <span class="block text-slate-500 text-xs uppercase tracking-wider font-semibold mb-1">Tanggal
                            Diajukan</span>
                        <p class="text-slate-800 font-medium">{{ $rfc->created_at->format('d M Y, H:i') }}</p>
                    </div>
                    <div>
                        <span
                            class="block text-slate-500 text-xs uppercase tracking-wider font-semibold mb-1">Kategori</span>
                        <p class="text-slate-800 font-medium">{{ $rfc->category ?? '-' }}</p>
                    </div>
                    <div>
                        <span class="block text-slate-500 text-xs uppercase tracking-wider font-semibold mb-1">Jenis
                            Perubahan</span>
                        <p class="text-slate-800 font-medium">{{ $rfc->change_type ?? '-' }}</p>
                    </div>
                    <div class="md:col-span-2">
                        <span class="block text-slate-500 text-xs uppercase tracking-wider font-semibold mb-1">Deskripsi
                            Lengkap</span>
                        <div class="p-3 bg-slate-50 rounded-lg text-slate-700 whitespace-pre-wrap">
                            {{ $rfc->description ?? 'Tidak ada deskripsi yang dilampirkan.' }}</div>
                    </div>

                    <!-- Render Lampiran jika ada relasinya -->
                    @if (isset($rfc->attachments) && $rfc->attachments->isNotEmpty())
                        <div class="md:col-span-2 mt-2">
                            <span
                                class="block text-slate-500 text-xs uppercase tracking-wider font-semibold mb-2">Dokumen
                                Lampiran</span>
                            <ul class="space-y-2">
                                @foreach ($rfc->attachments as $attachment)
                                    <li class="flex items-center gap-2 text-sm text-blue-600 hover:text-blue-800">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13">
                                            </path>
                                        </svg>
                                        <a href="{{ Storage::url($attachment->file_path) }}" target="_blank"
                                            class="underline">{{ $attachment->file_name }}</a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </div>
            </div>

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
                                            class="font-semibold">Catatan/Alasan:</span> {{ $decision->reason ?? '-' }}
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
