<template>
    <div class="max-w-4xl mx-auto">
        <!-- Header -->
        <div class="mb-6 flex justify-between items-center">
            <h1 class="text-2xl font-bold text-foreground">{{ $t('common.actions.edit') }} {{ $t('features.users.table.user') }}</h1>
            <router-link
                :to="{ name: 'users.index' }"
                class="text-muted-foreground hover:text-foreground text-sm flex items-center"
            >
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                {{ $t('common.actions.back') }}
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
                            <Button
                                v-if="form.avatar"
                                type="button"
                                variant="destructive"
                                size="sm"
                                @click="form.avatar = null"
                                class="mt-2"
                            >
                                {{ $t('features.users.form.removeAvatar') }}
                            </Button>
                        </div>
                    </div>
                </div>

                <!-- Basic Info -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-foreground mb-1">
                            {{ $t('features.users.form.name') }} <span class="text-destructive">*</span>
                        </label>
                        <Input
                            v-model="form.name"
                            type="text"
                            required
                            :placeholder="$t('features.users.form.placeholders.name')"
                        />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-foreground mb-1">
                            {{ $t('features.users.form.email') }} <span class="text-destructive">*</span>
                        </label>
                        <Input
                            v-model="form.email"
                            type="email"
                            required
                            :placeholder="$t('features.users.form.placeholders.email')"
                        />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-foreground mb-1">
                            {{ $t('features.users.form.password') }}
                        </label>
                        <Input
                            v-model="form.password"
                            type="password"
                            :placeholder="$t('features.users.form.placeholders.passwordCurrent')"
                        />
                        <p class="mt-1 text-xs text-muted-foreground">{{ $t('features.users.form.hints.passwordUpdate') }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-foreground mb-1">
                            {{ $t('features.users.form.phone') }}
                        </label>
                        <Input
                            v-model="form.phone"
                            type="tel"
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
                        <Textarea
                            v-model="form.bio"
                            :rows="3"
                            :placeholder="$t('features.users.form.placeholders.bio')"
                        />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-foreground mb-1">
                            {{ $t('features.users.form.website') }}
                        </label>
                        <Input
                            v-model="form.website"
                            type="url"
                            :placeholder="$t('features.users.form.placeholders.website')"
                        />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-foreground mb-1">
                            {{ $t('features.users.form.location') }}
                        </label>
                        <Input
                            v-model="form.location"
                            type="text"
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
                <Button
                    variant="outline"
                    as-child
                >
                    <router-link :to="{ name: 'users.index' }">
                        {{ $t('common.actions.cancel') }}
                    </router-link>
                </Button>
                <Button
                    type="submit"
                    :disabled="saving"
                >
                    {{ saving ? $t('common.messages.loading.saving') : $t('common.actions.save') }}
                </Button>
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
import Button from '../../../components/ui/button.vue';
import Input from '../../../components/ui/input.vue';
import Textarea from '../../../components/ui/textarea.vue';

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
