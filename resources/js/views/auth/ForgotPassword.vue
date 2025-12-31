<template>
    <div class="min-h-screen flex items-center justify-center bg-muted py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8">
            <div>
                <h2 class="mt-6 text-center text-3xl font-extrabold text-foreground">
                    {{ t('features.auth.forgotPassword.title') }}
                </h2>
                <p class="mt-2 text-center text-sm text-muted-foreground">
                    {{ t('features.auth.forgotPassword.subtitle') }}
                </p>
            </div>
            <form class="mt-8 space-y-6" @submit.prevent="handleSubmit">
                <div class="space-y-2">
                    <Label for="email">{{ t('common.labels.email') }}</Label>
                    <Input
                        id="email"
                        v-model="form.email"
                        name="email"
                        type="email"
                        required
                        :placeholder="t('features.auth.login.emailPlaceholder')"
                    />
                </div>

                <div v-if="message" class="rounded-md p-4" :class="messageType === 'error' ? 'bg-red-500/20 text-red-800' : 'bg-green-500/20 text-green-800'">
                    <p class="text-sm">{{ message }}</p>
                </div>

                <div>
                    <button
                        type="submit"
                        :disabled="loading || !isValid"
                        class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-primary-foreground bg-primary hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        <span v-if="loading">{{ t('features.auth.verifyEmail.sending') }}</span>
                        <span v-else>{{ t('features.auth.forgotPassword.submit') }}</span>
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
import { ref, reactive, computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { useAuthStore } from '../../stores/auth';
import { useFormValidation } from '../../composables/useFormValidation';
import { forgotPasswordSchema } from '../../schemas/auth';
import Input from '../../components/ui/input.vue';
import Label from '../../components/ui/label.vue';

const { t } = useI18n();
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
        message.value = result.message;
        messageType.value = 'success';
    } else {
        message.value = result.message;
        messageType.value = 'error';
    }

    loading.value = false;
};
</script>

