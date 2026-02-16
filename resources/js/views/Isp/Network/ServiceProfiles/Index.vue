<template>
    <div class="space-y-6 animate-in fade-in duration-500">
        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold tracking-tight">{{ t('isp.network.profiles.title') }}</h1>
                <p class="text-muted-foreground">{{ t('isp.network.profiles.subtitle') }}</p>
            </div>
            <Dialog v-model:open="isDialogOpen">
                <DialogTrigger as-child>
                    <Button class="gap-2 rounded-xl">
                        <Plus class="w-4 h-4" />
                        {{ t('isp.network.profiles.new') }}
                    </Button>
                </DialogTrigger>
                <DialogContent class="sm:max-w-[500px] rounded-2xl">
                    <DialogHeader>
                        <DialogTitle>{{ editId ? t('isp.network.profiles_manager.edit') : t('isp.network.profiles_manager.new') }}</DialogTitle>
                        <DialogDescription>
                            {{ t('isp.network.profiles_manager.desc') }}
                        </DialogDescription>
                    </DialogHeader>
                    <form @submit.prevent="saveProfile" class="space-y-4 py-4">
                        <div class="space-y-2">
                            <Label for="name">{{ t('common.labels.name') }}</Label>
                            <Input id="name" v-model="form.name" required class="rounded-xl" />
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <Label for="download">{{ t('isp.network.profiles.fields.download') }}</Label>
                                <Input id="download" type="number" v-model="form.download_limit" required class="rounded-xl" />
                            </div>
                            <div class="space-y-2">
                                <Label for="upload">{{ t('isp.network.profiles.fields.upload') }}</Label>
                                <Input id="upload" type="number" v-model="form.upload_limit" required class="rounded-xl" />
                            </div>
                        </div>
                        <div class="space-y-4 pt-2 border-t border-border/40">
                            <div class="flex items-center justify-between">
                                <div class="space-y-0.5">
                                    <Label>{{ t('isp.network.profiles.fields.fup_enabled') }}</Label>
                                    <p class="text-[0.8rem] text-muted-foreground">Aktifkan kebijakan penggunaan wajar.</p>
                                </div>
                                <Switch v-model:checked="form.fup_enabled" />
                            </div>
                            
                            <div v-if="form.fup_enabled" class="grid grid-cols-2 gap-4 animate-in slide-in-from-top-2 duration-300">
                                <div class="space-y-2">
                                    <Label for="fup_limit">{{ t('isp.network.profiles.fields.fup_limit') }}</Label>
                                    <Input id="fup_limit" type="number" v-model="(form.fup_limit_gb as any)" :required="form.fup_enabled" class="rounded-xl" />
                                </div>
                                <div class="space-y-2">
                                    <Label for="fup_speed">{{ t('isp.network.profiles.fields.fup_speed') }}</Label>
                                    <Input id="fup_speed" v-model="form.fup_speed" placeholder="e.g. 2M/2M" :required="form.fup_enabled" class="rounded-xl" />
                                </div>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <Label for="burst">{{ t('isp.network.profiles.fields.burst') }}</Label>
                            <Input id="burst" v-model="form.burst_limit" placeholder="e.g. 100M/100M 20M/20M" class="rounded-xl" />
                        </div>
                        <DialogFooter>
                            <Button type="submit" :disabled="isSaving" class="w-full rounded-xl">
                                <Loader2 v-if="isSaving" class="w-4 h-4 animate-spin mr-2" />
                                {{ t('common.actions.save') }}
                            </Button>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>
        </div>

        <!-- Profiles List -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div v-if="loading" class="col-span-full p-12 flex justify-center">
                <Loader2 class="w-8 h-8 animate-spin text-primary" />
            </div>
            
            <Card v-for="profile in profiles" :key="profile.id" class="group hover:border-primary/50 transition-all rounded-xl border-border/40 overflow-hidden shadow-sm">
                <CardHeader class="pb-2">
                    <div class="flex justify-between items-start">
                        <CardTitle class="text-xl">{{ profile.name }}</CardTitle>
                        <Badge :variant="profile.status === 'active' ? 'success' : 'outline'" class="rounded-xl">
                            {{ profile.status }}
                        </Badge>
                    </div>
                </CardHeader>
                <CardContent class="pb-4">
                    <div class="space-y-4">
                        <div class="flex items-center justify-between text-sm">
                            <div class="flex items-center gap-2 text-muted-foreground">
                                <ArrowDownCircle class="w-4 h-4 text-blue-500" />
                                {{ t('isp.network.profiles_manager.download_label') }}
                            </div>
                            <span class="font-bold text-lg">{{ profile.download_limit }} Mbps</span>
                        </div>
                        <div class="flex items-center justify-between text-sm">
                            <div class="flex items-center gap-2 text-muted-foreground">
                                <ArrowUpCircle class="w-4 h-4 text-success" />
                                {{ t('isp.network.profiles_manager.upload_label') }}
                            </div>
                            <span class="font-bold text-lg">{{ profile.upload_limit }} Mbps</span>
                        </div>
                        <div v-if="profile.burst_limit" class="text-xs p-2 bg-muted rounded-xl text-muted-foreground font-mono">
                            {{ t('isp.network.profiles_manager.burst_label') }}: {{ profile.burst_limit }}
                        </div>
                        <div v-if="profile.fup_enabled" class="flex items-center gap-2 p-2 bg-amber-500/5 text-amber-600 dark:text-amber-400 border border-amber-500/10 rounded-xl text-xs font-medium">
                            <ShieldAlert class="w-3.5 h-3.5" />
                            FUP: {{ profile.fup_limit_gb }} GB @ {{ profile.fup_speed }}
                        </div>
                    </div>
                </CardContent>
                <CardFooter class="bg-muted/50 p-2 gap-2">
                    <Button variant="ghost" size="icon" class="rounded-xl" @click="viewProfile(profile)" aria-label="View profile">
                        <Eye class="w-4 h-4" />
                    </Button>
                    <Button variant="ghost" size="icon" class="rounded-xl" @click="editProfile(profile)" aria-label="Edit profile">
                        <Pencil class="w-4 h-4" />
                    </Button>
                </CardFooter>
            </Card>
        </div>

        <!-- View Profile Modal -->
        <Dialog v-model:open="isViewDialogOpen">
            <DialogContent class="sm:max-w-[450px] rounded-2xl">
                <DialogHeader>
                    <div class="flex justify-between items-start mr-6">
                        <div>
                            <DialogTitle>{{ selectedProfile?.name }}</DialogTitle>
                            <DialogDescription>
                                {{ t('isp.network.profiles.subtitle') }}
                            </DialogDescription>
                        </div>
                        <Badge :variant="selectedProfile?.status === 'active' ? 'success' : 'outline'" class="rounded-xl">
                            {{ selectedProfile?.status }}
                        </Badge>
                    </div>
                </DialogHeader>
                <div class="space-y-6 py-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="p-4 bg-blue-500/5 rounded-xl border border-blue-500/10 space-y-1">
                            <div class="flex items-center gap-2 text-blue-500 mb-1">
                                <ArrowDownCircle class="w-4 h-4" />
                                <span class="text-xs font-bold">{{ t('isp.network.profiles_manager.download_label') }}</span>
                            </div>
                            <p class="text-2xl font-bold">{{ selectedProfile?.download_limit }} Mbps</p>
                        </div>
                        <div class="p-4 bg-success/5 rounded-xl border border-success/10 space-y-1">
                            <div class="flex items-center gap-2 text-success mb-1">
                                <ArrowUpCircle class="w-4 h-4" />
                                <span class="text-xs font-bold">{{ t('isp.network.profiles_manager.upload_label') }}</span>
                            </div>
                            <p class="text-2xl font-bold">{{ selectedProfile?.upload_limit }} Mbps</p>
                        </div>
                    </div>
                    
                    <div v-if="selectedProfile?.burst_limit" class="p-4 bg-muted/30 rounded-xl border border-border/40">
                        <span class="text-xs font-bold text-muted-foreground">{{ t('isp.network.profiles_manager.burst_label') }}</span>
                        <p class="text-sm font-mono mt-1">{{ selectedProfile.burst_limit }}</p>
                    </div>

                    <div v-if="selectedProfile?.fup_enabled" class="p-4 bg-amber-500/5 rounded-xl border border-amber-500/10 space-y-3">
                        <div class="flex items-center gap-2 text-amber-600">
                            <ShieldAlert class="w-4 h-4" />
                            <span class="text-xs font-bold">Fair Usage Policy (FUP)</span>
                        </div>
                        <div class="grid grid-cols-2 gap-4 pt-1">
                            <div class="space-y-0.5">
                                <p class="text-[10px] text-muted-foreground font-bold tracking-tight">Threshold</p>
                                <p class="text-lg font-bold">{{ selectedProfile.fup_limit_gb }} GB</p>
                            </div>
                            <div class="space-y-0.5">
                                <p class="text-[10px] text-muted-foreground font-bold tracking-tight">Throttled Speed</p>
                                <p class="text-lg font-bold">{{ selectedProfile.fup_speed }}</p>
                            </div>
                        </div>
                    </div>
                </div>
                <DialogFooter>
                    <Button variant="outline" @click="isViewDialogOpen = false" class="w-full rounded-xl">
                        {{ t('common.actions.close') }}
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { useI18n } from 'vue-i18n';
import type { IspPlan } from '@/types/isp';
import api from '@/services/api';
import { useToast } from '@/composables/useToast';
import { ensureArray } from '@/utils/responseParser';
import { 
    Button, 
    Input, 
    Label, 
    Badge,
    Card,
    CardContent,
    CardHeader,
    CardTitle,
    CardFooter,
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
    Switch
} from '@/components/ui';
import Plus from 'lucide-vue-next/dist/esm/icons/plus.js';
import Pencil from 'lucide-vue-next/dist/esm/icons/pencil.js';
import Eye from 'lucide-vue-next/dist/esm/icons/eye.js';
import Loader2 from 'lucide-vue-next/dist/esm/icons/loader-circle.js';
import ArrowDownCircle from 'lucide-vue-next/dist/esm/icons/circle-arrow-down.js';
import ArrowUpCircle from 'lucide-vue-next/dist/esm/icons/circle-arrow-up.js';
import ShieldAlert from 'lucide-vue-next/dist/esm/icons/shield-alert.js';

const { t } = useI18n();
const toast = useToast();


const profiles = ref<IspPlan[]>([]);
const loading = ref(true);
const isDialogOpen = ref(false);
const isViewDialogOpen = ref(false);
const isSaving = ref(false);
const editId = ref<number | null>(null);
const selectedProfile = ref<IspPlan | null>(null);

const form = ref({
    name: '',
    download_limit: 50,
    upload_limit: 20,
    burst_limit: '',
    fup_limit_gb: null as number | null,
    fup_speed: '',
    fup_enabled: false
});

const fetchProfiles = async () => {
    loading.value = true;
    try {
        const response = await api.get('/admin/janet/isp/network/profiles');
        if (response.data.success) {
            profiles.value = ensureArray<IspPlan>(response.data.data);
        }
    } catch (_error) {
        toast.error.default(t('isp.network.messages.error_profile'));
    } finally {
        loading.value = false;
    }
};

const saveProfile = async () => {
    isSaving.value = true;
    try {
        const payload = { ...form.value, id: editId.value };
        const response = await api.post('/admin/janet/isp/network/profiles', payload);
        if (response.data.success) {
            toast.success.default(t('isp.network.messages.success_profile'));
            isDialogOpen.value = false;
            resetForm();
            fetchProfiles();
        }
    } catch (_error) {
        toast.error.default(t('isp.network.messages.error_profile'));
    } finally {
        isSaving.value = false;
    }
};

const viewProfile = (profile: IspPlan) => {
    selectedProfile.value = profile;
    isViewDialogOpen.value = true;
};

const editProfile = (profile: IspPlan) => {
    editId.value = profile.id;
    form.value = {
        name: profile.name,
        download_limit: Number(profile.download_limit) || 0,
        upload_limit: Number(profile.upload_limit) || 0,
        burst_limit: profile.burst_limit || '',
        fup_limit_gb: profile.fup_limit_gb as number | null,
        fup_speed: profile.fup_speed || '',
        fup_enabled: profile.fup_enabled || false
    };
    isDialogOpen.value = true;
};

const resetForm = () => {
    editId.value = null;
    form.value = {
        name: '',
        download_limit: 50,
        upload_limit: 20,
        burst_limit: '',
        fup_limit_gb: null, // Default to null for input compatibility
        fup_speed: '',
        fup_enabled: false
    };
};

onMounted(() => {
    fetchProfiles();
});
</script>
