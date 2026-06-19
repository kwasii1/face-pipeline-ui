@props(['person', 'selected' => false])

@php
    /** @var \App\Models\Person $person */
@endphp

<button
    wire:click="togglePerson('{{ $person->id }}')"
    @class([
        'inline-flex items-center gap-2 px-3 py-1.5 rounded-full border font-mono text-xs transition-colors',
        'border-accent text-text-pri bg-surface-alt' => $selected,
        'border-border text-text-muted hover:border-border-light hover:text-text-pri' => ! $selected,
    ])
>
    <span @class([
        'w-5 h-5 rounded-full flex items-center justify-center text-[10px] font-bold shrink-0',
        'bg-accent text-bg' => $selected,
        'bg-surface-alt text-text-faint' => ! $selected,
    ])>
        {{ strtoupper(substr($person->name, 0, 1)) }}
    </span>
    {{ $person->name }}
</button>
