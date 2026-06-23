@props(['accept' => 'image/*'])

<div
    x-data="{ dragging: false, uploading: false, progress: 0 }"
    x-on:dragover.prevent="dragging = true"
    x-on:dragleave.prevent="dragging = false"
    x-on:drop.prevent="dragging = false; $refs.input.files = $event.dataTransfer.files; $refs.input.dispatchEvent(new Event('change'))"
    x-on:livewire-upload-start="uploading = true; progress = 0"
    x-on:livewire-upload-finish="uploading = false"
    x-on:livewire-upload-cancel="uploading = false"
    x-on:livewire-upload-error="uploading = false"
    x-on:livewire-upload-progress="progress = $event.detail.progress"
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

    <div x-show="uploading" class="mt-3">
        <progress
            max="100"
            x-bind:value="progress"
            class="w-full h-1.5 rounded-full [&::-webkit-progress-bar]:rounded-full [&::-webkit-progress-bar]:bg-surface-alt [&::-webkit-progress-value]:rounded-full [&::-webkit-progress-value]:bg-accent [&::-moz-progress-bar]:rounded-full [&::-moz-progress-bar]:bg-accent"
        ></progress>
        <p class="font-mono text-xs text-text-muted mt-1 text-center" x-text="'Uploading... ' + Math.round(progress) + '%'"></p>
    </div>
</div>
