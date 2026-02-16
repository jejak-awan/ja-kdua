<template>
    <div class="space-y-6 animate-in fade-in duration-500">
        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h2 class="text-3xl font-bold tracking-tight">{{ $t('isp.network.service_zones.title', 'Service Zones') }}</h2>
                <p class="text-muted-foreground">{{ $t('isp.network.service_zones.subtitle', 'Manage data server zones and active/backup switching') }}</p>
            </div>
            <Button @click="openForm()" class="gap-2 rounded-xl">
                <Plus class="w-4 h-4" />
                {{ $t('isp.network.service_zones.add', 'Add Zone') }}
            </Button>
        </div>

        <!-- Zones Grid -->
        <div v-if="loading" class="flex items-center justify-center py-20">
            <LoaderCircle class="w-8 h-8 animate-spin text-primary" />
        </div>

        <div v-else-if="zones.length === 0" class="text-center py-20">
            <Server class="w-12 h-12 mx-auto text-muted-foreground/40" />
            <h3 class="mt-4 text-lg font-medium">{{ $t('isp.network.service_zones.empty_title', 'No Service Zones') }}</h3>
            <p class="text-sm text-muted-foreground mt-1">{{ $t('isp.network.service_zones.empty_desc', 'Create your first service zone to manage data server routing') }}</p>
            <Button @click="openForm()" class="mt-4 gap-2 rounded-xl">
                <Plus class="w-4 h-4" />
                {{ $t('isp.network.service_zones.add', 'Add Zone') }}
            </Button>
        </div>

        <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <Card 
                v-for="zone in zones" 
                :key="zone.id"
                :class="[
                    'border-border/40 shadow-sm transition-all duration-300',
                    zone.is_active ? 'ring-2 ring-primary/50 bg-primary/5' : ''
                ]"
            >
                <CardContent class="p-6">
                    <div class="flex items-start justify-between">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2">
                                <h3 class="text-lg font-semibold truncate">{{ zone.name }}</h3>
                                <Badge v-if="zone.is_active" variant="default" class="text-[10px] bg-green-600 hover:bg-green-700 rounded-xl">
                                    {{ $t('isp.network.service_zones.active', 'Active') }}
                                </Badge>
                                <Badge v-else variant="outline" class="text-[10px] rounded-xl">
                                    {{ $t('isp.network.service_zones.standby', 'Standby') }}
                                </Badge>
                            </div>
                            <div class="mt-3 space-y-2 text-sm text-muted-foreground">
                                <div class="flex items-center gap-2">
                                    <Globe class="w-3.5 h-3.5 flex-shrink-0" />
                                    <span class="font-mono">{{ zone.ip_address || '-' }}</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <Shield class="w-3.5 h-3.5 flex-shrink-0" />
                                    <Badge variant="outline" class="text-[10px] rounded-xl">
                                        {{ zone.role === 'main' ? $t('isp.network.service_zones.role_main', 'Main') : $t('isp.network.service_zones.role_backup', 'Backup') }}
                                    </Badge>
                                </div>
                            </div>
                        </div>

                        <DropdownMenu>
                            <DropdownMenuTrigger as-child>
                                <Button variant="ghost" size="icon" class="h-8 w-8 rounded-xl">
                                    <EllipsisVertical class="h-4 w-4" />
                                </Button>
                            </DropdownMenuTrigger>
                            <DropdownMenuContent align="end" class="w-44 rounded-xl shadow-xl border-border/40">
                                <DropdownMenuItem @click="toggleActive(zone)">
                                    <Power class="mr-2 h-4 w-4" />
                                    {{ zone.is_active ? $t('isp.network.service_zones.deactivate', 'Deactivate') : $t('isp.network.service_zones.activate', 'Set Active') }}
                                </DropdownMenuItem>
                                <DropdownMenuItem @click="openForm(zone)">
                                    <Pencil class="mr-2 h-4 w-4" />
                                    {{ $t('common.actions.edit') }}
                                </DropdownMenuItem>
                                <DropdownMenuItem class="text-destructive focus:text-destructive" @click="confirmDelete(zone)">
                                    <Trash2 class="mr-2 h-4 w-4" />
                                    {{ $t('common.actions.delete') }}
                                </DropdownMenuItem>
                            </DropdownMenuContent>
                        </DropdownMenu>
                    </div>
                </CardContent>
            </Card>
        </div>

        <!-- Create/Edit Dialog -->
        <Dialog v-model:open="showFormModal">
            <DialogContent class="max-w-md rounded-2xl p-0 overflow-hidden border-none shadow-2xl">
                <div class="bg-popover p-6">
                    <DialogHeader class="pb-4">
                        <DialogTitle>
                            {{ editingZone ? $t('isp.network.service_zones.edit', 'Edit Zone') : $t('isp.network.service_zones.add', 'Add Zone') }}
                        </DialogTitle>
                    </DialogHeader>
                    <form @submit.prevent="saveZone" class="space-y-4">
                        <div class="space-y-2">
                            <Label>{{ $t('isp.network.service_zones.field_name', 'Zone Name') }}</Label>
                            <Input v-model="form.name" :placeholder="$t('isp.network.service_zones.field_name_placeholder', 'e.g. Primary Server')" required class="rounded-xl" />
                        </div>
                        <div class="space-y-2">
                            <Label>{{ $t('isp.network.service_zones.field_ip', 'IP Address') }}</Label>
                            <Input v-model="form.ip_address" placeholder="192.168.1.1" class="font-mono rounded-xl" />
                        </div>
                        <div class="space-y-2">
                            <Label>{{ $t('isp.network.service_zones.field_role', 'Role') }}</Label>
                            <Select v-model="form.role">
                                <SelectTrigger class="bg-background rounded-xl">
                                    <SelectValue :placeholder="$t('isp.network.service_zones.select_role', 'Select role')" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="main">{{ $t('isp.network.service_zones.role_main', 'Main') }}</SelectItem>
                                    <SelectItem value="backup">{{ $t('isp.network.service_zones.role_backup', 'Backup') }}</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                        <div class="flex justify-end gap-2 pt-2">
                            <Button variant="ghost" type="button" @click="showFormModal = false" class="rounded-xl">
                                {{ $t('common.actions.cancel') }}
                            </Button>
                            <Button type="submit" :disabled="saving" class="rounded-xl">
                                <LoaderCircle v-if="saving" class="w-4 h-4 mr-2 animate-spin" />
                                {{ editingZone ? $t('common.actions.save') : $t('common.actions.create') }}
                            </Button>
                        </div>
                    </form>
                </div>
            </DialogContent>
        </Dialog>

        <!-- Delete Confirmation -->
        <ConfirmModal
            :is-open="showDeleteModal"
            variant="destructive"
            :title="$t('isp.network.service_zones.delete_title', 'Delete Service Zone')"
            :description="$t('isp.network.service_zones.delete_confirm', 'Are you sure you want to delete this zone? This action cannot be undone.')"
            :confirm-text="$t('common.actions.delete')"
            :cancel-text="$t('common.actions.cancel')"
            @confirm="deleteZone"
            @cancel="showDeleteModal = false"
            @update:is-open="showDeleteModal = $event"
        />
    </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { useI18n } from 'vue-i18n';
import api from '@/services/api';
import { useToast } from '@/composables/useToast';
import { 
    Card, CardContent, 
    Button, Badge, Input, Label,
    Select, SelectTrigger, SelectValue, SelectContent, SelectItem,
    Dialog, DialogContent, DialogHeader, DialogTitle,
    DropdownMenu, DropdownMenuTrigger, DropdownMenuContent, DropdownMenuItem,
} from '@/components/ui';
import ConfirmModal from '@/components/ui/ConfirmModal.vue';

import Plus from 'lucide-vue-next/dist/esm/icons/plus.js';
import Server from 'lucide-vue-next/dist/esm/icons/server.js';
import Globe from 'lucide-vue-next/dist/esm/icons/globe.js';
import Shield from 'lucide-vue-next/dist/esm/icons/shield.js';
import EllipsisVertical from 'lucide-vue-next/dist/esm/icons/ellipsis-vertical.js';
import Power from 'lucide-vue-next/dist/esm/icons/power.js';
import Pencil from 'lucide-vue-next/dist/esm/icons/pencil.js';
import Trash2 from 'lucide-vue-next/dist/esm/icons/trash-2.js';
import LoaderCircle from 'lucide-vue-next/dist/esm/icons/loader-circle.js';

const { t } = useI18n();
const toast = useToast();

interface ServiceZone {
    id: number;
    name: string;
    ip_address: string | null;
    role: 'main' | 'backup' | null;
    is_active: boolean;
    status: string | null;
}

const loading = ref(true);
const saving = ref(false);
const zones = ref<ServiceZone[]>([]);
const showFormModal = ref(false);
const showDeleteModal = ref(false);
const editingZone = ref<ServiceZone | null>(null);
const deletingZone = ref<ServiceZone | null>(null);

const form = ref({
    name: '',
    ip_address: '',
    role: 'main' as 'main' | 'backup',
});

const fetchZones = async () => {
    loading.value = true;
    try {
        const response = await api.get('/admin/janet/isp/service-zones');
        zones.value = response.data.data || response.data;
    } catch (_error) {
        toast.error.default(t('common.messages.error.load', 'Failed to load data'));
    } finally {
        loading.value = false;
    }
};

const openForm = (zone?: ServiceZone) => {
    if (zone) {
        editingZone.value = zone;
        form.value = {
            name: zone.name,
            ip_address: zone.ip_address || '',
            role: zone.role || 'main',
        };
    } else {
        editingZone.value = null;
        form.value = { name: '', ip_address: '', role: 'main' };
    }
    showFormModal.value = true;
};

const saveZone = async () => {
    saving.value = true;
    try {
        if (editingZone.value) {
            await api.put(`/admin/janet/isp/service-zones/${editingZone.value.id}`, form.value);
            toast.success.default(t('common.messages.success.updated', 'Updated successfully'));
        } else {
            await api.post('/admin/janet/isp/service-zones', form.value);
            toast.success.default(t('common.messages.success.created', 'Created successfully'));
        }
        showFormModal.value = false;
        fetchZones();
    } catch (_error) {
        toast.error.default(t('common.messages.error.save', 'Failed to save'));
    } finally {
        saving.value = false;
    }
};

const toggleActive = async (zone: ServiceZone) => {
    try {
        await api.patch(`/admin/janet/isp/service-zones/${zone.id}/toggle-active`, {
            is_active: !zone.is_active
        });
        toast.success.default(
            zone.is_active 
                ? t('isp.network.service_zones.deactivated', 'Zone deactivated')
                : t('isp.network.service_zones.activated', 'Zone set as active')
        );
        fetchZones();
    } catch (_error) {
        toast.error.default(t('common.messages.error.update', 'Failed to update'));
    }
};

const confirmDelete = (zone: ServiceZone) => {
    deletingZone.value = zone;
    showDeleteModal.value = true;
};

const deleteZone = async () => {
    if (!deletingZone.value) return;
    try {
        await api.delete(`/admin/janet/isp/service-zones/${deletingZone.value.id}`);
        toast.success.default(t('common.messages.success.deleted', 'Deleted successfully'));
        fetchZones();
    } catch (_error) {
        toast.error.default(t('common.messages.error.delete', 'Failed to delete'));
    } finally {
        showDeleteModal.value = false;
        deletingZone.value = null;
    }
};

onMounted(fetchZones);
</script>
