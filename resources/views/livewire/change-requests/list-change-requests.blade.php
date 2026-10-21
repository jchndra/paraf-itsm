<div class="space-y-6">
    {{-- Header Banner & Ucapan Selamat Datang --}}
    <div
        class="bg-gradient-to-r from-blue-900 via-indigo-900 to-slate-900 rounded-2xl p-6 text-white shadow-lg relative overflow-hidden">
        <div class="relative z-10 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <span class="text-xs font-semibold uppercase tracking-wider text-blue-200">
                    Diskominfostandi Kota Bekasi
                </span>
                <h1 class="text-2xl font-bold mt-1">
                    Selamat Datang, {{ auth()->user()->name }}
                </h1>
                <p class="text-sm text-blue-100/80 mt-1 max-w-xl">
                    @if ($roleView === 'pemohon')
                        "Jadikan setiap perubahan sebagai langkah menuju layanan publik yang lebih baik."
                    @elseif($roleView === 'admin')
                        "Kelola penerimaan dan verifikasi administrasi dokumen perubahan secara teliti dan cepat."
                    @else
                        "Kelola perubahan layanan TI secara aman, terukur, transparan, dan terdokumentasi."
                    @endif
                </p>
            </div>

            @if ($roleView === 'pemohon')
                <div>
                    <a href="{{ route('change-requests.create') }}"
                        class="inline-flex items-center px-5 py-2.5 bg-blue-500 hover:bg-blue-600 text-white text-sm font-semibold rounded-xl shadow transition transform hover:-translate-y-0.5">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Ajukan RFC Baru
                    </a>
                </div>
            @endif
        </div>
    </div>

    {{-- STATS CARDS --}}
    @if ($roleView === 'pemohon')
        <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
                <p class="text-xs font-medium text-slate-500">RFC Saya</p>
                <p class="text-2xl font-bold text-slate-800 mt-1">{{ $stats['my_rfc'] }}</p>
            </div>
            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
                <p class="text-xs font-medium text-blue-600">{{ \App\Enums\RfcStatus::groupLabel('in_process') }}</p>
                <p class="text-2xl font-bold text-blue-600 mt-1">{{ $stats['in_process'] }}</p>
            </div>
            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
                <p class="text-xs font-medium text-amber-600">{{ \App\Enums\RfcStatus::groupLabel('needs_revision') }}</p>
                <p class="text-2xl font-bold text-amber-600 mt-1">{{ $stats['needs_revision'] }}</p>
            </div>
            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
                <p class="text-xs font-medium text-emerald-600">{{ \App\Enums\RfcStatus::groupLabel('approved') }}</p>
                <p class="text-2xl font-bold text-emerald-600 mt-1">{{ $stats['approved'] }}</p>
            </div>
            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
                <p class="text-xs font-medium text-rose-600">{{ \App\Enums\RfcStatus::groupLabel('rejected') }}</p>
                <p class="text-2xl font-bold text-rose-600 mt-1">{{ $stats['rejected'] }}</p>
            </div>
        </div>
    @elseif($roleView === 'admin')
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="bg-white p-5 rounded-2xl border border-blue-100 shadow-sm">
                <p class="text-xs font-medium text-blue-600">RFC Masuk</p>
                <p class="text-2xl font-bold text-blue-900 mt-1">{{ $stats['incoming'] }}</p>
            </div>
            <div class="bg-white p-5 rounded-2xl border border-amber-100 shadow-sm">
                <p class="text-xs font-medium text-amber-600">Perlu Revisi</p>
                <p class="text-2xl font-bold text-amber-600 mt-1">{{ $stats['needs_revision'] }}</p>
            </div>
            <div class="bg-white p-5 rounded-2xl border border-emerald-100 shadow-sm">
                <p class="text-xs font-medium text-emerald-600">Diteruskan</p>
                <p class="text-2xl font-bold text-emerald-600 mt-1">{{ $stats['forwarded'] }}</p>
            </div>
            <div class="bg-white p-5 rounded-2xl border border-rose-100 shadow-sm">
                <p class="text-xs font-medium text-rose-600">{{ \App\Enums\RfcStatus::groupLabel('rejected') }}</p>
                <p class="text-2xl font-bold text-rose-600 mt-1">{{ $stats['rejected'] }}</p>
            </div>
        </div>
    @else
        <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
            <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm">
                <p class="text-xs font-medium text-slate-500">Total RFC</p>
                <p class="text-2xl font-bold text-slate-900 mt-1">{{ $stats['total'] }}</p>
            </div>
            <div class="bg-white p-4 rounded-2xl border border-amber-100 shadow-sm">
                <p class="text-xs font-medium text-amber-600">Menunggu Persetujuan</p>
                <p class="text-2xl font-bold text-amber-600 mt-1">{{ $stats['pending_approval'] }}</p>
            </div>
            <div class="bg-white p-4 rounded-2xl border border-emerald-100 shadow-sm">
                <p class="text-xs font-medium text-emerald-600">Disetujui</p>
                <p class="text-2xl font-bold text-emerald-600 mt-1">{{ $stats['approved'] }}</p>
            </div>
            <div class="bg-white p-4 rounded-2xl border border-rose-100 shadow-sm">
                <p class="text-xs font-medium text-rose-600">{{ \App\Enums\RfcStatus::groupLabel('rejected') }}</p>
                <p class="text-2xl font-bold text-rose-600 mt-1">{{ $stats['rejected'] }}</p>
            </div>
            <div class="bg-white p-4 rounded-2xl border border-purple-100 shadow-sm">
                <p class="text-xs font-medium text-purple-600">Emergency</p>
                <p class="text-2xl font-bold text-purple-600 mt-1">{{ $stats['emergency'] }}</p>
            </div>
        </div>
    @endif

    {{-- TABEL KONTEN UTAMA --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h3 class="text-base font-bold text-slate-800">Daftar Permintaan Perubahan (RFC)</h3>
                <p class="text-xs text-slate-500">Pantau dan kelola seluruh usulan perubahan sistem informasi</p>
            </div>

            <div class="flex items-center gap-3">
                <input wire:model.live.debounce.300ms="search" type="text" placeholder="Cari No. RFC / Judul..."
                    class="px-3.5 py-2 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:outline-none w-48 md:w-64">
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead
                    class="bg-slate-50 text-slate-500 font-semibold uppercase tracking-wider border-b border-slate-100">
                    <tr>
                        <th class="px-5 py-3">No. RFC</th>
                        <th class="px-5 py-3">Judul Perubahan</th>
                        <th class="px-5 py-3">Pemohon</th>
                        <th class="px-5 py-3">Tanggal</th>
                        <th class="px-5 py-3">Status</th>
                        <th class="px-5 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($rfcs as $rfc)
                        @php
                            $statusVal = \App\Enums\RfcStatus::fromValue($rfc->status);
                        @endphp
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="px-5 py-3.5 font-bold text-blue-900">
                                {{ $rfc->rfc_number }}
                            </td>
                            <td class="px-5 py-3.5 font-medium text-slate-800">
                                {{ $rfc->title }}
                            </td>
                            <td class="px-5 py-3.5 text-slate-600">
                                {{ $rfc->submittedBy?->name ?? '-' }}
                            </td>
                            <td class="px-5 py-3.5 text-slate-500">
                                {{ $rfc->created_at?->format('d M Y') }}
                            </td>
                            <td class="px-5 py-3.5">
                                <x-status-badge :status="$statusVal ?? $rfc->status" />
                            </td>
                            <td class="px-5 py-3.5 text-right space-x-2">
                                <a href="{{ route('change-requests.show', $rfc) }}"
                                    class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium rounded-lg text-xs transition">
                                    Detail
                                </a>

                                {{-- Aksi Admin / Frontdesk --}}
                                @if ($roleView === 'admin' && in_array($statusVal, [\App\Enums\RfcStatus::SUBMITTED, \App\Enums\RfcStatus::RETURNED_TO_ADMIN], true))
                                    <a href="{{ route('change-requests.review', $rfc) }}"
                                        class="px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg text-xs transition">
                                        {{ $statusVal === \App\Enums\RfcStatus::RETURNED_TO_ADMIN ? 'Tindak Lanjut' : 'Verifikasi' }}
                                    </a>
                                @endif

                                {{-- Aksi Change Manager --}}
                                @if ($roleView === 'change_manager' && $statusVal === \App\Enums\RfcStatus::TICKET_CREATED)
                                    @if ($rfc->riskAssessment)
                                        <a href="{{ route('change-requests.record-decision', $rfc) }}"
                                            class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white font-medium rounded-lg text-xs transition">
                                            Keputusan CAB
                                        </a>
                                    @else
                                        <a href="{{ route('change-requests.assess-risk', $rfc) }}"
                                            class="px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg text-xs transition">
                                            Evaluasi
                                        </a>
                                    @endif
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-8 text-center text-slate-400">
                                Belum ada dokumen RFC yang tersedia.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-slate-100">
            {{ $rfcs->links() }}
        </div>
    </div>
</div>
