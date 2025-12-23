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
                    <div>
                        <label for="name" class="block text-sm font-medium text-foreground">{{ t('common.labels.name') }}</label>
                        <input
                            id="name"
                            v-model="form.name"
                            name="name"
                            type="text"
                            required
                            class="mt-1 appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-foreground rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                            :placeholder="t('features.auth.register.namePlaceholder')"
                        >
                        <p v-if="errors.name" class="mt-1 text-sm text-red-600">{{ errors.name[0] }}</p>
                    </div>
                    <div>
                        <label for="email" class="block text-sm font-medium text-foreground">{{ t('common.labels.email') }}</label>
                        <input
                            id="email"
                            v-model="form.email"
                            name="email"
                            type="email"
                            required
                            class="mt-1 appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-foreground rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                            :placeholder="t('features.auth.login.emailPlaceholder')"
                        >
                        <p v-if="errors.email" class="mt-1 text-sm text-red-600">{{ errors.email[0] }}</p>
                    </div>
                    <div>
                        <label for="password" class="block text-sm font-medium text-foreground">{{ t('common.labels.password') }}</label>
                        <input
                            id="password"
                            v-model="form.password"
                            name="password"
                            type="password"
                            required
                            class="mt-1 appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-foreground rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                            :placeholder="t('features.auth.login.passwordPlaceholder')"
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
                            class="mt-1 appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-foreground rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                            :placeholder="t('common.labels.confirmPassword')"
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
                        class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        <span v-if="loading">{{ t('common.loading.processing') }}</span>
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

const router = useRouter();
const { t } = useI18n();
const authStore = useAuthStore();

const form = reactive({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const errors = ref({});
const message = ref('');
const messageType = ref('');
const loading = ref(false);

const handleRegister = async () => {
    loading.value = true;
    errors.value = {};
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
        errors.value = result.errors || {};
    }

    loading.value = false;
};
</script>
