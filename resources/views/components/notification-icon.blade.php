@props([
    'status' => null,
])

@php
    $status = \App\Enums\RfcStatus::fromValue(
        is_object($status) && isset($status->value) ? $status->value : $status
    );

    [$tone, $icon] = match ($status) {
        \App\Enums\RfcStatus::APPROVED,
        \App\Enums\RfcStatus::IMPLEMENTED,
        \App\Enums\RfcStatus::CLOSED => [
            'bg-emerald-100 text-emerald-600',
            'M4.5 12.75l6 6 9-13.5',
        ],
        \App\Enums\RfcStatus::REJECTED,
        \App\Enums\RfcStatus::NEEDS_REVISION,
        \App\Enums\RfcStatus::FAILED => [
            'bg-rose-100 text-rose-600',
            'M6 18 18 6M6 6l12 12',
        ],
        \App\Enums\RfcStatus::TICKET_CREATED,
        \App\Enums\RfcStatus::RETURNED_TO_ADMIN => [
            'bg-indigo-100 text-indigo-600',
            'M11.25 11.25 12 12m0 0 3.75 3.75M12 12l3.75-3.75M12 12l-3.75 3.75M12 12 8.25 8.25M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z',
        ],
        \App\Enums\RfcStatus::SUBMITTED,
        \App\Enums\RfcStatus::IN_PROGRESS => [
            'bg-blue-100 text-blue-600',
            'M11.25 11.25 12 12m0 0 3.75 3.75M12 12l3.75-3.75M12 12l-3.75 3.75M12 12 8.25 8.25M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z',
        ],
        \App\Enums\RfcStatus::CANCELLED,
        \App\Enums\RfcStatus::DRAFT => [
            'bg-slate-100 text-slate-500',
            'M12 9v4m0 4h.01M10.34 3.94l-8.4 14.53A1.5 1.5 0 0 0 3.23 21h17.54a1.5 1.5 0 0 0 1.3-2.53l-8.4-14.53a1.5 1.5 0 0 0-2.6 0Z',
        ],
        default => [
            'bg-amber-100 text-amber-600',
            'M12 9v4m0 4h.01M10.34 3.94l-8.4 14.53A1.5 1.5 0 0 0 3.23 21h17.54a1.5 1.5 0 0 0 1.3-2.53l-8.4-14.53a1.5 1.5 0 0 0-2.6 0Z',
        ],
    };
@endphp

<span {{ $attributes->merge(['class' => "grid shrink-0 place-items-center rounded-full {$tone}"]) }}>
    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $icon }}" />
    </svg>
</span>
