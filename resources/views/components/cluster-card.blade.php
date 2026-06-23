@props(['cluster', 'mergeSelection' => [], 'wireClick' => null, 'wireClickMerge' => null])

<div
    {{ $attributes->merge(['class' => 'group relative bg-surface rounded-lg overflow-hidden border cursor-pointer transition-colors']) }}
    @class([
        'border-accent ring-1 ring-accent' => in_array($cluster->cluster_id, $mergeSelection),
        'border-border hover:border-border-light' => ! in_array($cluster->cluster_id, $mergeSelection),
    ])
    @isset($wireClick) wire:click="{{ $wireClick }}" @endisset
>
    <div class="aspect-square bg-surface-alt flex items-center justify-center">
        @if ($cluster->coverFace)
            <img
                src="{{ Storage::disk('shared')->url($cluster->coverFace->crop_path) }}"
                alt="Face crop"
                class="w-full h-full object-cover"
                loading="lazy"
            />
        @else
            <span class="font-mono text-xs text-text-faint">No cover</span>
        @endif
    </div>

    <div class="p-2 border-t border-border">
        <p class="font-mono text-xs text-text-pri truncate">{{ $cluster->cluster_id }}</p>
        <p class="font-mono text-[10px] text-text-muted mt-0.5">
            {{ $cluster->face_count }} {{ $cluster->face_count === 1 ? 'face' : 'faces' }}
            @if ($cluster->tagged_count > 0)
                &middot; {{ $cluster->tagged_count }} tagged
            @endif
            @if ($cluster->untagged_count > 0)
                &middot; {{ $cluster->untagged_count }} untagged
            @endif
        </p>
    </div>

    <div class="absolute top-1.5 right-1.5 z-10">
        <input
            type="checkbox"
            @checked(in_array($cluster->cluster_id, $mergeSelection))
            @isset($wireClickMerge) wire:click.stop="{{ $wireClickMerge }}" @endisset
            class="accent-accent w-4 h-4 opacity-0 group-hover:opacity-100 {{ in_array($cluster->cluster_id, $mergeSelection) ? 'opacity-100' : '' }} transition-opacity"
        />
    </div>
</div>
