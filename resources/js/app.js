(() => {
    let registered = false

    document.addEventListener('livewire:init', () => {
        if (registered) return
        registered = true

        Livewire.on('toast', (params) => {
            console.trace('[bridge] Livewire toast → CustomEvent', JSON.stringify(params))
            window.dispatchEvent(new CustomEvent('toast', { detail: params }))
        })
    })
})()
