<template>
    <div class="min-h-screen flex items-center justify-center bg-linear-to-br from-background via-muted/20 to-background px-4 py-2 sm:px-6 lg:px-8">
        <div class="w-full max-w-5xl flex flex-col md:flex-row bg-card rounded-3xl shadow-2xl shadow-primary/5 overflow-hidden border border-border/40 min-h-0 animate-fade-up">
<!-- Left Column: Decorative Graphic -->
            <div class="hidden md:flex md:w-1/2 relative overflow-hidden items-center justify-center p-6">
                <!-- Premium Background Image -->
                <div class="absolute inset-0 z-0">
                    <img :src="authBg" alt="Decorative background" class="w-full h-full object-cover">
                    <!-- Dark overlay for better contrast -->
                    <div class="absolute inset-0 bg-black/10"></div>
                </div>

                <!-- Glassmorphism Container -->
                <div class="relative z-10 w-full max-w-sm backdrop-blur-xl bg-background/30 p-6 rounded-[2rem] border border-white/20 shadow-2xl text-center flex flex-col items-center animate-fade-up">
                    <div class="inline-flex h-16 w-16 items-center justify-center rounded-2xl bg-white shadow-2xl mb-4 animate-bounce" style="animation-duration: 4s">
                        <img v-if="cmsStore.siteSettings?.site_logo" :src="cmsStore.siteSettings.site_logo" :alt="cmsStore.siteSettings.site_name" class="h-10 w-auto object-contain" />
                        <LayoutTemplate v-else class="h-8 w-8 text-primary" />
                    </div>
                    
                    <h3 class="text-2xl font-black text-white drop-shadow-md mb-2 tracking-tight">
                        {{ cmsStore.siteSettings?.site_name || 'Join the Elite' }}
                    </h3>
                    <p class="text-white/80 text-base leading-relaxed font-medium drop-shadow-sm">
                        {{ cmsStore.siteSettings?.site_description || t('features.auth.register.hero_subtitle') || 'Experience the most powerful content management ecosystem ever built.' }}
                    </p>
                    
                    <!-- Premium chips -->
                    <div class="mt-6 flex flex-wrap justify-center gap-2">
                        <div class="bg-white/10 backdrop-blur-md px-3 py-1.5 rounded-xl border border-white/20 text-[10px] font-black text-white hover:bg-white/20 transition-colors shadow-lg cursor-default uppercase tracking-wider">
                            Instant Setup
                        </div>
                        <div class="bg-white/10 backdrop-blur-md px-3 py-1.5 rounded-xl border border-white/20 text-[10px] font-black text-white hover:bg-white/20 transition-colors shadow-lg cursor-default uppercase tracking-wider">
                            API-First
                        </div>
                        <div class="bg-white/10 backdrop-blur-md px-3 py-1.5 rounded-xl border border-white/20 text-[10px] font-black text-white hover:bg-white/20 transition-colors shadow-lg cursor-default uppercase tracking-wider">
                            Enterprise Ready
                        </div>
                    </div>
                </div>

                <!-- Branding Tag -->
                <div class="absolute bottom-6 text-center w-full z-10">
                    <p class="text-[10px] text-white/40 font-black tracking-[0.2em] uppercase drop-shadow-sm">
                        {{ cmsStore.siteSettings?.site_name || 'Janari Digital Solutions' }} &bull; {{ new Date().getFullYear() }}
                    </p>
                </div>
            </div>

            <!-- Right Column: Registration Form -->
            <div class="w-full md:w-1/2 p-3 sm:p-5 md:p-6 flex flex-col justify-center animate-fade">
                <!-- Internal Branding -->
                <div class="flex items-center gap-2 mb-3 group justify-center">
                    <div class="bg-primary rounded-lg p-1.5 shadow-lg shadow-primary/20 transition-transform group-hover:scale-110">
                        <img v-if="cmsStore.siteSettings?.site_logo" :src="cmsStore.siteSettings.site_logo" :alt="cmsStore.siteSettings.site_name" class="h-5 w-auto object-contain invert grayscale brightness-200" />
                        <LayoutTemplate v-else class="h-5 w-5 text-primary-foreground" />
                    </div>
                    <span class="text-lg font-black tracking-tight text-foreground">{{ cmsStore.siteSettings?.site_name || 'Janari CMS' }}</span>
                </div>

                <div class="mb-3 text-center md:text-left">
                    <h2 class="text-xl font-black tracking-tight text-foreground">
                        Create account
                    </h2>
                    <p class="mt-0.5 text-muted-foreground text-[10px]">
                        {{ t('features.auth.register.subtitle') || 'Join our community and start building' }}
                    </p>
                </div>
                <form class="space-y-2" @submit.prevent="handleRegister">
                    <div class="space-y-1">
                        <Label for="name" class="text-[10px] uppercase tracking-wider font-bold ml-1 text-muted-foreground/80">{{ t('common.labels.name') }}</Label>
                        <Input
                            id="name"
                            v-model="form.name"
                            name="name"
                            type="text"
                            required
                            class="auth-input h-9 text-sm"
                            :class="errors.name ? 'border-destructive/50 ring-destructive/20 focus:border-destructive' : ''"
                            :placeholder="t('features.auth.register.namePlaceholder')"
                        />
                        <p v-if="errors.name" class="text-[10px] text-destructive font-medium ml-1">{{ errors.name[0] }}</p>
                    </div>

                    <div class="space-y-1">
                        <Label for="email" class="text-[10px] uppercase tracking-wider font-bold ml-1 text-muted-foreground/80">{{ t('common.labels.email') }}</Label>
                        <Input
                            id="email"
                            v-model="form.email"
                            name="email"
                            type="email"
                            required
                            class="auth-input h-9 text-sm"
                            :class="errors.email ? 'border-destructive/50 ring-destructive/20 focus:border-destructive' : ''"
                            :placeholder="t('features.auth.register.emailPlaceholder')"
                        />
                        <p v-if="errors.email" class="text-[10px] text-destructive font-medium ml-1">{{ errors.email[0] }}</p>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                        <div class="space-y-1">
                            <Label for="password" class="text-[10px] uppercase tracking-wider font-bold ml-1 text-muted-foreground/80">{{ t('common.labels.password') }}</Label>
                            <Input
                                id="password"
                                v-model="form.password"
                                name="password"
                                type="password"
                                required
                                class="auth-input h-9 text-sm"
                                :class="errors.password ? 'border-destructive/50 ring-destructive/20 focus:border-destructive' : ''"
                                :placeholder="t('features.auth.register.passwordPlaceholder')"
                            />
                        </div>
                        <div class="space-y-1">
                            <Label for="password_confirmation" class="text-[10px] uppercase tracking-wider font-bold ml-1 text-muted-foreground/80">{{ t('common.labels.confirmPassword') }}</Label>
                            <Input
                                id="password_confirmation"
                                v-model="form.password_confirmation"
                                name="password_confirmation"
                                type="password"
                                required
                                class="auth-input h-9 text-sm"
                                :placeholder="t('features.auth.register.confirmPasswordPlaceholder')"
                            />
                        </div>
                        <p v-if="errors.password" class="col-span-full text-[10px] text-destructive font-medium ml-1">{{ errors.password[0] }}</p>
                    </div>

                    <div class="flex items-center space-x-2 px-1">
                        <Checkbox 
                            id="terms" 
                            name="terms"
                            :checked="form.terms" 
                            @update:checked="(v) => form.terms = v"
                            class="h-4 w-4 rounded border-border/60 transition-colors data-[state=checked]:bg-primary"
                        />
                        <label for="terms" class="text-xs font-medium leading-none text-muted-foreground hover:text-foreground transition-colors cursor-pointer">
                            {{ t('features.auth.register.terms_prefix') }} <router-link :to="{ name: 'terms' }" class="text-primary hover:underline">{{ t('features.auth.register.terms_link') }}</router-link> {{ t('features.auth.register.terms_and') }} <router-link :to="{ name: 'privacy' }" class="text-primary hover:underline">{{ t('features.auth.register.privacy_link') }}</router-link>
                        </label>
                    </div>

                    <div class="rounded-xl overflow-hidden border border-border/40 bg-muted/5">
                        <CaptchaWrapper
                            ref="captchaRef"
                            action="register"
                            @verified="onCaptchaVerified"
                        />
                    </div>

                    <div v-if="message" class="rounded-xl p-2 text-[10px] border animate-fade" :class="messageType === 'error' ? 'bg-destructive/10 text-destructive border-destructive/20' : 'bg-success/10 text-success border-success/20'">
                        {{ message }}
                    </div>

                    <Button type="submit" class="w-full h-9 auth-button-gradient mt-1" :disabled="loading || !isValid || (captchaEnabled && !captchaVerified)">
                        <Loader2 v-if="loading" class="mr-2 h-3 w-3 animate-spin" />
                        <span v-if="loading" class="text-xs">{{ t('common.messages.loading.processing') }}</span>
                        <span v-else class="text-xs">{{ t('features.auth.register.submit') }}</span>
                    </Button>

                    <div class="relative my-3">
                        <div class="absolute inset-0 flex items-center">
                            <span class="w-full border-t border-border/40"></span>
                        </div>
                        <div class="relative flex justify-center text-[10px] uppercase tracking-wider">
                            <span class="bg-card px-2 text-muted-foreground font-bold">Or</span>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-2">
                        <Button variant="outline" type="button" class="h-9 rounded-lg hover:bg-muted/50 transition-colors border-border/40">
                            <svg viewBox="0 0 24 24" class="h-4 w-4 fill-current" xmlns="http://www.w3.org/2000/svg">
                                <path d="M12.48 10.92v3.28h7.84c-.24 1.84-.9 3.28-2.12 4.41-1.37 1.34-3.26 2.4-6.4 2.4-5.34 0-9.6-4.32-9.6-9.6s4.26-9.6 9.6-9.6c3.15 0 5.48 1.25 7.18 2.87l2.29-2.29C18.9 1.5 15.86 0 12.48 0 5.58 0 0 5.58 0 12.48s5.58 12.48 12.48 12.48c3.7 0 6.64-1.21 8.87-3.56 2.3-2.3 3-5.5 3-8.1 0-.6-.05-1.12-.15-1.57z" />
                            </svg>
                        </Button>
                        <Button variant="outline" type="button" class="h-9 rounded-lg hover:bg-muted/50 transition-colors border-border/40">
                            <Github class="h-4 w-4" />
                        </Button>
                    </div>

                    <div class="text-center text-[10px] text-muted-foreground mt-3">
                        {{ t('features.auth.register.alreadyHaveAccount') }} 
                        <router-link :to="{ name: 'login' }" class="font-bold text-primary hover:text-primary/80 transition-all ml-1">
                            {{ t('features.auth.register.login') }}
                        </router-link>
                    </div>
                </form>
            </div>
</div>
    </div>
</template>

<script setup lang="ts">
import { logger } from '@/utils/logger';
import { ref, reactive, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useI18n } from 'vue-i18n';
import { useAuthStore } from '../../stores/auth';
import { useCmsStore } from '../../stores/cms';
import { useFormValidation } from '../../composables/useFormValidation';
import { registerSchema } from '../../schemas/auth';
import Loader2 from 'lucide-vue-next/dist/esm/icons/loader-circle.js';
import LayoutTemplate from 'lucide-vue-next/dist/esm/icons/layout-template.js';
import Github from 'lucide-vue-next/dist/esm/icons/github.js';
import api from '../../services/api';
import authBg from '../../../images/auth-bg.png';

// Shadcn Components
import {
    Button,
    Input,
    Label,
    Checkbox
} from '../../components/ui';
import CaptchaWrapper, { type CaptchaPayload } from '../../components/captcha/CaptchaWrapper.vue';
import type { RegisterData } from '../../types/auth';

interface CaptchaWrapperInstance {
    enabled: boolean;
    method: string;
}

const router = useRouter();
const { t } = useI18n();
const authStore = useAuthStore();
const cmsStore = useCmsStore();
const { errors, validateWithZod, setErrors, clearErrors } = useFormValidation(registerSchema);

const captchaRef = ref<CaptchaWrapperInstance | null>(null);
const captchaVerified = ref(false);
const captchaToken = ref('');
const captchaAnswer = ref('');
const captchaEnabled = computed(() => captchaRef.value?.enabled || false);

const form = reactive({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    terms: false,
});

// CaptchaPayload imported from CaptchaWrapper.vue

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
        logger.error('Failed to check registration status:', error);
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

    const payload: RegisterData & { captcha_token?: string; captcha_answer?: string } = { 
        name: form.name,
        email: form.email,
        password: form.password,
        password_confirmation: form.password_confirmation,
    };
    
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
