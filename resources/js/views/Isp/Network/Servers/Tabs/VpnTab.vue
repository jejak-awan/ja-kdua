<script setup lang="ts">
import { ref, onMounted, watch, computed } from 'vue';
import { useI18n } from 'vue-i18n';
import {
    Card, CardHeader, CardTitle, CardContent,
    Button, Input, Badge, Label,
    Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter,
    Select, SelectTrigger, SelectValue, SelectContent, SelectItem,
    Tooltip, TooltipTrigger, TooltipContent, TooltipProvider
} from '@/components/ui';
import Search from 'lucide-vue-next/dist/esm/icons/search.js';
import Plus from 'lucide-vue-next/dist/esm/icons/plus.js';
import Server from 'lucide-vue-next/dist/esm/icons/server.js';
import ShieldCheck from 'lucide-vue-next/dist/esm/icons/shield-check.js';
import Trash2 from 'lucide-vue-next/dist/esm/icons/trash-2.js';

import RefreshCw from 'lucide-vue-next/dist/esm/icons/refresh-cw.js';
import Key from 'lucide-vue-next/dist/esm/icons/key.js';
import api from '@/services/api';
import { useToast } from '@/composables/useToast';
import { useConfirm } from '@/composables/useConfirm';
import type { NetworkNode } from '@/types/isp';
import { parseResponse } from '@/utils/responseParser';

const { t } = useI18n();
const toast = useToast();
const { confirm } = useConfirm();

const routers = ref<NetworkNode[]>([]);
const loadingRouters = ref(false);
const selectedRouterId = ref<number | null>(null);
const routerSearch = ref('');

interface VpnSecret {
    id: string;
    name: string;
    service: string;
    profile: string;
    remoteaddress?: string;
    comment?: string;
    [key: string]: unknown;
}

const secrets = ref<VpnSecret[]>([]);
const loadingSecrets = ref(false);

const showCreateModal = ref(false);
const savingSecret = ref(false);
const secretForm = ref({
    name: '',
    password: '',
    service: 'any',
    profile: 'default',
    remote_address: '',
    comment: ''
});

const fetchRouters = async () => {
    loadingRouters.value = true;
    try {
        const response = await api.get('/admin/janet/isp/routers', {
            params: {
                per_page: 100,
                connection_method: 'api',
                is_vpn_server: 1
            }
        });
        const parsed = parseResponse<NetworkNode>(response);
        routers.value = parsed.data;
        if (routers.value.length > 0 && !selectedRouterId.value) {
            selectedRouterId.value = routers.value[0].id;
        }
    } catch (error) {
        toast.error.action(error as Record<string, unknown>);
    } finally {
        loadingRouters.value = false;
    }
};

const fetchSecrets = async () => {
    if (!selectedRouterId.value) return;
    loadingSecrets.value = true;
    try {
        const response = await api.get(`/admin/janet/isp/routers/${selectedRouterId.value}/vpn-secrets`);
        secrets.value = response.data.data;
    } catch (error) {
        toast.error.action(error as Record<string, unknown>);
    } finally {
        loadingSecrets.value = false;
    }
};

const openCreateModal = () => {
    secretForm.value = {
        name: '',
        password: '',
        service: 'any',
        profile: 'default',
        remote_address: '',
        comment: ''
    };
    showCreateModal.value = true;
};

const saveSecret = async () => {
    if (!selectedRouterId.value) return;
    savingSecret.value = true;
    try {
        await api.post(`/admin/janet/isp/routers/${selectedRouterId.value}/vpn-secrets`, secretForm.value);
        toast.success.action(t('isp.network.vpn.messages.success_create'));
        showCreateModal.value = false;
        fetchSecrets();
    } catch (error) {
        toast.error.action(error as Record<string, unknown>);
    } finally {
        savingSecret.value = false;
    }
};

const deleteSecret = async (secretId: string, name: string) => {
    if (!selectedRouterId.value) return;
    const isConfirmed = await confirm({
        title: t('isp.network.vpn.modal.delete_title') || t('common.actions.delete'),
        message: t('common.messages.confirm.delete', { name }),
        variant: 'danger'
    });

    if (isConfirmed) {
        try {
            await api.delete(`/admin/janet/isp/routers/${selectedRouterId.value}/vpn-secrets/${secretId}`);
            toast.success.action(t('isp.network.vpn.messages.success_delete'));
            fetchSecrets();
        } catch (error) {
            toast.error.action(error as Record<string, unknown>);
        }
    }
};

watch(selectedRouterId, () => {
    fetchSecrets();
});

onMounted(() => {
    fetchRouters();
});

const filteredRouters = computed(() => {
    if (!routerSearch.value) return routers.value;
    return routers.value.filter((r: NetworkNode) => 
        r.name.toLowerCase().includes(routerSearch.value.toLowerCase()) ||
        r.ip_address.toLowerCase().includes(routerSearch.value.toLowerCase())
    );
});
</script>

<template>
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6 h-[calc(100vh-250px)]">
        <!-- Sidebar: Router List -->
        <Card class="lg:col-span-1 flex flex-col overflow-hidden rounded-xl border border-border/40 shadow-sm">
            <CardHeader class="p-4 bg-muted/50 border-b border-border/40">
                <CardTitle class="text-sm font-bold text-muted-foreground flex items-center gap-2">
                    <Server class="w-4 h-4" />
                    {{ t('isp.network.vpn.sidebar_title') }}
                </CardTitle>
                <div class="relative mt-2">
                    <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-slate-400" />
                    <Input 
                        v-model="routerSearch" 
                        :placeholder="t('isp.network.vpn.search_router')" 
                        class="pl-9 h-9 text-xs bg-card rounded-xl border-border/40"
                    />
                </div>
            </CardHeader>
            <CardContent class="p-0 overflow-y-auto flex-1">
                <div v-if="loadingRouters" class="p-8 text-center">
                    <RefreshCw class="w-6 h-6 animate-spin mx-auto text-primary opacity-20" />
                </div>
                <div v-else-if="filteredRouters.length === 0" class="p-8 text-center text-muted-foreground text-sm">
                    {{ t('isp.network.vpn.no_routers') }}
                </div>
                <div v-else class="divide-y divide-border/40">
                    <button
                        v-for="router in filteredRouters"
                        :key="router.id"
                        @click="selectedRouterId = router.id"
                        class="w-full p-4 flex flex-col text-left transition-colors relative group"
                        :class="selectedRouterId === router.id ? 'bg-primary/5' : 'hover:bg-muted/50'"
                    >
                        <div class="flex items-center justify-between mb-1">
                            <span class="font-bold text-sm tracking-tight" :class="selectedRouterId === router.id ? 'text-primary' : 'text-slate-900'">
                                {{ router.name }}
                            </span>
                            <Badge variant="outline" class="text-[10px] font-bold px-1.5 py-0 h-4 border-border/40 rounded-xl">
                                API
                            </Badge>
                        </div>
                        <span class="text-xs text-muted-foreground font-mono">{{ router.ip_address }}</span>
                        
                        <div v-if="selectedRouterId === router.id" class="absolute left-0 top-0 bottom-0 w-1 bg-primary rounded-r-xl"></div>
                    </button>
                </div>
            </CardContent>
        </Card>

        <!-- Main Content: VPN Secret Management -->
        <Card class="lg:col-span-3 flex flex-col overflow-hidden rounded-xl border border-border/40 shadow-sm bg-card">
            <template v-if="selectedRouterId">
                <CardHeader class="p-6 border-b border-border/40 flex flex-row items-center justify-between bg-card">
                    <div>
                        <CardTitle class="text-lg font-bold text-foreground flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-primary/10 flex items-center justify-center text-primary border border-primary/10">
                                <ShieldCheck class="w-6 h-6" />
                            </div>
                            {{ t('isp.network.vpn.title') }}
                        </CardTitle>
                        <p class="text-sm text-muted-foreground mt-1">
                            {{ t('isp.network.vpn.subtitle', { router: routers.find((r: NetworkNode) => r.id === selectedRouterId)?.name }) }}
                        </p>
                    </div>
                    <Button @click="openCreateModal" class="rounded-xl px-4 py-2 bg-primary hover:bg-primary/90 text-primary-foreground shadow-lg shadow-primary/20 transition-all active:scale-95">
                        <Plus class="w-4 h-4 mr-2" />
                        {{ t('isp.network.vpn.create') }}
                    </Button>
                </CardHeader>
                <CardContent class="p-0 overflow-y-auto flex-1">
                    <div v-if="loadingSecrets" class="p-12 text-center">
                        <RefreshCw class="w-8 h-8 animate-spin mx-auto text-primary mb-4" />
                        <p class="text-muted-foreground animate-pulse font-medium">{{ t('isp.network.vpn.creating') }}</p>
                    </div>
                    <div v-else-if="secrets.length === 0" class="p-20 text-center">
                        <div class="w-20 h-20 bg-muted rounded-full flex items-center justify-center mx-auto mb-4 border-2 border-dashed border-border">
                            <Key class="w-10 h-10 text-muted-foreground/30" />
                        </div>
                        <h3 class="text-lg font-bold text-foreground mb-1">{{ t('isp.network.vpn.empty_title') }}</h3>
                        <p class="text-muted-foreground max-w-xs mx-auto">{{ t('isp.network.vpn.empty_desc') }}</p>
                    </div>
                    <div v-else class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
                            <div 
                                v-for="secret in secrets" 
                                :key="secret.id"
                                class="group relative bg-card border border-border/80 rounded-2xl p-4 hover:border-primary/40 hover:shadow-xl hover:shadow-primary/5 transition-all duration-300 overflow-hidden"
                            >
                                <!-- Background Decoration -->
                                <div class="absolute -right-4 -bottom-4 w-16 h-16 text-muted opacity-10 group-hover:text-primary/10 group-hover:opacity-100 transition-all duration-500 transform rotate-12">
                                    <ShieldCheck class="w-full h-full" />
                                </div>

                                <div class="flex items-start justify-between mb-4 relative">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-lg bg-muted group-hover:bg-primary/5 flex items-center justify-center text-muted-foreground/50 group-hover:text-primary transition-colors">
                                            <Key class="w-5 h-5" />
                                        </div>
                                        <div>
                                            <h4 class="font-bold text-foreground group-hover:text-primary transition-colors truncate max-w-[150px]">
                                                {{ secret.name }}
                                            </h4>
                                            <p class="text-[10px] font-bold text-slate-400 flex items-center gap-1">
                                                {{ secret.service }} • {{ secret.profile }}
                                            </p>
                                        </div>
                                    </div>
                                    <div class="flex gap-1.5 opacity-0 group-hover:opacity-100 transition-all duration-200">
                                        <TooltipProvider>
                                            <Tooltip>
                                                <TooltipTrigger as-child>
                                                    <Button 
                                                        variant="ghost" 
                                                        size="icon" 
                                                        class="h-8 w-8 rounded-lg text-destructive hover:bg-destructive/10"
                                                        @click="deleteSecret(secret.id, secret.name)"
                                                    >
                                                        <Trash2 class="w-4 h-4" />
                                                    </Button>
                                                </TooltipTrigger>
                                                <TooltipContent>Delete Secret</TooltipContent>
                                            </Tooltip>
                                        </TooltipProvider>
                                    </div>
                                </div>

                                <div class="space-y-2.5 relative">
                                    <div class="flex items-center justify-between text-xs">
                                        <span class="text-muted-foreground">Remote Addr</span>
                                        <span class="font-mono font-medium text-foreground">{{ secret.remoteaddress || '-' }}</span>
                                    </div>
                                    <div class="pt-2 border-t border-border/40">
                                        <span class="text-[10px] font-bold text-muted-foreground block mb-1">Comment</span>
                                        <p class="text-xs text-muted-foreground line-clamp-2 italic">
                                            {{ secret.comment || 'No comment' }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </CardContent>
            </template>
            <div v-else class="flex-1 flex flex-col items-center justify-center p-12 bg-muted/20">
                <div class="w-16 h-16 bg-card rounded-xl shadow-sm border border-border/40 flex items-center justify-center mb-6">
                    <Server class="w-8 h-8 text-muted-foreground/30" />
                </div>
                <h3 class="text-xl font-bold text-foreground mb-2">{{ t('isp.network.vpn.select_router_title') }}</h3>
                <p class="text-muted-foreground max-w-sm text-center">
                    {{ t('isp.network.vpn.select_router_desc') }}
                </p>
            </div>
        </Card>

        <!-- Create Secret Modal -->
        <Dialog v-model:open="showCreateModal">
            <DialogContent class="sm:max-w-[450px] rounded-3xl border-none shadow-2xl p-0 overflow-hidden">
                <div class="bg-primary px-6 py-8 relative">
                    <div class="absolute right-0 top-0 w-32 h-32 bg-white/10 rounded-full -mr-16 -mt-16 blur-2xl"></div>
                    <div class="absolute left-0 bottom-0 w-24 h-24 bg-white/5 rounded-full -ml-12 -mb-12 blur-xl"></div>
                    
                    <DialogHeader class="relative text-white">
                        <DialogTitle class="text-2xl font-bold flex items-center gap-3">
                            <Plus class="w-7 h-7" />
                            {{ t('isp.network.vpn.modal.title') }}
                        </DialogTitle>
                        <p class="text-primary-foreground/70 text-sm mt-1">VPN Secret Configuration</p>
                    </DialogHeader>
                </div>
                
                <div class="p-6 space-y-5 bg-card">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-2 col-span-2">
                            <Label for="name" class="text-xs font-bold text-muted-foreground">{{ t('isp.network.vpn.modal.username') }}</Label>
                            <Input id="name" v-model="secretForm.name" placeholder="client_username" class="rounded-xl bg-muted/50 border-border/40 h-11 focus:ring-primary/20" />
                        </div>
                        <div class="space-y-2 col-span-2">
                            <Label for="password" class="text-xs font-bold text-muted-foreground">{{ t('isp.network.vpn.modal.password') }}</Label>
                            <Input id="password" type="password" v-model="secretForm.password" placeholder="••••••••" class="rounded-xl bg-muted/50 border-border/40 h-11" />
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <Label class="text-xs font-bold text-muted-foreground">{{ t('isp.network.vpn.modal.service') }}</Label>
                            <Select v-model="secretForm.service">
                                <SelectTrigger class="rounded-xl bg-muted/50 border-border/40 h-11">
                                    <SelectValue />
                                </SelectTrigger>
                                <SelectContent class="rounded-xl border-border">
                                    <SelectItem value="any">Any</SelectItem>
                                    <SelectItem value="pppoe">PPPoE</SelectItem>
                                    <SelectItem value="l2tp">L2TP</SelectItem>
                                    <SelectItem value="pptp">PPTP</SelectItem>
                                    <SelectItem value="ovpn">OpenVPN</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                        <div class="space-y-2">
                            <Label class="text-xs font-bold text-muted-foreground">{{ t('isp.network.vpn.modal.profile') }}</Label>
                            <Input v-model="secretForm.profile" placeholder="default" class="rounded-xl bg-muted/50 border-border/40 h-11" />
                        </div>
                    </div>

                    <div class="space-y-2">
                        <Label class="text-xs font-bold text-muted-foreground">{{ t('isp.network.vpn.modal.remote_address') }}</Label>
                        <Input v-model="secretForm.remote_address" placeholder="10.10.10.2" class="rounded-xl bg-muted/50 border-border/40 h-11 font-mono text-sm" />
                    </div>

                    <div class="space-y-2">
                        <Label class="text-xs font-bold text-muted-foreground">{{ t('isp.network.vpn.modal.comment') }}</Label>
                        <Input v-model="secretForm.comment" placeholder="Client description..." class="rounded-xl bg-muted/50 border-border/40 h-11" />
                    </div>
                </div>

                <DialogFooter class="p-6 bg-muted/30 border-t border-border/40 flex sm:justify-between items-center">
                    <Button variant="ghost" @click="showCreateModal = false" class="rounded-xl px-6 h-11 text-muted-foreground">
                        {{ t('common.actions.cancel') }}
                    </Button>
                    <Button @click="saveSecret" :loading="savingSecret" class="rounded-xl px-8 h-11 bg-primary shadow-lg shadow-primary/20">
                        <ShieldCheck class="w-4 h-4 mr-2" />
                        {{ t('isp.network.vpn.create') }}
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </div>
</template>

<style scoped>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    line-clamp: 2;
    overflow: hidden;
}
</style>
