<template>
    <div class="container mx-auto p-6">
        <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4 mb-6">
            <div>
                <h2 class="text-3xl font-bold tracking-tight text-foreground">{{ $t('isp.hotspot.cookies.title', 'Hotspot Cookies') }}</h2>
                <p class="text-sm text-muted-foreground mt-1">{{ $t('isp.hotspot.cookies.subtitle', 'Active "remember me" sessions for hotspot users') }}</p>
            </div>
            <Button @click="fetchCookies" :disabled="loading" class="rounded-xl">
                <RefreshCw v-if="loading" class="w-4 h-4 mr-2 animate-spin" />
                <RefreshCw v-else class="w-4 h-4 mr-2" />
                {{ $t('common.actions.refresh', 'Refresh') }}
            </Button>
        </div>

        <Card class="border-border/40 shadow-sm rounded-xl overflow-hidden">
            <CardContent class="p-0">
                <DataTable
                    :table="table"
                    :loading="loading"
                    :empty-message="t('isp.hotspot.cookies.empty', 'No active cookies found')"
                />
            </CardContent>
        </Card>
    </div>
</template>

<script setup lang="ts">
import { ref, onMounted, h } from 'vue';
import { useI18n } from 'vue-i18n';
import api from '@/services/api';
import { useToast } from '@/composables/useToast';
import { useConfirm } from '@/composables/useConfirm';
import { Button, Card, CardContent, DataTable } from '@/components/ui';
import { useVueTable, getCoreRowModel, getSortedRowModel, createColumnHelper, type SortingState } from '@tanstack/vue-table';

import RefreshCw from 'lucide-vue-next/dist/esm/icons/refresh-cw.js';
import Clock from 'lucide-vue-next/dist/esm/icons/clock.js';
import Trash2 from 'lucide-vue-next/dist/esm/icons/trash-2.js';

const { t } = useI18n();
const toast = useToast();
const { confirm } = useConfirm();

interface HotspotCookie {
    id: string;
    user: string;
    mac: string;
    domain: string;
    expires_in: string;
}

const loading = ref(false);
const cookies = ref<HotspotCookie[]>([]);
const sorting = ref<SortingState>([]);

const columnHelper = createColumnHelper<HotspotCookie>();

const columns = [
    columnHelper.accessor('user', {
        header: t('common.fields.user', 'User'),
        cell: ({ row }) => h('span', { class: 'font-medium' }, row.original.user)
    }),
    columnHelper.accessor('mac', {
        header: 'MAC',
        cell: ({ row }) => h('span', { class: 'font-mono text-sm' }, row.original.mac)
    }),
    columnHelper.accessor('domain', {
        header: 'Domain',
        cell: ({ row }) => h('span', { class: 'text-sm' }, row.original.domain || '-')
    }),
    columnHelper.accessor('expires_in', {
        header: t('isp.hotspot.cookies.expires_in', 'Expires In'),
        cell: ({ row }) => h('div', { class: 'flex items-center gap-2' }, [
            h(Clock, { class: 'w-4 h-4 text-muted-foreground' }),
            h('span', { class: 'text-sm' }, row.original.expires_in)
        ])
    }),
    columnHelper.display({
        id: 'actions',
        header: () => h('div', { class: 'text-right' }, t('common.fields.actions', 'Actions')),
        cell: ({ row }) => h('div', { class: 'flex justify-end' }, [
            h(Button, {
                variant: 'ghost',
                size: 'icon',
                onClick: () => deleteCookie(row.original.id),
                title: t('common.actions.delete', 'Delete'),
                class: 'h-8 w-8 text-destructive hover:text-destructive hover:bg-destructive/10'
            }, () => h(Trash2, { class: 'w-4 h-4' }))
        ])
    })
];

const table = useVueTable({
    get data() { return cookies.value },
    columns,
    state: {
        get sorting() { return sorting.value }
    },
    onSortingChange: updaterOrValue => {
        sorting.value = typeof updaterOrValue === 'function' ? updaterOrValue(sorting.value) : updaterOrValue;
    },
    getCoreRowModel: getCoreRowModel(),
    getSortedRowModel: getSortedRowModel(),
    getRowId: row => row.id,
});

const fetchCookies = async () => {
    loading.value = true;
    try {
        const res = await api.get('/admin/janet/isp/hotspot/cookies');
        cookies.value = res.data.data;
    } catch (error) {
        console.error('Failed to fetch hotspot cookies', error);
        toast.error.load(error);
    } finally {
        loading.value = false;
    }
};

const deleteCookie = async (id: string) => {
    const confirmed = await confirm({
        title: t('common.actions.delete', 'Delete'),
        message: t('isp.hotspot.cookies.confirm_delete', 'This will log the user out. Continue?'),
        variant: 'danger',
        confirmText: t('common.actions.delete', 'Delete'),
    });
    if (!confirmed) return;
    
    try {
        await api.delete(`/admin/janet/isp/hotspot/cookies/${id}`);
        toast.success.delete('Hotspot Cookie');
        fetchCookies();
    } catch (error) {
        console.error('Failed to delete hotspot cookie', error);
        toast.error.delete(error, 'Hotspot Cookie');
    }
};

onMounted(() => {
    fetchCookies();
});
</script>
