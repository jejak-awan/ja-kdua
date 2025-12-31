<template>
    <div class="min-h-screen flex items-center justify-center bg-muted py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8">
            <div>
                <h2 class="mt-6 text-center text-3xl font-extrabold text-foreground">
                    {{ t('features.auth.register.title') }}
                </h2>
            </div>
            <form class="mt-8 space-y-6" @submit.prevent="handleRegister">
                <div class="space-y-4">
                    <div class="space-y-2">
                        <Label for="name">{{ t('common.labels.name') }}</Label>
                        <Input
                            id="name"
                            v-model="form.name"
                            name="name"
                            type="text"
                            required
                            :class="{ 'border-destructive focus-visible:ring-destructive': errors.name }"
                            :placeholder="t('features.auth.register.namePlaceholder')"
                        />
                        <p v-if="errors.name" class="text-sm text-destructive">{{ errors.name[0] }}</p>
                    </div>
                    <div class="space-y-2">
                        <Label for="email">{{ t('common.labels.email') }}</Label>
                        <Input
                            id="email"
                            v-model="form.email"
                            name="email"
                            type="email"
                            required
                            :class="{ 'border-destructive focus-visible:ring-destructive': errors.email }"
                            :placeholder="t('features.auth.login.emailPlaceholder')"
                        />
                        <p v-if="errors.email" class="text-sm text-destructive">{{ errors.email[0] }}</p>
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
                            :placeholder="t('features.auth.login.passwordPlaceholder')"
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
                            :class="{ 'border-destructive focus-visible:ring-destructive': errors.password_confirmation }"
                            :placeholder="t('common.labels.confirmPassword')"
                        />
                        <p v-if="errors.password_confirmation" class="text-sm text-destructive">{{ errors.password_confirmation[0] }}</p>
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
                        <span v-else>{{ t('features.auth.register.submit') }}</span>
                    </button>
                </div>

                <div class="text-center">
                    <router-link
                        :to="{ name: 'login' }"
                        class="text-sm text-indigo-600 hover:text-indigo-500"
                    >
                        {{ t('features.auth.register.haveAccount') }} {{ t('features.auth.register.login') }}
                    </router-link>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive } from 'vue';
import { useRouter } from 'vue-router';
import { useI18n } from 'vue-i18n';
import { useAuthStore } from '../../stores/auth';
import { useFormValidation } from '../../composables/useFormValidation';
import { registerSchema } from '../../schemas/auth';
import Input from '../../components/ui/input.vue';
import Label from '../../components/ui/label.vue';

const router = useRouter();
const { t } = useI18n();
const authStore = useAuthStore();
const { errors, validateWithZod, setErrors, clearErrors } = useFormValidation(registerSchema);

const form = reactive({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const message = ref('');
const messageType = ref('');
const loading = ref(false);

const handleRegister = async () => {
    // Client-side validation first (instant feedback)
    if (!validateWithZod(form)) {
        return;
    }

    loading.value = true;
    clearErrors();
    message.value = '';

    const result = await authStore.register(form);

    if (result.success) {
        message.value = 'Registration successful! Please verify your email.';
        messageType.value = 'success';
        setTimeout(() => {
            router.push({ name: 'dashboard' });
        }, 2000);
    } else {
        message.value = result.message;
        messageType.value = 'error';
        setErrors(result.errors || {});
    }

    loading.value = false;
};
</script>
