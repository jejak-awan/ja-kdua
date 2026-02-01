<template>
    <div class="min-h-screen flex items-center justify-center bg-muted py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8">
            <div>
                <h2 class="mt-6 text-center text-3xl font-extrabold text-foreground">
                    {{ t('features.auth.verifyEmail.title') }}
                </h2>
                <p class="mt-2 text-center text-sm text-muted-foreground">
                    {{ t('features.auth.verifyEmail.description') }}
                </p>
            </div>

            <div v-if="message" class="rounded-md p-4" :class="messageType === 'error' ? 'bg-red-500/20 text-red-800' : 'bg-green-500/20 text-green-800'">
                <p class="text-sm">{{ message }}</p>
            </div>

            <div class="bg-card border border-border rounded-lg p-6">
                <div class="text-center mb-6">
                    <Mail class="mx-auto h-12 w-12 text-indigo-600" />
                    <p class="mt-4 text-sm text-muted-foreground">
                        {{ t('features.auth.verifyEmail.resendPrompt') }}
                    </p>
                </div>

                <div class="space-y-4">
                    <button
                        @click="handleResend"
                        :disabled="loading || resendCooldown > 0"
                        class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md text-sm font-medium text-primary-foreground bg-primary hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        <span v-if="loading">{{ t('features.auth.verifyEmail.sending') }}</span>
                        <span v-else-if="resendCooldown > 0">{{ t('features.auth.verifyEmail.cooldown', { seconds: resendCooldown }) }}</span>
                        <span v-else>{{ t('features.auth.verifyEmail.resendButton') }}</span>
                    </button>

                    <div class="text-center">
                        <router-link
                            :to="{ name: 'login' }"
                            class="text-sm text-indigo-600 hover:text-indigo-500"
                        >
                            {{ t('features.auth.forgotPassword.backToLogin') }}
                        </router-link>
                    </div>
                </div>
            </div>

            <div v-if="verified" class="bg-green-500/20 border border-green-200 rounded-lg p-6 text-center">
                <CheckCircle class="mx-auto h-12 w-12 text-green-600 mb-4" />
                <p class="text-green-800 font-medium">{{ t('features.auth.verifyEmail.success') }}</p>
                <p class="text-sm text-green-600 mt-2">{{ t('features.auth.verifyEmail.redirecting') }}</p>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import { useI18n } from 'vue-i18n';
import api from '../../services/api';
import Mail from 'lucide-vue-next/dist/esm/icons/mail.js';
import CheckCircle from 'lucide-vue-next/dist/esm/icons/circle-check.js';

const { t } = useI18n();
const router = useRouter();
const route = useRoute();

const message = ref('');
const messageType = ref('');
const loading = ref(false);
const verified = ref(false);
const resendCooldown = ref(0);
let cooldownInterval: ReturnType<typeof setInterval> | null = null;

onMounted(async () => {
    // Check if there's a verification token in the URL
    if (route.query.token && route.query.email) {
        await handleVerify(route.query.token as string, route.query.email as string);
    }
});

onUnmounted(() => {
    if (cooldownInterval) {
        clearInterval(cooldownInterval);
    }
});

const handleVerify = async (token: string, email: string) => {
    loading.value = true;
    message.value = '';
    messageType.value = '';

    try {
        const response = await api.post('/api/v1/verify-email', {
            token,
            email,
        });

        if (response.data.success) {
            verified.value = true;
            message.value = t('features.auth.verifyEmail.success');
            messageType.value = 'success';
            
            setTimeout(() => {
                router.push({ name: 'login' });
            }, 2000);
        } else {
            message.value = response.data.message || t('features.auth.verifyEmail.failed');
            messageType.value = 'error';
        }
    } catch (error: any) {
        message.value = error.response?.data?.message || t('features.auth.verifyEmail.failed');
        messageType.value = 'error';
    } finally {
        loading.value = false;
    }
};

const handleResend = async () => {
    loading.value = true;
    message.value = '';
    messageType.value = '';

    try {
        const email = route.query.email || '';
        const response = await api.post('/api/v1/resend-verification', {
            email,
        });

        if (response.data.success) {
            message.value = t('features.auth.verifyEmail.resendSuccess');
            messageType.value = 'success';
            
            // Start cooldown timer (60 seconds)
            resendCooldown.value = 60;
            cooldownInterval = setInterval(() => {
                resendCooldown.value--;
                if (resendCooldown.value <= 0) {
                    if (cooldownInterval) clearInterval(cooldownInterval);
                    cooldownInterval = null;
                }
            }, 1000);
        } else {
            message.value = response.data.message || t('features.auth.verifyEmail.failed');
            messageType.value = 'error';
        }
    } catch (error: any) {
        message.value = error.response?.data?.message || t('features.auth.verifyEmail.failed');
        messageType.value = 'error';
    } finally {
        loading.value = false;
    }
};
</script>

