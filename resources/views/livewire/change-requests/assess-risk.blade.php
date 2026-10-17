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
                <h1 class="text-2xl font-bold text-slate-900">Penilaian Risiko & Dampak Teknis</h1>
            </div>
            <p class="text-sm text-slate-500 mt-1 pl-9">Penyusunan Matriks Risiko, Rencana Mitigasi, dan Rollback untuk
                {{ $rfc->rfc_number }}</p>
        </div>
        <a href="{{ route('change-requests.show', $rfc->id) }}"
            class="px-4 py-2 border border-slate-300 rounded-lg text-sm font-medium text-slate-700 hover:bg-slate-50 transition"
            wire:navigate>
            Batal
        </a>
    </div>

    <!-- Ringkasan Informasi RFC -->
    <div class="bg-slate-50 p-4 border border-slate-200 rounded-xl space-y-2 text-sm">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
            <div><span class="text-slate-500 block text-xs font-semibold uppercase tracking-wider">Judul
                    Perubahan:</span> <strong class="text-slate-800">{{ $rfc->title }}</strong></div>
            <div><span class="text-slate-500 block text-xs font-semibold uppercase tracking-wider">Aplikasi
                    Target:</span> <strong class="text-slate-800">{{ $rfc->application->name ?? '-' }}</strong></div>
            <div><span class="text-slate-500 block text-xs font-semibold uppercase tracking-wider">Pemohon:</span>
                <strong class="text-slate-800">{{ $rfc->submittedBy->name ?? '-' }}</strong></div>
            <div><span class="text-slate-500 block text-xs font-semibold uppercase tracking-wider">OPD Pengaju:</span>
                <strong class="text-slate-800">{{ $rfc->application->opd->name ?? '-' }}</strong></div>
        </div>
    </div>

    <!-- Form Risk Assessment -->
    <form wire:submit.prevent="saveAssessment"
        class="bg-white p-6 sm:p-8 rounded-xl border border-slate-200 shadow-sm space-y-6">

        <!-- Matriks Dampak & Probabilitas -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div>
                <label for="impact_level" class="block text-sm font-semibold text-slate-700 mb-1">Tingkat Dampak Sistem
                    *</label>
                <select id="impact_level" wire:model="impact_level"
                    class="w-full text-sm px-3.5 py-2.5 border rounded-lg border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="low">Rendah (Low Impact)</option>
                    <option value="medium">Sedang (Medium Impact)</option>
                    <option value="high">Tinggi (High Impact)</option>
                </select>
                @error('impact_level')
                    <span class="text-xs text-red-500 block mt-1">{{ $message }}</span>
                @enderror
            </div>

            <div>
                <label for="probability_level" class="block text-sm font-semibold text-slate-700 mb-1">Probabilitas
                    Kejadian *</label>
                <select id="probability_level" wire:model="probability_level"
                    class="w-full text-sm px-3.5 py-2.5 border rounded-lg border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="low">Kecil Keduanya (Low)</option>
                    <option value="medium">Mungkin Terjadi (Medium)</option>
                    <option value="high">Hampir Pasti (High)</option>
                </select>
                @error('probability_level')
                    <span class="text-xs text-red-500 block mt-1">{{ $message }}</span>
                @enderror
            </div>

            <div>
                <label for="overall_risk_level" class="block text-sm font-semibold text-slate-700 mb-1">Tingkat Risiko
                    Keseluruhan *</label>
                <select id="overall_risk_level" wire:model="overall_risk_level"
                    class="w-full text-sm px-3.5 py-2.5 border rounded-lg border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="low">Low (Rendah)</option>
                    <option value="medium">Medium (Sedang)</option>
                    <option value="high">High (Tinggi)</option>
                    <option value="emergency">Emergency (Darurat)</option>
                </select>
                @error('overall_risk_level')
                    <span class="text-xs text-red-500 block mt-1">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <!-- Rencana Mitigasi -->
        <div>
            <label for="mitigation_plan" class="block text-sm font-semibold text-slate-700 mb-1">Rencana Mitigasi Risiko
                (Mitigation Plan) *</label>
            <textarea id="mitigation_plan" wire:model="mitigation_plan" rows="3"
                placeholder="Langkah pencegahan untuk meminimalisir risiko kendala teknis saat rilis..."
                class="w-full text-sm px-3.5 py-2.5 border rounded-lg border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"></textarea>
            @error('mitigation_plan')
                <span class="text-xs text-red-500 block mt-1">{{ $message }}</span>
            @enderror
        </div>

        <!-- Rencana Rollback -->
        <div>
            <label for="rollback_plan" class="block text-sm font-semibold text-slate-700 mb-1">Rencana Pengembalian
                (Rollback Plan) *</label>
            <textarea id="rollback_plan" wire:model="rollback_plan" rows="3"
                placeholder="Langkah pemulihan/kembali ke versi sebelumnya jika rilis mengalami kegagalan fatal..."
                class="w-full text-sm px-3.5 py-2.5 border rounded-lg border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"></textarea>
            @error('rollback_plan')
                <span class="text-xs text-red-500 block mt-1">{{ $message }}</span>
            @enderror
        </div>

        <!-- Rencana Pengujian -->
        <div>
            <label for="testing_plan" class="block text-sm font-semibold text-slate-700 mb-1">Rencana Pengujian Skenario
                (Testing Plan) *</label>
            <textarea id="testing_plan" wire:model="testing_plan" rows="3"
                placeholder="Skenario pengujian sistem (UAT, Staging Test) sebelum di-deploy ke Server Production..."
                class="w-full text-sm px-3.5 py-2.5 border rounded-lg border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"></textarea>
            @error('testing_plan')
                <span class="text-xs text-red-500 block mt-1">{{ $message }}</span>
            @enderror
        </div>

        <!-- Submit Button -->
        <div class="flex items-center justify-end space-x-3 pt-4 border-t border-slate-100">
            <button type="submit" wire:loading.attr="disabled"
                class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 text-white font-medium rounded-lg text-sm shadow-sm transition">
                Simpan Penilaian Risiko
            </button>
        </div>
    </form>
</div>
