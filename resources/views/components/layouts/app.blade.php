@props(['judul' => config('app.name')])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="Dasbor harga beras dan minyak goreng di Pulau Jawa, Kalimantan, dan Sumatra.">
        <title>{{ $judul }}</title>
        @fonts
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-slate-50 font-sans text-slate-800 antialiased">
        <a href="#isi-utama" class="sr-only rounded bg-merek-600 px-4 py-2 text-white focus:not-sr-only focus:absolute focus:left-4 focus:top-4 focus:z-50">
            Lompat ke isi utama
        </a>

        {{ $slot }}
    </body>
</html>
