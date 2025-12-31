<template>
    <div class="captcha-math">
        <div class="flex items-center space-x-2">
            <!-- Question Box -->
            <div class="flex-none bg-muted rounded-md border border-border px-3 py-2 min-w-[3.5rem] text-center">
                <span class="text-lg font-mono font-bold text-foreground">{{ question?.replace('=', '') }}</span>
            </div>

            <!-- Input and Actions wrapper -->
            <div class="flex-1 flex items-center space-x-2">
                <div class="relative flex-1">
                    <Input
                        v-model="answer"
                        type="tel"
                        class="w-full text-center font-mono pr-8"
                        :placeholder="t('features.auth.captcha.answer')"
                        :disabled="verified"
                        @keyup.enter="verify"
                    />
                    <div v-if="verified" class="absolute right-2 top-1/2 -translate-y-1/2 pointer-events-none">
                        <CheckCircle class="w-4 h-4 text-green-500" />
                    </div>
                </div>

                <!-- Verify Button (Icon only) -->
                 <Button 
                    type="button"
                    :variant="verified ? 'default' : 'secondary'"
                    size="icon"
                    class="flex-none w-10 h-10"
                    :title="t('common.actions.verify')"
                    :disabled="!answer || verified"
                    @click="verify"
                >
                    <CheckCircle v-if="verified" class="w-4 h-4" />
                    <span v-else class="text-xs font-bold">GO</span>
                </Button>

                <!-- Refresh (only if not verified) -->
                <Button 
                    v-if="!verified"
                    type="button"
                    variant="ghost"
                    size="icon"
                    class="flex-none w-8 h-8 text-muted-foreground hover:text-foreground"
                    :title="t('features.auth.captcha.refresh')"
                    @click="refresh"
                >
                    <RefreshCw class="w-4 h-4" />
                </Button>
            </div>
        </div>
        
        <!-- Error Message -->
        <p v-if="error" class="text-xs text-destructive mt-1.5 ml-1">{{ error }}</p>
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
