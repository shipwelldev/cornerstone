<div class="mx-auto grid min-h-[calc(100vh-10rem)] max-w-6xl items-center gap-14 px-6 py-16 lg:grid-cols-[1.15fr_0.85fr] lg:px-8 lg:py-24">
    <section>
        <p class="mb-5 font-mono text-xs font-semibold uppercase tracking-[0.25em] text-orange-300">
            Laravel application foundation
        </p>

        <h1 class="max-w-3xl text-5xl font-semibold tracking-[-0.04em] text-balance text-white sm:text-6xl lg:text-7xl">
            Build from a solid <span class="text-orange-300">cornerstone.</span>
        </h1>

        <p class="mt-7 max-w-2xl text-lg leading-8 text-stone-400">
            Livewire handles the interaction on the server. Tailwind CSS handles the responsive interface. Vite keeps the development loop fast.
        </p>

        <div class="mt-10 grid max-w-2xl gap-px overflow-hidden rounded-2xl border border-white/10 bg-white/10 sm:grid-cols-3">
            <div class="bg-stone-900/90 p-5">
                <p class="text-sm font-medium text-white">Livewire 4</p>
                <p class="mt-1 text-xs text-stone-400">Server-driven UI</p>
            </div>
            <div class="bg-stone-900/90 p-5">
                <p class="text-sm font-medium text-white">Tailwind 4</p>
                <p class="mt-1 text-xs text-stone-400">CSS-first config</p>
            </div>
            <div class="bg-stone-900/90 p-5">
                <p class="text-sm font-medium text-white">Vite 8</p>
                <p class="mt-1 text-xs text-stone-400">Production builds</p>
            </div>
        </div>
    </section>

    <section class="relative">
        <div class="absolute -inset-5 -z-10 rounded-[2rem] bg-gradient-to-br from-orange-400/20 via-transparent to-teal-400/20 blur-2xl"></div>
        <div class="overflow-hidden rounded-[1.75rem] border border-white/10 bg-stone-900/80 shadow-2xl shadow-black/40 backdrop-blur">
            <div class="flex items-center justify-between border-b border-white/10 px-6 py-4">
                <div class="flex gap-2" aria-hidden="true">
                    <span class="size-2.5 rounded-full bg-red-400"></span>
                    <span class="size-2.5 rounded-full bg-amber-300"></span>
                    <span class="size-2.5 rounded-full bg-emerald-400"></span>
                </div>
                <p class="font-mono text-[11px] uppercase tracking-widest text-stone-400">Livewire check</p>
            </div>

            <div class="p-7 sm:p-9">
                <p class="text-sm text-stone-400">Server-backed counter</p>
                <p class="mt-3 font-mono text-7xl font-semibold tracking-tighter text-white" aria-live="polite">{{ $count }}</p>

                <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                    <button
                        type="button"
                        wire:click="increment"
                        class="data-loading:pointer-events-none data-loading:opacity-60 inline-flex flex-1 items-center justify-center rounded-xl bg-orange-400 px-5 py-3 text-sm font-semibold text-stone-950 transition hover:bg-orange-300 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-orange-300"
                    >
                        Increment
                    </button>
                    <button
                        type="button"
                        wire:click="resetCounter"
                        @disabled($count === 0)
                        class="inline-flex items-center justify-center rounded-xl border border-white/10 px-5 py-3 text-sm font-semibold text-stone-300 transition hover:border-white/20 hover:bg-white/5 disabled:cursor-not-allowed disabled:opacity-40"
                    >
                        Reset
                    </button>
                </div>

                <p wire:loading class="mt-4 text-xs text-teal-300">Syncing with the server...</p>
                <p wire:loading.remove class="mt-4 text-xs text-stone-400">Click increment to verify Livewire requests.</p>
            </div>
        </div>
    </section>
</div>
