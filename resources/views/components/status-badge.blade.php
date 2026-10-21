@props([
    'status' => null,
    'label' => null,
])

@php
    $value = $status instanceof \App\Enums\RfcStatus
        ? $status->value
        : (is_object($status) && isset($status->value) ? $status->value : $status);

    $tone = \App\Enums\RfcStatus::badgeClassesFor($value);
    $defaultLabel = \App\Enums\RfcStatus::labelFor($value);
@endphp

<span
    {{ $attributes->merge(['class' => "inline-flex items-center rounded-full px-3 py-1 text-[11px] font-semibold {$tone}"]) }}>
    {{ $label ?? $defaultLabel }}
</span>
