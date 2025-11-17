<template>
    <div class="p-6">
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">My Profile</h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">Manage your account information and security</p>
        </div>

        <!-- Tabs -->
        <div class="mb-6 border-b border-gray-200 dark:border-gray-700">
            <nav class="flex space-x-8">
                <button
                    v-for="tab in tabs"
                    :key="tab.key"
                    @click="activeTab = tab.key"
                    :class="[
                        'py-4 px-1 border-b-2 font-medium text-sm transition-colors',
                        activeTab === tab.key
                            ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400'
                            : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300'
                    ]"
                >
                    {{ tab.label }}
                </button>
            </nav>
        </div>

        <!-- Profile Info Tab -->
        <div v-if="activeTab === 'profile'" class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <form @submit.prevent="updateProfile" class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Name
                        </label>
                        <input
                            v-model="profileForm.name"
                            type="text"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white"
                        />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Email
                        </label>
                        <input
                            v-model="profileForm.email"
                            type="email"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white"
                        />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Phone
                        </label>
                        <input
                            v-model="profileForm.phone"
                            type="text"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white"
                        />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Location
                        </label>
                        <input
                            v-model="profileForm.location"
                            type="text"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white"
                        />
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Bio
                    </label>
                    <textarea
                        v-model="profileForm.bio"
                        rows="4"
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white"
                    />
                </div>

                <div class="flex justify-end">
                    <button
                        type="submit"
                        :disabled="saving"
                        class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 disabled:opacity-50"
                    >
                        {{ saving ? 'Saving...' : 'Save Changes' }}
                    </button>
                </div>
            </form>
        </div>

        <!-- Password Tab -->
        <div v-if="activeTab === 'password'" class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <form @submit.prevent="updatePassword" class="space-y-6 max-w-md">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Current Password
                    </label>
                    <input
                        v-model="passwordForm.current_password"
                        type="password"
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white"
                    />
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        New Password
                    </label>
                    <input
                        v-model="passwordForm.password"
                        type="password"
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white"
                    />
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        Must be at least 8 characters with uppercase, lowercase, and number
                    </p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Confirm New Password
                    </label>
                    <input
                        v-model="passwordForm.password_confirmation"
                        type="password"
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white"
                    />
                </div>

                <div class="flex justify-end">
                    <button
                        type="submit"
                        :disabled="changingPassword"
                        class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 disabled:opacity-50"
                    >
                        {{ changingPassword ? 'Changing...' : 'Change Password' }}
                    </button>
                </div>
            </form>
        </div>

        <!-- Login History Tab -->
        <div v-if="activeTab === 'history'">
            <LoginHistory />
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import api from '../../services/api';
import LoginHistory from '../../components/admin/LoginHistory.vue';

const tabs = [
    { key: 'profile', label: 'Profile Information' },
    { key: 'password', label: 'Change Password' },
    { key: 'history', label: 'Login History' },
];

const activeTab = ref('profile');
const saving = ref(false);
const changingPassword = ref(false);

const profileForm = ref({
    name: '',
    email: '',
    phone: '',
    bio: '',
    location: '',
    website: '',
});

const passwordForm = ref({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const fetchProfile = async () => {
    try {
        const response = await api.get('/profile');
        if (response.data?.success) {
            const user = response.data.data;
            profileForm.value = {
                name: user.name || '',
                email: user.email || '',
                phone: user.phone || '',
                bio: user.bio || '',
                location: user.location || '',
                website: user.website || '',
            };
        }
    } catch (error) {
        console.error('Error fetching profile:', error);
    }
};

const updateProfile = async () => {
    saving.value = true;
    try {
        await api.put('/profile', profileForm.value);
        alert('Profile updated successfully');
        await fetchProfile();
    } catch (error) {
        alert(error.response?.data?.message || 'Failed to update profile');
    } finally {
        saving.value = false;
    }
};

const updatePassword = async () => {
    changingPassword.value = true;
    try {
        await api.put('/profile/password', passwordForm.value);
        alert('Password changed successfully');
        passwordForm.value = {
            current_password: '',
            password: '',
            password_confirmation: '',
        };
    } catch (error) {
        alert(error.response?.data?.message || 'Failed to change password');
    } finally {
        changingPassword.value = false;
    }
};

onMounted(() => {
    fetchProfile();
});
</script>

