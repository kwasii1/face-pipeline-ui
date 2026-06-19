@props(['show' => false, 'title' => null])

<div
    x-data="{ open: @json($show) }"
    x-on:open-modal.window="if ($event.detail.id === '{{ $attributes->get('id') }}') open = true"
    x-on:close-modal.window="open = false"
    x-show="open"
    x-transition.opacity.duration.200ms
    class="fixed inset-0 z-50 flex items-center justify-center p-4"
    style="display: none;"
    x-cloak
>
    <div
        class="absolute inset-0 bg-bg/80 backdrop-blur-sm"
        x-on:click="open = false"
    ></div>

    <div
        class="relative z-10 w-full max-w-lg bg-surface border border-border rounded-lg shadow-2xl"
        x-on:click.stop=""
    >
        @if ($title)
            <div class="flex items-center justify-between px-5 py-4 border-b border-border">
                <h2 class="font-mono text-sm font-medium text-text-pri">{{ $title }}</h2>
                <button
                    x-on:click="open = false"
                    class="text-text-muted hover:text-text-pri transition-colors"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        @endif

        <div class="p-5">
            {{ $slot }}
        </div>

        @if (isset($footer))
            <div class="px-5 py-4 border-t border-border">
                {{ $footer }}
            </div>
        @endif
    </div>
</div>
