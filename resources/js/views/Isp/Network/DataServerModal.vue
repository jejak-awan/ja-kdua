<script setup lang="ts">
import { ref, h, onMounted } from 'vue';
import { useI18n } from 'vue-i18n';
import { 
    useVueTable, 
    getCoreRowModel, 
    createColumnHelper,
} from '@tanstack/vue-table';
import Loader2 from 'lucide-vue-next/dist/esm/icons/loader-circle.js';
import api from '@/services/api';
import { 
    Dialog, 
    DialogContent, 
    DialogHeader, 
    DialogTitle, 
    Input,
    Button,
    DataTable
} from '@/components/ui';
import { useToast } from '@/composables/useToast';
import { ensureArray } from '@/utils/responseParser';

interface DataServer {
    id: number;
    name: string;
}

defineProps<{
    isOpen: boolean;
}>();

const emit = defineEmits(['close', 'refresh']);

const { t } = useI18n();
const { success, error: toastError } = useToast();

const serverList = ref<DataServer[]>([]);
const isLoading = ref(false);
const newServerName = ref('');
const isEditing = ref<number | null>(null);
const editName = ref('');
const isSaving = ref(false);

async function fetchServers() {
    isLoading.value = true;
    try {
        const response = await api.get('/admin/ja/isp/data-servers');
        serverList.value = ensureArray(response.data) as DataServer[];
    } catch (_err) {
        toastError.action(_err as Record<string, unknown>);
    } finally {
        isLoading.value = false;
    }
}

async function handleAdd() {
    if (!newServerName.value) return;
    isSaving.value = true;
    try {
        await api.post('/admin/ja/isp/data-servers', { name: newServerName.value });
        success.action(t('isp.network.data_server.messages.success_create'));
        newServerName.value = '';
        await fetchServers();
        emit('refresh');
    } catch (_err) {
        toastError.action(_err as Record<string, unknown>);
    } finally {
        isSaving.value = false;
    }
}

async function handleUpdate(id: number, name: string) {
    isLoading.value = true;
    try {
        await api.put(`/admin/ja/isp/data-servers/${id}`, { name });
        success.action(t('isp.network.data_server.messages.success_update'));
        isEditing.value = null;
        await fetchServers();
        emit('refresh');
    } catch (_err) {
        toastError.action(_err as Record<string, unknown>);
    } finally {
        isLoading.value = false;
    }
}

async function handleDelete(id: number) {
    if (!confirm(t('common.messages.confirm.delete', { item: t('isp.network.data_server.title') }))) return;
    isLoading.value = true;
    try {
        await api.delete(`/admin/ja/isp/data-servers/${id}`);
        success.action(t('isp.network.data_server.messages.success_delete'));
        await fetchServers();
        emit('refresh');
    } catch (_err) {
        toastError.action(_err as Record<string, unknown>);
    } finally {
        isLoading.value = false;
    }
}

const columnHelper = createColumnHelper<DataServer>();

const columns = [
    columnHelper.accessor('name', {
        header: t('isp.network.data_server.fields.name'),
        cell: info => {
            const server = info.row.original;
            if (isEditing.value === server.id) {
                return h(Input, {
                    modelValue: editName.value,
                    'onUpdate:modelValue': (val: string | number) => editName.value = String(val),
                    class: 'h-8 rounded-lg outline-none focus:ring-1 focus:ring-blue-500'
                });
            }
            return h('span', { class: 'font-medium text-slate-700' }, server.name);
        }
    }),
    columnHelper.display({
        id: 'actions',
        cell: info => {
            const server = info.row.original;
            if (isEditing.value === server.id) {
                return h('div', { class: 'flex items-center justify-end gap-2' }, [
                    h(Button, { 
                        size: 'sm', 
                        class: 'h-8 bg-blue-600 hover:bg-blue-700 text-white rounded-lg px-3',
                        onClick: () => handleUpdate(server.id, editName.value) 
                    }, { default: () => t('common.actions.save') }),
                    h(Button, { 
                        size: 'sm', 
                        variant: 'ghost',
                        class: 'h-8 rounded-lg',
                        onClick: () => isEditing.value = null 
                    }, { default: () => t('common.actions.cancel') })
                ]);
            }
            return h('div', { class: 'flex items-center justify-end gap-2' }, [
                h(Button, { 
                    size: 'sm', 
                    class: 'h-8 bg-blue-600 hover:bg-blue-700 text-white rounded-lg px-3',
                    onClick: () => { isEditing.value = server.id; editName.value = server.name; } 
                }, { default: () => t('common.actions.edit') }),
                h(Button, { 
                    size: 'sm', 
                    class: 'h-8 bg-red-500 hover:bg-red-600 text-white rounded-lg px-3',
                    onClick: () => handleDelete(server.id) 
                }, { default: () => t('common.actions.delete') })
            ]);
        }
    })
];

const table = useVueTable({
    get data() { return serverList.value },
    columns,
    getCoreRowModel: getCoreRowModel(),
});

onMounted(fetchServers);
</script>

<template>
    <Dialog :open="isOpen" @update:open="emit('close')">
        <DialogContent class="max-w-2xl rounded-2xl p-0 overflow-hidden border-none shadow-2xl">
            <div class="bg-white p-6">
                <DialogHeader class="flex flex-row items-center justify-between space-y-0 pb-6">
                    <DialogTitle class="text-xl font-bold text-slate-800 uppercase tracking-tight">
                        {{ t('isp.network.data_server.title') }}
                    </DialogTitle>
                </DialogHeader>

                <div class="space-y-6">
                    <!-- Instructional Box -->
                    <div class="bg-slate-50 border border-slate-100 rounded-xl p-5 space-y-3">
                        <ul class="space-y-2 text-[13px] text-slate-600 list-disc pl-4 leading-relaxed">
                            <li>{{ t('isp.network.data_server.instructions.edit_hint') }}</li>
                            <li>
                                <span class="font-semibold">{{ t('isp.network.data_server.instructions.matching_hint') }}</span>
                                <div class="mt-1 ml-4 space-y-1 text-slate-500 italic">
                                    <p>• {{ t('isp.network.data_server.instructions.hotspot_path') }}</p>
                                    <p>• {{ t('isp.network.data_server.instructions.ppp_path') }}</p>
                                </div>
                            </li>
                            <li>{{ t('isp.network.data_server.instructions.purpose') }}</li>
                        </ul>
                    </div>

                    <div class="flex items-center gap-2">
                        <Input 
                            v-model="newServerName" 
                            :placeholder="t('isp.network.data_server.fields.name')" 
                            @keyup.enter="handleAdd"
                            class="h-11 rounded-xl bg-white border-slate-200 focus:ring-blue-500 transition-all"
                        />
                        <Button @click="handleAdd" :disabled="isSaving" class="h-11 bg-blue-600 hover:bg-blue-700 text-white rounded-xl px-6 font-semibold shadow-sm transition-all whitespace-nowrap">
                            <Loader2 v-if="isSaving" class="h-4 w-4 mr-2 animate-spin" />
                            {{ t('isp.network.data_server.add') }}
                        </Button>
                    </div>

                    <div class="space-y-2">
                        <div class="text-[13px] font-bold text-slate-500 uppercase tracking-wider px-2">
                            {{ t('isp.network.data_server.fields.name') }}
                        </div>
                        <div class="border border-slate-100 rounded-2xl overflow-hidden bg-white">
                            <DataTable 
                                :table="table" 
                                :loading="isLoading"
                                class="border-none"
                            />
                        </div>
                    </div>
                </div>
            </div>
        </DialogContent>
    </Dialog>
</template>
