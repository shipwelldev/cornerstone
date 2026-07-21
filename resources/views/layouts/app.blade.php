<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <title>{{ $title ?? config('app.name') }}</title>

        @fonts
        @vite('resources/css/app.css')
        @livewireStyles
    </head>
    <body class="min-h-screen bg-stone-950 font-sans text-stone-100 antialiased">
        <div class="relative isolate min-h-screen overflow-hidden">
            <div class="pointer-events-none absolute inset-x-0 top-0 -z-10 h-96 bg-[radial-gradient(circle_at_top_left,rgba(251,146,60,0.18),transparent_45%),radial-gradient(circle_at_top_right,rgba(45,212,191,0.14),transparent_40%)]"></div>

            <header class="border-b border-white/10">
                <nav class="mx-auto flex max-w-6xl items-center justify-between px-6 py-5 lg:px-8" aria-label="Main navigation">
                    <a href="{{ route('home') }}" wire:navigate class="inline-flex items-center gap-3 font-semibold tracking-tight text-white">
                        <span class="grid size-9 place-items-center rounded-xl bg-orange-400 text-sm font-bold text-stone-950 shadow-lg shadow-orange-950/30">C</span>
                        <span>{{ config('app.name') }}</span>
                    </a>

                    <span class="rounded-full border border-emerald-300/20 bg-emerald-300/10 px-3 py-1 text-xs font-medium text-emerald-200">
                        Livewire + Tailwind ready
                    </span>
                </nav>
            </header>

            <main>
                {{ $slot }}
            </main>

            <footer class="mx-auto flex max-w-6xl items-center justify-between border-t border-white/10 px-6 py-6 text-xs text-stone-400 lg:px-8">
                <span>{{ config('app.name') }}</span>
                <span>Laravel {{ app()->version() }}</span>
            </footer>
        </div>

        @livewireScripts
    </body>
</html>
