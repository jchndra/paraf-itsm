@props([
    'name' => 'Pengguna',
])

@php
    $initials = collect(explode(' ', trim($name)))
        ->filter()
        ->take(2)
        ->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))
        ->implode('');
@endphp

<span
    {{ $attributes->merge(['class' => 'grid h-10 w-10 shrink-0 place-items-center rounded-full bg-gradient-to-br from-slate-700 to-slate-900 text-xs font-bold text-white ring-2 ring-white']) }}>
    {{ $initials ?: 'P' }}
</span>
