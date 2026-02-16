<template>
    <div class="space-y-6">
        <div>
            <h2 class="text-3xl font-bold tracking-tight">OLT Audit Logs</h2>
            <p class="text-muted-foreground">Historical record of all hardware-level SSH commands and responses.</p>
        </div>

        <Card class="border border-border/40 shadow-sm rounded-xl overflow-hidden">
            <div class="p-4 border-b border-border/40 flex flex-col md:flex-row gap-4 justify-between items-center">
                <div class="flex gap-2 w-full md:w-auto">
                    <Select v-model="oltFilter">
                        <SelectTrigger class="w-[200px] rounded-xl">
                            <SelectValue placeholder="All OLTs" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">All OLTs</SelectItem>
                            <SelectItem v-for="olt in olts" :key="olt.id" :value="olt.id.toString()">
                                {{ olt.name }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                    <Select v-model="statusFilter">
                        <SelectTrigger class="w-[140px] rounded-xl">
                            <SelectValue placeholder="All Status" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">All Status</SelectItem>
                            <SelectItem value="1">Success</SelectItem>
                            <SelectItem value="0">Failed</SelectItem>
                        </SelectContent>
                    </Select>
                </div>
                <div class="flex gap-2">
                    <Button variant="outline" @click="fetchLogs(1)" class="rounded-xl">
                        <RefreshCw class="w-4 h-4 mr-2" :class="{ 'animate-spin': loading }" />
                        Refresh
                    </Button>
                </div>
            </div>

            <div class="overflow-x-auto">
                <DataTable :table="table" :loading="loading" />
            </div>

            <div class="p-4 border-t" v-if="pagination">
                <Pagination
                    :current-page="pagination.current_page"
                    :total-items="pagination.total"
                    :per-page="pagination.per_page"
                    @page-change="fetchLogs"
                />
            </div>
        </Card>

        <!-- Log Details Modal -->
        <Dialog v-model:open="showDetails">
            <DialogContent class="sm:max-w-[800px]">
                <DialogHeader>
                    <DialogTitle>Command Interaction Details</DialogTitle>
                    <DialogDescription>
                        Executed on {{ selectedLog?.olt?.name }} at {{ selectedLog?.created_at }}
                    </DialogDescription>
                </DialogHeader>
                
                <div class="space-y-4 py-4">
                    <div class="space-y-1">
                        <Label class="text-xs font-bold text-muted-foreground">Command</Label>
                        <pre class="p-3 bg-muted rounded-xl font-mono text-sm overflow-x-auto">{{ selectedLog?.command }}</pre>
                    </div>
                    <div class="space-y-1">
                        <Label class="text-xs font-bold text-muted-foreground">Response</Label>
                        <pre class="p-3 bg-slate-950 text-slate-50 rounded-xl font-mono text-xs overflow-x-auto max-h-[400px] shadow-inner">{{ selectedLog?.response || 'No response captured.' }}</pre>
                    </div>
                    <div class="grid grid-cols-3 gap-4 text-sm">
                        <div>
                            <p class="text-muted-foreground">Duration</p>
                            <p class="font-medium">{{ selectedLog?.execution_time_ms }}ms</p>
                        </div>
                        <div>
                            <p class="text-muted-foreground">Status</p>
                            <Badge :variant="selectedLog?.is_success ? 'success' : 'destructive'" class="rounded-xl">
                                {{ selectedLog?.is_success ? 'Success' : 'Failed' }}
                            </Badge>
                        </div>
                        <div>
                            <p class="text-muted-foreground">Operator</p>
                            <p class="font-medium">{{ selectedLog?.user?.name || 'System' }}</p>
                        </div>
                    </div>
                </div>
                
                <DialogFooter>
                    <Button @click="showDetails = false" class="rounded-xl">Close</Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </div>
</template>

<script setup lang="ts">
import { ref, onMounted, h, watch } from 'vue';
import { 
    Button, Card, Badge, DataTable, Pagination, 
    Select, SelectContent, SelectItem, SelectTrigger, SelectValue,
    Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription, DialogFooter, Label
} from '@/components/ui';
import { createColumnHelper, useVueTable, getCoreRowModel } from '@tanstack/vue-table';
import RefreshCw from 'lucide-vue-next/dist/esm/icons/refresh-cw.js';
import Eye from 'lucide-vue-next/dist/esm/icons/eye.js';
import AlertCircle from 'lucide-vue-next/dist/esm/icons/circle-alert.js';
import CheckCircle2 from 'lucide-vue-next/dist/esm/icons/circle-check.js';
import api from '@/services/api';
import { parseResponse, type PaginationData } from '@/utils/responseParser';
import { useToast } from '@/composables/useToast';
import type { Olt, OltCommandLog } from '@/types/isp';

const toast = useToast();
const loading = ref(false);
const logs = ref<OltCommandLog[]>([]);
const olts = ref<Olt[]>([]);
const oltFilter = ref('all');
const statusFilter = ref('all');
const pagination = ref<PaginationData | null>(null);
const showDetails = ref(false);
const selectedLog = ref<OltCommandLog | null>(null);

const columnHelper = createColumnHelper<OltCommandLog>();
const columns = [
    columnHelper.accessor('created_at', {
        header: 'Timestamp',
        cell: info => h('span', { class: 'text-xs text-muted-foreground whitespace-nowrap' }, info.getValue())
    }),
    columnHelper.accessor('olt', {
        header: 'OLT',
        cell: info => h('span', { class: 'font-medium' }, info.getValue()?.name || 'Unknown')
    }),
    columnHelper.accessor('command', {
        header: 'Command',
        cell: info => h('code', { class: 'text-xs truncate max-w-[200px] block' }, info.getValue())
    }),
    columnHelper.accessor('is_success', {
        header: 'Status',
        cell: info => {
            const success = info.getValue();
            return h('div', { class: 'flex items-center gap-1' }, [
                success 
                    ? h(CheckCircle2, { class: 'w-4 h-4 text-success' })
                    : h(AlertCircle, { class: 'w-4 h-4 text-destructive' }),
                h('span', { class: success ? 'text-success' : 'text-destructive' }, success ? 'Success' : 'Failed')
            ]);
        }
    }),
    columnHelper.accessor('execution_time_ms', {
        header: 'Time',
        cell: info => `${info.getValue()}ms`
    }),
    columnHelper.display({
        id: 'actions',
        header: '',
        cell: info => h(Button, { 
            variant: 'ghost', 
            size: 'icon', 
            onClick: () => { selectedLog.value = info.row.original; showDetails.value = true; } 
        }, () => h(Eye, { class: 'w-4 h-4' }))
    })
];

const table = useVueTable({
    get data() { return logs.value },
    columns,
    getCoreRowModel: getCoreRowModel(),
});

const fetchLogs = async (page: number | unknown = 1) => {
    loading.value = true;
    try {
        const pageNum = typeof page === 'number' ? page : 1;
        const params: Record<string, string | number> = { page: pageNum, per_page: 15 };
        if (oltFilter.value !== 'all') params.olt_id = oltFilter.value;
        if (statusFilter.value !== 'all') params.success = statusFilter.value;
        
        const res = await api.get('/admin/janet/isp/olts/logs', { params });
        const parsed = parseResponse(res);
        logs.value = parsed.data as OltCommandLog[];
        pagination.value = parsed.pagination;
    } catch (_e) {
        toast.error.action(_e);
    } finally {
        loading.value = false;
    }
};

const fetchOlts = async () => {
    try {
        const res = await api.get('/admin/janet/isp/olts');
        olts.value = res.data.data;
    } catch (_e) { /* ignore */ }
};

watch([oltFilter, statusFilter], () => fetchLogs(1));

onMounted(() => {
    fetchLogs();
    fetchOlts();
});
</script>
