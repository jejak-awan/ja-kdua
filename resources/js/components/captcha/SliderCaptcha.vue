<template>
    <div class="captcha-slider">
        <p class="text-sm text-muted-foreground mb-2">{{ t('features.auth.captcha.slideToVerify') }}</p>
        
        <div 
            ref="trackRef"
            class="relative h-12 bg-muted rounded-lg border border-border overflow-hidden select-none"
            @mousedown="startDrag"
            @touchstart="startDrag"
        >
            <!-- Progress fill -->
            <div 
                class="absolute inset-y-0 left-0 bg-primary/20 transition-all duration-75"
                :style="{ width: `${progress}%` }"
            />
            
            <!-- Target indicator -->
            <div 
                class="absolute top-1 bottom-1 w-1 bg-primary/30 rounded"
                :style="{ left: `${targetPosition - 1}%` }"
            />
            
            <!-- Slider handle -->
            <div 
                class="absolute top-1 bottom-1 w-12 bg-background border-2 rounded-md flex items-center justify-center cursor-grab active:cursor-grabbing transition-colors"
                :class="[
                    verified ? 'border-green-500 bg-green-50 dark:bg-green-950' : 'border-primary hover:bg-muted',
                    dragging ? 'shadow-lg' : ''
                ]"
                :style="{ left: `${Math.min(progress, 100 - 15)}%` }"
            >
                <CheckCircle v-if="verified" class="w-5 h-5 text-green-500" />
                <GripVertical v-else class="w-5 h-5 text-muted-foreground" />
            </div>
            
            <!-- Instructions text -->
            <span 
                v-if="!dragging && !verified" 
                class="absolute inset-0 flex items-center justify-center text-sm text-muted-foreground pointer-events-none"
            >
                {{ t('features.auth.captcha.dragSlider') }}
            </span>
        </div>
        
        <p v-if="error" class="text-sm text-destructive mt-1">{{ error }}</p>
    </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { useI18n } from 'vue-i18n'
import { GripVertical, CheckCircle } from 'lucide-vue-next'
import api from '../../../services/api'

const { t } = useI18n()

const emit = defineEmits(['verified', 'error'])

const trackRef = ref(null)
const token = ref('')
const targetPosition = ref(75)
const progress = ref(0)
const dragging = ref(false)
const verified = ref(false)
const error = ref('')
const startX = ref(0)
const trackWidth = ref(0)

const generateCaptcha = async () => {
    try {
        const response = await api.get('/captcha/generate')
        const data = response.data?.data || response.data
        token.value = data.token
        targetPosition.value = data.target
        progress.value = 0
        verified.value = false
        error.value = ''
    } catch (e) {
        console.error('Failed to generate captcha:', e)
        error.value = 'Failed to load captcha'
    }
}

const startDrag = (e) => {
    if (verified.value) return
    
    dragging.value = true
    const clientX = e.type === 'touchstart' ? e.touches[0].clientX : e.clientX
    startX.value = clientX - (progress.value / 100) * trackWidth.value
    trackWidth.value = trackRef.value?.offsetWidth || 300
    
    document.addEventListener('mousemove', onDrag)
    document.addEventListener('mouseup', endDrag)
    document.addEventListener('touchmove', onDrag)
    document.addEventListener('touchend', endDrag)
}

const onDrag = (e) => {
    if (!dragging.value) return
    
    const clientX = e.type === 'touchmove' ? e.touches[0].clientX : e.clientX
    const deltaX = clientX - startX.value
    const newProgress = Math.max(0, Math.min(100, (deltaX / trackWidth.value) * 100))
    progress.value = newProgress
}

const endDrag = () => {
    if (!dragging.value) return
    
    dragging.value = false
    document.removeEventListener('mousemove', onDrag)
    document.removeEventListener('mouseup', endDrag)
    document.removeEventListener('touchmove', onDrag)
    document.removeEventListener('touchend', endDrag)
    
    // Check if close enough to target
    const tolerance = 5
    if (Math.abs(progress.value - targetPosition.value) <= tolerance) {
        verified.value = true
        emit('verified', { token: token.value, answer: String(Math.round(progress.value)) })
    } else {
        error.value = t('features.auth.captcha.tryAgain')
        progress.value = 0
        // Generate new captcha after failed attempt
        setTimeout(generateCaptcha, 1000)
    }
}

onMounted(() => {
    generateCaptcha()
})

onUnmounted(() => {
    document.removeEventListener('mousemove', onDrag)
    document.removeEventListener('mouseup', endDrag)
    document.removeEventListener('touchmove', onDrag)
    document.removeEventListener('touchend', endDrag)
})
</script>
