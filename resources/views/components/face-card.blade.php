@props(['face', 'clickable' => true])

@php
    /** @var \App\Models\Face $face */
    $isTagged = $face->person_id !== null;
    $tag = $isTagged ? 'tagged' : 'untagged';
@endphp

<div
    {{ $attributes->merge(['class' => 'corner-brackets group bg-surface rounded-lg overflow-hidden border border-border hover:border-border-light transition-colors']) }}
    @class(['cursor-pointer' => $clickable])
    @if($clickable) wire:click="$parent.selectFace('{{ $face->id }}')" @endif
>
    <div class="aspect-square bg-surface-alt flex items-center justify-center">
        <img
            src="{{ asset($face->crop_path) }}"
            alt="Face crop"
            class="w-full h-full object-cover"
            loading="lazy"
        />
    </div>

    <div class="p-2 border-t border-border">
        @if ($isTagged)
            <p class="font-mono text-xs text-text-pri truncate">{{ $face->person?->name ?? 'Unknown' }}</p>
        @else
            <p class="font-mono text-xs text-text-muted truncate">Unnamed</p>
            <span class="inline-block mt-1 px-1.5 py-px text-[10px] font-mono uppercase tracking-wider text-text-faint border border-border rounded">
                Review
            </span>
        @endif
    </div>
</div>
