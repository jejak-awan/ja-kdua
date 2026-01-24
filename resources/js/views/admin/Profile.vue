<template>
    <div class="p-6 space-y-6">
        <div>
            <h2 class="text-3xl font-bold tracking-tight">{{ $t('features.profile.title') }}</h2>
            <p class="text-muted-foreground">
                {{ $t('features.profile.subtitle') }}
            </p>
        </div>

        <Tabs v-model="activeTab" class="space-y-4">
            <TabsList>
                <TabsTrigger value="profile">{{ $t('features.profile.tabs.profile') }}</TabsTrigger>
                <TabsTrigger value="password">{{ $t('features.profile.tabs.password') }}</TabsTrigger>
                <TabsTrigger value="two-factor">{{ $t('features.profile.tabs.two-factor') }}</TabsTrigger>
                <TabsTrigger value="history">{{ $t('features.profile.tabs.history') }}</TabsTrigger>
            </TabsList>

            <TabsContent value="profile" class="space-y-4">
                <Card>
                    <CardHeader>
                        <CardTitle>{{ $t('features.profile.tabs.profile') }}</CardTitle>
                        <CardDescription>
                            {{ $t('features.profile.subtitle') }}
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-6">
                        <!-- Avatar Upload -->
                        <div class="flex items-center gap-x-6">
                             <div class="relative">
                                <Avatar class="h-24 w-24">
                                    <AvatarImage :src="profileForm.avatar || undefined" alt="Avatar" />
                                    <AvatarFallback class="text-lg">{{ getInitials(profileForm.name) }}</AvatarFallback>
                                </Avatar>
                                 <button
                                    v-if="profileForm.avatar"
                                    type="button"
                                    class="absolute -top-1 -right-1 rounded-full bg-destructive p-1 text-destructive-foreground hover:bg-destructive/90 shadow-sm"
                                    @click="profileForm.avatar = null"
                                    title="Remove Avatar"
                                >
                                    <X class="h-3 w-3" />
                                </button>
                             </div>
                             <div>
                                <MediaPicker
                                    :label="$t('features.users.form.selectAvatar')"
                                    @selected="(media: any) => profileForm.avatar = media.url"
                                />
                                <p class="mt-2 text-xs text-muted-foreground">
                                    JPG, GIF or PNG. 1MB max.
                                </p>
                             </div>
                        </div>

                        <Separator class="my-4" />

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-2">
                                <Label for="name">{{ $t('features.profile.form.name') }}</Label>
                                <Input id="name" v-model="profileForm.name" type="text" />
                            </div>

                            <div class="space-y-2">
                                <Label for="email">{{ $t('features.profile.form.email') }}</Label>
                                <Input id="email" v-model="profileForm.email" type="email" />
                            </div>

                            <div class="space-y-2">
                                <Label for="phone">{{ $t('features.profile.form.phone') }}</Label>
                                <Input id="phone" v-model="profileForm.phone" type="text" />
                            </div>

                            <div class="space-y-2">
                                <Label for="location">{{ $t('features.profile.form.location') }}</Label>
                                <Input id="location" v-model="profileForm.location" type="text" />
                            </div>
                        </div>

                        <div class="space-y-2">
                            <Label for="bio">{{ $t('features.profile.form.bio') }}</Label>
                            <Textarea id="bio" v-model="profileForm.bio" rows="4" />
                        </div>

                        <div class="flex justify-end">
                            <Button :disabled="saving || !isProfileDirty" @click="updateProfile">
                                <Loader2 v-if="saving" class="mr-2 h-4 w-4 animate-spin" />
                                {{ saving ? $t('features.profile.form.saving') : $t('features.profile.form.save') }}
                            </Button>
                        </div>
                    </CardContent>
                </Card>
            </TabsContent>

            <TabsContent value="password" class="space-y-4">
                <Card>
                    <CardHeader>
                        <CardTitle>{{ $t('features.profile.tabs.password') }}</CardTitle>
                        <CardDescription>
                            {{ $t('features.profile.form.passwordHelp') }}
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-4 max-w-lg">
                        <div class="space-y-2">
                            <Label for="current_password">{{ $t('features.profile.form.currentPassword') }}</Label>
                            <Input id="current_password" v-model="passwordForm.current_password" type="password" />
                        </div>

                        <div class="space-y-2">
                            <Label for="new_password">{{ $t('features.profile.form.newPassword') }}</Label>
                            <Input id="new_password" v-model="passwordForm.password" type="password" />
                        </div>

                        <div class="space-y-2">
                            <Label for="confirm_password">{{ $t('features.profile.form.confirmPassword') }}</Label>
                            <Input id="confirm_password" v-model="passwordForm.password_confirmation" type="password" />
                        </div>

                        <div class="flex justify-end pt-4">
                            <Button :disabled="changingPassword || !isPasswordValid" @click="updatePassword">
                                <Loader2 v-if="changingPassword" class="mr-2 h-4 w-4 animate-spin" />
                                {{ changingPassword ? $t('features.profile.form.changing') : $t('features.profile.form.changePassword') }}
                            </Button>
                        </div>
                    </CardContent>
                </Card>
            </TabsContent>

            <TabsContent value="two-factor" class="space-y-4">
                <Card>
                    <CardHeader>
                        <CardTitle>{{ $t('features.profile.tabs.twoFactor') }}</CardTitle>
                        <CardDescription>
                            {{ $t('features.profile.form.twoFactorDescription') || 'Secure your account with multi-factor authentication.' }}
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="pb-10">
                        <TwoFactorSettings />
                    </CardContent>
                </Card>
            </TabsContent>

            <TabsContent value="history" class="space-y-4">
                <Card>
                    <CardHeader>
                        <CardTitle>{{ $t('features.profile.tabs.history') }}</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <LoginHistory />
                    </CardContent>
                </Card>
            </TabsContent>
        </Tabs>
    </div>
</template>

<script setup lang="ts">
import { ref, onMounted, computed, type Ref } from 'vue';
import { useI18n } from 'vue-i18n';
import api from '../../services/api';
import toast from '../../services/toast';
import { useAuthStore } from '../../stores/auth';
// @ts-ignore - TODO: Migrate components
import LoginHistory from '../../components/admin/LoginHistory.vue';
// @ts-ignore - TODO: Migrate components
import TwoFactorSettings from '../../components/admin/TwoFactorSettings.vue';
// @ts-ignore - TODO: Migrate components
import MediaPicker from '../../components/MediaPicker.vue';

// Shadcn Components
// @ts-ignore
import Card from '../../components/ui/card.vue';
// @ts-ignore
import CardContent from '../../components/ui/card-content.vue';
// @ts-ignore
import CardDescription from '../../components/ui/card-description.vue';
// @ts-ignore
import CardHeader from '../../components/ui/card-header.vue';
// @ts-ignore
import CardTitle from '../../components/ui/card-title.vue';

// @ts-ignore
import Tabs from '../../components/ui/tabs.vue';
// @ts-ignore
import TabsContent from '../../components/ui/tabs-content.vue';
// @ts-ignore
import TabsList from '../../components/ui/tabs-list.vue';
// @ts-ignore
import TabsTrigger from '../../components/ui/tabs-trigger.vue';

// @ts-ignore
import Input from '../../components/ui/input.vue';
// @ts-ignore
import Button from '../../components/ui/button.vue';
// @ts-ignore
import Label from '../../components/ui/label.vue';
// @ts-ignore
import Textarea from '../../components/ui/textarea.vue';

// @ts-ignore
import Avatar from '../../components/ui/avatar.vue';
// @ts-ignore
import AvatarImage from '../../components/ui/avatar-image.vue';
// @ts-ignore
import AvatarFallback from '../../components/ui/avatar-fallback.vue';

import { Loader2, X } from 'lucide-vue-next';

// Simple Separator Component (Functional)
const Separator = { // Minimal local component or just use div
  template: '<div class="h-px bg-border w-full" />'
}

interface ProfileForm {
    name: string;
    email: string;
    phone: string;
    bio: string;
    location: string;
    website: string;
    avatar: string | null;
}

const { t } = useI18n();
const authStore = useAuthStore();

const activeTab = ref('profile');
const saving = ref(false);
const changingPassword = ref(false);

const profileForm: Ref<ProfileForm> = ref({
    name: '',
    email: '',
    phone: '',
    bio: '',
    location: '',
    website: '',
    avatar: null,
});

const initialProfileForm: Ref<ProfileForm | null> = ref(null);

const passwordForm = ref({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const getInitials = (name: string | null | undefined): string => {
    if (!name) return 'U';
    return name
        .split(' ')
        .map((word) => word[0])
        .join('')
        .toUpperCase()
        .slice(0, 2);
};

const isProfileDirty = computed(() => {
    if (!initialProfileForm.value) return false;
    return JSON.stringify(profileForm.value) !== JSON.stringify(initialProfileForm.value);
});

const isPasswordValid = computed(() => {
    return passwordForm.value.current_password && 
           passwordForm.value.password && 
           passwordForm.value.password.length >= 8 &&
           passwordForm.value.password === passwordForm.value.password_confirmation;
});

const fetchProfile = async () => {
    try {
        const response = await api.get('/profile');
        if (response.data?.success || response.data?.data) {
            const user = response.data.data || response.data;
            profileForm.value = {
                name: user.name || '',
                email: user.email || '',
                phone: user.phone || '',
                bio: user.bio || '',
                location: user.location || '',
                website: user.website || '',
                avatar: user.avatar || null,
            };
            initialProfileForm.value = JSON.parse(JSON.stringify(profileForm.value));
        }
    } catch (error) {
        console.error('Error fetching profile:', error);
        toast.error(t('common.messages.error.default'));
    }
};

const updateProfile = async () => {
    saving.value = true;
    try {
        await api.put('/profile', profileForm.value);
        toast.success(t('features.profile.messages.updateSuccess'));
        await authStore.fetchUser();
        await fetchProfile(); // Re-fetch to update initial state
    } catch (error: any) {
        const msg = error.response?.data?.message || t('features.profile.messages.updateFailed');
        toast.error(msg);
    } finally {
        saving.value = false;
    }
};

const updatePassword = async () => {
    changingPassword.value = true;
    try {
        await api.put('/profile/password', passwordForm.value);
        toast.success(t('features.profile.messages.passwordSuccess'));
        passwordForm.value = {
            current_password: '',
            password: '',
            password_confirmation: '',
        };
    } catch (error: any) {
        const msg = error.response?.data?.message || t('features.profile.messages.passwordFailed');
        toast.error(msg);
    } finally {
        changingPassword.value = false;
    }
};

onMounted(() => {
    fetchProfile();
});
</script>
