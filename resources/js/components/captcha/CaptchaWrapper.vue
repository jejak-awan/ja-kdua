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

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import Loader2 from 'lucide-vue-next/dist/esm/icons/loader-circle.js';import api from '../../services/api'
import SliderCaptcha from './SliderCaptcha.vue'
import MathCaptcha from './MathCaptcha.vue'
import ImageCaptcha from './ImageCaptcha.vue'

interface Props {
    action?: 'login' | 'register' | 'comment' | 'contact'
}

const props = withDefaults(defineProps<Props>(), {
    action: 'login',
})

const emit = defineEmits<{
    (e: 'verified', payload: any): void
}>()

const loading = ref(true)
const enabled = ref(false)
const method = ref('slider')

const fetchSettings = async () => {
    try {
        const response = await api.get('/captcha/settings')
        const data = response.data?.data || response.data
        
        if (props.action === 'login') {
            enabled.value = data.enabled_login
        } else if (props.action === 'register') {
            enabled.value = data.enabled_register
        } else if (props.action === 'comment') {
            enabled.value = data.enabled_comment || data.enabled_guest_comment
        } else if (props.action === 'contact') {
            enabled.value = data.enabled_contact
        } else {
            enabled.value = false
        }
        
        method.value = data.method
    } catch (e) {
        console.error('Failed to fetch captcha settings:', e)
        enabled.value = false
    } finally {
        loading.value = false
    }
}

const onVerified = (payload: any) => {
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
