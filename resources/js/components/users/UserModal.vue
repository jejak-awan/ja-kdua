<template>
    <div class="fixed inset-0 z-50 overflow-y-auto bg-black bg-opacity-50" @click.self="$emit('close')">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
                <!-- Header -->
                <div class="flex items-center justify-between p-6 border-b sticky top-0 bg-white z-10">
                    <h3 class="text-lg font-semibold">
                        {{ user ? 'Edit User' : 'Create User' }}
                    </h3>
                    <button
                        @click="$emit('close')"
                        class="text-gray-400 hover:text-gray-600"
                    >
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Content -->
                <form @submit.prevent="handleSubmit" class="p-6 space-y-4">
                    <!-- Avatar -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Avatar
                        </label>
                        <div class="flex items-center space-x-4">
                            <div v-if="form.avatar" class="flex-shrink-0">
                                <img
                                    :src="form.avatar"
                                    alt="Avatar"
                                    class="h-20 w-20 rounded-full object-cover"
                                />
                            </div>
                            <div v-else class="h-20 w-20 rounded-full bg-indigo-100 flex items-center justify-center">
                                <span class="text-indigo-600 font-medium text-xl">
                                    {{ form.name?.charAt(0)?.toUpperCase() || 'U' }}
                                </span>
                            </div>
                            <div>
                                <MediaPicker
                                    @selected="(media) => form.avatar = media.url"
                                    label="Select Avatar"
                                />
                                <button
                                    v-if="form.avatar"
                                    type="button"
                                    @click="form.avatar = null"
                                    class="mt-2 text-sm text-red-600 hover:text-red-800"
                                >
                                    Remove Avatar
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Name -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Name <span class="text-red-500">*</span>
                        </label>
                        <input
                            v-model="form.name"
                            type="text"
                            required
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                            placeholder="Full name"
                        />
                    </div>

                    <!-- Email -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Email <span class="text-red-500">*</span>
                        </label>
                        <input
                            v-model="form.email"
                            type="email"
                            required
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                            placeholder="user@example.com"
                        />
                    </div>

                    <!-- Password (only for new users) -->
                    <div v-if="!user">
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Password <span class="text-red-500">*</span>
                        </label>
                        <input
                            v-model="form.password"
                            type="password"
                            required
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                            placeholder="Minimum 8 characters"
                        />
                        <p class="mt-1 text-xs text-gray-500">Leave empty when editing to keep current password</p>
                    </div>

                    <!-- Phone -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Phone
                        </label>
                        <input
                            v-model="form.phone"
                            type="tel"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                            placeholder="+1234567890"
                        />
                    </div>

                    <!-- Bio -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Bio
                        </label>
                        <textarea
                            v-model="form.bio"
                            rows="3"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                            placeholder="User biography"
                        ></textarea>
                    </div>

                    <!-- Website -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Website
                        </label>
                        <input
                            v-model="form.website"
                            type="url"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                            placeholder="https://example.com"
                        />
                    </div>

                    <!-- Location -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Location
                        </label>
                        <input
                            v-model="form.location"
                            type="text"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                            placeholder="City, Country"
                        />
                    </div>

                    <!-- Roles -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Roles <span class="text-red-500">*</span>
                        </label>
                        <div v-if="loading" class="text-sm text-gray-500">
                            Loading roles...
                        </div>
                        <div v-else-if="availableRoles.length > 0" class="space-y-2 max-h-60 overflow-y-auto">
                            <label
                                v-for="role in availableRoles"
                                :key="role.id"
                                class="flex items-center"
                            >
                                <input
                                    v-model="form.roles"
                                    type="checkbox"
                                    :value="role.id"
                                    class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                                />
                                <span class="ml-2 text-sm text-gray-900">{{ role.name }}</span>
                            </label>
                        </div>
                        <p v-if="availableRoles.length === 0 && !loading" class="text-xs text-gray-500 mt-2">
                            No roles available
                        </p>
                    </div>
                </form>

                <!-- Footer -->
                <div class="flex items-center justify-end space-x-3 p-6 border-t sticky bottom-0 bg-white">
                    <button
                        @click="$emit('close')"
                        class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50"
                    >
                        Cancel
                    </button>
                    <button
                        @click="handleSubmit"
                        :disabled="saving"
                        class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 disabled:opacity-50"
                    >
                        {{ saving ? 'Saving...' : (user ? 'Update' : 'Create') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue';
import api from '../../services/api';
import MediaPicker from '../MediaPicker.vue';

const props = defineProps({
    user: {
        type: Object,
        default: null,
    },
});

const emit = defineEmits(['close', 'saved']);

const saving = ref(false);
const loading = ref(false);
const availableRoles = ref([]);

const form = ref({
    name: '',
    email: '',
    password: '',
    phone: '',
    bio: '',
    website: '',
    location: '',
    avatar: null,
    roles: [],
});

const fetchRoles = async () => {
    // Prevent multiple simultaneous calls
    if (loading.value || availableRoles.value.length > 0) {
        return;
    }
    
    loading.value = true;
    try {
        const response = await api.get('/admin/cms/roles');
        if (response && response.data) {
            availableRoles.value = response.data.data || response.data || [];
        } else {
            availableRoles.value = [];
        }
    } catch (error) {
        console.error('Failed to fetch roles:', error);
        availableRoles.value = [];
    } finally {
        loading.value = false;
    }
};

const loadUser = () => {
    if (props.user) {
        form.value = {
            name: props.user.name || '',
            email: props.user.email || '',
            password: '', // Don't load password
            phone: props.user.phone || '',
            bio: props.user.bio || '',
            website: props.user.website || '',
            location: props.user.location || '',
            avatar: props.user.avatar || null,
            roles: props.user.roles?.map(r => r.id) || [],
        };
    }
};

const handleSubmit = async () => {
    if (form.value.roles.length === 0) {
        alert('Please select at least one role');
        return;
    }

    saving.value = true;
    try {
        const payload = {
            ...form.value,
        };

        // Don't send password if empty (for updates)
        if (!payload.password) {
            delete payload.password;
        }

        if (props.user) {
            await api.put(`/admin/cms/users/${props.user.id}`, payload);
        } else {
            if (!payload.password) {
                alert('Password is required for new users');
                saving.value = false;
                return;
            }
            await api.post('/admin/cms/users', payload);
        }
        
        emit('saved');
    } catch (error) {
        console.error('Failed to save user:', error);
        alert(error.response?.data?.message || 'Failed to save user');
    } finally {
        saving.value = false;
    }
};

onMounted(() => {
    fetchRoles();
    loadUser();
});

// Watch for user prop changes
watch(() => props.user, (newUser) => {
    if (newUser) {
        loadUser();
    } else {
        // Reset form when creating new user
        form.value = {
            name: '',
            email: '',
            password: '',
            phone: '',
            bio: '',
            website: '',
            location: '',
            avatar: null,
            roles: [],
        };
    }
}, { immediate: true });
</script>

