<template>
    <div class="max-w-4xl mx-auto">
        <!-- Header -->
        <div class="mb-6 flex justify-between items-center">
            <h1 class="text-2xl font-bold text-foreground">{{ $t('common.actions.edit') }} {{ $t('features.users.table.user') }}</h1>
            <router-link
                :to="{ name: 'users.index' }"
                class="text-muted-foreground hover:text-foreground"
            >
                ← {{ $t('common.actions.back') }}
            </router-link>
        </div>

        <div v-if="loading" class="text-center py-12">
            <p class="text-muted-foreground">{{ $t('common.messages.loading.default') }}</p>
        </div>

        <form v-else @submit.prevent="handleSubmit" class="space-y-6">
            <!-- Main Content -->
            <div class="bg-card border border-border rounded-lg p-6 space-y-6">
                <!-- Avatar -->
                <div>
                    <label class="block text-sm font-medium text-foreground mb-2">
                        {{ $t('features.users.form.avatar') }}
                    </label>
                    <div class="flex items-center space-x-4">
                        <div v-if="form.avatar" class="flex-shrink-0">
                            <img
                                :src="form.avatar"
                                :alt="form.name"
                                class="h-24 w-24 rounded-full object-cover border border-border"
                            />
                        </div>
                        <div v-else class="h-24 w-24 rounded-full bg-muted flex items-center justify-center border border-border">
                            <span class="text-muted-foreground font-medium text-2xl">
                                {{ form.name?.charAt(0)?.toUpperCase() || 'U' }}
                            </span>
                        </div>
                        <div>
                            <MediaPicker
                                @selected="(media) => form.avatar = media.url"
                                :label="$t('features.users.form.selectAvatar')"
                            ></MediaPicker>
                            <button
                                v-if="form.avatar"
                                type="button"
                                @click="form.avatar = null"
                                class="mt-2 text-sm text-destructive hover:text-destructive/90 block"
                            >
                                {{ $t('features.users.form.removeAvatar') }}
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Basic Info -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-foreground mb-1">
                            {{ $t('features.users.form.name') }} <span class="text-destructive">*</span>
                        </label>
                        <input
                            v-model="form.name"
                            type="text"
                            required
                            class="w-full px-3 py-2 border border-input bg-card text-foreground rounded-md focus:outline-none focus:ring-2 focus:ring-primary"
                            :placeholder="$t('features.users.form.placeholders.name')"
                        />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-foreground mb-1">
                            {{ $t('features.users.form.email') }} <span class="text-destructive">*</span>
                        </label>
                        <input
                            v-model="form.email"
                            type="email"
                            required
                            class="w-full px-3 py-2 border border-input bg-card text-foreground rounded-md focus:outline-none focus:ring-2 focus:ring-primary"
                            :placeholder="$t('features.users.form.placeholders.email')"
                        />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-foreground mb-1">
                            {{ $t('features.users.form.password') }}
                        </label>
                        <input
                            v-model="form.password"
                            type="password"
                            class="w-full px-3 py-2 border border-input bg-card text-foreground rounded-md focus:outline-none focus:ring-2 focus:ring-primary"
                            :placeholder="$t('features.users.form.placeholders.passwordCurrent')"
                        />
                        <p class="mt-1 text-xs text-muted-foreground">{{ $t('features.users.form.hints.passwordUpdate') }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-foreground mb-1">
                            {{ $t('features.users.form.phone') }}
                        </label>
                        <input
                            v-model="form.phone"
                            type="tel"
                            class="w-full px-3 py-2 border border-input bg-card text-foreground rounded-md focus:outline-none focus:ring-2 focus:ring-primary"
                            :placeholder="$t('features.users.form.placeholders.phone')"
                        />
                    </div>
                </div>

                <!-- Additional Info -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-foreground mb-1">
                            {{ $t('features.users.form.bio') }}
                        </label>
                        <textarea
                            v-model="form.bio"
                            rows="3"
                            class="w-full px-3 py-2 border border-input bg-card text-foreground rounded-md focus:outline-none focus:ring-2 focus:ring-primary"
                            :placeholder="$t('features.users.form.placeholders.bio')"
                        ></textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-foreground mb-1">
                            {{ $t('features.users.form.website') }}
                        </label>
                        <input
                            v-model="form.website"
                            type="url"
                            class="w-full px-3 py-2 border border-input bg-card text-foreground rounded-md focus:outline-none focus:ring-2 focus:ring-primary"
                            :placeholder="$t('features.users.form.placeholders.website')"
                        />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-foreground mb-1">
                            {{ $t('features.users.form.location') }}
                        </label>
                        <input
                            v-model="form.location"
                            type="text"
                            class="w-full px-3 py-2 border border-input bg-card text-foreground rounded-md focus:outline-none focus:ring-2 focus:ring-primary"
                            :placeholder="$t('features.users.form.placeholders.location')"
                        />
                    </div>
                </div>

                <!-- Roles -->
                <div>
                    <label class="block text-sm font-medium text-foreground mb-2">
                        {{ $t('features.users.form.roles') }} <span class="text-destructive">*</span>
                    </label>
                    <div v-if="loadingRoles" class="text-sm text-muted-foreground">
                        {{ $t('common.messages.loading.default') }}
                    </div>
                    <div v-else-if="availableRoles.length > 0" class="flex flex-wrap gap-4">
                        <label
                            v-for="role in availableRoles"
                            :key="role.id"
                            class="flex items-center space-x-2 bg-transparent border border-input px-3 py-2 rounded-md cursor-pointer hover:bg-accent hover:text-accent-foreground transition-colors"
                        >
                            <input
                                v-model="form.roles"
                                type="checkbox"
                                :value="role.id"
                                class="h-4 w-4 text-primary focus:ring-primary border-input rounded"
                            />
                            <span class="text-sm text-foreground select-none">{{ role.name }}</span>
                        </label>
                    </div>
                    <p v-else class="text-sm text-destructive">
                        {{ $t('features.users.modals.user.noRoles') }}
                    </p>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex justify-end space-x-4">
                <router-link
                    :to="{ name: 'users.index' }"
                    class="inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 border border-input bg-background hover:bg-accent hover:text-accent-foreground h-10 px-4 py-2"
                >
                    {{ $t('common.actions.cancel') }}
                </router-link>
                <button
                    type="submit"
                    :disabled="saving"
                    class="inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 bg-primary text-primary-foreground hover:bg-primary/80 h-10 px-4 py-2"
                >
                    {{ saving ? $t('common.messages.loading.saving') : $t('common.actions.save') }}
                </button>
            </div>
        </form>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import { useI18n } from 'vue-i18n';
import api from '../../../services/api';
import MediaPicker from '../../../components/MediaPicker.vue';
import { parseResponse, ensureArray, parseSingleResponse } from '../../../utils/responseParser';

const router = useRouter();
const route = useRoute();
const { t } = useI18n();

const loading = ref(true);
const saving = ref(false);
const loadingRoles = ref(false);
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
    loadingRoles.value = true;
    try {
        const response = await api.get('/admin/cms/roles');
        const { data } = parseResponse(response);
        availableRoles.value = ensureArray(data);
    } catch (error) {
        console.error('Failed to fetch roles:', error);
    } finally {
        loadingRoles.value = false;
    }
};

const fetchUser = async () => {
    loading.value = true;
    try {
        const userId = route.params.id;
        const response = await api.get(`/admin/cms/users/${userId}`);
        const data = parseSingleResponse(response);
        
        form.value = {
            name: data.name || '',
            email: data.email || '',
            password: '',
            phone: data.phone || '',
            bio: data.bio || '',
            website: data.website || '',
            location: data.location || '',
            avatar: data.avatar || null,
            roles: data.roles?.map(r => r.id) || [],
        };
    } catch (error) {
        console.error('Failed to fetch user:', error);
        alert(t('features.users.messages.fetchFailed'));
        router.push({ name: 'users.index' });
    } finally {
        loading.value = false;
    }
};

const handleSubmit = async () => {
    if (form.value.roles.length === 0) {
        alert(t('features.users.messages.roleRequired'));
        return;
    }

    saving.value = true;
    try {
        const payload = { ...form.value };
        if (!payload.password) delete payload.password;
        
        await api.put(`/admin/cms/users/${route.params.id}`, payload);
        router.push({ name: 'users.index' });
    } catch (error) {
        console.error('Failed to update user:', error);
        alert(error.response?.data?.message || t('features.users.messages.updateFailed'));
    } finally {
        saving.value = false;
    }
};

onMounted(async () => {
    await fetchRoles();
    await fetchUser();
});
</script>
