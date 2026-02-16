https://rlradius.id/<template>
    <div class="space-y-6">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold tracking-tight">{{ t('isp.billing.plans_manager.title') }}</h1>
                <p class="text-muted-foreground">{{ t('isp.billing.plans_manager.subtitle') }}</p>
            </div>
            <Button @click="openCreateModal" class="gap-2 rounded-xl">
                <Plus class="w-4 h-4" />
                {{ t('isp.billing.plans_manager.new') }}
            </Button>
        </div>

        <Card>
            <div class="p-4 border-b border-border/40 flex flex-col md:flex-row justify-between items-center gap-4">
                <div class="relative w-full md:w-72">
                    <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-muted-foreground" />
                    <Input v-model="search" :placeholder="t('isp.billing.fields.search_placeholder')" class="pl-9" />
                </div>
            </div>

            <DataTable 
                :table="table" 
                :loading="loading" 
                :empty-message="t('isp.billing.messages.no_data')"
            />
        </Card>

        <ProfileModal 
            v-model:open="isModalOpen" 
            :profile="selectedProfile" 
            :loading="isSaving"
            @save="handleSave"
        />

        <ConfirmModal 
            :is-open="isDeleteAlertOpen"
            variant="destructive"
            :title="t('isp.billing.plans_manager.delete')"
            :description="t('isp.billing.plans_manager.delete_confirm')"
            :confirm-text="t('common.actions.delete')"
            :cancel-text="t('common.actions.cancel')"
            @confirm="confirmDelete"
            @cancel="isDeleteAlertOpen = false"
            @update:is-open="isDeleteAlertOpen = $event"
        />
    </div>
</template>

<script setup lang="ts">
import { ref, onMounted, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import api from '@/services/api';
import { useToast } from '@/composables/useToast';
import { 
    ConfirmModal,
    Button, Input, Card, Badge, 
    DataTable
} from '@/components/ui';
import { 
    useVueTable, 
    getCoreRowModel, 
    createColumnHelper
} from '@tanstack/vue-table';
import { h } from 'vue';
import Plus from 'lucide-vue-next/dist/esm/icons/plus.js';
import Search from 'lucide-vue-next/dist/esm/icons/search.js';
import Pencil from 'lucide-vue-next/dist/esm/icons/pencil.js';
import Trash2 from 'lucide-vue-next/dist/esm/icons/trash-2.js';
import ProfileModal from './Modals/ProfileModal.vue';
import type { IspPlan } from '@/types/isp';

const { t } = useI18n();
const toast = useToast();

const profiles = ref<IspPlan[]>([]);
const loading = ref(false);
const search = ref('');
const isModalOpen = ref(false);
const isDeleteAlertOpen = ref(false);
const isSaving = ref(false);
const selectedProfile = ref<IspPlan | null>(null);
const profileToDelete = ref<IspPlan | null>(null);

const columnHelper = createColumnHelper<IspPlan>();

const columns = [
    columnHelper.accessor('name', {
        header: t('isp.billing.plans_manager.fields.name'),
        cell: info => h('span', { class: 'font-medium' }, info.getValue()),
    }),
    columnHelper.accessor('mikrotik_group', {
        header: t('isp.billing.plans_manager.fields.mikrotik_group'),
        cell: info => h(Badge, { variant: 'outline' }, () => info.getValue() || '-'),
    }),
    columnHelper.accessor('price', {
        header: t('isp.billing.plans_manager.fields.price'),
        cell: info => `Rp ${new Intl.NumberFormat('id-ID').format(Number(info.getValue()) || 0)}`,
    }),
    columnHelper.accessor('active_period', {
        header: t('isp.billing.plans_manager.fields.active_period'),
        cell: info => `${info.getValue()} Days`,
    }),
    columnHelper.accessor('type', {
        header: t('isp.billing.plans_manager.fields.type'),
        cell: info => h(Badge, { variant: info.getValue() === 'hotspot' ? 'secondary' : 'default' }, () => info.getValue()),
    }),
    columnHelper.display({
        id: 'status',
        header: t('isp.billing.plans_manager.fields.status'),
        cell: info => {
            const profile = info.row.original;
            const isActive = profile.is_active !== false; 
            return h(Badge, { variant: isActive ? 'success' : 'destructive' }, 
                () => isActive ? t('isp.billing.plans_manager.options.active') : t('isp.billing.plans_manager.options.inactive')
            );
        }
    }),
    columnHelper.display({
        id: 'actions',
        header: () => h('div', { class: 'text-right' }, t('isp.billing.fields.actions')),
        cell: info => {
            return h('div', { class: 'flex justify-end gap-2' }, [
                h(Button, {
                    variant: 'ghost',
                    size: 'icon',
                    onClick: () => openEditModal(info.row.original)
                }, () => h(Pencil, { class: 'w-4 h-4' })),
                h(Button, {
                    variant: 'ghost',
                    size: 'icon',
                    class: 'text-destructive hover:text-destructive',
                    onClick: () => promptDelete(info.row.original)
                }, () => h(Trash2, { class: 'w-4 h-4' }))
            ]);
        }
    })
];

const table = useVueTable({
    get data() { return profiles.value },
    columns,
    getCoreRowModel: getCoreRowModel(),
});

const fetchProfiles = async () => {
    loading.value = true;
    try {
        const response = await api.get('/admin/janet/isp/subscription-profiles', {
            params: { search: search.value }
        });
        profiles.value = response.data.data.data || response.data.data; // Handle pagination or list
    } catch (_error) {
        toast.error.default(t('isp.billing.messages.error_load'));
    } finally {
        loading.value = false;
    }
};

const openCreateModal = () => {
    selectedProfile.value = null;
    isModalOpen.value = true;
};

const openEditModal = (profile: IspPlan) => {
    selectedProfile.value = profile;
    isModalOpen.value = true;
};

const handleSave = async (formData: Record<string, unknown>) => {
    isSaving.value = true;
    try {
        if (selectedProfile.value) {
            await api.put(`/admin/janet/isp/subscription-profiles/${selectedProfile.value.id}`, formData);
        } else {
            await api.post('/admin/janet/isp/subscription-profiles', formData);
        }
        toast.success.action(t('common.messages.success.saved'));
        isModalOpen.value = false;
        fetchProfiles();
    } catch (_error) {
        toast.error.action(_error as Error);
    } finally {
        isSaving.value = false;
    }
};

const promptDelete = (profile: IspPlan) => {
    profileToDelete.value = profile;
    isDeleteAlertOpen.value = true;
};

const confirmDelete = async () => {
    if (!profileToDelete.value) return;
    try {
        await api.delete(`/admin/janet/isp/subscription-profiles/${profileToDelete.value.id}`);
        toast.success.action(t('common.messages.success.deleted'));
        fetchProfiles();
    } catch (_error) {
        toast.error.action(_error as Error);
    } finally {
        isDeleteAlertOpen.value = false;
        profileToDelete.value = null;
    }
};

watch(search, () => {
    // Debounce could be added here
    fetchProfiles();
});

onMounted(() => {
    fetchProfiles();
});
</script>
