<template>
    <div class="captcha-image">
        <p class="text-sm text-muted-foreground mb-2">{{ t('features.auth.captcha.enterCode') }}</p>
        
        <div class="flex flex-col gap-3">
            <!-- Captcha image -->
            <div class="flex items-center gap-2">
                <div class="flex-1 bg-muted rounded-lg border border-border p-2 flex items-center justify-center min-h-[60px]">
                    <img 
                        v-if="imageData" 
                        :src="imageData" 
                        alt="Captcha"
                        class="h-12 select-none"
                        draggable="false"
                    />
                    <Loader2 v-else class="w-5 h-5 animate-spin text-muted-foreground" />
                </div>
                
                <button 
                    type="button"
                    class="p-2 rounded-lg border border-border hover:bg-muted transition-colors"
                    :title="t('features.auth.captcha.refresh')"
                    @click="refresh"
                >
                    <RefreshCw class="w-5 h-5 text-muted-foreground" />
                </button>
            </div>
            
            <!-- Input and verify -->
            <div class="flex items-center gap-2">
                <Input
                    v-model="answer"
                    type="text"
                    class="flex-1 text-center text-lg font-mono uppercase tracking-wider"
                    :placeholder="t('features.auth.captcha.typeCode')"
                    :disabled="verified"
                    maxlength="6"
                    autocomplete="off"
                    @keyup.enter="verify"
                />
                
                <Button 
                    type="button"
                    :variant="verified ? 'default' : 'outline'"
                    :disabled="answer.length < 6 || verified"
                    @click="verify"
                >
                    <CheckCircle v-if="verified" class="w-4 h-4 text-green-500" />
                    <span v-else>{{ t('common.actions.verify') }}</span>
                </Button>
            </div>
        </div>
        
        <p v-if="error" class="text-sm text-destructive mt-2">{{ error }}</p>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useI18n } from 'vue-i18n'
import { CheckCircle, RefreshCw, Loader2 } from 'lucide-vue-next'
import api from '../../services/api'
import Button from '../ui/button.vue'
import Input from '../ui/input.vue'

const { t } = useI18n()

const emit = defineEmits(['verified', 'error'])

const token = ref('')
const imageData = ref('')
const answer = ref('')
const verified = ref(false)
const error = ref('')

const generateCaptcha = async () => {
    try {
        imageData.value = ''
        const response = await api.get('/captcha/generate')
        const data = response.data?.data || response.data
        token.value = data.token
        imageData.value = data.image
        answer.value = ''
        verified.value = false
        error.value = ''
    } catch (e) {
        console.error('Failed to generate captcha:', e)
        error.value = 'Failed to load captcha'
    }
}

const verify = () => {
    if (answer.value.length < 6 || verified.value) return
    
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
