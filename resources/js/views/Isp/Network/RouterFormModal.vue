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
    SelectValue 
} from '@/components/ui';
import { useToast } from '@/composables/useToast';

const props = defineProps<{
    isOpen: boolean;
    router: NetworkNode | null;
}>();

const emit = defineEmits(['close', 'refresh']);

const { t } = useI18n();
const { success, error: toastError } = useToast();

const form = ref({
    name: '',
    ip_address: '',
    secret: '',
    connection_type: 'IP PUBLIC' as 'IP PUBLIC' | 'VPN RADIUS',
    management_port: 80,
    connection_method: 'none' as 'none' | 'snmp' | 'api',
    api_username: '',
    api_password: '',
    api_port: 8728,
    snmp_community: 'public',
    snmp_port: 161,
    status: 'active'
});

const isSaving = ref(false);

watch(() => props.router, (newVal) => {
    if (newVal) {
        form.value = {
            name: newVal.name,
            ip_address: newVal.ip_address || '',
            secret: newVal.secret || '',
            connection_type: newVal.connection_type || 'IP PUBLIC',
            management_port: newVal.management_port || 80,
            connection_method: newVal.connection_method || 'none',
            api_username: newVal.api_username || '',
            api_password: newVal.api_password || '',
            api_port: newVal.api_port || 8728,
            snmp_community: newVal.snmp_community || 'public',
            snmp_port: newVal.snmp_port || 161,
            status: newVal.status || 'active'
        };
    } else {
        form.value = {
            name: '',
            ip_address: '',
            secret: '',
            connection_type: 'IP PUBLIC',
            management_port: 80,
            connection_method: 'none',
            api_username: '',
            api_password: '',
            api_port: 8728,
            snmp_community: 'public',
            snmp_port: 161,
            status: 'active'
        };
    }
}, { immediate: true });

async function handleSubmit() {
    isSaving.value = true;
    try {
        if (props.router) {
            await api.put(`/admin/ja/isp/routers/${props.router.id}`, form.value);
            success.action(t('isp.network.router.messages.success_update'));
        } else {
            await api.post('/admin/ja/isp/routers', { ...form.value, type: 'Router' });
            success.action(t('isp.network.router.messages.success_create'));
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
        <DialogContent class="max-w-xl rounded-2xl p-0 overflow-hidden border-none shadow-2xl">
            <div class="bg-white p-6">
                <DialogHeader class="pb-6">
                    <DialogTitle class="text-xl font-bold uppercase tracking-tight text-slate-800">
                        {{ router ? t('isp.network.router.edit') : t('isp.network.router.new') }}
                    </DialogTitle>
                </DialogHeader>

                <div class="grid grid-cols-2 gap-6">
                    <!-- Basic Info Side -->
                    <div class="space-y-4">
                        <div class="space-y-2">
                            <Label class="text-xs font-bold uppercase tracking-wider text-slate-500">{{ t('isp.network.router.fields.name') }}</Label>
                            <Input v-model="form.name" :placeholder="t('isp.network.router.fields.name')" class="h-10 rounded-xl bg-slate-50 border-slate-200" />
                        </div>
                        
                        <div class="space-y-2">
                            <Label class="text-xs font-bold uppercase tracking-wider text-slate-500">{{ t('isp.network.router.fields.ip') }}</Label>
                            <Input v-model="form.ip_address" placeholder="10.0.0.1" class="h-10 rounded-xl bg-slate-50 border-slate-200 font-mono" />
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <Label class="text-xs font-bold uppercase tracking-wider text-slate-500">{{ t('isp.network.router.fields.connection_type') }}</Label>
                                <Select v-model="form.connection_type">
                                    <SelectTrigger class="h-10 rounded-xl bg-slate-50 border-slate-200">
                                        <SelectValue :placeholder="t('isp.network.router.fields.connection_type')" />
                                    </SelectTrigger>
                                    <SelectContent class="rounded-xl">
                                        <SelectItem value="IP PUBLIC">IP PUBLIC</SelectItem>
                                        <SelectItem value="VPN RADIUS">VPN RADIUS</SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                            <div class="space-y-2">
                                <Label class="text-xs font-bold uppercase tracking-wider text-slate-500">{{ t('isp.network.router.fields.port') }}</Label>
                                <Input v-model.number="form.management_port" type="number" class="h-10 rounded-xl bg-slate-50 border-slate-200" />
                            </div>
                        </div>

                        <div class="space-y-2">
                            <Label class="text-xs font-bold uppercase tracking-wider text-slate-500">{{ t('isp.network.router.fields.secret') }}</Label>
                            <Input v-model="form.secret" type="password" placeholder="••••••••" class="h-10 rounded-xl bg-slate-50 border-slate-200" />
                        </div>
                    </div>

                    <!-- Connectivity Side -->
                    <div class="space-y-4 bg-slate-50/50 p-4 rounded-2xl border border-slate-100">
                        <div class="space-y-2">
                            <Label class="text-xs font-bold uppercase tracking-tight text-slate-800 flex items-center gap-2">
                                <div class="w-1.5 h-1.5 rounded-full bg-blue-500"></div>
                                {{ t('isp.network.connectivity.method') }}
                            </Label>
                            <Select v-model="form.connection_method">
                                <SelectTrigger class="h-10 rounded-xl bg-white border-slate-200 shadow-sm transition-all">
                                    <SelectValue placeholder="Select Method" />
                                </SelectTrigger>
                                <SelectContent class="rounded-xl">
                                    <SelectItem value="none">{{ t('isp.network.data_server.script.options.pppoe_only') }} (NONE)</SelectItem>
                                    <SelectItem value="snmp">SNMP</SelectItem>
                                    <SelectItem value="api">MIKROTIK API</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>

                        <template v-if="form.connection_method === 'api'">
                            <div class="space-y-4 pt-2 border-t border-slate-100">
                                <div class="space-y-2">
                                    <Label class="text-[10px] font-bold uppercase tracking-wider text-slate-400">{{ t('isp.network.data_server.connectivity.username') }}</Label>
                                    <Input v-model="form.api_username" placeholder="admin" class="h-9 rounded-lg bg-white border-slate-200 text-sm" />
                                </div>
                                <div class="space-y-2">
                                    <Label class="text-[10px] font-bold uppercase tracking-wider text-slate-400">{{ t('isp.network.data_server.connectivity.password') }}</Label>
                                    <Input v-model="form.api_password" type="password" placeholder="••••••••" class="h-9 rounded-lg bg-white border-slate-200 text-sm" />
                                </div>
                                <div class="space-y-2">
                                    <Label class="text-[10px] font-bold uppercase tracking-wider text-slate-400">{{ t('isp.network.router.fields.api_port') }}</Label>
                                    <Input v-model.number="form.api_port" type="number" class="h-9 rounded-lg bg-white border-slate-200 text-sm" />
                                </div>
                            </div>
                        </template>

                        <template v-if="form.connection_method === 'snmp'">
                            <div class="space-y-4 pt-2 border-t border-slate-100">
                                <div class="space-y-2">
                                    <Label class="text-[10px] font-bold uppercase tracking-wider text-slate-400">{{ t('isp.network.data_server.connectivity.snmp_community') }}</Label>
                                    <Input v-model="form.snmp_community" placeholder="public" class="h-9 rounded-lg bg-white border-slate-200 text-sm" />
                                </div>
                                <div class="space-y-2">
                                    <Label class="text-[10px] font-bold uppercase tracking-wider text-slate-400">{{ t('isp.network.router.fields.snmp_port') }}</Label>
                                    <Input v-model.number="form.snmp_port" type="number" class="h-9 rounded-lg bg-white border-slate-200 text-sm" />
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                <div class="mt-8 flex justify-end gap-3">
                    <Button variant="outline" @click="emit('close')" class="h-11 rounded-xl px-6">{{ t('common.actions.cancel') }}</Button>
                    <Button @click="handleSubmit" :disabled="isSaving" class="h-11 rounded-xl px-10 bg-blue-600 hover:bg-blue-700 text-white font-bold shadow-lg shadow-blue-100 transition-all active:scale-[0.98]">
                        <Loader2 v-if="isSaving" class="w-4 h-4 mr-2 animate-spin" />
                        {{ router ? t('isp.network.router.edit') : t('isp.network.router.new') }}
                    </Button>
                </div>
            </div>
        </DialogContent>
    </Dialog>
</template>
