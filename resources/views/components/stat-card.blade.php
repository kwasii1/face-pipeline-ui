@props(['label', 'value', 'href' => null])

@php
    $classes = 'block p-4 bg-surface rounded-lg border border-border hover:border-border-light transition-colors';
@endphp

@if ($href)
    <a href="{{ $href }}" wire:navigate class="{{ $classes }}">
        <p class="font-mono text-[10px] uppercase tracking-widest text-text-muted mb-2">{{ $label }}</p>
        <p class="font-mono text-2xl font-medium text-text-pri tabular-nums">{{ $value }}</p>
    </a>
@else
    <div class="{{ $classes }}">
        <p class="font-mono text-[10px] uppercase tracking-widest text-text-muted mb-2">{{ $label }}</p>
        <p class="font-mono text-2xl font-medium text-text-pri tabular-nums">{{ $value }}</p>
    </div>
@endif
