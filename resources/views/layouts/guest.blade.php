<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'PARAF') }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>

<body
    class="font-sans antialiased text-slate-900 bg-slate-100 min-h-screen flex flex-col justify-center items-center p-4">
    <div class="mb-6 text-center">
        <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">PARAF</h1>
        <p class="text-xs text-slate-500 mt-1 uppercase tracking-wider font-semibold">Perubahan terkendali, keputusan
            tercatat.</p>
    </div>

    <main class="w-full max-w-md">
        {{ $slot }}
    </main>

    @livewireScripts
</body>

</html>
