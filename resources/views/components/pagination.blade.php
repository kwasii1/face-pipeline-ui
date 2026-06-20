@if ($paginator->hasPages())
    <nav class="flex items-center justify-center gap-2 mt-6">
        @if ($paginator->onFirstPage())
            <span class="px-3 py-1.5 font-mono text-xs text-text-faint border border-border rounded select-none opacity-40">Prev</span>
        @else
            <button
                wire:click="previousPage('{{ $paginator->getPageName() }}')"
                class="px-3 py-1.5 font-mono text-xs text-text-muted border border-border rounded hover:border-border-light hover:text-text-pri transition-colors"
            >
                Prev
            </button>
        @endif

        <span class="px-3 py-1.5 font-mono text-xs text-text-muted tabular-nums">
            {{ $paginator->currentPage() }} / {{ $paginator->lastPage() }}
        </span>

        @if ($paginator->hasMorePages())
            <button
                wire:click="nextPage('{{ $paginator->getPageName() }}')"
                class="px-3 py-1.5 font-mono text-xs text-text-muted border border-border rounded hover:border-border-light hover:text-text-pri transition-colors"
            >
                Next
            </button>
        @else
            <span class="px-3 py-1.5 font-mono text-xs text-text-faint border border-border rounded select-none opacity-40">Next</span>
        @endif
    </nav>
@endif
