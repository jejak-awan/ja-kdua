<template>
    <div class="min-h-screen flex items-center justify-center bg-muted/40 px-4 py-12 sm:px-6 lg:px-8">
        <Card class="w-full max-w-md">
            <CardHeader class="space-y-1">
                <CardTitle class="text-2xl font-bold text-center tracking-tight">
                    {{ t('features.auth.register.title') }}
                </CardTitle>
                <CardDescription class="text-center">
                    {{ t('features.auth.register.subtitle') || 'Create an account to get started' }}
                </CardDescription>
            </CardHeader>
            <CardContent>
                <form class="space-y-4" @submit.prevent="handleRegister">
                    <div class="space-y-2">
                        <Label for="name">{{ t('common.labels.name') }}</Label>
                        <Input
                            id="name"
                            v-model="form.name"
                            name="name"
                            type="text"
                            required
                            :class="errors.name ? 'border-destructive focus-visible:ring-destructive' : ''"
                            :placeholder="t('features.auth.register.namePlaceholder')"
                        />
                        <p v-if="errors.name" class="text-sm text-destructive font-medium">{{ errors.name[0] }}</p>
                    </div>
                    <div class="space-y-2">
                        <Label for="email">{{ t('common.labels.email') }}</Label>
                        <Input
                            id="email"
                            v-model="form.email"
                            name="email"
                            type="email"
                            required
                            :class="errors.email ? 'border-destructive focus-visible:ring-destructive' : ''"
                            :placeholder="t('features.auth.login.emailPlaceholder')"
                        />
                        <p v-if="errors.email" class="text-sm text-destructive font-medium">{{ errors.email[0] }}</p>
                    </div>
                    <div class="space-y-2">
                        <Label for="password">{{ t('common.labels.password') }}</Label>
                        <Input
                            id="password"
                            v-model="form.password"
                            name="password"
                            type="password"
                            required
                            :class="errors.password ? 'border-destructive focus-visible:ring-destructive' : ''"
                            :placeholder="t('features.auth.login.passwordPlaceholder')"
                        />
                        <p v-if="errors.password" class="text-sm text-destructive font-medium">{{ errors.password[0] }}</p>
                    </div>
                    <div class="space-y-2">
                        <Label for="password_confirmation">{{ t('common.labels.confirmPassword') }}</Label>
                        <Input
                            id="password_confirmation"
                            v-model="form.password_confirmation"
                            name="password_confirmation"
                            type="password"
                            required
                            :class="errors.password_confirmation ? 'border-destructive focus-visible:ring-destructive' : ''"
                            :placeholder="t('common.labels.confirmPassword')"
                        />
                        <p v-if="errors.password_confirmation" class="text-sm text-destructive font-medium">{{ errors.password_confirmation[0] }}</p>
                    </div>

                    <!-- Captcha -->
                    <CaptchaWrapper 
                        ref="captchaRef"
                        action="register"
                        @verified="onCaptchaVerified"
                    />

                    <div v-if="message" class="rounded-md p-3 text-sm border" :class="messageType === 'error' ? 'bg-destructive/15 text-destructive border-destructive/20' : 'bg-emerald-500/15 text-emerald-600 dark:text-emerald-400 border-emerald-500/20'">
                        {{ message }}
                    </div>

                    <Button type="submit" class="w-full" :disabled="loading || !isValid || (captchaEnabled && !captchaVerified)">
                        <Loader2 v-if="loading" class="mr-2 h-4 w-4 animate-spin" />
                        <span v-if="loading">{{ t('common.messages.loading.processing') }}</span>
                        <span v-else>{{ t('features.auth.register.submit') }}</span>
                    </Button>

                    <div class="text-center text-sm text-muted-foreground mt-4">
                        {{ t('features.auth.register.haveAccount') }} 
                        <router-link :to="{ name: 'login' }" class="font-medium text-primary hover:underline">
                            {{ t('features.auth.register.login') }}
                        </router-link>
                    </div>
                </form>
            </CardContent>
        </Card>
    </div>
</template>

<script setup lang="ts">
import { ref, reactive, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useI18n } from 'vue-i18n';
import { useAuthStore } from '../../stores/auth';
import { useFormValidation } from '../../composables/useFormValidation';
import { registerSchema } from '../../schemas/auth';
import Loader2 from 'lucide-vue-next/dist/esm/icons/loader-circle.js';
import api from '../../services/api';

// Shadcn Components
// Shadcn Components
import {
    Card,
    CardHeader,
    CardTitle,
    CardDescription,
    CardContent,
    Button,
    Input,
    Label
} from '../../components/ui';
import CaptchaWrapper from '../../components/captcha/CaptchaWrapper.vue';

const router = useRouter();
const { t } = useI18n();
const authStore = useAuthStore();
const { errors, validateWithZod, setErrors, clearErrors } = useFormValidation(registerSchema);

const captchaRef = ref<any>(null);
const captchaVerified = ref(false);
const captchaToken = ref('');
const captchaAnswer = ref('');
const captchaEnabled = computed(() => captchaRef.value?.enabled || false);

const form = reactive({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

interface CaptchaPayload {
    token: string;
    answer: string;
}

const onCaptchaVerified = (payload: CaptchaPayload) => {
    captchaToken.value = payload.token;
    captchaAnswer.value = payload.answer;
    captchaVerified.value = true;
};

const isValid = computed(() => {
    return !!form.name && 
           !!form.email && 
           !!form.password && 
           !!form.password_confirmation && 
           form.password === form.password_confirmation;
});

const message = ref('');
const messageType = ref('');
const loading = ref(false);
const checkingSettings = ref(true);

// Check if registration is enabled on mount
onMounted(async () => {
    try {
        const response = await api.get('/public/settings');
        const settings = response.data?.data || response.data;
        
        if (!settings.enable_registration) {
            // Redirect to login with info message
            router.replace({ name: 'login', query: { info: 'registration_disabled' } });
            return;
        }
    } catch (error) {
        console.error('Failed to check registration status:', error);
        // Allow registration if we can't check settings (fail-open)
    } finally {
        checkingSettings.value = false;
    }
});

const handleRegister = async () => {
    // Client-side validation first (instant feedback)
    if (!validateWithZod(form)) {
        return;
    }
    
    if (captchaEnabled.value && !captchaVerified.value) {
        message.value = t('features.auth.captcha.required');
        messageType.value = 'error';
        return;
    }

    loading.value = true;
    clearErrors();
    message.value = '';

    const payload: any = { ...form };
    
    if (captchaEnabled.value) {
        payload.captcha_token = captchaToken.value;
        payload.captcha_answer = captchaAnswer.value;
    }

    const result = await authStore.register(payload);

    if (result.success) {
        message.value = 'Registration successful! Please verify your email.';
        messageType.value = 'success';
        setTimeout(() => {
            router.push({ name: 'dashboard' });
        }, 2000);
    } else {
        message.value = result.message || '';
        messageType.value = 'error';
        setErrors(result.errors || {});
        
        // Reset captcha on failure
        if (captchaRef.value?.method === 'slider' || captchaRef.value?.method === 'math' || captchaRef.value?.method === 'image') {
            captchaVerified.value = false;
            captchaToken.value = '';
            captchaAnswer.value = '';
        }
    }

    loading.value = false;
};
</script>
