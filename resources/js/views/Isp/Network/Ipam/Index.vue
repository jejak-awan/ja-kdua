<template>
    <div class="space-y-6 animate-in fade-in duration-500">
        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h2 class="text-3xl font-bold tracking-tight">{{ t('isp.network.ipam.title') }}</h2>
                <p class="text-muted-foreground">{{ t('isp.network.subtitle') }}</p>
            </div>
            <Dialog v-model:open="isSubnetDialogOpen">
                <DialogTrigger as-child>
                    <Button class="gap-2 rounded-xl">
                        <Globe class="w-4 h-4" />
                        {{ t('isp.network.subnets.new') }}
                    </Button>
                </DialogTrigger>
                <Button variant="outline" class="gap-2 rounded-xl ml-2" @click="openSettings">
                    <Settings class="w-4 h-4" />
                    {{ t('isp.network.ipam.settings.title') }}
                </Button>
                <DialogContent class="sm:max-w-[500px] rounded-2xl border-none shadow-2xl">
                    <DialogHeader>
                        <DialogTitle>{{ t('isp.network.subnets_manager.new') }}</DialogTitle>
                        <DialogDescription>
                            {{ t('isp.network.subnets_manager.desc') }}
                        </DialogDescription>
                    </DialogHeader>
                    <form @submit.prevent="saveSubnet" class="space-y-4 py-4">
                        <div class="space-y-2">
                            <Label>{{ t('isp.network.subnets_manager.fields.node') }}</Label>
                            <Select v-model="subnetForm.node_id">
                                <SelectTrigger class="rounded-xl">
                                    <SelectValue :placeholder="t('common.select_node')" />
                                </SelectTrigger>
                                <SelectContent class="rounded-xl">
                                    <SelectItem v-for="node in nodes" :key="node.id" :value="node.id.toString()">
                                        {{ node.name }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                        <div class="space-y-2">
                            <Label>{{ t('common.labels.name') }}</Label>
                            <Input v-model="subnetForm.name" required class="rounded-xl" />
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <Label>{{ t('isp.network.subnets.fields.prefix') }}</Label>
                                <Input v-model="subnetForm.prefix" placeholder="10.0.0.0/24" required class="rounded-xl" />
                            </div>
                            <div class="space-y-2">
                                <Label>{{ t('isp.network.subnets_manager.fields.type') }}</Label>
                                <Select v-model="subnetForm.type">
                                    <SelectTrigger class="rounded-xl">
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent class="rounded-xl">
                                        <SelectItem value="LAN">LAN</SelectItem>
                                        <SelectItem value="WAN">WAN</SelectItem>
                                        <SelectItem value="CGNAT">CGNAT</SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <Label>{{ t('isp.network.subnets.fields.gateway') }}</Label>
                                <Input v-model="subnetForm.gateway" placeholder="10.0.0.1" class="rounded-xl" />
                            </div>
                            <div class="space-y-2">
                                <Label>{{ t('isp.network.subnets.fields.vlan') }}</Label>
                                <Input type="number" v-model="subnetForm.vlan_id" class="rounded-xl" />
                            </div>
                        </div>
                        <DialogFooter>
                            <Button type="submit" :disabled="isSavingSubnet" class="w-full rounded-xl py-6 text-base font-semibold">
                                <Loader2 v-if="isSavingSubnet" class="w-4 h-4 animate-spin mr-2" />
                                {{ t('isp.network.subnets_manager.new_pool') }}
                            </Button>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
            <!-- Sidebar: Subnets -->
            <div class="lg:col-span-1 space-y-4">
                <div class="flex items-center justify-between px-2">
                    <h3 class="text-[10px] font-bold text-muted-foreground">{{ t('isp.network.subnets_manager.pool') }}</h3>
                    <Badge variant="secondary" class="rounded-full px-2 py-0 h-5 text-[10px]">{{ subnets.length }}</Badge>
                </div>
                <div class="space-y-2 max-h-[calc(100vh-280px)] overflow-y-auto pr-2 custom-scrollbar">
                    <button 
                        v-for="subnet in subnets" 
                        :key="subnet.id"
                        class="w-full text-left p-4 rounded-xl border border-border/40 transition-all duration-200 group shadow-sm"
                        :class="selectedSubnet?.id === subnet.id 
                            ? 'bg-primary/10 border-primary ring-1 ring-primary/20 shadow-sm' 
                            : 'bg-card hover:border-primary/40 hover:bg-muted/30'"
                        @click="selectSubnet(subnet)"
                    >
                        <div class="font-bold text-sm tracking-tight group-hover:text-primary transition-colors">{{ subnet.prefix }}</div>
                        <div class="text-xs text-muted-foreground mt-1 truncate">{{ subnet.name }}</div>
                        <div class="flex items-center gap-2 mt-4">
                            <Badge variant="outline" class="text-[10px] py-0 h-4 border-muted-foreground/20 font-medium rounded-xl bg-background/50">
                                {{ subnet.node?.name || 'Unassigned' }}
                            </Badge>
                            <Badge variant="outline" class="text-[10px] py-0 h-4 border-muted-foreground/20 font-medium rounded-xl bg-background/50">
                                VLAN {{ subnet.vlan_id || 'N/A' }}
                            </Badge>
                        </div>
                    </button>
                </div>
            </div>

            <!-- Main: IP Pool -->
            <div class="lg:col-span-3">
                <div v-if="!selectedSubnet" class="h-96 flex flex-col items-center justify-center bg-card rounded-xl border border-dashed border-border/40 text-muted-foreground/60 transition-colors hover:border-primary/20 shadow-sm">
                    <div class="p-6 rounded-full bg-muted/30 mb-4 ring-8 ring-muted/10">
                        <Network class="w-12 h-12 opacity-40" />
                    </div>
                    <p class="font-medium tracking-tight">{{ t('isp.network.subnets_manager.select_subnet') }}</p>
                    <p class="text-xs mt-1">Select a subnet from the pool to manage addressing</p>
                </div>

                <div v-else class="space-y-4 animate-in slide-in-from-right-2 duration-300">
                    <Card class="rounded-xl border border-border/40 shadow-sm overflow-hidden bg-card">
                        <div class="p-4 border-b border-border/40 flex flex-col md:flex-row md:items-center justify-between gap-4">
                            <div class="flex items-center gap-4">
                                <div class="p-2 rounded-xl bg-primary/10">
                                    <Network class="w-5 h-5 text-primary" />
                                </div>
                                <div>
                                    <h2 class="text-lg font-bold tracking-tight">{{ selectedSubnet.prefix }} <span class="text-muted-foreground font-normal text-sm ml-2">Pool</span></h2>
                                    <div class="flex items-center gap-2 text-xs text-muted-foreground mt-0.5">
                                        <span class="font-mono text-[10px] bg-muted px-1.5 py-0.5 rounded">{{ selectedSubnet.gateway }}</span>
                                        <span>•</span>
                                        <span>{{ pagination?.total || 0 }} {{ t('isp.network.subnets_manager.generated') }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="flex items-center gap-2 w-full md:w-auto">
                                <div class="relative flex-1 md:flex-none">
                                    <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-muted-foreground/60" />
                                    <Input 
                                        v-model="ipSearch" 
                                        :placeholder="t('isp.network.ipam.search')" 
                                        class="h-9 pl-9 w-full md:w-[240px] rounded-xl border-border/60 bg-muted/20 focus-visible:ring-primary/20"
                                    />
                                    <div v-if="loadingIps" class="absolute right-3 top-1/2 -translate-y-1/2">
                                        <Loader2 class="w-4 h-4 animate-spin text-muted-foreground" />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Standardized Bulk Action Bar -->
                        <Transition
                            enter-active-class="transition-[opacity,transform] duration-200 ease-out"
                            enter-from-class="opacity-0 -translate-y-2"
                            enter-to-class="opacity-100 translate-y-0"
                            leave-active-class="transition-[opacity,transform] duration-150 ease-in"
                            leave-from-class="opacity-100 translate-y-0"
                            leave-to-class="opacity-0 -translate-y-2"
                        >
                            <div v-if="selectedRowsCount > 0" class="flex items-center gap-4 py-2 px-4 bg-primary/5 border-b border-primary/20 animate-in slide-in-from-top-2">
                                <span class="text-xs font-bold text-primary">
                                    {{ selectedRowsCount }} {{ t('isp.network.ipam.title') }} Selected
                                </span>
                                <div class="h-4 w-px bg-primary/20 mx-2"></div>
                                <div class="flex items-center gap-2">
                                    <Select v-model="bulkActionSelection" @update:model-value="handleBulkAction">
                                        <SelectTrigger class="w-[160px] h-8 text-[11px] rounded-lg bg-background border-primary/20 font-semibold shadow-none">
                                            <SelectValue :placeholder="t('features.content.list.bulkActions')" />
                                        </SelectTrigger>
                                        <SelectContent class="rounded-xl border-primary/10">
                                            <SelectItem value="Available" class="text-xs py-2">Set Available</SelectItem>
                                            <SelectItem value="Reserved" class="text-xs py-2 text-warning">Set Reserved</SelectItem>
                                            <SelectItem value="Assigned" class="text-xs py-2 text-success">Set Assigned</SelectItem>
                                        </SelectContent>
                                    </Select>
                                    <Button variant="ghost" size="sm" class="h-8 rounded-lg text-xs hover:bg-primary/10" @click="table.resetRowSelection()">
                                        {{ t('common.actions.cancel') }}
                                    </Button>
                                </div>
                            </div>
                        </Transition>

                        <DataTable 
                            :table="table" 
                            :loading="loadingIps" 
                            :empty-message="t('common.messages.no_data')"
                            class="min-h-[400px]"
                        />

                        <div class="p-4 border-t border-border/40" v-if="pagination">
                            <Pagination
                                :current-page="pagination.current_page"
                                :total-items="pagination.total"
                                v-model:per-page="perPage"
                                @page-change="handlePageChange"
                            />
                        </div>
                    </Card>
                </div>
            </div>
        </div>

        <!-- IP Management Modal -->
        <Dialog v-model:open="isIpModalOpen">
            <DialogContent class="sm:max-w-[420px] rounded-2xl border-none shadow-2xl">
                <DialogHeader>
                    <DialogTitle class="text-xl font-bold tracking-tight">{{ t('isp.network.ip_manager.edit') }}</DialogTitle>
                    <DialogDescription class="flex items-center gap-2 mt-1">
                        <span class="font-mono text-xs bg-muted px-2 py-1 rounded-md">{{ selectedIp?.address }}</span>
                    </DialogDescription>
                </DialogHeader>
                <form @submit.prevent="handleSaveIp" class="space-y-5 py-4">
                    <div class="space-y-2">
                        <Label class="text-xs font-bold text-muted-foreground">{{ t('isp.network.ip_manager.fields.status') }}</Label>
                        <div class="grid grid-cols-3 gap-2">
                            <div 
                                v-for="status in ['Available', 'Reserved', 'Assigned']" 
                                :key="status"
                                class="cursor-pointer border-2 rounded-xl p-3 text-center transition-all duration-200"
                                :class="ipForm.status === status ? 
                                    (status === 'Available' ? 'bg-primary/10 border-primary text-primary' : 
                                    (status === 'Reserved' ? 'bg-warning/10 border-warning text-warning' : 'bg-success/10 border-success text-success')) : 
                                    'border-border/60 hover:border-border text-muted-foreground'"
                                @click="ipForm.status = status"
                            >
                                <p class="text-[11px] font-bold">{{ status }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="space-y-2">
                        <Label class="text-xs font-bold text-muted-foreground">{{ t('isp.network.ip_manager.fields.notes') }}</Label>
                        <Textarea v-model="ipForm.notes" rows="3" class="rounded-xl border-border/60 focus:ring-primary/20" :placeholder="t('isp.network.ip_manager.fields.notes_placeholder')" />
                    </div>
                    <DialogFooter class="pt-2">
                        <Button type="submit" :disabled="isSavingIp" class="w-full rounded-xl py-6 font-bold">
                            <Loader2 v-if="isSavingIp" class="w-4 h-4 animate-spin mr-2" />
                            {{ t('common.actions.save') }}
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>

        <!-- View IP Details Modal -->
        <Dialog v-model:open="isViewModalOpen">
            <DialogContent class="sm:max-w-[420px] rounded-2xl border-none shadow-2xl">
                <DialogHeader>
                    <div class="flex justify-between items-start pr-4">
                        <div class="space-y-1">
                            <DialogTitle class="text-2xl font-black tracking-tight font-mono">{{ selectedIp?.address }}</DialogTitle>
                            <DialogDescription class="flex items-center gap-2">
                                <Badge variant="outline" class="rounded-xl font-mono text-[10px]">{{ selectedSubnet?.prefix }}</Badge>
                                <span class="text-[10px] text-muted-foreground">•</span>
                                <span class="text-[10px] text-muted-foreground font-medium">VLAN {{ selectedSubnet?.vlan_id }}</span>
                            </DialogDescription>
                        </div>
                        <Badge :variant="selectedIp ? (selectedIp.status === 'Assigned' ? 'success' : (selectedIp.status === 'Reserved' ? 'warning' : 'outline')) : 'default'" class="rounded-full px-3">
                            {{ selectedIp?.status }}
                        </Badge>
                    </div>
                </DialogHeader>
                <div class="space-y-6 py-6">
                    <div v-if="selectedIp?.device" class="relative group p-6 bg-primary/5 rounded-3xl border border-primary/10 overflow-hidden">
                        <div class="absolute -right-4 -top-4 w-24 h-24 bg-primary/5 rounded-full blur-2xl transition-all group-hover:blur-3xl"></div>
                        <div class="relative flex items-start gap-4">
                            <div class="p-3 bg-primary/20 rounded-xl ring-4 ring-primary/5 shadow-inner">
                                <Globe class="w-6 h-6 text-primary" />
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-[10px] text-primary/70 font-black">Assigned Device</p>
                                <p class="text-lg font-bold mt-0.5 tracking-tight truncate">{{ selectedIp.device.serial_number }}</p>
                                <div class="mt-4 flex items-center justify-between bg-background/50 p-3 rounded-xl border border-primary/10">
                                    <span class="text-xs text-muted-foreground">{{ t('isp.network.ipam.customer') }}:</span>
                                    <span class="text-xs font-bold truncate max-w-[140px]">{{ selectedIp.device.customer?.name }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div v-if="selectedIp?.notes" class="p-6 bg-muted/30 rounded-xl border border-border/40">
                        <div class="flex items-center gap-2 mb-2 text-muted-foreground">
                            <div class="w-1.5 h-1.5 rounded-full bg-border"></div>
                            <span class="text-[10px] font-black">{{ t('isp.network.ip_manager.fields.notes') }}</span>
                        </div>
                        <p class="text-sm leading-relaxed text-foreground/80 font-medium">{{ selectedIp.notes }}</p>
                    </div>

                    <div v-if="!selectedIp?.device && !selectedIp?.notes" class="flex flex-col items-center justify-center p-12 text-center text-muted-foreground/60 italic text-xs">
                        <div class="w-1 h-12 bg-muted/40 rounded-full mb-4"></div>
                        No additional metadata for this address
                    </div>
                </div>
                <DialogFooter>
                    <Button variant="outline" @click="isViewModalOpen = false" class="w-full rounded-2xl py-6 font-bold border-border/60 hover:bg-muted/50 transition-colors">
                        {{ t('common.actions.close') }}
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>

        <!-- Settings Dialog -->
        <Dialog v-model:open="isSettingsDialogOpen">
            <DialogContent class="sm:max-w-[500px] rounded-2xl border-none shadow-2xl">
                <DialogHeader>
                    <DialogTitle>{{ t('isp.network.ipam.settings.title') }}</DialogTitle>
                    <DialogDescription>
                        {{ t('isp.network.ipam.settings.desc') }}
                    </DialogDescription>
                </DialogHeader>
                <form @submit.prevent="saveSettings" class="space-y-4 py-4">
                    <div class="space-y-2">
                        <Label>{{ t('isp.network.ipam.settings.cidr') }}</Label>
                        <Input v-model="settingsForm.isp_local_network_cidr" placeholder="10.0.0.0/8" required class="rounded-xl" />
                        <p class="text-[10px] text-muted-foreground">{{ t('isp.network.ipam.settings.cidr_help') }}</p>
                    </div>
                    <div class="space-y-2">
                        <Label>{{ t('isp.network.ipam.settings.subnet_size') }}</Label>
                        <Select v-model="settingsForm.isp_subnet_size">
                            <SelectTrigger class="rounded-xl">
                                <SelectValue />
                            </SelectTrigger>
                            <SelectContent class="rounded-xl">
                                <SelectItem value="30">/30 (4 IPs - 2 Usable)</SelectItem>
                                <SelectItem value="29">/29 (8 IPs - 6 Usable)</SelectItem>
                                <SelectItem value="28">/28 (16 IPs - 14 Usable)</SelectItem>
                                <SelectItem value="24">/24 (256 IPs - 254 Usable)</SelectItem>
                            </SelectContent>
                        </Select>
                        <p class="text-[10px] text-muted-foreground">{{ t('isp.network.ipam.settings.subnet_help') }}</p>
                    </div>
                    <DialogFooter>
                        <Button type="submit" :disabled="isSavingSettings" class="w-full rounded-xl py-6 text-base font-semibold">
                            <Loader2 v-if="isSavingSettings" class="w-4 h-4 animate-spin mr-2" />
                            {{ t('common.actions.save_changes') }}
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, watch, h } from 'vue';
import { useI18n } from 'vue-i18n';
import api from '@/services/api';
import { useToast } from '@/composables/useToast';
import { ensureArray, parseResponse, type PaginationData } from '@/utils/responseParser';
import { 
    Button, 
    Input, 
    Label, 
    Badge,
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
    Textarea,
    DataTable,
    Checkbox,
    Card,
    Pagination
} from '@/components/ui';
import { 
    useVueTable, 
    getCoreRowModel, 
    createColumnHelper,
} from '@tanstack/vue-table';
import Network from 'lucide-vue-next/dist/esm/icons/network.js';
import Search from 'lucide-vue-next/dist/esm/icons/search.js';
import Globe from 'lucide-vue-next/dist/esm/icons/globe.js';
import Eye from 'lucide-vue-next/dist/esm/icons/eye.js';
import Loader2 from 'lucide-vue-next/dist/esm/icons/loader-circle.js';
import Settings from 'lucide-vue-next/dist/esm/icons/settings.js';
import type { NetworkSubnet, IpAddress } from '@/types/isp';

const { t } = useI18n();
const toast = useToast();

// --- State ---
const subnets = ref<NetworkSubnet[]>([]);
const nodes = ref<{id: number, name: string}[]>([]);
const ips = ref<IpAddress[]>([]);
const selectedSubnet = ref<NetworkSubnet | null>(null);
const loading = ref(true);
const loadingIps = ref(false);
const isSubnetDialogOpen = ref(false);
const isSavingSubnet = ref(false);
const isIpModalOpen = ref(false);
const isViewModalOpen = ref(false);
const isSavingIp = ref(false);
const selectedIp = ref<IpAddress | null>(null);
const ipSearch = ref('');
const rowSelection = ref({});
const bulkActionSelection = ref('');
const perPage = ref(20);
const pagination = ref<PaginationData | null>(null);

const isSettingsDialogOpen = ref(false);
const isSavingSettings = ref(false);
const settingsForm = ref({
    isp_local_network_cidr: '',
    isp_subnet_size: ''
});

const ipForm = ref({
    status: '',
    notes: ''
});

const subnetForm = ref({
    node_id: '',
    name: '',
    prefix: '',
    type: 'LAN',
    gateway: '',
    vlan_id: ''
});

// --- Computed ---
const selectedRowsCount = computed(() => Object.keys(rowSelection.value).length);

// --- TanStack Table Setup ---
const columnHelper = createColumnHelper<IpAddress>();

const columns = [
    columnHelper.display({
        id: 'select',
        header: ({ table }) => h(Checkbox, {
            checked: table.getIsAllPageRowsSelected(),
            'onUpdate:checked': (value) => table.toggleAllPageRowsSelected(!!value),
            ariaLabel: 'Select all',
            onClick: (e: MouseEvent) => e.stopPropagation(),
            class: 'rounded-md border-border/60 data-[state=checked]:bg-primary data-[state=checked]:border-primary shadow-sm'
        }),
        cell: ({ row }) => h(Checkbox, {
            checked: row.getIsSelected(),
            'onUpdate:checked': (value) => row.toggleSelected(!!value),
            ariaLabel: 'Select row',
            onClick: (e: MouseEvent) => e.stopPropagation(),
            class: 'rounded-md border-border/60 data-[state=checked]:bg-primary data-[state=checked]:border-primary shadow-sm'
        }),
        size: 50,
    }),
    columnHelper.accessor('address', {
        header: t('isp.network.ip_manager.fields.address'),
        cell: info => h('span', { class: 'font-mono text-sm font-bold tracking-tight' }, info.getValue()),
    }),
    columnHelper.accessor('status', {
        header: t('isp.network.ip_manager.fields.status'),
        cell: info => {
            const status = info.getValue();
            return h(Badge, { 
                variant: status === 'Assigned' ? 'success' : (status === 'Reserved' ? 'warning' : 'outline'),
                class: 'px-2 py-0 h-5 font-bold text-[10px] rounded-full'
            }, () => status);
        }
    }),
    columnHelper.display({
        id: 'assignment',
        header: t('isp.network.ip_manager.fields.assignment'),
        cell: info => {
            const ip = info.row.original;
            if (ip.device) {
                return h('div', { class: 'flex items-center gap-3' }, [
                    h('div', { class: 'w-1 h-8 rounded-full bg-primary/20' }),
                    h('div', { class: 'flex flex-col' }, [
                        h('span', { class: 'font-bold text-xs tracking-tight' }, ip.device.serial_number),
                        h('span', { class: 'text-[10px] text-muted-foreground font-medium' }, ip.device.customer?.name)
                    ])
                ]);
            } else if (ip.notes) {
                return h('div', { class: 'flex items-center gap-2 text-xs text-muted-foreground italic' }, [
                    h('span', { class: 'opacity-50' }, '•'),
                    h('span', { class: 'line-clamp-1' }, ip.notes)
                ]);
            }
            return h('span', { class: 'text-[10px] text-muted-foreground font-bold tracking-[0.1em] opacity-30' }, t('isp.network.ipam.available'));
        }
    }),
    columnHelper.display({
        id: 'actions',
        header: () => h('div', { class: 'text-right' }, t('common.actions.title')),
        cell: info => {
            const ip = info.row.original;
            return h('div', { class: 'flex justify-end gap-1' }, [
                h(Button, {
                    variant: 'ghost',
                    size: 'icon',
                    class: 'h-8 w-8 rounded-xl bg-muted/30 hover:bg-primary/10 hover:text-primary transition-all duration-200',
                    onClick: (e: MouseEvent) => { e.stopPropagation(); viewIp(ip); }
                }, () => h(Eye, { class: 'w-4 h-4' })),
                h(Button, {
                    variant: 'ghost',
                    size: 'sm',
                    class: 'h-8 px-3 rounded-xl bg-muted/30 hover:bg-primary/10 hover:text-primary font-bold text-xs transition-all duration-200',
                    onClick: (e: MouseEvent) => { e.stopPropagation(); openEditIpModal(ip); }
                }, () => t('common.actions.edit'))
            ]);
        }
    })
];

const table = useVueTable({
    get data() { return ips.value },
    columns,
    state: {
        get rowSelection() { return rowSelection.value },
    },
    onRowSelectionChange: (updaterOrValue) => {
        rowSelection.value = typeof updaterOrValue === 'function' ? updaterOrValue(rowSelection.value) : updaterOrValue;
    },
    getCoreRowModel: getCoreRowModel(),
});

// --- Actions ---
const handleBulkAction = async (action: string) => {
    if (!action || !selectedSubnet.value) return;
    const selectedRows = table.getSelectedRowModel().rows;
    const ids = selectedRows.map(r => r.original.id);
    
    try {
        await Promise.all(ids.map(id => 
            api.patch(`/admin/janet/isp/network/subnets/${selectedSubnet.value!.id}/ips/${id}`, { status: action })
        ));
        toast.success.action(t('isp.network.messages.success_ip'));
        table.resetRowSelection();
        fetchIps(selectedSubnet.value.id, pagination.value?.current_page || 1);
    } catch (error) {
        toast.error.action(error as Record<string, unknown>);
    }
    bulkActionSelection.value = '';
};

const fetchSubnets = async () => {
    loading.value = true;
    try {
        const response = await api.get('/admin/janet/isp/network/subnets');
        if (response.data.success) {
            subnets.value = ensureArray<NetworkSubnet>(response.data.data);
            if (subnets.value.length > 0 && !selectedSubnet.value) {
                selectSubnet(subnets.value[0]);
            }
        }
    } catch (_error) {
        toast.error.default('Failed to load subnets');
    } finally {
        loading.value = false;
    }
};

const fetchNodes = async () => {
    try {
        const response = await api.get('/admin/janet/isp/infra');
        if (response.data.success) {
            nodes.value = response.data.data;
        }
    } catch (_error) {
        console.error('Failed to fetch nodes');
    }
};

const fetchIps = async (subnetId: number, page = 1) => {
    loadingIps.value = true;
    try {
        const params: Record<string, string | number> = { 
            page, 
            per_page: perPage.value 
        };
        if (ipSearch.value) params.search = ipSearch.value;

        const response = await api.get(`/admin/janet/isp/network/subnets/${subnetId}/ips`, { params });
        const parsed = parseResponse(response);
        ips.value = parsed.data as IpAddress[];
        pagination.value = parsed.pagination;
    } catch (_error) {
        toast.error.default(t('isp.network.messages.error_ips'));
    } finally {
        loadingIps.value = false;
    }
};

const selectSubnet = (subnet: NetworkSubnet) => {
    selectedSubnet.value = subnet;
    ipSearch.value = '';
    table.resetRowSelection();
    fetchIps(subnet.id, 1);
};

const viewIp = (ip: IpAddress) => {
    selectedIp.value = ip;
    isViewModalOpen.value = true;
};

const saveSubnet = async () => {
    isSavingSubnet.value = true;
    try {
        const response = await api.post('/admin/janet/isp/network/subnets', subnetForm.value);
        if (response.data.success) {
            toast.success.default(t('isp.network.messages.success_subnet'));
            isSubnetDialogOpen.value = false;
            fetchSubnets();
        }
    } catch (_error) {
        toast.error.default(t('isp.network.messages.error_subnet'));
    } finally {
        isSavingSubnet.value = false;
    }
};

const openEditIpModal = (ip: IpAddress) => {
    selectedIp.value = ip;
    ipForm.value = {
        status: ip.status,
        notes: ip.notes || ''
    };
    isIpModalOpen.value = true;
};

const handleSaveIp = async () => {
    if (!selectedIp.value || !selectedSubnet.value) return;
    isSavingIp.value = true;
    try {
        await api.patch(`/admin/janet/isp/network/subnets/${selectedSubnet.value.id}/ips/${selectedIp.value.id}`, ipForm.value);
        toast.success.action(t('isp.network.messages.success_ip'));
        isIpModalOpen.value = false;
        fetchIps(selectedSubnet.value.id, pagination.value?.current_page || 1);
    } catch (error) {
        toast.error.action(error as Record<string, unknown>);
    } finally {
        isSavingIp.value = false;
    }
};

const handlePageChange = (page: number) => {
    if (selectedSubnet.value) {
        fetchIps(selectedSubnet.value.id, page);
    }
};

// --- Settings ---
const openSettings = async () => {
    isSettingsDialogOpen.value = true;
    try {
        const response = await api.get('/admin/janet/settings/group/isp');
        if (response.data) {
            const settings = response.data;
            // Map settings to form using key/value
            if (Array.isArray(settings)) {
                settings.forEach((s: { key: string, value: string }) => {
                    if (s.key === 'isp_local_network_cidr') settingsForm.value.isp_local_network_cidr = s.value;
                    if (s.key === 'isp_subnet_size') settingsForm.value.isp_subnet_size = s.value;
                });
            }
        }
    } catch (_error) {
        toast.error.default('Failed to load settings');
    }
};

const saveSettings = async () => {
    isSavingSettings.value = true;
    try {
        await api.post('/admin/janet/settings/bulk-update', {
            settings: [
                { key: 'isp_local_network_cidr', value: settingsForm.value.isp_local_network_cidr },
                { key: 'isp_subnet_size', value: settingsForm.value.isp_subnet_size }
            ]
        });
        toast.success.default(t('common.messages.settings_saved'));
        isSettingsDialogOpen.value = false;
    } catch (_error) {
        toast.error.default(t('common.errors.save_failed'));
    } finally {
        isSavingSettings.value = false;
    }
};

// --- Watchers ---
watch([ipSearch, perPage], () => {
    if (selectedSubnet.value) {
        // Reset to first page on search or per-page change
        fetchIps(selectedSubnet.value.id, 1);
    }
});

onMounted(() => {
    fetchSubnets();
    fetchNodes();
});
</script>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
    width: 4px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: rgba(var(--primary-rgb), 0.1);
    border-radius: 20px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: rgba(var(--primary-rgb), 0.2);
}
</style>
