<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'e-Tuntutan') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />
    @fluxAppearance
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-zinc-50 dark:bg-zinc-950 antialiased font-sans">

    <div class="min-h-screen flex">

        {{-- Left: Background Image Panel --}}
        <div class="hidden lg:flex lg:w-2/3 xl:w-2/3 relative overflow-hidden">

            {{-- Background image --}}
            <img
                src="{{ asset('images/background-tuntutan-4.jpg') }}"
                alt="e-Tuntutan"
                class="absolute inset-0 w-full h-full object-cover"
            >

            {{-- Overlay --}}
            <div class="absolute inset-0 bg-sky-900/30"></div>

            {{-- Content over image --}}
            <div class="relative z-10 flex flex-col justify-between p-12 w-full">
                {{-- Brand --}}
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-white backdrop-blur flex items-center justify-center overflow-hidden">
                        <img src="{{ asset('images/logo-clab.png') }}" alt="CLAB" class="w-7 h-7 object-contain" />
                    </div>
                    <span class="text-xl font-semibold text-white">e-Tuntutan</span>
                </div>

                {{-- Hero text --}}
                <div>
                    <p class="text-white text-lg max-w-md">
                        Streamlined claim management for FWHS, Green Card, and PERKESO — from submission to closure.
                    </p>

                    <div class="flex gap-6 mt-10">
                        <div>
                            <p class="text-2xl font-bold text-white">3</p>
                            <p class="text-white text-sm">Claim Types</p>
                        </div>
                        <div class="w-px bg-white/50"></div>
                        <div>
                            <p class="text-2xl font-bold text-white">Digital</p>
                            <p class="text-white text-sm">No More Emails</p>
                        </div>
                        <div class="w-px bg-white/50"></div>
                        <div>
                            <p class="text-2xl font-bold text-white">CLAB</p>
                            <p class="text-white text-sm">Managed</p>
                        </div>
                    </div>
                </div>

                <p class="text-white text-sm">© {{ date('Y') }} CLAB. All rights reserved.</p>
            </div>
        </div>

        {{-- Right: Auth Card --}}
        <div class="w-full lg:w-1/3 flex flex-col items-center justify-center p-6 sm:p-10 bg-white dark:bg-zinc-900 min-h-screen">

            <div class="w-full max-w-sm">
                {{ $slot }}
            </div>
        </div>

    </div>

@fluxScripts
</body>
</html>
