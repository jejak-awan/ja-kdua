<template>
    <div class="min-h-screen flex items-center justify-center bg-muted/40 px-4 py-12 sm:px-6 lg:px-8">
        <Card class="w-full max-w-md">
            <CardHeader class="space-y-1">
                <CardTitle class="text-2xl font-bold text-center tracking-tight">
                    {{ t('features.auth.login.title') }}
                </CardTitle>
                <CardDescription class="text-center">
                    {{ t('features.auth.login.subtitle') || 'Enter your email to sign in to your account' }}
                </CardDescription>
            </CardHeader>
            <CardContent>
                <form class="space-y-4" @submit.prevent="handleLogin">
                    <div class="space-y-2">
                        <Label for="email">{{ t('common.labels.email') }}</Label>
                        <Input
                            id="email"
                            v-model="form.email"
                            name="email"
                            type="email"
                            autocomplete="email"
                            required
                            :class="{ 'border-destructive focus-visible:ring-destructive': errors.email }"
                            :placeholder="t('features.auth.login.emailPlaceholder')"
                        />
                        <p v-if="errors.email" class="text-sm text-destructive font-medium">
                            {{ Array.isArray(errors.email) ? errors.email[0] : errors.email }}
                        </p>
                    </div>
                    <div class="space-y-2">
                        <div class="flex items-center justify-between">
                            <Label for="password">{{ t('common.labels.password') }}</Label>
                            <router-link
                                :to="{ name: 'forgot-password' }"
                                class="text-sm font-medium text-primary hover:underline"
                            >
                                {{ t('features.auth.login.forgotPassword') }}
                            </router-link>
                        </div>
                        <Input
                            id="password"
                            v-model="form.password"
                            name="password"
                            type="password"
                            autocomplete="current-password"
                            required
                            :class="{ 'border-destructive focus-visible:ring-destructive': errors.password }"
                            :placeholder="t('features.auth.login.passwordPlaceholder')"
                        />
                        <p v-if="errors.password" class="text-sm text-destructive font-medium">
                            {{ Array.isArray(errors.password) ? errors.password[0] : errors.password }}
                        </p>
                    </div>

                    <div class="flex items-center space-x-2">
                        <Checkbox 
                            id="remember-me" 
                            name="remember-me"
                            :checked="form.remember" 
                            @update:checked="(v) => form.remember = v"
                        />
                        <label for="remember-me" class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">
                            {{ t('features.auth.login.rememberMe') }}
                        </label>
                    </div>

                    <div v-if="timeoutMessage" class="rounded-md bg-amber-500/15 p-3 text-sm text-amber-600 dark:text-amber-400 border border-amber-500/20">
                        {{ timeoutMessage }}
                    </div>

                    <div v-if="rateLimited" class="rounded-md bg-destructive/15 p-3 text-sm text-destructive border border-destructive/20">
                        <p class="font-semibold">{{ t('features.auth.messages.tooManyAttempts') }}</p>
                        <p>{{ t('features.auth.messages.retryDetails', { time: formatRetryTime(retryAfter) }) }}</p>
                    </div>

                    <div v-if="message && !errors.email && !errors.password && !rateLimited" class="rounded-md p-3 text-sm border" :class="messageType === 'error' ? 'bg-destructive/15 text-destructive border-destructive/20' : 'bg-emerald-500/15 text-emerald-600 dark:text-emerald-400 border-emerald-500/20'">
                        {{ message }}
                    </div>

                    <Button type="submit" class="w-full" :disabled="loading || rateLimited || !isValid">
                        <Loader2 v-if="loading" class="mr-2 h-4 w-4 animate-spin" />
                        <span v-if="loading">{{ t('features.auth.login.submit') }}...</span>
                        <span v-else-if="rateLimited">{{ t('features.media.modals.bulk.wait') }}...</span>
                        <span v-else>{{ t('features.auth.login.submit') }}</span>
                    </Button>

                    <!-- Registration Disabled Info -->
                    <div v-if="registrationDisabledMessage" class="rounded-md bg-amber-500/15 p-3 text-sm text-amber-600 dark:text-amber-400 border border-amber-500/20">
                        {{ registrationDisabledMessage }}
                    </div>

                    <div v-if="registrationEnabled" class="text-center text-sm text-muted-foreground mt-4">
                        {{ t('features.auth.login.noAccount') }} 
                        <router-link :to="{ name: 'register' }" class="font-medium text-primary hover:underline">
                            {{ t('features.auth.login.register') }}
                        </router-link>
                    </div>
                </form>
            </CardContent>
        </Card>
    </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted, onUnmounted } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import { useI18n } from 'vue-i18n';
import { useAuthStore } from '../../stores/auth';
import { useFormValidation } from '../../composables/useFormValidation';
import { loginSchema } from '../../schemas/auth';
import { Loader2 } from 'lucide-vue-next';
import api from '../../services/api';

// Shadcn Components
import Card from '../../components/ui/card.vue';
import CardHeader from '../../components/ui/card-header.vue';
import CardTitle from '../../components/ui/card-title.vue';
import CardDescription from '../../components/ui/card-description.vue';
import CardContent from '../../components/ui/card-content.vue';
import Button from '../../components/ui/button.vue';
import Input from '../../components/ui/input.vue';
import Label from '../../components/ui/label.vue';
import Checkbox from '../../components/ui/checkbox.vue';

const router = useRouter();
const route = useRoute();
const authStore = useAuthStore();
const { t } = useI18n();
const { errors, validateWithZod, setErrors, clearErrors } = useFormValidation(loginSchema);

const form = reactive({
    email: '',
    password: '',
    remember: false,
});

const isValid = computed(() => {
    return !!form.email && !!form.password;
});

const message = ref('');
const messageType = ref('');
const loading = ref(false);
const rateLimited = ref(false);
const retryAfter = ref(0);
let retryTimer = null;
const registrationEnabled = ref(true);
const registrationDisabledMessage = ref('');

// Check for session timeout
const timeoutMessage = computed(() => {
    if (route.query.timeout === '1') {
        return t('features.auth.messages.timeout');
    }
    return null;
});

onMounted(async () => {
    // Check if registration is enabled
    try {
        const response = await api.get('/api/v1/public/settings');
        const settings = response.data?.data || response.data;
        registrationEnabled.value = settings.enable_registration !== false;
    } catch (error) {
        console.error('Failed to fetch public settings:', error);
        // Default to showing registration link if we can't check
        registrationEnabled.value = true;
    }

    // Check for registration_disabled redirect info
    if (route.query.info === 'registration_disabled') {
        registrationDisabledMessage.value = 'Registration is currently disabled.';
        // Clear the query param
        setTimeout(() => {
            router.replace({ name: 'login', query: {} });
        }, 5000);
    }

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
    // Client-side validation first (instant feedback)
    if (!validateWithZod(form)) {
        return;
    }

    loading.value = true;
    clearErrors();
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
                // Handle validation errors from server
                if (result.errors && Object.keys(result.errors).length > 0) {
                    setErrors(result.errors);
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
