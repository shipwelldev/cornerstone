@props(['question'])

<div
    x-data="{ open: false }"
    x-id="['faq-panel']"
    {{ $attributes->merge(['class' => 'border-b border-white/10']) }}
>
    <h3>
        <button
            type="button"
            data-test="faq-toggle"
            x-on:click="open = ! open"
            x-bind:aria-expanded="open"
            x-bind:aria-controls="$id('faq-panel')"
            class="flex w-full items-center justify-between gap-6 py-5 text-left text-sm font-semibold text-white transition hover:text-orange-200 focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-orange-300"
        >
            <span>{{ $question }}</span>
            <svg
                aria-hidden="true"
                viewBox="0 0 20 20"
                fill="none"
                class="size-4 shrink-0 text-stone-500 transition motion-reduce:transition-none"
                x-bind:class="open && 'rotate-45 text-orange-300'"
            >
                <path d="M10 4v12M4 10h12" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
            </svg>
        </button>
    </h3>

    <div
        x-cloak
        x-show="open"
        x-transition.opacity.duration.150ms
        x-bind:id="$id('faq-panel')"
        data-test="faq-panel"
        class="pb-5 text-sm leading-7 text-stone-400"
    >
        {{ $slot }}
    </div>
</div>
