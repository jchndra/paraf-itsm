<div class="p-6 bg-gray-50 min-h-screen">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 tracking-tight">PARAF - Daftar Request for Change</h1>
            <p class="text-sm text-gray-600">Perubahan terkendali, keputusan tercatat.</p>
        </div>
        @can('rfc.create')
            <a href="{{ route('change-requests.create') }}"
                class="px-4 py-2 bg-gray-900 hover:bg-black text-white rounded text-sm font-medium transition" wire:navigate>
                + Ajukan RFC Baru
            </a>
        @endcan
    </div>

    <!-- Filter Bar Padat -->
    <div class="bg-white p-4 rounded border border-gray-200 mb-6 grid grid-cols-1 md:grid-cols-4 gap-3">
        <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari Nomor RFC / Judul..."
            class="text-sm border-gray-300 rounded focus:ring-gray-500 focus:border-gray-500">

        <select wire:model.live="statusFilter"
            class="text-sm border-gray-300 rounded focus:ring-gray-500 focus:border-gray-500">
            <option value="">-- Semua Status --</option>
            <option value="submitted">Diajukan</option>
            <option value="revision_required">Perlu Perbaikan</option>
            <option value="complete">Lengkap</option>
            <option value="assessed">Sudah Dinilai</option>
            <option value="approved">Disetujui</option>
            <option value="closed">Ditutup</option>
        </select>

        <select wire:model.live="changeTypeFilter"
            class="text-sm border-gray-300 rounded focus:ring-gray-500 focus:border-gray-500">
            <option value="">-- Semua Jenis Change --</option>
            <option value="standard">Standard</option>
            <option value="normal">Normal</option>
            <option value="emergency">Emergency</option>
        </select>

        <select wire:model.live="opdFilter"
            class="text-sm border-gray-300 rounded focus:ring-gray-500 focus:border-gray-500">
            <option value="">-- Semua OPD --</option>
            @foreach ($opds as $opd)
                <option value="{{ $opd->id }}">{{ $opd->code }} - {{ $opd->name }}</option>
            @endforeach
        </select>
    </div>

    <!-- Tabel Data Padat -->
    <div class="bg-white rounded border border-gray-200 overflow-hidden">
        <table class="w-full text-left text-sm border-collapse">
            <thead>
                <tr class="bg-gray-100 border-b border-gray-200 text-gray-700 font-semibold">
                    <th class="p-3">Nomor RFC</th>
                    <th class="p-3">Judul Perubahan</th>
                    <th class="p-3">Sistem / OPD</th>
                    <th class="p-3">Pemohon</th>
                    <th class="p-3">Jenis</th>
                    <th class="p-3">Status</th>
                    <th class="p-3">Jatuh Tempo</th>
                    <th class="p-3 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($changeRequests as $rfc)
                    @php
                        $statusVal = is_object($rfc->status) ? $rfc->status->value ?? $rfc->status->name : $rfc->status;
                    @endphp
                    <tr class="hover:bg-gray-50">
                        <td class="p-3 font-mono font-medium text-gray-900">{{ $rfc->rfc_number }}</td>
                        <td class="p-3 font-medium text-gray-800">{{ $rfc->title }}</td>
                        <td class="p-3 text-gray-600">
                            {{ $rfc->application->name }} <br>
                            <span class="text-xs text-gray-400">{{ $rfc->application->opd->code }}</span>
                        </td>
                        <td class="p-3 text-gray-600">
                            {{ $rfc->submittedBy->name }}
                            @if ($rfc->is_physical_letter)
                                <span
                                    class="inline-block px-1.5 py-0.5 text-[10px] bg-yellow-100 text-yellow-800 rounded">Surat
                                    Fisik</span>
                            @endif
                        </td>
                        <td class="p-3 uppercase text-xs font-bold">
                            @if ($rfc->change_type === 'emergency')
                                <span class="text-red-600">Emergency</span>
                            @else
                                <span class="text-gray-700">{{ $rfc->change_type ?? '-' }}</span>
                            @endif
                        </td>
                        <td class="p-3">
                            <span class="px-2 py-1 text-xs rounded font-medium bg-gray-200 text-gray-800">
                                {{ is_object($rfc->status) && method_exists($rfc->status, 'label') ? $rfc->status->label() : \App\Enums\RfcStatus::tryFrom($statusVal)?->label() ?? ucfirst(str_replace('_', ' ', $statusVal)) }}
                            </span>
                        </td>
                        <td class="p-3 text-gray-600 text-xs">
                            {{ $rfc->target_implementation_date ? \Carbon\Carbon::parse($rfc->target_implementation_date)->format('d/m/Y H:i') : '-' }}
                        </td>
                        <td class="p-3 text-right space-x-2 whitespace-nowrap">
                            <a href="{{ route('change-requests.show', $rfc->id) }}"
                                class="text-gray-900 underline font-medium hover:text-black" wire:navigate>Detail</a>

                            @if (auth()->user()->hasRole(['Change Manager', 'Super Admin']) && in_array($statusVal, ['complete', 'reviewed', 'submitted']))
                                <a href="{{ route('change-requests.decision', $rfc->id) }}"
                                    class="inline-flex items-center px-2.5 py-1 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-medium rounded shadow-sm transition"
                                    wire:navigate>
                                    Catat Keputusan
                                </a>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="p-4 text-center text-gray-500">Tidak ada data Request for Change.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $changeRequests->links() }}
    </div>
</div>
