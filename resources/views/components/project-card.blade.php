@props(['project' => null, 'isNew' => false])

@if ($isNew)
    <button
        wire:click="$dispatch('open-modal', { id: 'create-project' })"
        class="flex flex-col items-center justify-center p-6 bg-surface rounded-lg border border-dashed border-border hover:border-border-light transition-colors text-text-muted hover:text-text-pri min-h-[160px]"
    >
        <svg class="w-8 h-8 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4v16m8-8H4"/>
        </svg>
        <span class="font-mono text-xs">New Project</span>
    </button>
@else
    <a
        href="{{ route('project.overview', $project) }}"
        wire:navigate
        {{ $attributes->merge(['class' => 'block p-6 bg-surface rounded-lg border border-border hover:border-border-light transition-colors']) }}
    >
        <h3 class="font-mono text-sm font-medium text-text-pri truncate">{{ $project->name }}</h3>
        <div class="flex gap-4 mt-3">
            <span class="font-mono text-xs text-text-muted">{{ $project->photos_count ?? 0 }} photos</span>
            <span class="font-mono text-xs text-text-muted">{{ $project->faces_count ?? 0 }} faces</span>
        </div>
    </a>
@endif
