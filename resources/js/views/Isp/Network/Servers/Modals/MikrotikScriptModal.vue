<script setup lang="ts">
import { ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import { 
    Dialog, 
    DialogContent, 
    DialogHeader, 
    DialogTitle, 
    Label,
    Input,
    Button,
    Select, 
    SelectContent, 
    SelectItem, 
    SelectTrigger, 
    SelectValue 
} from '@/components/ui';
import { type NetworkNode } from '@/types/isp';
import Download from 'lucide-vue-next/dist/esm/icons/download.js';
import FileCode from 'lucide-vue-next/dist/esm/icons/file-code.js';
import Copy from 'lucide-vue-next/dist/esm/icons/copy.js';
import Loader2 from 'lucide-vue-next/dist/esm/icons/loader-circle.js';
import api from '@/services/api';
import { useToast } from '@/composables/useToast';

const props = defineProps<{
    isOpen: boolean;
    router: NetworkNode | null;
}>();

const emit = defineEmits(['close']);

const { t } = useI18n();
const { success, error: toastError } = useToast();

const options = ref({
    os_version: 'v7',
    tunnel_type: 'none',
    service: 'vpn_only',
    connection_type: 'IP PUBLIC',
    vpn_ip: ''
});

watch(() => props.isOpen, (newVal) => {
    if (newVal && props.router) {
        options.value.connection_type = (props.router.connection_type as 'IP PUBLIC' | 'VPN RADIUS') || 'IP PUBLIC';
        generatedScript.value = '';
    }
});

watch(() => options.value.connection_type, (newVal) => {
    if (newVal === 'IP PUBLIC') {
        options.value.tunnel_type = 'none';
        options.value.vpn_ip = '';
    } else if (options.value.tunnel_type === 'none') {
        options.value.tunnel_type = 'ovpn';
    }
});

const isGenerating = ref(false);
const generatedScript = ref('');

async function handleGenerate() {
    if (!props.router) return;
    
    isGenerating.value = true;
    try {
        const response = await api.post(`/admin/janet/isp/routers/${props.router.id}/script`, {
            version: options.value.os_version,
            tunnel_type: options.value.tunnel_type,
            service: options.value.service,
            connection_type: options.value.connection_type,
            vpn_ip: options.value.vpn_ip
        });
        
        generatedScript.value = response.data.data.script;
        
        // Auto download if user wants or just show it? 
        // For now, let's just enable the download button by storing the result.
        success.action(t('isp.infra.router.messages.script_generated'));
    } catch (error) {
        toastError.action(error as Record<string, unknown>);
    } finally {
        isGenerating.value = false;
    }
}

function handleDownload() {
    if (!generatedScript.value) return;
    
    const blob = new Blob([generatedScript.value], { type: 'text/plain' });
    const url = URL.createObjectURL(blob);
    const link = document.createElement('a');
    link.href = url;
    link.download = `janet-${props.router?.name.toLowerCase().replace(/\s+/g, '_')}.rsc`;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    URL.revokeObjectURL(url);
}

async function copyToClipboard() {
    if (!generatedScript.value) return;
    try {
        await navigator.clipboard.writeText(generatedScript.value);
        success.action(t('common.messages.copied'));
    } catch (_err) {
        toastError.action({ message: 'Failed to copy' });
    }
}
</script>

<template>
    <Dialog :open="isOpen" @update:open="emit('close')">
        <DialogContent class="max-w-md rounded-2xl overflow-hidden p-0 border-none shadow-2xl">
            <div class="bg-popover p-6">
                <DialogHeader class="pb-6 relative border-b border-slate-50">
                    <DialogTitle class="text-[17px] font-bold text-slate-700 flex items-center gap-2">
                        <FileCode class="h-5 w-5 text-blue-500" />
                        {{ t('isp.infra.router.script_modal_title') }}
                    </DialogTitle>
                </DialogHeader>

                <div class="space-y-4 py-6">
                    <!-- Router Info (Read-only) -->
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-1.5">
                            <Label class="text-[10px] font-bold tracking-wider text-slate-400 ml-1">
                                {{ t('isp.infra.nodes.fields.name') }}
                            </Label>
                            <Input :value="router?.name" disabled class="h-9 rounded-lg bg-slate-50 border-slate-100 text-slate-500 text-xs font-medium" />
                        </div>
                        <div class="space-y-1.5">
                            <Label class="text-[10px] font-bold tracking-wider text-slate-400 ml-1">
                                {{ t('isp.infra.nodes.fields.ip') }}
                            </Label>
                            <Input :value="router?.ip_address" disabled class="h-9 rounded-lg bg-slate-50 border-slate-100 text-slate-500 text-xs font-mono" />
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <Label class="text-[11px] font-bold tracking-wider text-slate-400 ml-1">
                                {{ t('isp.infra.router.help.mode') }}
                            </Label>
                            <Select v-model="options.connection_type">
                                <SelectTrigger class="h-10 rounded-xl border-slate-200">
                                    <SelectValue />
                                </SelectTrigger>
                                <SelectContent class="rounded-xl">
                                    <SelectItem value="IP PUBLIC">IP PUBLIC</SelectItem>
                                    <SelectItem value="VPN RADIUS">VPN RADIUS</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                        <div v-if="options.connection_type === 'VPN RADIUS'" class="space-y-2">
                            <Label class="text-[11px] font-bold tracking-wider text-slate-400 ml-1">
                                {{ t('isp.infra.router.help.tunnel') }}
                            </Label>
                            <Select v-model="options.tunnel_type">
                                <SelectTrigger class="h-10 rounded-xl border-slate-200">
                                    <SelectValue />
                                </SelectTrigger>
                                <SelectContent class="rounded-xl">
                                    <SelectItem value="ovpn">OVPN</SelectItem>
                                    <SelectItem value="sstp">SSTP</SelectItem>
                                    <SelectItem value="l2tp">L2TP</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                        <div v-else class="space-y-2">
                            <Label class="text-[11px] font-bold tracking-wider text-slate-400 ml-1">
                                OS Version
                            </Label>
                            <Select v-model="options.os_version">
                                <SelectTrigger class="h-10 rounded-xl border-slate-200">
                                    <SelectValue />
                                </SelectTrigger>
                                <SelectContent class="rounded-xl">
                                    <SelectItem value="v6">RouterOS v6</SelectItem>
                                    <SelectItem value="v7">RouterOS v7</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                    </div>

                    <div v-if="options.connection_type === 'VPN RADIUS'" class="grid grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <Label class="text-[11px] font-bold tracking-wider text-slate-400 ml-1">
                                OS Version
                            </Label>
                            <Select v-model="options.os_version">
                                <SelectTrigger class="h-10 rounded-xl border-slate-200">
                                    <SelectValue />
                                </SelectTrigger>
                                <SelectContent class="rounded-xl">
                                    <SelectItem value="v6">RouterOS v6</SelectItem>
                                    <SelectItem value="v7">RouterOS v7</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                        <div class="space-y-2">
                            <Label class="text-[11px] font-bold tracking-wider text-slate-400 ml-1">
                                {{ t('isp.infra.router.help.rear_vpn_ip') }}
                            </Label>
                            <Input v-model="options.vpn_ip" placeholder="172.31.x.x" class="h-10 rounded-xl border-slate-100 placeholder:text-slate-300 text-xs text-blue-600 font-mono" />
                        </div>
                    </div>

                    <div class="space-y-2">
                        <Label class="text-[11px] font-bold tracking-wider text-slate-400 ml-1">
                            Script Service
                        </Label>
                        <Select v-model="options.service">
                            <SelectTrigger class="h-10 rounded-xl border-slate-200">
                                <SelectValue />
                            </SelectTrigger>
                            <SelectContent class="rounded-xl">
                                <SelectItem value="vpn_only">VPN Initial Script</SelectItem>
                                <SelectItem value="pppoe_only">PPPoE Only</SelectItem>
                                <SelectItem value="hotspot_only">Hotspot Only</SelectItem>
                                <SelectItem value="both">PPPoE + Hotspot</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>

                    <!-- Script Preview Area -->
                    <div v-if="generatedScript" class="mt-4 space-y-2">
                        <Label class="text-[11px] font-bold tracking-wider text-slate-400 ml-1">{{ t('isp.infra.router.help.preview') }}</Label>
                        <div class="p-3 bg-slate-900 rounded-xl overflow-hidden">
                            <pre class="text-[10px] text-green-400 font-mono overflow-y-auto max-h-[150px] whitespace-pre-wrap">{{ generatedScript }}</pre>
                        </div>
                    </div>
                </div>

                <div class="pt-4 flex flex-col gap-2">
                    <Button 
                        v-if="!generatedScript"
                        @click="handleGenerate" 
                        class="w-full h-11 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-bold shadow-lg shadow-blue-100 transition-all active:scale-[0.98]"
                        :disabled="isGenerating"
                    >
                        <Loader2 v-if="isGenerating" class="h-4 w-4 mr-2 animate-spin" />
                        <FileCode v-else class="h-4 w-4 mr-2" />
                        {{ isGenerating ? t('common.labels.processing') : t('isp.infra.router.help.generate') }}
                    </Button>
                    
                    <div v-else class="grid grid-cols-2 gap-3">
                        <Button 
                            variant="outline"
                            @click="copyToClipboard" 
                            class="h-11 border-blue-100 text-blue-600 hover:bg-blue-50 rounded-xl font-bold transition-all active:scale-[0.98]"
                        >
                            <Copy class="h-4 w-4 mr-2" />
                            {{ t('common.actions.copy') }}
                        </Button>
                        <Button 
                            @click="handleDownload" 
                            class="h-11 bg-green-600 hover:bg-green-700 text-white rounded-xl font-bold shadow-lg shadow-green-100 transition-all active:scale-[0.98]"
                        >
                            <Download class="h-4 w-4 mr-2" />
                            Download RSC
                        </Button>
                    </div>

                    <Button 
                        v-if="generatedScript"
                        variant="ghost" 
                        @click="generatedScript = ''" 
                        class="text-[11px] text-slate-400 py-1"
                    >
                        {{ t('isp.infra.router.help.reset') }}
                    </Button>
                </div>
            </div>
        </DialogContent>
    </Dialog>
</template>
