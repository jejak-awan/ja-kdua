<template>
    <div class="space-y-6 animate-in fade-in duration-500">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold tracking-tight">{{ $t('isp.admin.odp.title', 'ODP Manager') }}</h1>
                <p class="text-muted-foreground">{{ $t('isp.admin.odp.subtitle', 'Manage Optical Distribution Points and port assignments') }}</p>
            </div>
            <Button @click="showCreateModal = true">
                <Plus class="w-4 h-4 mr-2" />
                {{ $t('isp.admin.odp.create', 'Add New ODP') }}
            </Button>
        </div>

        <Card class="border-border/40 shadow-sm">
            <CardHeader class="p-4 border-b border-border/40">
                <div class="flex items-center gap-4">
                    <div class="relative flex-1 max-w-sm">
                        <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-muted-foreground" />
                        <input 
                            v-model="search"
                            type="text" 
                            class="w-full bg-muted/50 border-border/50 rounded-lg pl-10 pr-4 py-2 text-sm focus:ring-2 focus:ring-primary/20 transition-shadow"
                            :placeholder="$t('common.placeholders.search', 'Search ODPs...')"
                        />
                    </div>
                </div>
            </CardHeader>
            <CardContent class="p-0">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="text-xs text-muted-foreground uppercase bg-muted/30">
                            <tr>
                                <th class="px-6 py-3 font-medium">{{ $t('common.labels.name', 'ODP Name') }}</th>
                                <th class="px-6 py-3 font-medium">{{ $t('isp.admin.infra.olt', 'Parent OLT') }}</th>
                                <th class="px-6 py-3 font-medium text-center">{{ $t('isp.admin.odp.ports', 'Ports (Used/Total)') }}</th>
                                <th class="px-6 py-3 font-medium">{{ $t('common.labels.status', 'Status') }}</th>
                                <th class="px-6 py-3 font-medium text-right">{{ $t('common.labels.actions', 'Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border/40">
                            <tr v-for="odp in filteredOdps" :key="odp.id" class="hover:bg-muted/20 transition-colors group">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="p-2 rounded-lg bg-primary/10 text-primary">
                                            <Box class="w-4 h-4" />
                                        </div>
                                        <div>
                                            <p class="font-medium underline decoration-primary/30 underline-offset-4">{{ odp.name }}</p>
                                            <p class="text-[10px] text-muted-foreground">{{ odp.location_address }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <Badge variant="secondary" class="font-mono text-[10px]">
                                        {{ odp.olt?.name || 'Unknown OLT' }}
                                    </Badge>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex flex-col items-center gap-1">
                                        <div class="w-24 bg-muted h-1.5 rounded-full overflow-hidden">
                                            <div 
                                                class="h-full transition-[width] duration-500" 
                                                :class="getProgressColor(odp.customers_count, odp.total_ports)"
                                                :style="{ width: (odp.customers_count / odp.total_ports * 100) + '%' }"
                                            ></div>
                                        </div>
                                        <span class="text-[10px] whitespace-nowrap">{{ odp.customers_count }} / {{ odp.total_ports }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <Badge :variant="getStatusVariant(odp.status)">{{ odp.status }}</Badge>
                                </td>
                                <td class="px-6 py-4 text-right opacity-0 group-hover:opacity-100 transition-opacity">
                                    <div class="flex items-center justify-end gap-2">
                                        <Button variant="ghost" size="icon" class="h-8 w-8" @click="editOdp(odp)">
                                            <Pencil class="w-3.5 h-3.5" />
                                        </Button>
                                        <Button variant="ghost" size="icon" class="h-8 w-8 text-destructive" @click="deleteOdp(odp.id)">
                                            <Trash2 class="w-3.5 h-3.5" />
                                        </Button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div v-if="loading" class="p-12 flex flex-col items-center justify-center text-muted-foreground">
                    <LoaderCircle class="w-8 h-8 animate-spin mb-4" />
                    <p class="text-sm">Loading infrastructure data...</p>
                </div>

                <div v-else-if="filteredOdps.length === 0" class="p-12 text-center text-muted-foreground">
                    <p class="text-sm">No ODPs found.</p>
                </div>
            </CardContent>
        </Card>
    </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { useI18n } from 'vue-i18n';
import api from '@/services/api';
import { 
    Card, CardHeader, CardContent, 
    Button, Badge 
} from '@/components/ui';
import Plus from 'lucide-vue-next/dist/esm/icons/plus.js';
import Search from 'lucide-vue-next/dist/esm/icons/search.js';
import Pencil from 'lucide-vue-next/dist/esm/icons/pencil.js';
import Trash2 from 'lucide-vue-next/dist/esm/icons/trash-2.js';
import Box from 'lucide-vue-next/dist/esm/icons/box.js';
import LoaderCircle from 'lucide-vue-next/dist/esm/icons/loader-circle.js';

const { t } = useI18n();
const loading = ref(true);
// eslint-disable-next-line @typescript-eslint/no-explicit-any
const odps = ref<any[]>([]);
const search = ref('');
const showCreateModal = ref(false);

const fetchData = async () => {
    loading.value = true;
    try {
        const response = await api.get('/admin/ja/isp/odps');
        odps.value = response.data.data;
    } catch (error) {
        console.error('Failed to fetch ODPs', error);
    } finally {
        loading.value = false;
    }
};

const filteredOdps = computed(() => {
    if (!search.value) return odps.value;
    const s = search.value.toLowerCase();
    return odps.value.filter(o => 
        o.name.toLowerCase().includes(s) || 
        o.location_address?.toLowerCase().includes(s)
    );
});

// eslint-disable-next-line @typescript-eslint/no-explicit-any
const getStatusVariant = (status: string): any => {
    switch (status) {
        case 'Active': return 'success';
        case 'Full': return 'warning';
        case 'Maintenance': return 'outline';
        case 'Inactive': return 'secondary';
        default: return 'secondary';
    }
};

const getProgressColor = (used: number, total: number) => {
    const ratio = used / total;
    if (ratio >= 1) return 'bg-destructive';
    if (ratio >= 0.8) return 'bg-warning';
    return 'bg-success';
};

// eslint-disable-next-line @typescript-eslint/no-explicit-any
const editOdp = (odp: any) => {
    // Boilerplate for now
    console.warn('Edit ODP', odp.id);
};

const deleteOdp = async (id: number) => {
    if (!confirm(t('common.messages.confirm_delete', 'Are you sure?'))) return;
    try {
        await api.delete(`/admin/ja/isp/odps/${id}`);
        await fetchData();
    } catch (error) {
        console.error('Delete failed', error);
    }
};

onMounted(fetchData);
</script>
