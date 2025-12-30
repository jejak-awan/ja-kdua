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
                    <div>
                        <label for="email" class="block text-sm font-medium text-foreground">{{ t('common.labels.email') }}</label>
                        <input
                            id="email"
                            v-model="form.email"
                            name="email"
                            type="email"
                            required
                            class="mt-1 appearance-none relative block w-full px-3 py-2 border border-input placeholder-gray-500 text-foreground rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                        >
                    </div>
                    <div>
                        <label for="token" class="block text-sm font-medium text-foreground">{{ t('features.auth.resetPassword.tokenLabel') }}</label>
                        <input
                            id="token"
                            v-model="form.token"
                            name="token"
                            type="text"
                            required
                            class="mt-1 appearance-none relative block w-full px-3 py-2 border border-input placeholder-gray-500 text-foreground rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                        >
                    </div>
                    <div>
                        <label for="password" class="block text-sm font-medium text-foreground">{{ t('common.labels.password') }}</label>
                        <input
                            id="password"
                            v-model="form.password"
                            name="password"
                            type="password"
                            required
                            class="mt-1 appearance-none relative block w-full px-3 py-2 border border-input placeholder-gray-500 text-foreground rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                        >
                        <p v-if="errors.password" class="mt-1 text-sm text-red-600">{{ errors.password[0] }}</p>
                    </div>
                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-foreground">{{ t('common.labels.confirmPassword') }}</label>
                        <input
                            id="password_confirmation"
                            v-model="form.password_confirmation"
                            name="password_confirmation"
                            type="password"
                            required
                            class="mt-1 appearance-none relative block w-full px-3 py-2 border border-input placeholder-gray-500 text-foreground rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                        >
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

const router = useRouter();
const route = useRoute();
const { t } = useI18n();
const authStore = useAuthStore();

const form = reactive({
    email: '',
    token: '',
    password: '',
    password_confirmation: '',
});

const errors = ref({});
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
    loading.value = true;
    errors.value = {};
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
        errors.value = result.errors || {};
    }

    loading.value = false;
};
</script>

