<template>
    <div class="min-h-screen flex items-center justify-center bg-muted py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8">
            <div>
                <h2 class="mt-6 text-center text-3xl font-extrabold text-foreground">
                    {{ t('features.auth.resetPassword.title') }}
                </h2>
            </div>
            <form class="mt-8 space-y-6" @submit.prevent="handleSubmit">
                <div class="space-y-4">
                    <div class="space-y-2">
                        <Label for="email">{{ t('common.labels.email') }}</Label>
                        <Input
                            id="email"
                            v-model="form.email"
                            name="email"
                            type="email"
                            required
                        />
                    </div>
                    <div class="space-y-2">
                        <Label for="token">{{ t('features.auth.resetPassword.tokenLabel') }}</Label>
                        <Input
                            id="token"
                            v-model="form.token"
                            name="token"
                            type="text"
                            required
                        />
                    </div>
                    <div class="space-y-2">
                        <Label for="password">{{ t('common.labels.password') }}</Label>
                        <Input
                            id="password"
                            v-model="form.password"
                            name="password"
                            type="password"
                            required
                            :class="{ 'border-destructive focus-visible:ring-destructive': errors.password }"
                        />
                        <p v-if="errors.password" class="text-sm text-destructive">{{ errors.password[0] }}</p>
                    </div>
                    <div class="space-y-2">
                        <Label for="password_confirmation">{{ t('common.labels.confirmPassword') }}</Label>
                        <Input
                            id="password_confirmation"
                            v-model="form.password_confirmation"
                            name="password_confirmation"
                            type="password"
                            required
                        />
                    </div>
                </div>

                <div v-if="message" class="rounded-md p-4" :class="messageType === 'error' ? 'bg-red-500/20 text-red-800' : 'bg-green-500/20 text-green-800'">
                    <p class="text-sm">{{ message }}</p>
                </div>

                <div>
                    <button
                        type="submit"
                        :disabled="loading"
                        class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-primary-foreground bg-primary hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        <span v-if="loading">{{ t('common.messages.loading.processing') }}</span>
                        <span v-else>{{ t('features.auth.resetPassword.submit') }}</span>
                    </button>
                </div>

                <div class="text-center">
                    <router-link
                        :to="{ name: 'login' }"
                        class="text-sm text-indigo-600 hover:text-indigo-500"
                    >
                        {{ t('features.auth.forgotPassword.backToLogin') }}
                    </router-link>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import { useI18n } from 'vue-i18n';
import { useAuthStore } from '../../stores/auth';
import { useFormValidation } from '../../composables/useFormValidation';
import { resetPasswordSchema } from '../../schemas/auth';
import Input from '../../components/ui/input.vue';
import Label from '../../components/ui/label.vue';

const router = useRouter();
const route = useRoute();
const { t } = useI18n();
const authStore = useAuthStore();
const { errors, validateWithZod, setErrors, clearErrors } = useFormValidation(resetPasswordSchema);

const form = reactive({
    email: '',
    token: '',
    password: '',
    password_confirmation: '',
});

const message = ref('');
const messageType = ref('');
const loading = ref(false);

onMounted(() => {
    if (route.query.token) {
        form.token = route.query.token;
    }
    if (route.query.email) {
        form.email = route.query.email;
    }
});

const handleSubmit = async () => {
    // Client-side validation first
    if (!validateWithZod(form)) {
        return;
    }

    loading.value = true;
    clearErrors();
    message.value = '';

    const result = await authStore.resetPassword(form);

    if (result.success) {
        message.value = result.message;
        messageType.value = 'success';
        setTimeout(() => {
            router.push({ name: 'login' });
        }, 2000);
    } else {
        message.value = result.message;
        messageType.value = 'error';
        setErrors(result.errors || {});
    }

    loading.value = false;
};
</script>

