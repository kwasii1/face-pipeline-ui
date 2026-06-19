<div
    x-data="{
        toasts: [],
        add(detail) {
            console.trace('[toast] add called', JSON.stringify(detail))
            const id = Date.now() + Math.random()
            this.toasts.push({ id, message: detail.message, type: detail.type || 'success' })
            if (this.toasts.length > 5) this.toasts.shift()
            setTimeout(() => this.remove(id), 4000)
        },
        remove(id) {
            this.toasts = this.toasts.filter(t => t.id !== id)
        },
    }"
    x-init="window.addEventListener('toast', e => add(e.detail))"
    class="fixed bottom-4 right-4 z-[100] flex flex-col-reverse gap-2 max-w-sm w-full pointer-events-none"
>
    <template x-for="toast in toasts" :key="toast.id">
        <div
            x-show="true"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="translate-x-full opacity-0"
            x-transition:enter-end="translate-x-0 opacity-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="translate-x-0 opacity-100"
            x-transition:leave-end="translate-x-full opacity-0"
            class="pointer-events-auto bg-surface border border-border rounded-lg px-4 py-3 flex items-start gap-3 shadow-2xl"
            :class="toast.type === 'success' ? 'border-l-accent !border-l-2' : (toast.type === 'error' ? '!border-l-2 border-l-text-pri' : 'border-l-border-light')"
        >
            <div class="shrink-0 mt-0.5">
                <template x-if="toast.type === 'success'">
                    <svg class="w-4 h-4 text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                </template>
                <template x-if="toast.type === 'error'">
                    <svg class="w-4 h-4 text-text-pri" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </template>
                <template x-if="toast.type === 'info'">
                    <svg class="w-4 h-4 text-text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </template>
            </div>

            <p class="font-mono text-xs text-text-pri flex-1 leading-relaxed" x-text="toast.message"></p>

            <button
                @click="remove(toast.id)"
                class="shrink-0 text-text-faint hover:text-text-pri transition-colors"
            >
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
    </template>
</div>
