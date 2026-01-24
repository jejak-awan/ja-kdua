import { ref, computed, onMounted, onUnmounted, inject } from 'vue'

export function useResponsiveDevice() {
    const builder = inject('builder', null)

    if (builder) {
        // We are in builder mode, rely on builder's selected device
        return computed(() => (builder as any).device.value)
    }

    // We are in frontend mode, listen to window resize
    const device = ref('desktop')

    const checkDevice = () => {
        if (typeof window === 'undefined') return

        const w = window.innerWidth
        if (w < 768) device.value = 'mobile'
        else if (w < 1024) device.value = 'tablet'
        else device.value = 'desktop'
    }

    onMounted(() => {
        checkDevice()
        window.addEventListener('resize', checkDevice)
    })

    onUnmounted(() => {
        window.removeEventListener('resize', checkDevice)
    })

    return computed(() => device.value)
}
