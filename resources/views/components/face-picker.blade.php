@props(['faces', 'selectedId' => null, 'action' => null, 'emptyText' => 'No faces available.'])

<div>
    @if ($faces->isEmpty())
        <p class="font-sans text-xs text-text-muted py-4 text-center">{{ $emptyText }}</p>
    @else
        <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-5 gap-2 mb-4">
            @foreach ($faces as $face)
                <button
                    @if($action)
                        wire:click="{{ $action }}('{{ $face->id }}')"
                    @endif
                    @class([
                        'group bg-surface-alt rounded-lg overflow-hidden border transition-colors text-left',
                        'border-accent ring-1 ring-accent' => $selectedId === $face->id,
                        'border-border hover:border-border-light' => $selectedId !== $face->id,
                    ])
                >
                    <div class="aspect-square bg-surface-alt flex items-center justify-center">
                        <img
                            src="{{ Storage::disk('shared')->url($face->crop_path) }}"
                            alt="Face crop"
                            class="w-full h-full object-cover"
                            loading="lazy"
                        />
                    </div>
                    <div class="p-1.5">
                        <p class="font-mono text-[10px] text-text-pri truncate">
                            {{ $face->person?->name ?? 'Unnamed' }}
                        </p>
                    </div>
                </button>
            @endforeach
        </div>

        {{ $faces->links('components.pagination') }}
    @endif
</div>
