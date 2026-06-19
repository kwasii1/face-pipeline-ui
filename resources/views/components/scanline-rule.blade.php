@props(['accent' => false])

<div {{ $attributes->merge(['class' => 'flex items-center gap-1.5']) }}>
    <span @class([
        'block w-1 h-1 border border-border shrink-0',
        '!border-accent' => $accent,
    ])></span>
    <span @class([
        'block flex-1 h-px',
        $accent ? 'bg-accent' : 'bg-border',
    ])></span>
    <span @class([
        'block w-1 h-1 border border-border shrink-0',
        '!border-accent' => $accent,
    ])></span>
</div>
