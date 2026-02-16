<template>
    <div class="space-y-6">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-foreground">{{ t('isp.monitor.radius_logs.title') }}</h1>
                <p class="text-sm text-muted-foreground">{{ t('isp.monitor.radius_logs.subtitle') }}</p>
            </div>
            <div class="flex items-center gap-3">
                <Button 
                    variant="outline" 
                    size="icon" 
                    @click="fetchLogs" 
                    :disabled="loading"
                    class="rounded-xl"
                >
                    <RefreshCw class="w-4 h-4" :class="{ 'animate-spin': loading }" />
                </Button>
            </div>
        </div>

        <Card class="overflow-hidden border border-border/40 shadow-sm bg-card/40 backdrop-blur-md rounded-xl">
            <div class="p-0">
                <DataTable
                    :table="table"
                    :loading="loading"
                    :empty-message="t('isp.monitor.radius_logs.empty')"
                />
            </div>
        </Card>
    </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { useI18n } from 'vue-i18n';
import { 
    Card, 
    Button, 
    Badge,
    DataTable
} from '@/components/ui';

import { h } from 'vue';
import { 
    useVueTable, 
    getCoreRowModel, 
    createColumnHelper,
    getSortedRowModel,
    type SortingState
} from '@tanstack/vue-table';

import RefreshCw from 'lucide-vue-next/dist/esm/icons/refresh-cw.js';
import api from '@/services/api';
import { useToast } from '@/composables/useToast';
import dayjs from 'dayjs';

const { t } = useI18n();
const toast = useToast();

interface RadiusLog {
    id: number;
    username: string;
    pass: string;
    reply: string;
    authdate: string;
    nasipaddress: string;
}

const logs = ref<RadiusLog[]>([]);
const loading = ref(false);

const columnHelper = createColumnHelper<RadiusLog>();

const columns = [
    columnHelper.accessor('authdate', {
        header: t('isp.monitor.radius_logs.table.time'),
        cell: ({ row }) => h('div', { class: 'text-xs text-muted-foreground' }, formatDate(row.original.authdate))
    }),
    columnHelper.accessor('username', {
        header: t('isp.monitor.radius_logs.table.user_pass'),
        cell: ({ row }) => h('div', { class: 'flex flex-col' }, [
            h('span', { class: 'font-semibold text-sm' }, row.original.username),
            h('span', { class: 'text-[10px] text-muted-foreground font-mono opacity-50' }, maskPassword(row.original.pass))
        ])
    }),
    columnHelper.accessor('reply', {
        header: t('isp.monitor.radius_logs.table.status'),
        cell: ({ row }) => h(Badge, {
            variant: row.original.reply === 'Access-Accept' ? 'success' : 'destructive',
            class: 'text-[9px] font-bold'
        }, row.original.reply)
    }),
    columnHelper.accessor('nasipaddress', {
        header: t('isp.monitor.radius_logs.table.nas_ip'),
        cell: ({ row }) => h('div', { class: 'text-xs font-mono text-muted-foreground' }, row.original.nasipaddress)
    }),
    columnHelper.display({
        id: 'message',
        header: t('isp.monitor.radius_logs.table.reply_message'),
        cell: ({ row }) => h('div', { 
            class: 'text-xs max-w-[300px] truncate',
            title: row.original.reply 
        }, getShortReply(row.original.reply))
    })
];

const sorting = ref<SortingState>([]);

const table = useVueTable({
    get data() { return logs.value },
    columns,
    state: {
        get sorting() { return sorting.value },
    },
    onSortingChange: updaterOrValue => {
        sorting.value = typeof updaterOrValue === 'function' ? updaterOrValue(sorting.value) : updaterOrValue;
    },
    getCoreRowModel: getCoreRowModel(),
    getSortedRowModel: getSortedRowModel(),
    getRowId: row => String(row.id),
});

const fetchLogs = async () => {
    loading.value = true;
    try {
        const res = await api.get('/admin/janet/isp/radius/logs');
        logs.value = res.data.data || [];
    } catch (_e) {
        toast.error.default(t('isp.monitor.radius_logs.messages.error_load'));
    } finally {
        loading.value = false;
    }
};

const formatDate = (dateStr: string) => {
    return dayjs(dateStr).format('DD MMM, HH:mm:ss');
};

const maskPassword = (pass: string) => {
    if (!pass) return 'N/A';
    return pass.length > 2 ? pass.substring(0, 2) + '*'.repeat(5) : '***';
};

const getShortReply = (reply: string) => {
    if (reply === 'Access-Accept') return 'Login Sukses';
    if (reply === 'Access-Reject') return 'Password Salah / User Tidak Ditemukan';
    return reply;
};

onMounted(() => {
    fetchLogs();
});
</script>
