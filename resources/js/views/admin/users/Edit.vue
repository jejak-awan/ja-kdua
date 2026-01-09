<template>
    <div class="max-w-4xl mx-auto">
        <!-- Header -->
        <div class="mb-6 flex justify-between items-center">
            <h1 class="text-2xl font-bold text-foreground">{{ $t('common.actions.edit') }} {{ $t('features.users.table.user') }}</h1>
            <router-link :to="{ name: 'users.index' }">
                <Button variant="ghost" class="pl-0 hover:bg-transparent hover:text-foreground">
                    <ArrowLeft class="w-4 h-4 mr-2" />
                    {{ $t('common.actions.back') }}
                </Button>
            </router-link>
        </div>

        <div v-if="loading" class="flex flex-col items-center justify-center py-12">
            <Loader2 class="h-8 w-8 animate-spin text-muted-foreground mb-4" />
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
                    <div class="space-y-2">
                        <label class="block text-sm font-medium text-foreground">
                            {{ $t('features.users.form.name') }} <span class="text-destructive">*</span>
                        </label>
                        <Input
                            v-model="form.name"
                            type="text"
                            required
                            :class="{ 'border-destructive focus-visible:ring-destructive': errors.name }"
                            :placeholder="$t('features.users.form.placeholders.name')"
                        />
                        <p v-if="errors.name" class="text-sm text-destructive">
                            {{ Array.isArray(errors.name) ? errors.name[0] : errors.name }}
                        </p>
                    </div>

                    <div class="space-y-2">
                        <label class="block text-sm font-medium text-foreground">
                            {{ $t('features.users.form.email') }} <span class="text-destructive">*</span>
                        </label>
                        <Input
                            v-model="form.email"
                            type="email"
                            required
                            :class="{ 'border-destructive focus-visible:ring-destructive': errors.email }"
                            :placeholder="$t('features.users.form.placeholders.email')"
                        />
                        <p v-if="errors.email" class="text-sm text-destructive">
                            {{ Array.isArray(errors.email) ? errors.email[0] : errors.email }}
                        </p>
                    </div>

                    <div class="space-y-2">
                        <label class="block text-sm font-medium text-foreground">
                            {{ $t('features.users.form.password') }}
                        </label>
                        <Input
                            v-model="form.password"
                            type="password"
                            :class="{ 'border-destructive focus-visible:ring-destructive': errors.password }"
                            :placeholder="$t('features.users.form.placeholders.passwordCurrent') + ' (min 8, A-Z, a-z, 0-9)'"
                        />
                        <p class="text-xs text-muted-foreground">{{ $t('features.users.form.hints.passwordUpdate') }}</p>
                        <p v-if="errors.password" class="text-sm text-destructive">
                            {{ Array.isArray(errors.password) ? errors.password[0] : errors.password }}
                        </p>
                    </div>

                    <div class="space-y-2">
                        <label class="block text-sm font-medium text-foreground">
                            {{ $t('features.users.form.passwordConfirmation') || 'Confirm Password' }}
                        </label>
                        <Input
                            v-model="form.password_confirmation"
                            type="password"
                            :class="{ 'border-destructive focus-visible:ring-destructive': errors.password_confirmation }"
                            :placeholder="$t('features.users.form.placeholders.passwordConfirmation') || 'Repeat password'"
                        />
                        <p v-if="errors.password_confirmation" class="text-sm text-destructive">
                            {{ Array.isArray(errors.password_confirmation) ? errors.password_confirmation[0] : errors.password_confirmation }}
                        </p>
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

                    <div class="flex items-center space-x-2 mt-4">
                        <Checkbox
                            id="is_verified"
                            :checked="form.is_verified"
                            @update:checked="(checked) => form.is_verified = checked"
                        />
                        <label
                            for="is_verified"
                            class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70 cursor-pointer select-none"
                        >
                            {{ $t('features.users.form.verified') || 'Email Verified' }}
                        </label>
                    </div>
                </div>

                <!-- Roles -->
                <div>
                    <label class="block text-sm font-medium text-foreground mb-2">
                        {{ $t('features.users.form.roles') }} <span class="text-destructive">*</span>
                    </label>
                    <div v-if="loadingRoles" class="flex items-center text-sm text-muted-foreground">
                        <Loader2 class="w-4 h-4 mr-2 animate-spin" />
                        {{ $t('common.messages.loading.default') }}
                    </div>
                    <div v-else-if="availableRoles.length > 0" class="flex flex-wrap gap-4">
                        <div
                            v-for="role in availableRoles"
                            :key="role.id"
                            class="flex items-center space-x-2 border border-input px-3 py-2 rounded-md hover:bg-accent/50 transition-colors"
                        >
                            <Checkbox
                                :id="`role-${role.id}`"
                                :checked="form.roles.includes(role.id)"
                                :disabled="getRoleRank(role.name) > authStore.getRoleRank()"
                                @update:checked="(checked) => {
                                    if (checked) form.roles.push(role.id);
                                    else form.roles = form.roles.filter(id => id !== role.id);
                                }"
                            />
                            <label
                                :for="`role-${role.id}`"
                                class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70 cursor-pointer select-none"
                                :class="{ 'opacity-50': getRoleRank(role.name) > authStore.getRoleRank() }"
                            >
                                {{ role.name }}
                            </label>
                        </div>
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
                    :disabled="saving || !isDirty"
                >
                    <Loader2 v-if="saving" class="w-4 h-4 mr-2 animate-spin" />
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
import { parseResponse, ensureArray, parseSingleResponse } from '../../../utils/responseParser';
import { useToast } from '../../../composables/useToast';
import { useFormValidation } from '../../../composables/useFormValidation';
import { editUserSchema } from '../../../schemas';
import Button from '../../../components/ui/button.vue';
import Input from '../../../components/ui/input.vue';
import Textarea from '../../../components/ui/textarea.vue';
import Checkbox from '../../../components/ui/checkbox.vue';
import { ArrowLeft, Loader2 } from 'lucide-vue-next';
import { useAuthStore, ROLE_RANKS } from '../../../stores/auth';
import { computed } from 'vue';

const router = useRouter();
const route = useRoute();
const { t } = useI18n();
const authStore = useAuthStore();
const toast = useToast();
const { errors, validateWithZod, setErrors, clearErrors } = useFormValidation(editUserSchema);

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
    is_verified: false,
    password_confirmation: '',
});

const initialForm = ref(null);

const isDirty = computed(() => {
    if (!initialForm.value) return false;
    return JSON.stringify(form.value) !== JSON.stringify(initialForm.value);
});

const getRoleRank = (roleName) => ROLE_RANKS[roleName] || 0;

const fetchRoles = async () => {
    loadingRoles.value = true;
    try {
        const response = await api.get('/admin/ja/roles');
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
        const response = await api.get(`/admin/ja/users/${userId}`);
        const data = parseSingleResponse(response);
        
        // Guard: hierarchy check
        // Allow if self OR if super-admin (rank >= 100) OR if strictly higher rank
        const isSuperAdmin = authStore.getRoleRank() >= 100;
        if (!isSuperAdmin && !authStore.isHigherThan(data) && authStore.user?.id !== data.id) {
            toast.error.action(new Error(t('features.users.messages.hierarchy_restriction')));
            router.push({ name: 'users.index' });
            return;
        }

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
            is_verified: !!data.email_verified_at,
        };
        initialForm.value = JSON.parse(JSON.stringify(form.value));
    } catch (error) {
        console.error('Failed to fetch user:', error);
        toast.error.load();
        router.push({ name: 'users.index' });
    } finally {
        loading.value = false;
    }
};

const handleSubmit = async () => {
    // Client-side validation first
    if (!validateWithZod(form.value)) {
        return;
    }

    if (form.value.roles.length === 0) {
        setErrors({ roles: [t('features.users.messages.roleRequired')] });
        return;
    }

    saving.value = true;
    clearErrors();

    try {
        const payload = { ...form.value };
        if (!payload.password) delete payload.password;
        
        await api.put(`/admin/ja/users/${route.params.id}`, payload);
        
        // Refresh auth user if updating self
        if (authStore.user?.id === Number(route.params.id)) {
            await authStore.fetchUser();
        }

        toast.success.update('User');
        initialForm.value = JSON.parse(JSON.stringify(form.value));
        router.push({ name: 'users.index' });
    } catch (error) {
        if (error.response?.status === 422) {
            setErrors(error.response.data.errors || {});
        } else {
            toast.error.fromResponse(error);
        }
    } finally {
        saving.value = false;
    }
};

onMounted(async () => {
    await fetchRoles();
    await fetchUser();
});
</script>
