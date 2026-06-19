@props(['photo'])

@php
    /** @var \App\Models\Photo $photo */
    $faceCount = $photo->faces_count ?? $photo->faces()->count();
@endphp

<div class="group relative bg-surface rounded-lg overflow-hidden border border-border hover:border-border-light transition-colors">
    <div class="aspect-[4/3] bg-surface-alt flex items-center justify-center">
        <img
            src="{{ asset($photo->path) }}"
            alt=""
            class="w-full h-full object-cover"
            loading="lazy"
        />
    </div>

    @if ($faceCount > 0)
        <span class="absolute top-2 right-2 inline-flex items-center gap-1 px-2 py-0.5 bg-bg/80 backdrop-blur-sm rounded text-xs font-mono text-text-muted">
            {{ $faceCount }} {{ $faceCount === 1 ? 'face' : 'faces' }}
        </span>
    @endif

    <div class="absolute inset-x-0 bottom-0 p-3 bg-gradient-to-t from-bg/90 to-transparent opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
        <button
            wire:click="deletePhoto('{{ $photo->id }}')"
            class="font-mono text-xs text-text-muted hover:text-accent transition-colors uppercase tracking-wider"
        >
            Delete
        </button>
    </div>
</div>
