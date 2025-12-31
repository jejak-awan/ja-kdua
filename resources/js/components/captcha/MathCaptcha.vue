<template>
    <div class="captcha-math">
        <p class="text-sm text-muted-foreground mb-2">{{ t('features.auth.captcha.solveQuestion') }}</p>
        
        <div class="flex items-center gap-3">
            <div class="flex-1 bg-muted rounded-lg border border-border p-3 text-center">
                <span class="text-xl font-mono font-bold text-foreground">{{ question }}</span>
            </div>
            
            <Input
                v-model="answer"
                type="number"
                class="w-24 text-center text-lg font-mono"
                :placeholder="t('features.auth.captcha.answer')"
                :disabled="verified"
                @keyup.enter="verify"
            />
            
            <Button 
                type="button"
                :variant="verified ? 'default' : 'outline'"
                :disabled="!answer || verified"
                @click="verify"
            >
                <CheckCircle v-if="verified" class="w-4 h-4 text-green-500" />
                <span v-else>{{ t('common.actions.verify') }}</span>
            </Button>
        </div>
        
        <div class="flex items-center justify-between mt-2">
            <p v-if="error" class="text-sm text-destructive">{{ error }}</p>
            <button 
                type="button"
                class="text-sm text-muted-foreground hover:text-foreground flex items-center gap-1"
                @click="refresh"
            >
                <RefreshCw class="w-3 h-3" />
                {{ t('features.auth.captcha.newQuestion') }}
            </button>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useI18n } from 'vue-i18n'
import { CheckCircle, RefreshCw } from 'lucide-vue-next'
import api from '../../services/api'
import Button from '../ui/button.vue'
import Input from '../ui/input.vue'

const { t } = useI18n()

const emit = defineEmits(['verified', 'error'])

const token = ref('')
const question = ref('...')
const answer = ref('')
const verified = ref(false)
const error = ref('')

const generateCaptcha = async () => {
    try {
        const response = await api.get('/captcha/generate')
        const data = response.data?.data || response.data
        token.value = data.token
        question.value = data.question
        answer.value = ''
        verified.value = false
        error.value = ''
    } catch (e) {
        console.error('Failed to generate captcha:', e)
        error.value = 'Failed to load captcha'
    }
}

const verify = () => {
    if (!answer.value || verified.value) return
    
    verified.value = true
    emit('verified', { token: token.value, answer: answer.value })
}

const refresh = () => {
    generateCaptcha()
}

onMounted(() => {
    generateCaptcha()
})
</script>
