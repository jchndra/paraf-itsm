<div class="w-full max-w-md p-8 bg-white rounded-xl shadow-md border border-slate-200">
    <div class="mb-6 text-center">
        <h2 class="text-2xl font-bold text-slate-800">Masuk ke PARAF</h2>
        <p class="text-sm text-slate-500 mt-1">Masukkan kredensial akun Anda untuk melanjutkan</p>
    </div>

    <form wire:submit="login" class="space-y-5">
        <div>
            <label for="email" class="block text-sm font-medium text-slate-700 mb-1">Alamat Email</label>
            <input wire:model="email" id="email" type="email" required autofocus
                class="w-full px-3 py-2 border rounded-lg text-slate-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 {{ $errors->has('email') ? 'border-red-500' : 'border-slate-300' }}">
            @error('email')
                <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span>
            @enderror
        </div>

        <div>
            <label for="password" class="block text-sm font-medium text-slate-700 mb-1">Kata Sandi</label>
            <input wire:model="password" id="password" type="password" required
                class="w-full px-3 py-2 border rounded-lg text-slate-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 {{ $errors->has('password') ? 'border-red-500' : 'border-slate-300' }}">
            @error('password')
                <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span>
            @enderror
        </div>

        <div class="flex items-center justify-between">
            <label class="flex items-center text-sm text-slate-600 cursor-pointer">
                <input wire:model="remember" type="checkbox"
                    class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 mr-2">
                Ingat saya
            </label>
        </div>

        <button type="submit"
            class="w-full py-2.5 px-4 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition-colors duration-150">
            Masuk
        </button>
    </form>
</div>
