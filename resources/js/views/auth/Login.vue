<template>
    <div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8">
            <div>
                <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
                    Sign in to your account
                </h2>
            </div>
            <form class="mt-8 space-y-6" @submit.prevent="handleLogin">
                <div class="rounded-md shadow-sm -space-y-px">
                    <div>
                        <label for="email" class="sr-only">Email address</label>
                        <input
                            id="email"
                            v-model="form.email"
                            name="email"
                            type="email"
                            autocomplete="email"
                            required
                            class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-t-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 focus:z-10 sm:text-sm"
                            placeholder="Email address"
                        />
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
                            class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-b-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 focus:z-10 sm:text-sm"
                            placeholder="Password"
                        />
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
                            class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                        />
                        <label for="remember-me" class="ml-2 block text-sm text-gray-900">
                            Remember me
                        </label>
                    </div>

                    <div class="text-sm">
                        <router-link
                            :to="{ name: 'forgot-password' }"
                            class="font-medium text-indigo-600 hover:text-indigo-500"
                        >
                            Forgot your password?
                        </router-link>
                    </div>
                </div>

                <div v-if="message && !errors.email && !errors.password" class="rounded-md p-4" :class="messageType === 'error' ? 'bg-red-50 text-red-800' : 'bg-green-50 text-green-800'">
                    <p class="text-sm">{{ message }}</p>
                </div>

                <div>
                    <button
                        type="submit"
                        :disabled="loading"
                        class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        <span v-if="loading">Signing in...</span>
                        <span v-else>Sign in</span>
                    </button>
                </div>

                <div class="text-center">
                    <router-link
                        :to="{ name: 'register' }"
                        class="text-sm text-indigo-600 hover:text-indigo-500"
                    >
                        Don't have an account? Sign up
                    </router-link>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '../../stores/auth';

const router = useRouter();
const authStore = useAuthStore();

const form = reactive({
    email: '',
    password: '',
    remember: false,
});

const errors = ref({});
const message = ref('');
const messageType = ref('');
const loading = ref(false);

const handleLogin = async () => {
    loading.value = true;
    errors.value = {};
    message.value = '';
    messageType.value = '';

    try {
        const result = await authStore.login({
            email: form.email.trim(),
            password: form.password,
        });

        if (result.success) {
            message.value = 'Login successful!';
            messageType.value = 'success';
            setTimeout(() => {
                router.push({ name: 'dashboard' });
            }, 500);
        } else {
            // Handle validation errors
            if (result.errors && Object.keys(result.errors).length > 0) {
                errors.value = result.errors;
                // Don't show general message if we have field-specific errors
                message.value = '';
            } else {
                // General error message (no field-specific errors)
                message.value = result.message || 'Login failed. Please check your credentials.';
                messageType.value = 'error';
            }
        }
    } catch (error) {
        console.error('Login error:', error);
        message.value = 'An unexpected error occurred. Please try again.';
        messageType.value = 'error';
    } finally {
        loading.value = false;
    }
};
</script>
