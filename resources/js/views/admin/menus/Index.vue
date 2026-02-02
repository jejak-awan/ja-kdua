<template>
    <div class="h-full flex flex-col">
        <!-- Header - Clean, just title -->
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-foreground">{{ t('features.menus.title') }}</h1>
            <p class="text-sm text-muted-foreground">{{ t('features.menus.subtitle') }}</p>
        </div>

        <!-- Main Content Area -->
        <div v-if="isLoading && !selectedMenuId" class="flex-1 flex items-center justify-center min-h-[400px]">
            <Loader2 class="w-8 h-8 animate-spin text-muted-foreground" />
        </div>

        <div v-else-if="menus.length === 0 && trashedFilter === 'without'" class="flex-1 flex flex-col items-center justify-center min-h-[400px] border-2 border-dashed border-border rounded-lg bg-muted/10">
            <MenuSquare class="w-16 h-16 text-muted-foreground/20 mb-4" />
            <h3 class="text-lg font-medium mb-2">{{ t('features.menus.messages.empty') }}</h3>
            <p class="text-sm text-muted-foreground mb-6 text-center max-w-[400px]">
                {{ t('features.menus.messages.emptyDescription') }}
            </p>
            <Button @click="openCreateModal">
                <Plus class="w-4 h-4 mr-2" />
                {{ t('features.menus.actions.createFirst') }}
            </Button>
        </div>

        <div v-else class="flex-1">
            <MenuBuilder 
                ref="builderRef"
                :menu-id="selectedMenuId" 
                :key="selectedMenuId"
                :menus="menus"
                :trashed-filter="trashedFilter"
                :trashed-count="trashedCount"
                :is-trashed="!!selectedMenu?.deleted_at"
                @menu-updated="handleMenuUpdated"
                @create-menu="openCreateModal"
                @delete-menu="deleteCurrentMenu"
                @restore-menu="restoreCurrentMenu"
                @select-menu="handleSelectMenu"
                @update:trashed-filter="trashedFilter = $event"
            />
        </div>

        <!-- Create Menu Modal -->
        <MenuModal
            v-if="showCreateModal"
            @close="showCreateModal = false"
            @saved="handleMenuCreated"
        />
    </div>
</template>

<script setup lang="ts">
import { logger } from '@/utils/logger';
import { ref, onMounted, watch, computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { useRouter, useRoute } from 'vue-router';
import api from '../../../services/api';
import { useToast } from '../../../composables/useToast';
import { useConfirm } from '../../../composables/useConfirm';
import MenuBuilder from '../../../components/menus/MenuBuilder.vue';
import MenuModal from '../../../components/menus/MenuModal.vue';
import { 
    Button, 
    Select, 
    SelectTrigger, 
    SelectValue, 
    SelectContent, 
    SelectItem 
} from '@/components/ui';
import Plus from 'lucide-vue-next/dist/esm/icons/plus.js';
import Trash2 from 'lucide-vue-next/dist/esm/icons/trash-2.js';
import Loader2 from 'lucide-vue-next/dist/esm/icons/loader-circle.js';
import MenuSquare from 'lucide-vue-next/dist/esm/icons/square-menu.js';
import Save from 'lucide-vue-next/dist/esm/icons/save.js';
import RotateCcw from 'lucide-vue-next/dist/esm/icons/rotate-ccw.js';
import Undo2 from 'lucide-vue-next/dist/esm/icons/undo-2.js';
import Redo2 from 'lucide-vue-next/dist/esm/icons/redo-2.js';
import { parseResponse, ensureArray } from '../../../utils/responseParser';

const { t } = useI18n();
const router = useRouter();
const route = useRoute();
const toast = useToast();
const { confirm } = useConfirm();

const menus = ref<any[]>([]);
const showCreateModal = ref(false);
const selectedMenuId = ref<any>(null);
const isLoading = ref(true);
const trashedFilter = ref('without');
const trashedCount = ref(0);
const builderRef = ref<any>(null);

// Computed
const selectedMenu = computed(() => {
    return menus.value.find(m => m.id.toString() === selectedMenuId.value?.toString());
});

// Fetch all menus
const fetchMenus = async () => {
    isLoading.value = true;
    try {
        const response = await api.get('/admin/ja/menus', {
            params: {
                trashed: trashedFilter.value
            }
        });
        
        // Capture trashed count from meta
        trashedCount.value = response.data?.meta?.trashed_count ?? 0;

        const { data } = parseResponse(response);
        menus.value = ensureArray(data);

        // If a menu is selected but not in the list (e.g. filtered out), deselect it
        // exception: if we are in 'with' mode?
        if (selectedMenuId.value) {
            const exists = menus.value.find(m => m.id.toString() === selectedMenuId.value?.toString());
            if (!exists) {
                // Try to keep selection if plausible? No, safer to deselect or let user reselect
                // But don't auto-deselect if checking history?
                // For now, if filtered out, it's gone.
                // But wait, if I switch filter to 'trashed only', active menu disappears.
                // selectedMenu becomes undefined.
                // MenuBuilder handles !menuId.
                // So valid.
            }
        }
        
        // Auto-select logic
        if (menus.value.length > 0) {
            if (!selectedMenuId.value) {
                selectedMenuId.value = menus.value[0].id.toString();
            }
        } else {
            selectedMenuId.value = null;
        }
    } catch (error: any) {
        logger.error('Failed to fetch menus:', error);
        toast.error.action(t('features.menus.messages.loadingFailed') || 'Failed to load menus');
    } finally {
        isLoading.value = false;
    }
};

const openCreateModal = () => {
    showCreateModal.value = true;
};

const handleMenuCreated = async (newMenu: any) => {
    showCreateModal.value = false;
    await fetchMenus();
    if (newMenu && newMenu.id) {
        selectedMenuId.value = newMenu.id.toString();
    }
    // Toast is already shown in MenuModal.vue
};

const handleSelectMenu = (menuId: any) => {
    selectedMenuId.value = menuId;
};

const handleMenuUpdated = async () => {
    const currentId = selectedMenuId.value;
    await fetchMenus();
    selectedMenuId.value = currentId;
};

const deleteCurrentMenu = async () => {
    if (!selectedMenu.value) return;
    const isTrashed = !!selectedMenu.value.deleted_at;

    const confirmed = await confirm({
        title: isTrashed ? t('common.actions.forceDelete') : t('features.menus.actions.delete'),
        message: isTrashed 
            ? `Are you sure you want to PERMANENTLY delete ${selectedMenu.value.name}?` 
            : t('features.menus.messages.deleteConfirm', { name: selectedMenu.value.name }),
        variant: 'danger',
        confirmText: isTrashed ? t('common.actions.forceDelete') : t('common.actions.delete'),
    });

    if (!confirmed) return;

    try {
        if (isTrashed) {
             await api.delete(`/admin/ja/menus/${selectedMenuId.value}/force-delete`);
             toast.success.action(t('common.messages.success.deleted', { item: 'Menu' }));
        } else {
            await api.delete(`/admin/ja/menus/${selectedMenuId.value}`);
            toast.success.delete(t('features.menus.title'));
        }
        selectedMenuId.value = null;
        await fetchMenus();
    } catch (error: any) {
        logger.error('Error deleting menu:', error);
        toast.error.delete(error, t('features.menus.title'));
    }
};

const restoreCurrentMenu = async () => {
    if (!selectedMenu.value) return;

     const confirmed = await confirm({
        title: t('common.actions.restore'),
        message: `Restore ${selectedMenu.value.name}?`,
        variant: 'info',
        confirmText: t('common.actions.restore'),
    });

    if (!confirmed) return;

    try {
        await api.post(`/admin/ja/menus/${selectedMenuId.value}/restore`);
        toast.success.restore('Menu');
        await fetchMenus();
    } catch (error: any) {
         logger.error('Error restoring menu:', error);
        toast.error.fromResponse(error);
    }
};

onMounted(() => {
    fetchMenus();
});

watch(trashedFilter, () => {
    selectedMenuId.value = null;
    fetchMenus();
});
</script>
