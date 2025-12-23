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

            <div class="bg-card shadow rounded-lg p-6">
                <div class="text-center mb-6">
                    <svg class="mx-auto h-12 w-12 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                    <p class="mt-4 text-sm text-muted-foreground">
                        {{ t('features.auth.verifyEmail.resendPrompt') }}
                    </p>
                </div>

                <div class="space-y-4">
                    <button
                        @click="handleResend"
                        :disabled="loading || resendCooldown > 0"
                        class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed"
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
                <svg class="mx-auto h-12 w-12 text-green-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <p class="text-green-800 font-medium">{{ t('features.auth.verifyEmail.success') }}</p>
                <p class="text-sm text-green-600 mt-2">{{ t('features.auth.verifyEmail.redirecting') }}</p>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import { useI18n } from 'vue-i18n';
import api from '../../services/api';

const { t } = useI18n();
const router = useRouter();
const route = useRoute();

const message = ref('');
const messageType = ref('');
const loading = ref(false);
const verified = ref(false);
const resendCooldown = ref(0);
let cooldownInterval = null;

onMounted(async () => {
    // Check if there's a verification token in the URL
    if (route.query.token && route.query.email) {
        await handleVerify(route.query.token, route.query.email);
    }
});

onUnmounted(() => {
    if (cooldownInterval) {
        clearInterval(cooldownInterval);
    }
});

const handleVerify = async (token, email) => {
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
    } catch (error) {
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
                    clearInterval(cooldownInterval);
                    cooldownInterval = null;
                }
            }, 1000);
        } else {
            message.value = response.data.message || t('features.auth.verifyEmail.failed');
            messageType.value = 'error';
        }
    } catch (error) {
        message.value = error.response?.data?.message || t('features.auth.verifyEmail.failed');
        messageType.value = 'error';
    } finally {
        loading.value = false;
    }
};
</script>

