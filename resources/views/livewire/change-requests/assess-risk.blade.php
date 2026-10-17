<div class="p-6 bg-gray-50 min-h-screen">
    <div class="max-w-4xl mx-auto bg-white p-6 rounded border border-gray-200">
        <h1 class="text-xl font-bold text-gray-900 mb-4">Analisis Risiko RFC - {{ $rfc->rfc_number }}</h1>

        <form wire:submit.prevent="saveRiskAssessment" class="space-y-4 text-sm">
            <div>
                <label class="block font-medium text-gray-700">Tingkat Dampak (Impact Level)</label>
                <select wire:model="impactLevel" class="w-full mt-1 border-gray-300 rounded focus:ring-gray-500">
                    <option value="low">Low</option>
                    <option value="medium">Medium</option>
                    <option value="high">High</option>
                    <option value="critical">Critical</option>
                </select>
            </div>

            <div>
                <label class="block font-medium text-gray-700">Tingkat Probabilitas (Probability Level)</label>
                <select wire:model="probabilityLevel" class="w-full mt-1 border-gray-300 rounded focus:ring-gray-500">
                    <option value="low">Low</option>
                    <option value="medium">Medium</option>
                    <option value="high">High</option>
                </select>
            </div>

            <div>
                <label class="block font-medium text-gray-700">Skor Risiko (Risk Score)</label>
                <select wire:model="riskScore" class="w-full mt-1 border-gray-300 rounded focus:ring-gray-500">
                    <option value="low">Low</option>
                    <option value="medium">Medium</option>
                    <option value="high">High</option>
                    <option value="critical">Critical</option>
                </select>
            </div>

            <div>
                <label class="block font-medium text-gray-700">Rencana Mitigasi</label>
                <textarea wire:model="mitigationPlan" rows="3" class="w-full mt-1 border-gray-300 rounded focus:ring-gray-500"></textarea>
            </div>

            <div>
                <label class="block font-medium text-gray-700">Rencana Rollback</label>
                <textarea wire:model="rollbackPlan" rows="3" class="w-full mt-1 border-gray-300 rounded focus:ring-gray-500"></textarea>
            </div>

            <div class="flex justify-end pt-4">
                <button type="submit"
                    class="px-4 py-2 bg-gray-900 text-white rounded font-medium hover:bg-black">Simpan Analisis
                    Risiko</button>
            </div>
        </form>
    </div>
</div>
