<template>
    <div class="fixed inset-0 z-50 overflow-y-auto bg-black bg-opacity-50" @click.self="$emit('close')">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="bg-card rounded-lg shadow-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
                <!-- Header -->
                <div class="flex items-center justify-between p-6 border-b sticky top-0 bg-card z-10">
                    <h3 class="text-lg font-semibold">
                        {{ user ? $t('features.users.modals.user.titleEdit') : $t('features.users.modals.user.titleCreate') }}
                    </h3>
                    <button
                        @click="$emit('close')"
                        class="text-gray-400 hover:text-muted-foreground"
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
                        <label class="block text-sm font-medium text-foreground mb-2">
                            {{ $t('features.users.modals.user.avatar') }}
                        </label>
                        <div class="flex items-center space-x-4">
                            <div v-if="form.avatar" class="flex-shrink-0">
                                <img
                                    :src="form.avatar"
                                    :alt="$t('features.users.modals.user.avatar')"
                                    class="h-20 w-20 rounded-full object-cover"
                                >
                            </div>
                            <div v-else class="h-20 w-20 rounded-full bg-indigo-100 flex items-center justify-center">
                                <span class="text-indigo-600 font-medium text-xl">
                                    {{ form.name?.charAt(0)?.toUpperCase() || 'U' }}
                                </span>
                            </div>
                            <div>
                                <MediaPicker
                                    @selected="(media) => form.avatar = media.url"
                                    :label="$t('features.users.modals.user.selectAvatar')"
                                />
                                <button
                                    v-if="form.avatar"
                                    type="button"
                                    @click="form.avatar = null"
                                    class="mt-2 text-sm text-red-600 hover:text-red-800"
                                >
                                    {{ $t('features.users.modals.user.removeAvatar') }}
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Name -->
                    <div>
                        <label class="block text-sm font-medium text-foreground mb-1">
                            {{ $t('features.users.modals.user.name') }} <span class="text-red-500">*</span>
                        </label>
                        <input
                            v-model="form.name"
                            type="text"
                            required
                            class="w-full px-3 py-2 border border-input bg-card text-foreground rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                            :placeholder="$t('features.users.modals.user.namePlaceholder')"
                        >
                    </div>

                    <!-- Email -->
                    <div>
                        <label class="block text-sm font-medium text-foreground mb-1">
                            {{ $t('features.users.modals.user.email') }} <span class="text-red-500">*</span>
                        </label>
                        <input
                            v-model="form.email"
                            type="email"
                            required
                            class="w-full px-3 py-2 border border-input bg-card text-foreground rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                            :placeholder="$t('features.users.modals.user.emailPlaceholder')"
                        >
                    </div>

                    <!-- Password (only for new users) -->
                    <div v-if="!user">
                        <label class="block text-sm font-medium text-foreground mb-1">
                            {{ $t('features.users.modals.user.password') }} <span class="text-red-500">*</span>
                        </label>
                        <input
                            v-model="form.password"
                            type="password"
                            required
                            class="w-full px-3 py-2 border border-input bg-card text-foreground rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                            :placeholder="$t('features.users.modals.user.passwordPlaceholder')"
                        >
                        <p class="mt-1 text-xs text-muted-foreground">{{ $t('features.users.modals.user.passwordHint') }}</p>
                    </div>

                    <!-- Phone -->
                    <div>
                        <label class="block text-sm font-medium text-foreground mb-1">
                            {{ $t('features.users.modals.user.phone') }}
                        </label>
                        <input
                            v-model="form.phone"
                            type="tel"
                            class="w-full px-3 py-2 border border-input bg-card text-foreground rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                            :placeholder="$t('features.users.modals.user.phonePlaceholder')"
                        >
                    </div>

                    <!-- Bio -->
                    <div>
                        <label class="block text-sm font-medium text-foreground mb-1">
                            {{ $t('features.users.modals.user.bio') }}
                        </label>
                        <textarea
                            v-model="form.bio"
                            rows="3"
                            class="w-full px-3 py-2 border border-input bg-card text-foreground rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                            :placeholder="$t('features.users.modals.user.bioPlaceholder')"
                        />
                    </div>

                    <!-- Website -->
                    <div>
                        <label class="block text-sm font-medium text-foreground mb-1">
                            {{ $t('features.users.modals.user.website') }}
                        </label>
                        <input
                            v-model="form.website"
                            type="url"
                            class="w-full px-3 py-2 border border-input bg-card text-foreground rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                            :placeholder="$t('features.users.modals.user.websitePlaceholder')"
                        >
                    </div>

                    <!-- Location -->
                    <div>
                        <label class="block text-sm font-medium text-foreground mb-1">
                            {{ $t('features.users.modals.user.location') }}
                        </label>
                        <input
                            v-model="form.location"
                            type="text"
                            class="w-full px-3 py-2 border border-input bg-card text-foreground rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                            :placeholder="$t('features.users.modals.user.locationPlaceholder')"
                        >
                    </div>

                    <!-- Roles -->
                    <div>
                        <label class="block text-sm font-medium text-foreground mb-1">
                            {{ $t('features.users.modals.user.roles') }} <span class="text-red-500">*</span>
                        </label>
                        <div v-if="loading" class="text-sm text-muted-foreground">
                            {{ $t('features.users.modals.user.loadingRoles') }}
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
                                    class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-input rounded"
                                >
                                <span class="ml-2 text-sm text-foreground">{{ role.name }}</span>
                            </label>
                        </div>
                        <p v-if="availableRoles.length === 0 && !loading" class="text-xs text-muted-foreground mt-2">
                            {{ $t('features.users.modals.user.noRoles') }}
                        </p>
                    </div>
                </form>

                <!-- Footer -->
                <div class="flex items-center justify-end space-x-3 p-6 border-t sticky bottom-0 bg-card">
                    <button
                        @click="$emit('close')"
                        class="px-4 py-2 border border-input bg-card text-foreground rounded-md text-foreground hover:bg-muted"
                    >
                        {{ $t('common.cancel') }}
                    </button>
                    <button
                        @click="handleSubmit"
                        :disabled="saving"
                        class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 disabled:opacity-50"
                    >
                        {{ saving ? $t('features.users.modals.user.saving') : (user ? $t('common.update') : $t('common.create')) }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import api from '../../services/api';
import MediaPicker from '../MediaPicker.vue';

const props = defineProps({
    user: {
        type: Object,
        default: null,
    },
});

const emit = defineEmits(['close', 'saved']);

const { t } = useI18n();
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
        alert(t('features.users.messages.roleRequired'));
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
                alert(t('features.users.messages.passwordRequired'));
                saving.value = false;
                return;
            }
            await api.post('/admin/cms/users', payload);
        }
        
        emit('saved');
    } catch (error) {
        console.error('Failed to save user:', error);
        alert(error.response?.data?.message || t('features.users.messages.saveFailed'));
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

