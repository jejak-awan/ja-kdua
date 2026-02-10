<script setup lang="ts">
import { ref } from 'vue';
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

const props = defineProps<{
    isOpen: boolean;
    router: NetworkNode | null;
}>();

const emit = defineEmits(['close']);

const { t } = useI18n();

const options = ref({
    os_version: '',
    tunnel_type: '',
    service: ''
});

const vpnServerIp = '172.31.233.1'; // Example central VPN server IP

function generateScript() {
    if (!props.router) return '';

    let script = `# Generated for ${props.router.name}\n`;
    script += `# IP Address: ${props.router.ip_address}\n\n`;

    // 1. Basic Connection Setup (VPN / Tunnel)
    if (options.value.tunnel_type === 'OVPN') {
        script += `/interface ovpn-client add connect-to=${vpnServerIp} name=vpn-k2net user="${props.router.name}" password="${props.router.secret || 'password'}" profile=default-encryption\n`;
    } else if (options.value.tunnel_type === 'SSTP') {
        script += `/interface sstp-client add connect-to=${vpnServerIp} name=vpn-k2net user="${props.router.name}" password="${props.router.secret || 'password'}" profile=default-encryption\n`;
    } else if (options.value.tunnel_type === 'L2TP') {
        script += `/interface l2tp-client add connect-to=${vpnServerIp} name=vpn-k2net user="${props.router.name}" password="${props.router.secret || 'password'}" profile=default-encryption\n`;
    }

    script += `\n# Service-specific configuration\n`;

    // 2. Service-specific logic
    if (options.value.service.includes('PPPoE')) {
        script += `/radius add address=${vpnServerIp} secret=radius_secret service=ppp\n`;
        script += `/ppp aaa set use-radius=yes\n`;
    }
    
    if (options.value.service.includes('HOTSPOT')) {
        script += `/radius add address=${vpnServerIp} secret=radius_secret service=hotspot\n`;
        script += `/ip hotspot profile set [ find default=yes ] use-radius=yes\n`;
    }

    if (options.value.os_version === 'v7') {
        script += `\n# RouterOS v7 specific optimizations\n`;
        script += `/routing table add name=rt-vpn fib\n`;
    }

    return script;
}

function handleDownload() {
    const content = generateScript();
    const blob = new Blob([content], { type: 'text/plain' });
    const url = URL.createObjectURL(blob);
    const link = document.createElement('a');
    link.href = url;
    link.download = `k2net-setup-${props.router?.name || 'router'}.rsc`;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    URL.revokeObjectURL(url);
}
</script>

<template>
    <Dialog :open="isOpen" @update:open="emit('close')">
        <DialogContent class="max-w-md rounded-2xl overflow-hidden p-0 border-none shadow-2xl">
            <div class="bg-white p-6">
                <DialogHeader class="pb-6 relative border-b border-slate-50">
                    <DialogTitle class="text-[17px] font-bold text-slate-700 flex items-center gap-2">
                        <FileCode class="h-5 w-5 text-blue-500" />
                        {{ t('isp.network.data_server.script.title') }}
                    </DialogTitle>
                </DialogHeader>

                <div class="space-y-5 py-6">
                    <!-- Router Info (Read-onlyish/Pre-filled) -->
                    <div class="space-y-2">
                        <Label class="text-[11px] font-bold uppercase tracking-wider text-slate-400">
                            {{ t('isp.network.data_server.script.fields.router_name') }}
                        </Label>
                        <Input 
                            :value="router?.name" 
                            disabled 
                            class="h-10 rounded-xl bg-slate-50 border-slate-100 text-slate-500 font-medium" 
                        />
                    </div>

                    <div class="space-y-2">
                        <Label class="text-[11px] font-bold uppercase tracking-wider text-slate-400">
                            {{ t('isp.network.data_server.script.fields.ip_address') }}
                        </Label>
                        <Input 
                            :value="router?.ip_address" 
                            disabled 
                            class="h-10 rounded-xl bg-slate-50 border-slate-100 text-slate-500 font-mono" 
                        />
                    </div>

                    <!-- Options -->
                    <div class="space-y-2">
                        <Label class="text-[11px] font-bold uppercase tracking-wider text-slate-400">
                            {{ t('isp.network.data_server.script.os_version') }}
                        </Label>
                        <Select v-model="options.os_version">
                            <SelectTrigger class="h-10 rounded-xl border-slate-200">
                                <SelectValue :placeholder="t('isp.network.data_server.script.options.select')" />
                            </SelectTrigger>
                            <SelectContent class="rounded-xl">
                                <SelectItem value="v6">{{ t('isp.network.data_server.script.options.os_v6') }}</SelectItem>
                                <SelectItem value="v7">{{ t('isp.network.data_server.script.options.os_v7') }}</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>

                    <div class="space-y-2">
                        <Label class="text-[11px] font-bold uppercase tracking-wider text-slate-400">
                            {{ t('isp.network.data_server.script.tunnel_type') }}
                        </Label>
                        <Select v-model="options.tunnel_type">
                            <SelectTrigger class="h-10 rounded-xl border-slate-200">
                                <SelectValue :placeholder="t('isp.network.data_server.script.options.select')" />
                            </SelectTrigger>
                            <SelectContent class="rounded-xl">
                                <SelectItem value="OVPN">OVPN</SelectItem>
                                <SelectItem value="SSTP">SSTP</SelectItem>
                                <SelectItem value="L2TP">L2TP</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>

                    <div class="space-y-2">
                        <Label class="text-[11px] font-bold uppercase tracking-wider text-slate-400">
                            {{ t('isp.network.data_server.script.service') }}
                        </Label>
                        <Select v-model="options.service">
                            <SelectTrigger class="h-10 rounded-xl border-slate-200">
                                <SelectValue :placeholder="t('isp.network.data_server.script.options.select')" />
                            </SelectTrigger>
                            <SelectContent class="rounded-xl">
                                <SelectItem value="SCRIPT VPN">{{ t('isp.network.data_server.script.options.vpn_script') }}</SelectItem>
                                <SelectItem value="PPPoE ONLY">{{ t('isp.network.data_server.script.options.pppoe_only') }}</SelectItem>
                                <SelectItem value="HOTSPOT ONLY">{{ t('isp.network.data_server.script.options.hotspot_only') }}</SelectItem>
                                <SelectItem value="PPPoE + HOTSPOT">{{ t('isp.network.data_server.script.options.pppoe_hotspot') }}</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                </div>

                <div class="pt-2">
                    <Button 
                        @click="handleDownload" 
                        class="w-full h-11 bg-green-600 hover:bg-green-700 text-white rounded-xl font-bold shadow-lg shadow-green-100 transition-all active:scale-[0.98]"
                        :disabled="!options.os_version || !options.service"
                    >
                        <Download class="h-4 w-4 mr-2" />
                        {{ t('isp.network.data_server.script.download') }}
                    </Button>
                </div>
            </div>
        </DialogContent>
    </Dialog>
</template>
