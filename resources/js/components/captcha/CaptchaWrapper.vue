<template>
    <div v-if="loading" class="flex items-center justify-center p-4">
        <Loader2 class="w-5 h-5 animate-spin text-muted-foreground" />
    </div>
    
    <div v-else-if="enabled" class="captcha-wrapper">
        <SliderCaptcha v-if="method === 'slider'" @verified="onVerified" />
        <MathCaptcha v-else-if="method === 'math'" @verified="onVerified" />
        <ImageCaptcha v-else-if="method === 'image'" @verified="onVerified" />
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { Loader2 } from 'lucide-vue-next'
import api from '../../services/api'
import SliderCaptcha from './SliderCaptcha.vue'
import MathCaptcha from './MathCaptcha.vue'
import ImageCaptcha from './ImageCaptcha.vue'

const props = defineProps({
    action: {
        type: String,
        default: 'login', // 'login' or 'register'
    },
})

const emit = defineEmits(['verified'])

const loading = ref(true)
const enabled = ref(false)
const method = ref('slider')

const fetchSettings = async () => {
    try {
        const response = await api.get('/captcha/settings')
        const data = response.data?.data || response.data
        
        enabled.value = props.action === 'login' ? data.enabled_login : data.enabled_register
        method.value = data.method
    } catch (e) {
        console.error('Failed to fetch captcha settings:', e)
        enabled.value = false
    } finally {
        loading.value = false
    }
}

const onVerified = (payload) => {
    emit('verified', payload)
}

onMounted(() => {
    fetchSettings()
})

// Expose state for parent components
defineExpose({
    enabled,
    method,
})
</script>
