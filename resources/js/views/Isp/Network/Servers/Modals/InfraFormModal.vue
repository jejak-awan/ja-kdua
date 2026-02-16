<script setup lang="ts">
import { ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import Loader2 from 'lucide-vue-next/dist/esm/icons/loader-circle.js';
import api from '@/services/api';
import type { NetworkNode } from '@/types/isp';
import { 
    Dialog, 
    DialogContent, 
    DialogHeader, 
    DialogTitle, 
    Input,
    Button,
    Label,
    Select, 
    SelectContent, 
    SelectItem, 
    SelectTrigger, 
    SelectValue,
    Switch,
    Tabs, TabsList, TabsTrigger, TabsContent
} from '@/components/ui';
import { useToast } from '@/composables/useToast';
import Globe from 'lucide-vue-next/dist/esm/icons/globe.js';
import Zap from 'lucide-vue-next/dist/esm/icons/zap.js';
import Shield from 'lucide-vue-next/dist/esm/icons/shield.js';
import Key from 'lucide-vue-next/dist/esm/icons/key.js';
import Terminal from 'lucide-vue-next/dist/esm/icons/terminal.js';
import NetworkIcon from 'lucide-vue-next/dist/esm/icons/network.js';
import Settings2 from 'lucide-vue-next/dist/esm/icons/settings-2.js';
import RefreshCw from 'lucide-vue-next/dist/esm/icons/refresh-cw.js';
import MapPin from 'lucide-vue-next/dist/esm/icons/map-pin.js';
import MapIcon from 'lucide-vue-next/dist/esm/icons/map.js';

const props = defineProps<{
    isOpen: boolean;
    router: NetworkNode | null;
    initialTab?: string;
}>();

const emit = defineEmits(['close', 'refresh']);

const { t } = useI18n();
const { success, error: toastError } = useToast();

const form = ref({
    name: '',
    description: '',
    ip_address: '',
    secret: '',
    connection_type: 'IP PUBLIC' as 'IP PUBLIC' | 'VPN RADIUS',
    management_port: 3799,
    connection_method: 'api' as 'snmp' | 'api',
    api_username: '',
    api_password: '',
    api_port: 8728,
    snmp_community: 'public',
    snmp_port: 161,
    radius_enabled: true,
    radius_secret: '',
    status: 'active',
    is_vpn_server: false,
    type: 'Router' as 'Router' | 'OLT' | 'POP',
    location_lat: 0,
    location_lng: 0
});

const activeTab = ref('general');
const isCustomDescription = ref(false);
const selectedPresetDescription = ref('');

const descriptionPresets = [
    'Core Router',
    'Router Gateway',
    'Distribution Router',
    'Custom'
];

function handleDescriptionChange(val: string) {
    if (val === 'Custom') {
        isCustomDescription.value = true;
        form.value.description = '';
    } else {
        isCustomDescription.value = false;
        form.value.description = val;
    }
}

function generateRadiusSecret() {
    const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789!@#$%^&*';
    let secret = '';
    for (let i = 0; i < 16; i++) {
        secret += chars.charAt(Math.floor(Math.random() * chars.length));
    }
    form.value.radius_secret = secret;
}

const isSaving = ref(false);
const autoAllocateIp = ref(false);

function toggleAutoAllocate(val: boolean) {
    autoAllocateIp.value = val;
    if (val) {
        form.value.ip_address = '';
    }
}

watch(() => form.value.connection_type, (newVal) => {
    if (newVal === 'IP PUBLIC') {
        autoAllocateIp.value = false;
    }
});

watch(() => props.router, (newVal) => {
    if (newVal) {
        form.value = {
            name: newVal.name,
            description: newVal.description || '',
            ip_address: newVal.ip_address || '',
            secret: newVal.secret || '',
            connection_type: newVal.connection_type || 'IP PUBLIC',
            management_port: newVal.management_port || 3799,
            connection_method: (newVal.connection_method && newVal.connection_method !== 'none' ? newVal.connection_method : 'api') as 'api' | 'snmp',
            api_username: newVal.api_username || '',
            api_password: newVal.api_password || '',
            api_port: newVal.api_port || 8728,
            snmp_community: newVal.snmp_community || 'public',
            snmp_port: newVal.snmp_port || 161,
            radius_enabled: newVal.radius_enabled ?? true,
            radius_secret: newVal.radius_secret || '',
            status: newVal.status || 'active',
            is_vpn_server: newVal.is_vpn_server || false,
            type: (newVal.type as 'Router' | 'OLT' | 'POP') || 'Router',
            location_lat: Number(newVal.location_lat) || 0,
            location_lng: Number(newVal.location_lng) || 0
        };
        
        if (descriptionPresets.includes(newVal.description || '')) {
            selectedPresetDescription.value = newVal.description || '';
            isCustomDescription.value = false;
        } else if (newVal.description) {
            selectedPresetDescription.value = 'Custom';
            isCustomDescription.value = true;
        } else {
            selectedPresetDescription.value = '';
            isCustomDescription.value = false;
        }

        autoAllocateIp.value = false;
    } else {
        form.value = {
            name: '',
            description: '',
            ip_address: '',
            secret: '',
            connection_type: 'IP PUBLIC',
            management_port: 3799,
            connection_method: 'api',
            api_username: '',
            api_password: '',
            api_port: 8728,
            snmp_community: 'public',
            snmp_port: 161,
            radius_enabled: true,
            radius_secret: '',
            status: 'active',
            is_vpn_server: false,
            type: 'Router',
            location_lat: 0,
            location_lng: 0
        };
        selectedPresetDescription.value = '';
        isCustomDescription.value = false;
        generateRadiusSecret();
        autoAllocateIp.value = false;
    }
}, { immediate: true });

watch(() => props.isOpen, (open) => {
    if (open) activeTab.value = props.initialTab || 'general';
});

async function handleSubmit() {
    isSaving.value = true;
    try {
        if (props.router) {
            const endpoint = `/admin/janet/isp/infra/${props.router.id}`;
            await api.put(endpoint, form.value);
            success.action(t('isp.infra.messages.success_update'));
        } else {
            const endpoint = '/admin/janet/isp/infra';
            await api.post(endpoint, form.value);
            success.action(t('isp.infra.messages.success_create'));
        }
        emit('refresh');
        emit('close');
    } catch (error) {
        toastError.action(error as Record<string, unknown>);
    } finally {
        isSaving.value = false;
    }
}
</script>

<template>
    <Dialog :open="isOpen" @update:open="emit('close')">
        <DialogContent class="max-w-2xl rounded-2xl p-0 overflow-hidden border-none shadow-2xl">
            <div class="bg-background">
                <DialogHeader class="p-6 pb-4 bg-muted/30 border-b border-border">
                    <DialogTitle class="text-xl font-bold tracking-tight text-foreground flex items-center gap-3">
                        <div class="p-2 bg-blue-600 rounded-lg text-white">
                            <NetworkIcon class="w-5 h-5" />
                        </div>
                        {{ router ? t('isp.infra.modals.edit_title') : t('isp.infra.modals.create_title') }}
                    </DialogTitle>
                </DialogHeader>

                <Tabs v-model="activeTab" class="w-full">
                    <div v-if="router" class="px-6 pt-4">
                        <TabsList class="grid w-full grid-cols-2 rounded-xl bg-muted p-1">
                            <TabsTrigger value="general" class="rounded-lg py-2 text-xs font-bold data-[state=active]:bg-background data-[state=active]:shadow-sm">
                                <Settings2 class="mr-2 h-3.5 w-3.5" />
                                {{ t('isp.infra.router.tabs.general') }}
                            </TabsTrigger>
                            <TabsTrigger v-if="form.type === 'Router'" value="integration" class="rounded-lg py-2 text-xs font-bold data-[state=active]:bg-background data-[state=active]:shadow-sm">
                                <Zap class="mr-2 h-3.5 w-3.5" />
                                {{ t('isp.infra.router.tabs.integration') }}
                            </TabsTrigger>
                        </TabsList>
                    </div>

                    <div class="max-h-[60vh] overflow-y-auto">
                        <TabsContent value="general" class="mt-0 p-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                <div class="space-y-6">
                                    <div class="space-y-4">
                                        <h3 class="text-[11px] font-bold text-muted-foreground tracking-tight flex items-center gap-2">
                                            <Settings2 class="w-3 h-3" />
                                            {{ t('common.labels.general') }}
                                        </h3>
                                        
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div class="space-y-2">
                                                <Label class="text-xs font-semibold text-muted-foreground ml-1">Node Type</Label>
                                                <Select v-model="form.type">
                                                    <SelectTrigger class="h-11 rounded-xl bg-muted/50 border-input focus:bg-background transition-all shadow-sm">
                                                        <SelectValue />
                                                    </SelectTrigger>
                                                    <SelectContent class="rounded-xl border-border shadow-xl">
                                                        <SelectItem value="Router">{{ t('isp.infra.router.types.router') }}</SelectItem>
                                                        <SelectItem value="OLT">{{ t('isp.infra.router.types.olt') }}</SelectItem>
                                                        <SelectItem value="POP">{{ t('isp.infra.router.types.pop') }}</SelectItem>
                                                    </SelectContent>
                                                </Select>
                                            </div>
                                            <div class="space-y-2">
                                                <Label class="text-xs font-semibold text-muted-foreground ml-1">{{ t('isp.infra.nodes.fields.name') }}</Label>
                                                <Input v-model="form.name" :placeholder="t('isp.infra.nodes.fields.name')" class="h-11 rounded-xl bg-muted/50 border-input focus:bg-background transition-all shadow-sm" />
                                            </div>
                                        </div>

                                        <div class="space-y-2">
                                            <Label class="text-xs font-semibold text-muted-foreground ml-1">{{ t('common.labels.description') }}</Label>
                                            <Select v-model="selectedPresetDescription" @update:model-value="handleDescriptionChange">
                                                <SelectTrigger class="h-11 rounded-xl bg-muted/50 border-input focus:bg-background transition-all shadow-sm">
                                                    <SelectValue :placeholder="t('isp.infra.router.presets.selection_placeholder')" />
                                                </SelectTrigger>
                                                <SelectContent class="rounded-xl border-border shadow-xl">
                                                    <SelectItem v-for="preset in descriptionPresets" :key="preset" :value="preset">
                                                        {{ preset === 'Custom' ? t('isp.infra.router.presets.custom') : t(`isp.infra.router.presets.${preset.toLowerCase().split(' ')[0]}`) }}
                                                    </SelectItem>
                                                </SelectContent>
                                            </Select>
                                            <Transition
                                                enter-active-class="transition duration-200 ease-out"
                                                enter-from-class="transform -translate-y-2 opacity-0"
                                                enter-to-class="transform translate-y-0 opacity-100"
                                            >
                                                <Input 
                                                    v-if="isCustomDescription" 
                                                    v-model="form.description" 
                                                    :placeholder="t('isp.infra.router.placeholders.custom_desc')" 
                                                    class="h-11 rounded-xl bg-muted/50 border-input focus:bg-background transition-all mt-2 shadow-sm" 
                                                />
                                            </Transition>
                                        </div>
                                        
                                        <div class="space-y-2">
                                            <div class="flex items-center justify-between">
                                                <Label class="text-xs font-semibold text-slate-600 ml-1">{{ t('isp.infra.nodes.fields.ip') }}</Label>
                                                <div v-if="form.connection_type === 'VPN RADIUS'" class="flex items-center gap-2">
                                                    <Label class="text-[10px] font-bold tracking-wider text-blue-500 cursor-pointer" for="auto-ip">{{ t('isp.infra.router.fields.auto_allocate') }}</Label>
                                                    <Switch id="auto-ip" :checked="autoAllocateIp" @update:checked="toggleAutoAllocate" />
                                                </div>
                                            </div>
                                            <Input 
                                                v-model="form.ip_address" 
                                                :placeholder="autoAllocateIp ? t('isp.infra.router.placeholders.auto_allocate') : t('isp.infra.router.placeholders.ip')" 
                                                :disabled="autoAllocateIp"
                                                class="h-11 rounded-xl bg-muted/50 border-border/50 font-mono focus:bg-background transition-all disabled:bg-muted disabled:text-muted-foreground shadow-sm" 
                                            />
                                        </div>

                                        <div class="space-y-2">
                                            <Label class="text-xs font-semibold text-slate-600 ml-1">{{ t('isp.infra.router.fields.connection_type') }}</Label>
                                            <Select v-model="form.connection_type">
                                                <SelectTrigger class="h-11 rounded-xl bg-muted/50 border-border/50 focus:bg-background transition-all shadow-sm">
                                                    <SelectValue />
                                                </SelectTrigger>
                                                <SelectContent class="rounded-xl border-slate-100 shadow-xl">
                                                    <SelectItem value="IP PUBLIC">IP PUBLIC</SelectItem>
                                                    <SelectItem value="VPN RADIUS">VPN RADIUS</SelectItem>
                                                </SelectContent>
                                            </Select>
                                        </div>

                                        <div v-if="form.type === 'Router' && form.connection_type === 'IP PUBLIC'" class="p-4 bg-blue-500/10 rounded-xl border border-blue-500/20 flex items-center justify-between shadow-sm">
                                            <div class="space-y-0.5">
                                                <Label class="text-xs font-bold text-foreground">{{ t('isp.infra.router.fields.vpn_server') }}</Label>
                                                <p class="text-[10px] text-muted-foreground italic">{{ t('isp.infra.router.help.vpn_server') }}</p>
                                            </div>
                                            <Switch v-model:checked="form.is_vpn_server" />
                                        </div>

                                        <!-- Geography / Location Section -->
                                        <div class="space-y-4 pt-4 border-t border-slate-100/60">
                                            <h3 class="text-[11px] font-bold text-slate-400 tracking-tight flex items-center gap-2">
                                                <MapPin class="w-3 h-3 text-red-400" />
                                                {{ t('isp.infra.router.fields.physical_location') }}
                                            </h3>
                                            <div class="grid grid-cols-2 gap-4">
                                                <div class="space-y-2">
                                                    <Label class="text-[10px] font-bold text-slate-400 ml-1">{{ t('isp.infra.nodes.fields.lat') }}</Label>
                                                    <div class="relative group">
                                                        <Input v-model.number="form.location_lat" type="number" step="0.00000001" class="h-10 rounded-xl bg-slate-50 border-slate-100 pl-8 text-xs font-mono shadow-sm" />
                                                        <MapIcon class="w-3.5 h-3.5 absolute left-2.5 top-3.5 text-slate-400 group-focus-within:text-blue-500 transition-colors" />
                                                    </div>
                                                </div>
                                                <div class="space-y-2">
                                                    <Label class="text-[10px] font-bold text-slate-400 ml-1">{{ t('isp.infra.nodes.fields.lng') }}</Label>
                                                    <div class="relative group">
                                                        <Input v-model.number="form.location_lng" type="number" step="0.00000001" class="h-10 rounded-xl bg-slate-50 border-slate-100 pl-8 text-xs font-mono shadow-sm" />
                                                        <MapIcon class="w-3.5 h-3.5 absolute left-2.5 top-3.5 text-slate-400 group-focus-within:text-blue-500 transition-colors" />
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="space-y-6">
                                    <div class="bg-indigo-500/10 p-6 rounded-2xl border border-dashed border-indigo-500/20 space-y-4 shadow-sm">
                                        <h3 class="text-[11px] font-bold text-indigo-400 tracking-tight flex items-center gap-2">
                                            <Shield class="w-3 h-3" />
                                            Configuration Status
                                        </h3>
                                        <div class="space-y-3">
                                            <div class="flex items-center gap-3 p-3 bg-card rounded-xl border border-border shadow-sm">
                                                <div :class="`p-2 rounded-lg ${form.status === 'active' ? 'bg-green-500/10 text-green-600' : 'bg-amber-500/10 text-amber-600'}`">
                                                    <Activity class="w-4 h-4" />
                                                </div>
                                                <div>
                                                    <p class="text-xs font-bold text-foreground capitalize">{{ t(`isp.common.status.${form.status}`) }}</p>
                                                    <p class="text-[10px] text-muted-foreground">
                                                        {{ form.status === 'active' ? t('isp.infra.router.help.status_active') : t('isp.infra.router.help.status_inactive') }}
                                                    </p>
                                                </div>
                                            </div>
                                            <div class="p-4 bg-card rounded-xl border border-border shadow-sm space-y-2">
                                                <p class="text-[11px] text-muted-foreground leading-relaxed" v-html="t('isp.infra.router.help.integration_desc', { context: router ? t('isp.infra.router.help.context_above') : t('isp.infra.router.help.context_after') })">
                                                </p>
                                            </div>
                                        </div>
                                    </div>

                                    <div v-if="!router" class="p-4 bg-muted/30 rounded-xl border border-border">
                                        <p class="text-[10px] text-muted-foreground italic">
                                            {{ t('isp.infra.router.help.vpn_radius_note') }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </TabsContent>

                        <TabsContent value="integration" class="mt-0 p-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                <div class="space-y-6">
                                    <div class="bg-blue-500/10 p-5 rounded-2xl border border-blue-500/20 space-y-4 shadow-sm">
                                        <h3 class="text-[11px] font-bold text-blue-500 tracking-tight flex items-center gap-2">
                                            <Zap class="w-3 h-3" />
                                            Monitoring Operations
                                        </h3>
                                        
                                        <div class="space-y-2">
                                            <Select v-model="form.connection_method">
                                                <SelectTrigger class="h-11 rounded-xl bg-background border-blue-500/30 shadow-sm transition-all focus:ring-blue-500">
                                                    <SelectValue />
                                                </SelectTrigger>
                                                <SelectContent class="rounded-xl shadow-xl">
                                                    <SelectItem value="api">
                                                        <div class="flex items-center gap-2">
                                                            <Terminal class="w-4 h-4 text-blue-600" />
                                                            <span>MIKROTIK API</span>
                                                        </div>
                                                    </SelectItem>
                                                    <SelectItem value="snmp">
                                                        <div class="flex items-center gap-2">
                                                            <Globe class="w-4 h-4 text-blue-500" />
                                                            <span>SNMP OPS</span>
                                                        </div>
                                                    </SelectItem>
                                                </SelectContent>
                                            </Select>
                                        </div>

                                        <Transition
                                            enter-active-class="transition duration-200 ease-out"
                                            enter-from-class="transform scale-95 opacity-0"
                                            enter-to-class="transform scale-100 opacity-100"
                                        >
                                            <div v-if="form.connection_method === 'api'" class="space-y-4 pt-4 border-t border-blue-500/20">
                                                <div class="grid grid-cols-2 gap-4">
                                                    <div class="space-y-2">
                                                        <Label class="text-[10px] font-bold tracking-wider text-muted-foreground ml-1">{{ t('isp.infra.nodes.fields.api_username') }}</Label>
                                                        <Input v-model="form.api_username" placeholder="admin" class="h-10 rounded-lg bg-background border-input text-sm shadow-sm" />
                                                    </div>
                                                    <div class="space-y-2">
                                                        <Label class="text-[10px] font-bold tracking-wider text-muted-foreground ml-1">{{ t('isp.infra.router.fields.api_port') }}</Label>
                                                        <Input v-model.number="form.api_port" type="number" :placeholder="t('isp.infra.router.placeholders.port')" class="h-10 rounded-lg bg-background border-input text-sm shadow-sm" />
                                                    </div>
                                                </div>
                                                <div class="space-y-2">
                                                    <Label class="text-[10px] font-bold tracking-wider text-muted-foreground ml-1">{{ t('isp.infra.nodes.fields.api_password') }}</Label>
                                                    <Input v-model="form.api_password" type="password" placeholder="••••••••" class="h-10 rounded-lg bg-background border-input text-sm shadow-sm" />
                                                </div>
                                            </div>
                                        </Transition>

                                        <Transition
                                            enter-active-class="transition duration-200 ease-out"
                                            enter-from-class="transform scale-95 opacity-0"
                                            enter-to-class="transform scale-100 opacity-100"
                                        >
                                            <div v-if="form.connection_method === 'snmp'" class="space-y-4 pt-4 border-t border-blue-500/20">
                                                <div class="space-y-2">
                                                    <Label class="text-[10px] font-bold tracking-wider text-muted-foreground ml-1">{{ t('isp.infra.nodes.fields.snmp_community') }}</Label>
                                                    <Input v-model="form.snmp_community" placeholder="public" class="h-10 rounded-lg bg-background border-input text-sm shadow-sm" />
                                                </div>
                                                <div class="space-y-2">
                                                    <Label class="text-[10px] font-bold tracking-wider text-muted-foreground ml-1">{{ t('isp.infra.router.fields.snmp_port') }}</Label>
                                                    <Input v-model.number="form.snmp_port" type="number" class="h-10 rounded-lg bg-background border-input text-sm shadow-sm" />
                                                </div>
                                            </div>
                                        </Transition>
                                    </div>
                                </div>

                                <div class="space-y-6">
                                    <div class="bg-indigo-500/10 p-5 rounded-2xl border border-indigo-500/20 space-y-4 shadow-sm">
                                        <div class="flex items-center justify-between">
                                            <h3 class="text-[11px] font-bold text-indigo-500 tracking-tight flex items-center gap-2">
                                                <Key class="w-3 h-3" />
                                                {{ t('isp.infra.router.fields.radius_secret') }}
                                            </h3>
                                            <Switch v-model:checked="form.radius_enabled" />
                                        </div>

                                        <Transition
                                            enter-active-class="transition duration-200 ease-out"
                                            enter-from-class="transform scale-95 opacity-0"
                                            enter-to-class="transform scale-100 opacity-100"
                                        >
                                            <div v-if="form.radius_enabled" class="space-y-4 pt-2">
                                                <div class="space-y-2">
                                                    <div class="flex items-center justify-between ml-1">
                                                        <Label class="text-[10px] font-bold tracking-wider text-muted-foreground">Secret Key</Label>
                                                        <button 
                                                            type="button"
                                                            @click="generateRadiusSecret"
                                                            class="text-[10px] text-blue-500 hover:text-blue-600 flex items-center gap-1 font-bold"
                                                        >
                                                            <RefreshCw class="w-3 h-3" />
                                                            {{ t('isp.common.actions.regenerate') }}
                                                        </button>
                                                    </div>
                                                    <div class="relative group">
                                                        <Input v-model="form.radius_secret" placeholder="••••••••" class="h-10 rounded-lg bg-background border-input text-sm pr-10 font-mono shadow-sm" />
                                                        <Shield class="w-4 h-4 absolute right-3 top-3 text-muted-foreground group-focus-within:text-indigo-500 transition-colors" />
                                                    </div>
                                                    <p class="text-[10px] text-indigo-400 italic leading-tight">{{ t('isp.infra.router.help.radius_secret_match') }}</p>
                                                </div>
                                                <div class="space-y-2">
                                                    <Label class="text-[10px] font-bold tracking-wider text-muted-foreground ml-1">{{ t('isp.infra.router.fields.management_port') }}</Label>
                                                    <Input v-model.number="form.management_port" type="number" class="h-10 rounded-lg bg-background border-input text-sm shadow-sm" />
                                                </div>
                                            </div>
                                        </Transition>
                                    </div>
                                </div>
                            </div>
                        </TabsContent>
                    </div>
                </Tabs>

                <div class="p-6 bg-muted/30 border-t border-border flex justify-end gap-3">
                    <Button variant="ghost" @click="emit('close')" class="h-11 rounded-xl px-6 text-muted-foreground hover:bg-muted transition-all font-semibold">{{ t('common.actions.cancel') }}</Button>
                    <Button @click="handleSubmit" :disabled="isSaving" class="h-11 rounded-xl px-10 bg-blue-600 hover:bg-blue-700 text-white font-bold shadow-lg shadow-blue-200 transition-all active:scale-[0.98]">
                        <Loader2 v-if="isSaving" class="w-4 h-4 mr-2 animate-spin" />
                        {{ router ? t('isp.infra.modals.save') : t('isp.infra.nodes.add_node') }}
                    </Button>
                </div>
            </div>
        </DialogContent>
    </Dialog>
</template>
