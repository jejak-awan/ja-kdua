<template>
    <Transition
        enter-active-class="transition duration-300 ease-out"
        enter-from-class="opacity-0"
        enter-to-class="opacity-100"
        leave-active-class="transition duration-200 ease-in"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0"
    >
        <div v-if="visible" class="fixed inset-0 z-[100] flex items-center justify-center bg-slate-900/60 backdrop-blur-sm">
            <div class="w-full max-w-sm rounded-2xl bg-white p-8 shadow-2xl border border-slate-200 text-center">
                <div class="mb-6 flex justify-center">
                    <div class="flex h-16 w-16 items-center justify-center rounded-xl bg-blue-50 text-blue-600">
                        <ShieldCheckIcon class="h-10 w-10" />
                    </div>
                </div>
                
                <h3 class="mb-2 text-xl font-bold text-slate-900">{{ title || $t('features.security.shield.challenge.title') }}</h3>
                <p class="mb-6 text-sm text-slate-500 leading-relaxed">{{ message || $t('features.security.shield.challenge.message') }}</p>

                <div class="relative h-1.5 w-full overflow-hidden rounded-full bg-slate-100">
                    <div 
                        class="absolute h-full bg-blue-600 transition-all duration-500 linear"
                        :style="{ width: progress + '%' }"
                    ></div>
                </div>
                
                <div class="mt-3 flex items-center justify-between text-[10px] font-medium uppercase tracking-wider text-slate-400">
                    <span>{{ statusText || $t('features.security.shield.challenge.status.initializing') }}</span>
                    <span>{{ Math.round(progress || 0) }}%</span>
                </div>
            </div>
        </div>
    </Transition>
</template>

<script setup lang="ts">
import { onMounted, onUnmounted } from 'vue'
import ShieldCheckIcon from 'lucide-vue-next/dist/esm/icons/shield-check.js';

interface Props {
    visible: boolean
    title?: string
    message?: string
    progress?: number
    statusText?: string
}

defineProps<Props>()

// Prevent body scroll when overlay is visible
const toggleBodyScroll = (disable: boolean) => {
    if (disable) {
        document.body.style.overflow = 'hidden'
    } else {
        document.body.style.overflow = ''
    }
}

onMounted(() => {
    // If initially visible
    // if (props.visible) toggleBodyScroll(true)
})

onUnmounted(() => {
    toggleBodyScroll(false)
})
</script>
