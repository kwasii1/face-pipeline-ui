@props(['title' => 'Nothing here', 'description' => null])

<div {{ $attributes->merge(['class' => 'flex flex-col items-center justify-center py-16 px-4 text-center']) }}>
    <div class="w-12 h-12 mb-4 rounded-full bg-surface-alt border border-border flex items-center justify-center">
        <svg class="w-5 h-5 text-text-faint" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
        </svg>
    </div>
    <h3 class="font-mono text-sm text-text-muted mb-1">{{ $title }}</h3>
    @if ($description)
        <p class="font-sans text-sm text-text-faint max-w-sm">{{ $description }}</p>
    @endif
</div>
