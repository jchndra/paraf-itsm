<div class="max-w-4xl mx-auto py-8 px-4">
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="p-6 bg-slate-900 text-white">
            <h2 class="text-xl font-bold">Penutupan Tiket RFC</h2>
            <p class="text-sm text-slate-400 mt-1">Nomor RFC: {{ $rfc->rfc_number }}</p>
        </div>

        <form wire:submit="closeTicket" class="p-6 space-y-6">
            @error('status')
                <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-xs font-medium text-rose-700">
                    {{ $message }}</div>
            @enderror

            <div>
                <label for="closure_notes" class="block text-sm font-medium text-slate-700 mb-1">
                    Catatan Penutupan & Evaluasi Akhir (PIR) <span class="text-red-500">*</span>
                </label>
                <textarea 
                    wire:model="closure_notes" 
                    id="closure_notes" 
                    rows="4" 
                    class="w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                    placeholder="Masukkan ringkasan hasil implementasi, catatan evaluasi, atau keterangan penutupan tiket..."
                ></textarea>
                @error('closure_notes') 
                    <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span> 
                @enderror
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                <a href="{{ route('change-requests.show', $rfc->id) }}" wire:navigate class="px-4 py-2 text-sm font-medium text-slate-700 bg-slate-100 rounded-lg hover:bg-slate-200 transition">
                    Batal
                </a>
                <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-emerald-600 rounded-lg hover:bg-emerald-700 transition">
                    Tutup Tiket RFC
                </button>
            </div>
        </form>
    </div>
</div>