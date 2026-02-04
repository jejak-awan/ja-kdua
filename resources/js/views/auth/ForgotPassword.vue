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
                    <div class="inline-flex h-16 w-16 items-center justify-center rounded-2xl bg-white shadow-2xl mb-4 animate-pulse">
                        <img v-if="cmsStore.siteSettings?.site_logo" :src="cmsStore.siteSettings.site_logo" :alt="cmsStore.siteSettings.site_name" class="h-10 w-auto object-contain" />
                        <LayoutTemplate v-else class="h-8 w-8 text-primary" />
                    </div>
                    
                    <h3 class="text-2xl font-black text-white drop-shadow-md mb-2 tracking-tight">
                        {{ cmsStore.siteSettings?.site_name || 'Security First' }}
                    </h3>
                    <p class="text-white/80 text-base leading-relaxed font-medium drop-shadow-sm">
                        {{ cmsStore.siteSettings?.site_description || 'We take your security seriously. Your data is protected with industrial-grade encryption.' }}
                    </p>
                </div>

                <!-- Branding Tag -->
                <div class="absolute bottom-6 text-center w-full z-10">
                    <p class="text-[10px] text-white/40 font-black tracking-[0.2em] uppercase drop-shadow-sm">
                        {{ cmsStore.siteSettings?.site_name || 'Janari Digital Solutions' }} &bull; {{ new Date().getFullYear() }}
                    </p>
                </div>
            </div>

            <!-- Right Column: Forgot Password Form -->
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
                        Forgot password?
                    </h2>
                    <p class="mt-0.5 text-muted-foreground text-[10px]">
                        {{ t('features.auth.forgotPassword.subtitle') || 'Enter your email and we\'ll send you a reset link' }}
                    </p>
                </div>

                <form class="space-y-2" @submit.prevent="handleSubmit">
                    <div class="space-y-1">
                        <Label for="email" class="text-[10px] uppercase tracking-wider font-bold ml-1 text-muted-foreground/80">{{ t('common.labels.email') }}</Label>
                        <Input
                            id="email"
                            v-model="form.email"
                            name="email"
                            type="email"
                            autocomplete="email"
                            required
                            class="auth-input h-9 text-sm"
                            :class="errors.email ? 'border-destructive/50 ring-destructive/20 focus:border-destructive' : ''"
                            :placeholder="t('features.auth.login.emailPlaceholder')"
                        />
                        <p v-if="errors.email" class="text-[10px] text-destructive font-medium ml-1">{{ errors.email[0] }}</p>
                    </div>

                    <div v-if="message" class="rounded-xl p-2 text-[10px] border animate-fade" :class="messageType === 'error' ? 'bg-destructive/10 text-destructive border-destructive/20' : 'bg-success/10 text-success border-success/20'">
                        {{ message }}
                    </div>

                    <Button type="submit" class="w-full h-9 auth-button-gradient mt-1" :disabled="loading || !isValid">
                        <Loader2 v-if="loading" class="mr-2 h-3 w-3 animate-spin" />
                        <span v-if="loading" class="text-xs">{{ t('features.auth.verifyEmail.sending') }}</span>
                        <span v-else class="text-xs">{{ t('features.auth.forgotPassword.submit') }}</span>
                    </Button>

                    <div class="text-center text-[10px] text-muted-foreground mt-3">
                        <router-link :to="{ name: 'login' }" class="inline-flex items-center font-bold text-primary hover:text-primary/80 transition-all group">
                            <ArrowLeft class="mr-2 h-3 w-3 transition-transform group-hover:-translate-x-1" />
                            {{ t('features.auth.forgotPassword.backToLogin') }}
                        </router-link>
                    </div>
                </form>
            </div>
</div>
    </div>
</template>

<script setup lang="ts">
import { ref, reactive, computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { useAuthStore } from '../../stores/auth';
import { useCmsStore } from '../../stores/cms';
import { useFormValidation } from '../../composables/useFormValidation';
import { forgotPasswordSchema } from '../../schemas/auth';
import Loader2 from 'lucide-vue-next/dist/esm/icons/loader-circle.js';
import ArrowLeft from 'lucide-vue-next/dist/esm/icons/arrow-left.js';
import LayoutTemplate from 'lucide-vue-next/dist/esm/icons/layout-template.js';
import authBg from '../../../images/auth-bg.png';

// Shadcn Components
import {
    Button,
    Input,
    Label
} from '../../components/ui';

const { t } = useI18n();
const cmsStore = useCmsStore();
const authStore = useAuthStore();
const { errors, validateWithZod, clearErrors } = useFormValidation(forgotPasswordSchema);

const form = reactive({
    email: '',
});

const isValid = computed(() => {
    return !!form.email;
});

const message = ref('');
const messageType = ref('');
const loading = ref(false);

const handleSubmit = async () => {
    // Client-side validation first
    if (!validateWithZod(form)) {
        return;
    }

    loading.value = true;
    clearErrors();
    message.value = '';

    const result = await authStore.forgotPassword(form.email);

    if (result.success) {
        message.value = result.message || '';
        messageType.value = 'success';
    } else {
        message.value = result.message || '';
        messageType.value = 'error';
    }

    loading.value = false;
};
</script>

