<template>
    <div class="space-y-6 animate-in fade-in duration-500">
        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold tracking-tight">{{ t('isp.support.admin.title') }}</h1>
                <p class="text-muted-foreground">{{ t('isp.support.admin.subtitle') }}</p>
            </div>
            <div class="flex items-center gap-2">
                <Button variant="outline" class="gap-2 rounded-xl" @click="fetchTickets()">
                    <RefreshCw class="w-4 h-4" :class="{ 'animate-spin': loading }" />
                    {{ t('common.actions.refresh') }}
                </Button>
            </div>
        </div>

        <!-- Stats Overview -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <Card v-for="stat in stats" :key="stat.label" class="overflow-hidden border-border/50 shadow-sm rounded-2xl">
                <CardContent class="p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-muted-foreground">{{ stat.label }}</p>
                            <h3 class="text-2xl font-bold mt-1">{{ stat.value }}</h3>
                        </div>
                        <div class="p-3 rounded-xl" :class="stat.bgClass">
                            <component :is="stat.icon" class="w-5 h-5" :class="stat.iconClass" />
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>

        <!-- Filters & Table -->
        <Card class="rounded-2xl border border-border shadow-sm overflow-hidden text-sm">
            <div class="p-4 border-b border-border/40 flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="flex items-center gap-2 max-w-sm w-full">
                    <Search class="w-4 h-4 text-muted-foreground" />
                    <Input 
                        v-model="search" 
                        :placeholder="t('isp.support.admin.search_placeholder')" 
                        class="h-9 border-none focus-visible:ring-0"
                    />
                </div>
                <div class="flex items-center gap-2">
                    <Select v-model="filterStatus">
                        <SelectTrigger class="w-[150px] h-9 rounded-xl">
                            <SelectValue placeholder="Status" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">{{ t('common.status.all') }}</SelectItem>
                            <SelectItem value="Open">{{ t('isp.support.statuses.Open') }}</SelectItem>
                            <SelectItem value="In Progress">{{ t('isp.support.statuses.In Progress') }}</SelectItem>
                            <SelectItem value="Resolved">{{ t('isp.support.statuses.Resolved') }}</SelectItem>
                            <SelectItem value="Closed">{{ t('isp.support.statuses.Closed') }}</SelectItem>
                        </SelectContent>
                    </Select>
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
                    <span class="text-sm font-medium">
                        {{ selectedRowsCount }} {{ t('isp.support.admin.title') }} Selected
                    </span>
                    <div class="h-4 w-px bg-border mx-2"></div>
                    <div class="flex items-center gap-2">
                        <Select v-model="bulkActionSelection" @update:model-value="handleBulkAction">
                            <SelectTrigger class="w-[160px] h-8 text-xs rounded-lg">
                                <SelectValue :placeholder="t('features.content.list.bulkActions')" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="Resolved" class="text-success focus:text-success">Mark Resolved</SelectItem>
                                <SelectItem value="In Progress" class="text-blue-500 focus:text-blue-500">Set In Progress</SelectItem>
                                <SelectItem value="Closed" class="text-muted-foreground focus:text-muted-foreground">Close Tickets</SelectItem>
                            </SelectContent>
                        </Select>
                        <Button variant="ghost" size="sm" class="h-8" @click="table.resetRowSelection()">
                            {{ t('common.actions.cancel') }}
                        </Button>
                    </div>
                </div>
            </Transition>

            <DataTable 
                :table="table" 
                :loading="loading" 
                :empty-message="t('common.messages.no_data')"
            />
            
            <div class="p-4 border-t border-border/40" v-if="pagination">
                <Pagination
                    :current-page="pagination.current_page"
                    :total-items="pagination.total"
                    :per-page="pagination.per_page"
                    @page-change="handlePageChange"
                />
            </div>
        </Card>

        <!-- Ticket Detail Modal -->
        <Dialog v-model:open="isDetailOpen">
            <DialogContent class="sm:max-w-[600px] rounded-2xl">
                <DialogHeader>
                    <div class="flex justify-between items-start pr-8">
                        <div>
                            <DialogTitle class="text-xl">#{{ selectedTicket?.id }} - {{ selectedTicket?.subject }}</DialogTitle>
                            <DialogDescription class="mt-1">
                                {{ selectedTicket?.user?.name }} ({{ selectedTicket?.user?.email }})
                            </DialogDescription>
                        </div>
                        <Badge :variant="getStatusVariant(selectedTicket?.status || '')">
                            {{ selectedTicket?.status }}
                        </Badge>
                    </div>
                </DialogHeader>
                
                <div class="space-y-6 py-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="p-3 bg-muted/40 rounded-xl">
                            <p class="text-[10px] text-muted-foreground font-bold opacity-60">{{ t('isp.support.fields.category') }}</p>
                            <p class="text-sm font-medium">{{ selectedTicket?.category }}</p>
                        </div>
                        <div class="p-3 bg-muted/40 rounded-xl">
                            <p class="text-[10px] text-muted-foreground font-bold opacity-60">{{ t('isp.support.fields.priority') }}</p>
                            <p class="text-sm font-medium">{{ selectedTicket?.priority }}</p>
                        </div>
                    </div>

                    <div class="space-y-2">
                        <Label class="text-xs font-bold text-muted-foreground tracking-tight">{{ t('isp.support.fields.message') }}</Label>
                        <div class="p-4 bg-muted/20 rounded-2xl border border-border/50 text-sm whitespace-pre-wrap leading-relaxed">
                            {{ selectedTicket?.message }}
                        </div>
                    </div>

                    <div class="flex items-center justify-between text-[10px] text-muted-foreground font-bold opacity-60 px-1">
                        <span>{{ t('isp.support.headers.created') }}: {{ selectedTicket ? formatDate(selectedTicket.created_at) : '' }}</span>
                    </div>
                </div>

                <DialogFooter class="sm:justify-between gap-4">
                    <div class="flex gap-2">
                        <Select :model-value="selectedTicket?.status" @update:model-value="(val) => updateTicketStatus(selectedTicket?.id || 0, val)">
                            <SelectTrigger class="w-[140px] h-9 rounded-xl">
                                <SelectValue />
                            </SelectTrigger>
                            <SelectContent class="rounded-xl">
                                <SelectItem value="Open">{{ t('isp.support.statuses.Open') }}</SelectItem>
                                <SelectItem value="In Progress">{{ t('isp.support.statuses.In Progress') }}</SelectItem>
                                <SelectItem value="Resolved">{{ t('isp.support.statuses.Resolved') }}</SelectItem>
                                <SelectItem value="Closed">{{ t('isp.support.statuses.Closed') }}</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                    <Button variant="outline" @click="isDetailOpen = false" class="rounded-xl">{{ t('common.actions.close') }}</Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, watch, h } from 'vue';
import { useI18n } from 'vue-i18n';
import api from '@/services/api';
import { useToast } from '@/composables/useToast';
import { parseResponse, type PaginationData } from '@/utils/responseParser';
import { 
    Button, 
    Input, 
    Badge,
    Card,
    CardContent,
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
    DialogDescription,
    DialogFooter,
    Label,
    DataTable,
    Checkbox
} from '@/components/ui';
import { 
    useVueTable, 
    getCoreRowModel, 
    createColumnHelper
} from '@tanstack/vue-table';
import Search from 'lucide-vue-next/dist/esm/icons/search.js';
import RefreshCw from 'lucide-vue-next/dist/esm/icons/refresh-cw.js';
import TicketIcon from 'lucide-vue-next/dist/esm/icons/ticket.js';
import AlertCircle from 'lucide-vue-next/dist/esm/icons/circle-alert.js';
import CheckCircle2 from 'lucide-vue-next/dist/esm/icons/circle-check.js';
import Clock from 'lucide-vue-next/dist/esm/icons/clock.js';
import Eye from 'lucide-vue-next/dist/esm/icons/eye.js';
import dayjs from 'dayjs';
import type { IspSupportTicket } from '@/types/isp';

const { t } = useI18n();
const toast = useToast();

const tickets = ref<IspSupportTicket[]>([]);
const loading = ref(true);
const search = ref('');
const filterStatus = ref('all');
const pagination = ref<PaginationData | null>(null);
const isDetailOpen = ref(false);
const selectedTicket = ref<IspSupportTicket | null>(null);
const rowSelection = ref({});
const bulkActionSelection = ref('');

// --- TanStack Table Setup ---
const columnHelper = createColumnHelper<IspSupportTicket>();

const columns = [
    columnHelper.display({
        id: 'select',
        header: ({ table }) => h(Checkbox, {
            checked: table.getIsAllPageRowsSelected(),
            'onUpdate:checked': (value) => table.toggleAllPageRowsSelected(!!value),
            ariaLabel: 'Select all',
            onClick: (e: MouseEvent) => e.stopPropagation(),
        }),
        cell: ({ row }) => h(Checkbox, {
            checked: row.getIsSelected(),
            'onUpdate:checked': (value) => row.toggleSelected(!!value),
            ariaLabel: 'Select row',
            onClick: (e: MouseEvent) => e.stopPropagation(),
        }),
        size: 50,
    }),
    columnHelper.accessor('id', {
        header: 'ID',
        cell: info => h('span', { class: 'font-mono text-muted-foreground' }, `#${info.getValue()}`),
    }),
    columnHelper.display({
        id: 'user',
        header: t('isp.support.headers.user'),
        cell: info => {
            const ticket = info.row.original;
            return h('div', { class: 'flex flex-col' }, [
                h('span', { class: 'font-medium' }, ticket.user?.name || 'Unknown'),
                h('span', { class: 'text-xs text-muted-foreground' }, ticket.user?.email || '')
            ]);
        }
    }),
    columnHelper.accessor('subject', {
        header: t('isp.support.headers.subject'),
        cell: info => {
            const ticket = info.row.original;
            return h('div', { class: 'flex flex-col' }, [
                h('span', { class: 'font-medium line-clamp-1' }, info.getValue()),
                h('span', { class: 'text-xs text-muted-foreground italic' }, `${ticket.category} • ${ticket.priority}`)
            ]);
        }
    }),
    columnHelper.accessor('status', {
        header: t('isp.support.headers.status'),
        cell: info => h(Badge, { variant: getStatusVariant(info.getValue()) }, () => info.getValue()),
    }),
    columnHelper.accessor('created_at', {
        header: t('isp.support.headers.created'),
        cell: info => formatDate(info.getValue()),
    }),
    columnHelper.display({
        id: 'actions',
        header: () => h('div', { class: 'text-right' }, t('common.actions.title')),
        cell: info => {
            const ticket = info.row.original;
            return h('div', { class: 'flex justify-end gap-1' }, [
                h(Button, {
                    variant: 'ghost',
                    size: 'icon',
                    onClick: () => viewTicket(ticket)
                }, () => h(Eye, { class: 'w-4 h-4' })),
            ]);
        }
    })
];

const table = useVueTable({
    get data() { return tickets.value },
    columns,
    state: {
        get rowSelection() { return rowSelection.value },
    },
    onRowSelectionChange: (updaterOrValue) => {
        rowSelection.value = typeof updaterOrValue === 'function' ? updaterOrValue(rowSelection.value) : updaterOrValue;
    },
    getCoreRowModel: getCoreRowModel(),
});

const selectedRowsCount = computed(() => Object.keys(rowSelection.value).length);

const stats = computed(() => {
    const list = tickets.value || [];
    return [
        { label: t('isp.support.admin.title'), value: list.length, icon: TicketIcon, bgClass: 'bg-primary/10', iconClass: 'text-primary' },
        { label: t('isp.support.statuses.Open'), value: list.filter(t => t.status === 'Open').length, icon: AlertCircle, bgClass: 'bg-destructive/10', iconClass: 'text-destructive' },
        { label: t('isp.support.statuses.In Progress'), value: list.filter(t => t.status === 'In Progress').length, icon: Clock, bgClass: 'bg-blue-500/10', iconClass: 'text-blue-500' },
        { label: t('isp.support.statuses.Resolved'), value: list.filter(t => t.status === 'Resolved' || t.status === 'Closed').length, icon: CheckCircle2, bgClass: 'bg-success/10', iconClass: 'text-success' }
    ];
});

const getStatusVariant = (status: string) => {
    switch (status) {
        case 'Open': return 'destructive';
        case 'In Progress': return 'secondary';
        case 'Resolved': return 'success';
        case 'Closed': return 'outline';
        default: return 'default';
    }
};

const fetchTickets = async (page = 1) => {
    loading.value = true;
    try {
        const params: Record<string, string | number> = { page, per_page: 10 };
        if (search.value) params.search = search.value;
        if (filterStatus.value !== 'all') params.status = filterStatus.value;

        const response = await api.get('/admin/janet/isp/support/tickets', { params });
        const parsed = parseResponse(response);
        tickets.value = parsed.data as IspSupportTicket[];
        pagination.value = parsed.pagination;
    } catch (_error) {
        toast.error.default(t('isp.support.messages.error_fetch'));
    } finally {
        loading.value = false;
    }
};

const updateTicketStatus = async (id: number, status: string) => {
    try {
        const response = await api.patch(`/admin/janet/isp/support/tickets/${id}/status`, { status });
        if (response.data.success) {
            toast.success.default(t('common.messages.success.action'));
            if (selectedTicket.value?.id === id) selectedTicket.value.status = status;
            fetchTickets(pagination.value?.current_page || 1);
        }
    } catch (_error) {
        toast.error.default(t('common.messages.error_update'));
    }
};

const handleBulkAction = async (action: string) => {
    if (!action) return;
    const selectedRows = table.getSelectedRowModel().rows;
    const ids = selectedRows.map(r => r.original.id);
    
    try {
        await Promise.all(ids.map(id => api.patch(`/admin/janet/isp/support/tickets/${id}/status`, { status: action })));
        toast.success.action(t('common.messages.success.action'));
        table.resetRowSelection();
        fetchTickets();
    } catch (error) {
        toast.error.action(error as Record<string, unknown>);
    }
    bulkActionSelection.value = '';
};

const formatDate = (date: string) => dayjs(date).format('MMM D, HH:mm');
const viewTicket = (ticket: IspSupportTicket) => {
    selectedTicket.value = ticket;
    isDetailOpen.value = true;
};
const handlePageChange = (page: number) => fetchTickets(page);

watch([search, filterStatus], () => fetchTickets(1));

onMounted(() => fetchTickets());
</script>
