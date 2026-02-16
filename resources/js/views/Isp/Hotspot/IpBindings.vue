<template>
    <div class="container mx-auto p-6">
        <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4 mb-6">
            <div>
                <h2 class="text-3xl font-bold tracking-tight text-foreground">{{ $t('isp.hotspot.ip_bindings.title', 'IP Bindings') }}</h2>
                <p class="text-sm text-muted-foreground mt-1">{{ $t('isp.hotspot.ip_bindings.subtitle', 'Manage MAC/IP bypass rules for hotspot login') }}</p>
            </div>
            <Button @click="showAddModal = true" class="rounded-xl">
                <Plus class="w-4 h-4 mr-2" />
                {{ $t('common.actions.add', 'Add') }}
            </Button>
        </div>

        <Card class="border-border/40 shadow-sm rounded-xl overflow-hidden">
            <CardContent class="p-0">
                <DataTable
                    :table="table"
                    :loading="loading"
                    :empty-message="t('isp.hotspot.ip_bindings.empty', 'No IP bindings found')"
                />
            </CardContent>
        </Card>

        <!-- Add Modal -->
        <Dialog v-model:open="showAddModal">
            <DialogContent class="sm:max-w-[425px]">
                <DialogHeader>
                    <DialogTitle>{{ $t('isp.hotspot.ip_bindings.add_title', 'Add IP Binding') }}</DialogTitle>
                    <DialogDescription>{{ $t('isp.hotspot.ip_bindings.add_desc', 'Bypass hotspot login for a specific MAC or IP address') }}</DialogDescription>
                </DialogHeader>
                <div class="grid gap-4 py-4">
                    <div class="grid gap-2">
                        <Label for="mac">MAC Address</Label>
                        <Input v-model="formData.mac" id="mac" placeholder="00:11:22:33:44:55" class="rounded-xl" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="address">IP Address</Label>
                        <Input v-model="formData.address" id="address" placeholder="192.168.1.100" class="rounded-xl" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="type">Type</Label>
                        <Select v-model="formData.type">
                            <SelectTrigger id="type" class="rounded-xl">
                                <SelectValue />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="bypassed">Bypassed</SelectItem>
                                <SelectItem value="blocked">Blocked</SelectItem>
                                <SelectItem value="regular">Regular</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                    <div class="grid gap-2">
                        <Label for="comment">{{ $t('common.fields.comment', 'Comment') }}</Label>
                        <Input v-model="formData.comment" id="comment" placeholder="Staff device" class="rounded-xl" />
                    </div>
                </div>
                <DialogFooter>
                    <Button variant="outline" @click="showAddModal = false" class="rounded-xl">{{ $t('common.actions.cancel', 'Cancel') }}</Button>
                    <Button @click="addBinding" :disabled="saving" class="rounded-xl">
                        <LoaderCircle v-if="saving" class="w-4 h-4 mr-2 animate-spin" />
                        {{ $t('common.actions.add', 'Add') }}
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </div>
</template>

<script setup lang="ts">
import { ref, onMounted, h } from 'vue';
import { useI18n } from 'vue-i18n';
import api from '@/services/api';
import { useToast } from '@/composables/useToast';
import { useConfirm } from '@/composables/useConfirm';
import { 
    Button, Card, CardContent, DataTable,
    Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription, DialogFooter, 
    Input, Label, Select, SelectTrigger, SelectValue, SelectContent, SelectItem 
} from '@/components/ui';
import { useVueTable, getCoreRowModel, getSortedRowModel, createColumnHelper, type SortingState } from '@tanstack/vue-table';

import Plus from 'lucide-vue-next/dist/esm/icons/plus.js';
import LoaderCircle from 'lucide-vue-next/dist/esm/icons/loader-circle.js';
import Power from 'lucide-vue-next/dist/esm/icons/power.js';
import Trash2 from 'lucide-vue-next/dist/esm/icons/trash-2.js';

const { t } = useI18n();
const toast = useToast();
const { confirm } = useConfirm();

interface IpBinding {
    id: string;
    mac: string;
    address: string;
    type: string;
    disabled: boolean;
    comment: string | null;
}

const loading = ref(false);
const saving = ref(false);
const bindings = ref<IpBinding[]>([]);
const showAddModal = ref(false);
const sorting = ref<SortingState>([]);
const formData = ref({
    mac: '',
    address: '',
    type: 'bypassed',
    comment: '',
});

const typeClass = (type: string) => {
    switch (type) {
        case 'bypassed': return 'bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-300';
        case 'blocked': return 'bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-300';
        default: return 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300';
    }
};

const columnHelper = createColumnHelper<IpBinding>();

const columns = [
    columnHelper.accessor('mac', {
        header: 'MAC',
        cell: ({ row }) => h('span', { class: 'font-mono text-sm' }, row.original.mac || '-')
    }),
    columnHelper.accessor('address', {
        header: t('common.fields.address', 'Address'),
        cell: ({ row }) => h('span', { class: 'font-mono text-sm' }, row.original.address || '-')
    }),
    columnHelper.accessor('type', {
        header: 'Type',
        cell: ({ row }) => h('span', { 
            class: `px-2 py-1 text-xs rounded-full font-medium ${typeClass(row.original.type)}` 
        }, row.original.type)
    }),
    columnHelper.accessor('disabled', {
        header: t('common.fields.status', 'Status'),
        cell: ({ row }) => {
            const isDisabled = row.original.disabled;
            return h('span', { 
                class: `flex items-center gap-1 ${isDisabled ? 'text-red-500' : 'text-green-500'}` 
            }, [
                h('span', { class: `w-2 h-2 rounded-full ${isDisabled ? 'bg-red-500' : 'bg-green-500'}` }),
                isDisabled ? 'Disabled' : 'Enabled'
            ]);
        }
    }),
    columnHelper.accessor('comment', {
        header: t('common.fields.comment', 'Comment'),
        cell: ({ row }) => h('span', { class: 'text-sm text-muted-foreground' }, row.original.comment || '-')
    }),
    columnHelper.display({
        id: 'actions',
        header: () => h('div', { class: 'text-right' }, t('common.fields.actions', 'Actions')),
        cell: ({ row }) => h('div', { class: 'flex items-center justify-end gap-1' }, [
            h(Button, {
                variant: 'ghost',
                size: 'icon',
                onClick: () => toggleBinding(row.original),
                title: row.original.disabled ? 'Enable' : 'Disable',
                class: 'h-8 w-8'
            }, () => h(Power, { class: `w-4 h-4 ${row.original.disabled ? 'text-green-500' : 'text-yellow-500'}` })),
            h(Button, {
                variant: 'ghost',
                size: 'icon',
                onClick: () => deleteBinding(row.original.id),
                title: t('common.actions.delete', 'Delete'),
                class: 'h-8 w-8 text-destructive hover:text-destructive hover:bg-destructive/10'
            }, () => h(Trash2, { class: 'w-4 h-4' }))
        ])
    })
];

const table = useVueTable({
    get data() { return bindings.value },
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

const fetchBindings = async () => {
    loading.value = true;
    try {
        const res = await api.get('/admin/janet/isp/hotspot/ip-bindings');
        bindings.value = res.data.data;
    } catch (error) {
        console.error('Failed to fetch IP bindings', error);
        toast.error.load(error);
    } finally {
        loading.value = false;
    }
};

const addBinding = async () => {
    saving.value = true;
    try {
        await api.post('/admin/janet/isp/hotspot/ip-bindings', formData.value);
        toast.success.action(t('isp.hotspot.ip_bindings.add_success', 'IP Binding added successfully'));
        showAddModal.value = false;
        formData.value = { mac: '', address: '', type: 'bypassed', comment: '' };
        fetchBindings();
    } catch (error) {
        console.error('Failed to add IP binding', error);
        toast.error.create(error, 'IP Binding');
    } finally {
        saving.value = false;
    }
};

const deleteBinding = async (id: string) => {
    const confirmed = await confirm({
        title: t('common.actions.delete', 'Delete'),
        message: t('common.confirm.delete', 'Are you sure?'),
        variant: 'danger',
        confirmText: t('common.actions.delete', 'Delete'),
    });
    if (!confirmed) return;
    
    try {
        await api.delete(`/admin/janet/isp/hotspot/ip-bindings/${id}`);
        toast.success.delete('IP Binding');
        fetchBindings();
    } catch (error) {
        console.error('Failed to delete IP binding', error);
        toast.error.delete(error, 'IP Binding');
    }
};

const toggleBinding = async (binding: IpBinding) => {
    try {
        await api.patch(`/admin/janet/isp/hotspot/ip-bindings/${binding.id}/toggle`, {
            disabled: !binding.disabled
        });
        toast.success.update('IP Binding');
        fetchBindings();
    } catch (error) {
        console.error('Failed to toggle IP binding', error);
        toast.error.update(error, 'IP Binding');
    }
};

onMounted(() => {
    fetchBindings();
});
</script>
