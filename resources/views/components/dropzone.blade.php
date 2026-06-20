@props(['accept' => 'image/*'])

<div
    x-data="{ dragging: false }"
    x-on:dragover.prevent="dragging = true"
    x-on:dragleave.prevent="dragging = false"
    x-on:drop.prevent="dragging = false; $refs.input.files = $event.dataTransfer.files; $refs.input.dispatchEvent(new Event('change'))"
    class="relative"
>
    <label
        @class([
            'flex flex-col items-center justify-center p-10 border-2 border-dashed rounded-lg cursor-pointer transition-colors',
            'border-accent bg-surface-alt' => 'dragging',
            'border-border hover:border-border-light' => '!dragging',
        ])
        x-bind:class="dragging ? 'border-accent bg-surface-alt' : 'border-border hover:border-border-light'"
    >
        <svg class="w-10 h-10 text-text-faint mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
        </svg>
        <p class="font-mono text-sm text-text-muted mb-1">Drop photos here</p>
        <p class="font-sans text-xs text-text-faint">or click to browse</p>

        <input
            type="file"
            x-ref="input"
            accept="{{ $accept }}"
            multiple
            class="hidden"
            wire:model.live="newPhotos"
        />
    </label>
</div>
