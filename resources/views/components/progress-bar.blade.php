@props(['current' => 0, 'total' => 0, 'status' => 'pending'])

@php
    $pct = $total > 0 ? min(100, round(($current / $total) * 100)) : 0;
    $isProcessing = $status === 'processing';
@endphp

<div {{ $attributes->merge(['class' => 'flex items-center gap-2']) }}>
    <div class="flex-1 h-1 bg-border rounded-full overflow-hidden">
        <div
            class="h-full bg-accent rounded-full transition-all duration-700 ease-out"
            style="width: {{ $pct }}%"
        ></div>
    </div>
    @if ($isProcessing)
        <span class="relative flex h-2 w-2">
            <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-accent opacity-75"></span>
            <span class="relative inline-flex h-2 w-2 rounded-full bg-accent"></span>
        </span>
    @endif
    <span class="font-mono text-xs text-text-muted tabular-nums">{{ $current }}/{{ $total }}</span>
</div>
