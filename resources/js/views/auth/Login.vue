<template>
    <div class="min-h-screen flex items-center justify-center bg-muted py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8">
            <div>
                <h2 class="mt-6 text-center text-3xl font-extrabold text-foreground">
                    {{ t('features.auth.login.title') }}
                </h2>
            </div>
            <form class="mt-8 space-y-6" @submit.prevent="handleLogin">
                <div class="rounded-md -space-y-px">
                    <div>
                        <label for="email" class="sr-only">Email address</label>
                        <input
                            id="email"
                            v-model="form.email"
                            name="email"
                            type="email"
                            autocomplete="email"
                            required
                            class="appearance-none rounded-none relative block w-full px-3 py-2 border border-input placeholder-gray-500 text-foreground rounded-t-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 focus:z-10 sm:text-sm"
                            :placeholder="t('features.auth.login.emailPlaceholder')"
                        >
                        <p v-if="errors.email" class="mt-1 text-sm text-red-600">
                            {{ Array.isArray(errors.email) ? errors.email[0] : errors.email }}
                        </p>
                    </div>
                    <div>
                        <label for="password" class="sr-only">Password</label>
                        <input
                            id="password"
                            v-model="form.password"
                            name="password"
                            type="password"
                            autocomplete="current-password"
                            required
                            class="appearance-none rounded-none relative block w-full px-3 py-2 border border-input placeholder-gray-500 text-foreground rounded-b-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 focus:z-10 sm:text-sm"
                            :placeholder="t('features.auth.login.passwordPlaceholder')"
                        >
                        <p v-if="errors.password" class="mt-1 text-sm text-red-600">
                            {{ Array.isArray(errors.password) ? errors.password[0] : errors.password }}
                        </p>
                    </div>
                </div>

                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <input
                            id="remember-me"
                            name="remember-me"
                            type="checkbox"
                            v-model="form.remember"
                            class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-input rounded"
                        >
                        <label for="remember-me" class="ml-2 block text-sm text-foreground">
                            {{ t('features.auth.login.rememberMe') }}
                        </label>
                    </div>

                    <div class="text-sm">
                        <router-link
                            :to="{ name: 'forgot-password' }"
                            class="font-medium text-indigo-600 hover:text-indigo-500"
                        >
                            {{ t('features.auth.login.forgotPassword') }}
                        </router-link>
                    </div>
                </div>

                <div v-if="timeoutMessage" class="rounded-md bg-amber-50 p-4 mb-4 border border-amber-200">
                    <div class="flex">
                        <svg class="h-5 w-5 text-amber-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <p class="text-sm text-amber-800">{{ timeoutMessage }}</p>
                    </div>
                </div>

                <div v-if="rateLimited" class="rounded-md bg-red-500/20 p-4 mb-4 border border-red-200">
                    <div class="flex">
                        <svg class="h-5 w-5 text-red-600 mr-3 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <div class="flex-1">
                            <p class="text-sm font-semibold text-red-800 mb-1">{{ t('features.auth.messages.tooManyAttempts') }}</p>
                            <p class="text-sm text-red-700">
                                {{ t('features.auth.messages.retryDetails', { time: formatRetryTime(retryAfter) }) }}
                            </p>
                        </div>
                    </div>
                </div>

                <div v-if="message && !errors.email && !errors.password && !rateLimited" class="rounded-md p-4" :class="messageType === 'error' ? 'bg-red-500/20 text-red-800' : 'bg-green-500/20 text-green-800'">
                    <p class="text-sm">{{ message }}</p>
                </div>

                <div>
                    <button
                        type="submit"
                        :disabled="loading || rateLimited"
                        class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-primary-foreground bg-primary hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        <span v-if="loading">{{ t('features.auth.login.submit') }}...</span>
                        <span v-else-if="rateLimited">{{ t('features.media.modals.bulk.wait') }}...</span>
                        <span v-else>{{ t('features.auth.login.submit') }}</span>
                    </button>
                </div>

                <div class="text-center">
                    <router-link
                        :to="{ name: 'register' }"
                        class="text-sm text-indigo-600 hover:text-indigo-500"
                    >
                        {{ t('features.auth.login.noAccount') }} {{ t('features.auth.login.register') }}
                    </router-link>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted, onUnmounted } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import { useI18n } from 'vue-i18n';
import { useAuthStore } from '../../stores/auth';

const router = useRouter();
const route = useRoute();
const authStore = useAuthStore();
const { t } = useI18n();

const form = reactive({
    email: '',
    password: '',
    remember: false,
});

const errors = ref({});
const message = ref('');
const messageType = ref('');
const loading = ref(false);
const rateLimited = ref(false);
const retryAfter = ref(0);
let retryTimer = null;

// Check for session timeout
const timeoutMessage = computed(() => {
    if (route.query.timeout === '1') {
        return t('features.auth.messages.timeout');
    }
    return null;
});

onMounted(() => {
    // Clear timeout query param after displaying message
    if (route.query.timeout) {
        setTimeout(() => {
            router.replace({ name: 'login', query: { ...route.query, timeout: undefined } });
        }, 5000);
    }
});

const formatRetryTime = (seconds) => {
    if (seconds <= 0) return t('features.auth.retry.moment');
    const minutes = Math.floor(seconds / 60);
    const secs = seconds % 60;
    
    if (minutes > 0) {
        if (secs > 0) {
            return t('features.auth.retry.minutesSeconds', { minutes, seconds: secs });
        }
        return t('features.auth.retry.minutes', minutes);
    }
    return t('features.auth.retry.seconds', secs);
};

const startRetryTimer = (initialSeconds) => {
    if (retryTimer) {
        clearInterval(retryTimer);
    }
    
    retryAfter.value = initialSeconds;
    
    retryTimer = setInterval(() => {
        retryAfter.value--;
        if (retryAfter.value <= 0) {
            clearInterval(retryTimer);
            retryTimer = null;
            rateLimited.value = false;
        }
    }, 1000);
};

const handleLogin = async () => {
    loading.value = true;
    errors.value = {};
    message.value = '';
    messageType.value = '';
    rateLimited.value = false;
    
    if (retryTimer) {
        clearInterval(retryTimer);
        retryTimer = null;
    }

    try {
        const result = await authStore.login({
            email: form.email.trim(),
            password: form.password,
        });

        if (result.success) {
            message.value = t('features.auth.messages.success');
            messageType.value = 'success';
            rateLimited.value = false;
            
            // Fetch user data to ensure we have the latest info (roles, permissions)
            await authStore.fetchUser();
            
            // Check if there's a redirect query parameter
            const redirectPath = route.query.redirect;
            
            // Use a slightly longer delay to let Pinia/Router fully settled
            setTimeout(() => {
                const target = (redirectPath && typeof redirectPath === 'string' && !redirectPath.includes('/login') && !redirectPath.includes('/419')) 
                    ? redirectPath 
                    : { name: 'dashboard' };
                
                router.replace(target);
            }, 600);
        } else {
            // Handle rate limiting
            if (result.rateLimited && result.retryAfter) {
                rateLimited.value = true;
                startRetryTimer(result.retryAfter);
                message.value = '';
            } else {
                rateLimited.value = false;
                // Handle validation errors
                if (result.errors && Object.keys(result.errors).length > 0) {
                    errors.value = result.errors;
                    // Don't show general message if we have field-specific errors
                    message.value = '';
                } else {
                    // General error message (no field-specific errors)
                    message.value = result.message || t('features.auth.messages.failed');
                    messageType.value = 'error';
                }
            }
        }
    } catch (error) {
        console.error('Login error:', error);
        message.value = t('features.auth.messages.error');
        messageType.value = 'error';
        rateLimited.value = false;
    } finally {
        loading.value = false;
    }
};

// Cleanup on unmount
onUnmounted(() => {
    if (retryTimer) {
        clearInterval(retryTimer);
    }
});
</script>
