<template>
    <div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8">
            <div>
                <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
                    Reset your password
                </h2>
                <p class="mt-2 text-center text-sm text-gray-600">
                    Enter your email address and we'll send you a link to reset your password.
                </p>
            </div>
            <form class="mt-8 space-y-6" @submit.prevent="handleSubmit">
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700">Email address</label>
                    <input
                        id="email"
                        v-model="form.email"
                        name="email"
                        type="email"
                        required
                        class="mt-1 appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                        placeholder="Email address"
                    >
                </div>

                <div v-if="message" class="rounded-md p-4" :class="messageType === 'error' ? 'bg-red-50 text-red-800' : 'bg-green-50 text-green-800'">
                    <p class="text-sm">{{ message }}</p>
                </div>

                <div>
                    <button
                        type="submit"
                        :disabled="loading"
                        class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        <span v-if="loading">Sending...</span>
                        <span v-else>Send reset link</span>
                    </button>
                </div>

                <div class="text-center">
                    <router-link
                        :to="{ name: 'login' }"
                        class="text-sm text-indigo-600 hover:text-indigo-500"
                    >
                        Back to login
                    </router-link>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive } from 'vue';
import { useAuthStore } from '../../stores/auth';

const authStore = useAuthStore();

const form = reactive({
    email: '',
});

const message = ref('');
const messageType = ref('');
const loading = ref(false);

const handleSubmit = async () => {
    loading.value = true;
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

