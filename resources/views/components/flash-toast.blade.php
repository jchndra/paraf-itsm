@php
    $flashError = session('error');
    $flashMessage = session('message') ?? session('success') ?? session('status');
    $flashWarning = session('warning');
@endphp

@if ($flashError || $flashMessage || $flashWarning)
    @php
        $toastType = $flashError ? 'error' : ($flashWarning ? 'warning' : 'success');
        $toastMessage = $flashError ?? $flashWarning ?? $flashMessage;
        $toastStyles = match ($toastType) {
            'error' => 'border-rose-200 bg-rose-50 text-rose-900',
            'warning' => 'border-amber-200 bg-amber-50 text-amber-900',
            default => 'border-emerald-200 bg-emerald-50 text-emerald-900',
        };
        $iconStyles = match ($toastType) {
            'error' => 'bg-rose-100 text-rose-600',
            'warning' => 'bg-amber-100 text-amber-600',
            default => 'bg-emerald-100 text-emerald-600',
        };
    @endphp

    <div x-data="{ show: true }" x-show="show" x-cloak x-transition.opacity.duration.200ms
        x-init="setTimeout(() => show = false, 7000)"
        class="fixed right-4 top-4 z-[90] w-[calc(100%-2rem)] max-w-sm" role="status" aria-live="polite">
        <div class="flex items-start gap-3 rounded-2xl border px-4 py-3.5 shadow-lg {{ $toastStyles }}">
            <span class="grid h-8 w-8 shrink-0 place-items-center rounded-full {{ $iconStyles }}">
                @if ($toastType === 'error')
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v4m0 4h.01M10.34 3.94l-8.4 14.53A1.5 1.5 0 0 0 3.23 21h17.54a1.5 1.5 0 0 0 1.3-2.53l-8.4-14.53a1.5 1.5 0 0 0-2.6 0Z" />
                    </svg>
                @elseif ($toastType === 'warning')
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v4m0 4h.01M12 3a9 9 0 1 1 0 18 9 9 0 0 1 0-18Z" />
                    </svg>
                @else
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                @endif
            </span>

            <p class="min-w-0 flex-1 pt-0.5 text-sm font-medium leading-snug">{{ $toastMessage }}</p>

            <button type="button" x-on:click="show = false"
                class="shrink-0 rounded-lg p-1 text-current opacity-60 transition hover:opacity-100"
                aria-label="Tutup notifikasi">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M6 18 18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    </div>
@endif
