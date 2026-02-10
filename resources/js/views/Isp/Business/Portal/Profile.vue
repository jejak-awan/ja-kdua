<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { useI18n } from 'vue-i18n';
import api from '@/services/api';
import { useToast } from '@/composables/useToast';
import { 
    Card, CardHeader, CardTitle, CardContent, 
    Button, Input, Label, Tabs, TabsList, TabsTrigger, TabsContent 
} from '@/components/ui';
import User from 'lucide-vue-next/dist/esm/icons/user.js';
import ShieldCheck from 'lucide-vue-next/dist/esm/icons/shield-check.js';
import Mail from 'lucide-vue-next/dist/esm/icons/mail.js';
import Phone from 'lucide-vue-next/dist/esm/icons/phone.js';
import MapPin from 'lucide-vue-next/dist/esm/icons/map-pin.js';
import LoaderCircle from 'lucide-vue-next/dist/esm/icons/loader-circle.js';
import Save from 'lucide-vue-next/dist/esm/icons/save.js';
import Key from 'lucide-vue-next/dist/esm/icons/key.js';

const { t } = useI18n();
const toast = useToast();

const loading = ref(true);
const saving = ref(false);
const activeTab = ref('info');

const formData = ref({
    name: '',
    email: '',
    phone: '',
    address: '',
    current_password: '',
    new_password: '',
    new_password_confirmation: ''
});

const fetchProfile = async () => {
    loading.value = true;
    try {
        const response = await api.get('/admin/ja/isp/member/dashboard');
        const userData = response.data?.data?.user || {};
        const customerData = response.data?.data?.customer || {};
        
        formData.value.name = userData.name || '';
        formData.value.email = userData.email || '';
        formData.value.phone = customerData.phone || '';
        formData.value.address = customerData.address || '';
    } catch (_error) {
        toast.error.default(t('common.messages.error_load'));
    } finally {
        loading.value = false;
    }
};

const handleUpdate = async () => {
    saving.value = true;
    try {
        await api.put('/admin/ja/isp/member/profile', formData.value);
        toast.success.default(t('isp.member.profile.update_success'));
        
        // Clear password fields
        formData.value.current_password = '';
        formData.value.new_password = '';
        formData.value.new_password_confirmation = '';
    } catch (error: unknown) {
        toast.error.action(error as Error);
    } finally {
        saving.value = false;
    }
};

onMounted(() => {
    fetchProfile();
});
</script>

<template>
  <div class="p-6 max-w-4xl mx-auto space-y-6 animate-in fade-in slide-in-from-bottom-4 duration-500">
    <div class="flex items-center gap-4">
        <div class="p-3 bg-primary/10 rounded-2xl text-primary">
            <User class="w-8 h-8" />
        </div>
        <div>
            <h1 class="text-2xl font-bold tracking-tight">{{ t('isp.member.profile.title') }}</h1>
            <p class="text-muted-foreground">{{ t('isp.member.profile.subtitle') }}</p>
        </div>
    </div>

    <div v-if="loading" class="h-64 flex items-center justify-center">
        <LoaderCircle class="w-8 h-8 animate-spin text-primary" />
    </div>

    <Tabs v-else v-model="activeTab" class="w-full">
        <TabsList class="grid w-full grid-cols-2 mb-8 p-1 bg-muted/50 rounded-xl">
            <TabsTrigger value="info" class="rounded-lg gap-2">
                <ShieldCheck class="w-4 h-4" />
                {{ t('isp.member.profile.tabs.info') }}
            </TabsTrigger>
            <TabsTrigger value="security" class="rounded-lg gap-2">
                <Key class="w-4 h-4" />
                {{ t('isp.member.profile.tabs.security') }}
            </TabsTrigger>
        </TabsList>

        <form @submit.prevent="handleUpdate">
            <TabsContent value="info" class="space-y-6">
                <Card class="rounded-2xl border-border shadow-sm overflow-hidden">
                    <CardHeader class="border-b bg-muted/20">
                        <CardTitle class="text-sm font-bold uppercase tracking-wider text-primary">
                            {{ t('isp.member.profile.tabs.info') }}
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="p-6 space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-2">
                                <Label class="flex items-center gap-2 text-xs font-bold uppercase opacity-60">
                                    <User class="w-3 h-3" />
                                    {{ t('isp.member.profile.full_name') }}
                                </Label>
                                <Input v-model="formData.name" class="rounded-xl" />
                            </div>
                            <div class="space-y-2">
                                <Label class="flex items-center gap-2 text-xs font-bold uppercase opacity-60">
                                    <Mail class="w-3 h-3" />
                                    {{ t('isp.member.profile.email') }}
                                </Label>
                                <Input v-model="formData.email" type="email" class="rounded-xl" />
                            </div>
                            <div class="space-y-2">
                                <Label class="flex items-center gap-2 text-xs font-bold uppercase opacity-60">
                                    <Phone class="w-3 h-3" />
                                    {{ t('isp.member.profile.phone') }}
                                </Label>
                                <Input v-model="formData.phone" placeholder="0812..." class="rounded-xl" />
                            </div>
                        </div>

                        <div class="space-y-2">
                            <Label class="flex items-center gap-2 text-xs font-bold uppercase opacity-60">
                                <MapPin class="w-3 h-3" />
                                {{ t('isp.member.profile.address') }}
                            </Label>
                            <Input v-model="formData.address" class="rounded-xl" />
                        </div>
                    </CardContent>
                </Card>
            </TabsContent>

            <TabsContent value="security" class="space-y-6">
                <Card class="rounded-2xl border-border shadow-sm overflow-hidden border-t-4 border-t-primary">
                    <CardHeader class="border-b bg-muted/20">
                        <CardTitle class="text-sm font-bold uppercase tracking-wider text-primary">
                            {{ t('isp.member.profile.security_title') }}
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="p-6 space-y-6">
                        <div class="space-y-2">
                            <Label class="text-xs font-bold uppercase opacity-60">{{ t('isp.member.profile.current_password') }}</Label>
                            <Input v-model="formData.current_password" type="password" class="rounded-xl" />
                            <p class="text-[10px] text-muted-foreground">{{ t('isp.member.profile.required_for_change') || 'Required to authorize changes.' }}</p>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-4 border-t">
                            <div class="space-y-2">
                                <Label class="text-xs font-bold uppercase opacity-60">{{ t('isp.member.profile.new_password') }}</Label>
                                <Input v-model="formData.new_password" type="password" class="rounded-xl" />
                            </div>
                            <div class="space-y-2">
                                <Label class="text-xs font-bold uppercase opacity-60">{{ t('isp.member.profile.confirm_password') }}</Label>
                                <Input v-model="formData.new_password_confirmation" type="password" class="rounded-xl" />
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </TabsContent>

            <div class="flex justify-end pt-4">
                <Button type="submit" :loading="saving" class="rounded-xl px-8 gap-2 shadow-lg shadow-primary/20">
                    <Save class="w-4 h-4" />
                    {{ t('common.labels.save_changes') }}
                </Button>
            </div>
        </form>
    </Tabs>
  </div>
</template>
