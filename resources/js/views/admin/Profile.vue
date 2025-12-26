<template>
    <div class="p-6">
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-foreground">{{ $t('features.profile.title') }}</h1>
            <p class="text-muted-foreground mt-1">{{ $t('features.profile.subtitle') }}</p>
        </div>

        <!-- Shadcn Tabs -->
        <Tabs v-model="activeTab" class="w-full">
            <TabsList class="mb-6">
                <TabsTrigger value="profile">{{ $t('features.profile.tabs.profile') }}</TabsTrigger>
                <TabsTrigger value="password">{{ $t('features.profile.tabs.password') }}</TabsTrigger>
                <TabsTrigger value="two-factor">{{ $t('features.profile.tabs.two-factor') }}</TabsTrigger>
                <TabsTrigger value="history">{{ $t('features.profile.tabs.history') }}</TabsTrigger>
            </TabsList>

            <!-- Profile Info Tab -->
            <TabsContent value="profile">
                <div class="bg-card rounded-lg border border-border p-6">
                    <form @submit.prevent="updateProfile" class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-foreground mb-2">
                                    {{ $t('features.profile.form.name') }}
                                </label>
                                <Input
                                    v-model="profileForm.name"
                                    type="text"
                                />
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-foreground mb-2">
                                    {{ $t('features.profile.form.email') }}
                                </label>
                                <Input
                                    v-model="profileForm.email"
                                    type="email"
                                />
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-foreground mb-2">
                                    {{ $t('features.profile.form.phone') }}
                                </label>
                                <Input
                                    v-model="profileForm.phone"
                                    type="text"
                                />
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-foreground mb-2">
                                    {{ $t('features.profile.form.location') }}
                                </label>
                                <Input
                                    v-model="profileForm.location"
                                    type="text"
                                />
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-foreground mb-2">
                                {{ $t('features.profile.form.bio') }}
                            </label>
                            <Textarea
                                v-model="profileForm.bio"
                                rows="4"
                            />
                        </div>

                        <div class="flex justify-end">
                            <Button
                                :disabled="saving"
                            >
                                {{ saving ? $t('features.profile.form.saving') : $t('features.profile.form.save') }}
                            </Button>
                        </div>
                    </form>
                </div>
            </TabsContent>

            <!-- Password Tab -->
            <TabsContent value="password">
                <div class="bg-card rounded-lg border border-border p-6">
                    <form @submit.prevent="updatePassword" class="space-y-6 max-w-md">
                        <div>
                            <label class="block text-sm font-medium text-foreground mb-2">
                                {{ $t('features.profile.form.currentPassword') }}
                            </label>
                            <Input
                                v-model="passwordForm.current_password"
                                type="password"
                            />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-foreground mb-2">
                                {{ $t('features.profile.form.newPassword') }}
                            </label>
                            <Input
                                v-model="passwordForm.password"
                                type="password"
                            />
                            <p class="mt-1 text-xs text-muted-foreground">
                                {{ $t('features.profile.form.passwordHelp') }}
                            </p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-foreground mb-2">
                                {{ $t('features.profile.form.confirmPassword') }}
                            </label>
                            <Input
                                v-model="passwordForm.password_confirmation"
                                type="password"
                            />
                        </div>

                        <div class="flex justify-end">
                            <Button
                                :disabled="changingPassword"
                            >
                                {{ changingPassword ? $t('features.profile.form.changing') : $t('features.profile.form.changePassword') }}
                            </Button>
                        </div>
                    </form>
                </div>
            </TabsContent>

            <!-- Two-Factor Authentication Tab -->
            <TabsContent value="two-factor">
                <TwoFactorSettings />
            </TabsContent>

            <!-- Login History Tab -->
            <TabsContent value="history">
                <LoginHistory />
            </TabsContent>
        </Tabs>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useI18n } from 'vue-i18n';
import api from '../../services/api';
import LoginHistory from '../../components/admin/LoginHistory.vue';
import TwoFactorSettings from '../../components/admin/TwoFactorSettings.vue';
import Tabs from '../../components/ui/tabs.vue';
import TabsList from '../../components/ui/tabs-list.vue';
import TabsTrigger from '../../components/ui/tabs-trigger.vue';
import TabsContent from '../../components/ui/tabs-content.vue';
import Input from '../../components/ui/input.vue';
import Button from '../../components/ui/button.vue';
import Textarea from '../../components/ui/textarea.vue';

const { t } = useI18n();

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
        alert(t('features.profile.messages.updateSuccess'));
        await fetchProfile();
    } catch (error) {
        alert(error.response?.data?.message || t('features.profile.messages.updateFailed'));
    } finally {
        saving.value = false;
    }
};

const updatePassword = async () => {
    changingPassword.value = true;
    try {
        await api.put('/profile/password', passwordForm.value);
        alert(t('features.profile.messages.passwordSuccess'));
        passwordForm.value = {
            current_password: '',
            password: '',
            password_confirmation: '',
        };
    } catch (error) {
        alert(error.response?.data?.message || t('features.profile.messages.passwordFailed'));
    } finally {
        changingPassword.value = false;
    }
};

onMounted(() => {
    fetchProfile();
});
</script>
