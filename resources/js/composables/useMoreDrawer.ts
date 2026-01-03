import { ref } from 'vue'

// Shared drawer state using module-level singleton pattern
const isMoreDrawerOpen = ref(false)

export function useMoreDrawer() {
    const open = () => {
        // Blur the currently focused element to prevent aria-hidden conflict
        if (document.activeElement instanceof HTMLElement) {
            document.activeElement.blur()
        }
        isMoreDrawerOpen.value = true
    }

    const close = () => {
        isMoreDrawerOpen.value = false
    }

    const toggle = () => {
        isMoreDrawerOpen.value = !isMoreDrawerOpen.value
    }

    return {
        isOpen: isMoreDrawerOpen,
        open,
        close,
        toggle
    }
}
