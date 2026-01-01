<template>
    <div class="h-full flex flex-col">
        <!-- Header / Selection Area -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
            <div class="flex flex-col gap-1 w-full sm:w-auto">
                <h1 class="text-2xl font-bold text-foreground">{{ t('features.menus.title') }}</h1>
                <p class="text-sm text-muted-foreground">{{ t('features.menus.subtitle') }}</p>
            </div>
            
            <div class="flex items-center gap-2 w-full sm:w-auto">
                <div v-if="menus.length > 0" class="flex items-center gap-1.5 w-full sm:w-auto">
                    <Select v-model="trashedFilter" class="w-[140px]">
                         <SelectTrigger class="w-[140px]">
                            <SelectValue :placeholder="t('common.labels.status')" />
                        </SelectTrigger>
                        <SelectContent>
                             <SelectItem value="without">{{ t('common.labels.activeOnly') }}</SelectItem>
                             <SelectItem value="with">{{ t('common.labels.includesTrashed') }}</SelectItem>
                             <SelectItem value="only">{{ t('common.labels.trashedOnly') }}</SelectItem>
                        </SelectContent>
                    </Select>
                    <Select v-model="selectedMenuId" :disabled="loading">
                        <SelectTrigger class="w-full sm:w-[180px]">
                            <SelectValue :placeholder="t('features.menus.actions.selectMenu')" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem v-for="menu in menus" :key="menu.id" :value="menu.id.toString()">
                                {{ menu.name }} <span v-if="menu.deleted_at">({{ t('common.labels.deleted') }})</span>
                            </SelectItem>
                        </SelectContent>
                    </Select>
                    <span class="text-xs text-muted-foreground whitespace-nowrap hidden lg:inline-block px-1">{{ t('features.menus.actions.or') }}</span>
                </div>
                
                <Button @click="openCreateModal" variant="outline" class="whitespace-nowrap px-3 h-10 border-dashed">
                    <Plus class="w-4 h-4 mr-2" />
                    {{ t('features.menus.actions.create') }}
                </Button>
                
                <!-- Integrated Builder Actions -->
                <div v-if="selectedMenuId" class="flex items-center gap-2 border-l pl-2 ml-2">
                     <Button 
                        variant="ghost" 
                        size="icon" 
                        @click="builderRef?.fetchMenu()"
                        class="h-10 w-10 text-muted-foreground hover:text-foreground"
                        :title="t('common.actions.refresh')"
                    >
                        <RotateCcw class="w-4 h-4" />
                    </Button>
                    
                    <Button
                        @click="builderRef?.saveMenu()"
                        :disabled="!builderRef || builderRef.saving || !builderRef.isDirty"
                        class="h-10 px-4 shadow-sm"
                    >
                        <Loader2 v-if="builderRef?.saving" class="w-4 h-4 mr-2 animate-spin" />
                        <Save v-else class="w-4 h-4 mr-2" />
                        {{ builderRef?.saving ? t('features.menus.actions.saving') : t('features.menus.actions.save') }}
                    </Button>

                    <Button
                        v-if="selectedMenu && selectedMenu.deleted_at"
                        @click="restoreCurrentMenu"
                        variant="ghost"
                        size="icon"
                        class="h-10 w-10 text-emerald-600 hover:text-emerald-700 hover:bg-emerald-50"
                        :title="t('common.actions.restore')"
                    >
                        <RotateCcw class="w-4 h-4" />
                    </Button>

                    <Button
                        @click="deleteCurrentMenu"
                        variant="ghost"
                        size="icon"
                        class="h-10 w-10 text-destructive hover:text-destructive hover:bg-destructive/10"
                        :title="selectedMenu && selectedMenu.deleted_at ? t('common.actions.forceDelete') : t('features.menus.actions.delete')"
                    >
                        <Trash2 class="w-4 h-4" />
                    </Button>
                </div>
            </div>
        </div>

        <!-- Main Content Area -->
        <div v-if="loading && !selectedMenuId" class="flex-1 flex items-center justify-center min-h-[400px]">
            <Loader2 class="w-8 h-8 animate-spin text-muted-foreground" />
        </div>

        <div v-else-if="menus.length === 0" class="flex-1 flex flex-col items-center justify-center min-h-[400px] border-2 border-dashed border-border rounded-lg bg-muted/10">
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

        <div v-else-if="selectedMenuId && selectedMenu && !selectedMenu.deleted_at" class="flex-1">
            <MenuBuilder 
                ref="builderRef"
                :menu-id="selectedMenuId" 
                :key="selectedMenuId"
                @menu-updated="handleMenuUpdated"
            />
        </div>
        
        <div v-else-if="selectedMenuId && selectedMenu && selectedMenu.deleted_at" class="flex-1 flex flex-col items-center justify-center min-h-[400px] border-2 border-dashed border-border rounded-lg bg-destructive/5">
            <Trash2 class="w-16 h-16 text-destructive/20 mb-4" />
            <h3 class="text-lg font-medium mb-2 text-destructive">{{ t('common.labels.deleted') }}</h3>
            <p class="text-sm text-muted-foreground mb-6 text-center max-w-[400px]">
                This menu has been deleted. Restore it to make changes.
            </p>
             <Button @click="restoreCurrentMenu" variant="outline" class="border-emerald-200 text-emerald-700 hover:bg-emerald-50">
                <RotateCcw class="w-4 h-4 mr-2" />
                {{ t('common.actions.restore') }}
            </Button>
        </div>

        <!-- Create Menu Modal -->
        <MenuModal
            v-if="showCreateModal"
            @close="showCreateModal = false"
            @saved="handleMenuCreated"
        />
    </div>
</template>

<script setup>
import { ref, onMounted, watch, computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { useRouter, useRoute } from 'vue-router';
import api from '../../../services/api';
import { useToast } from '../../../composables/useToast';
import { useConfirm } from '../../../composables/useConfirm';
import MenuBuilder from '../../../components/menus/MenuBuilder.vue';
import MenuModal from '../../../components/menus/MenuModal.vue';
import Button from '../../../components/ui/button.vue';
import Select from '../../../components/ui/select.vue';
import SelectTrigger from '../../../components/ui/select-trigger.vue';
import SelectValue from '../../../components/ui/select-value.vue';
import SelectContent from '../../../components/ui/select-content.vue';
import SelectItem from '../../../components/ui/select-item.vue';
import { 
    Plus, Trash2, Loader2, MenuSquare, Save, RotateCcw
} from 'lucide-vue-next';
import { parseResponse, ensureArray } from '../../../utils/responseParser';

const { t } = useI18n();
const router = useRouter();
const route = useRoute();
const toast = useToast();
const { confirm } = useConfirm();

const menus = ref([]);
const loading = ref(false);
const showCreateModal = ref(false);
const selectedMenuId = ref(null);
const trashedFilter = ref('without');
const builderRef = ref(null);

const selectedMenu = computed(() => menus.value.find(m => m.id.toString() === selectedMenuId.value));

const fetchMenus = async () => {
    loading.value = true;
    try {
        const params = {};
         if (trashedFilter.value !== 'without') {
            params.trashed = trashedFilter.value;
        }

        const response = await api.get('/admin/cms/menus', { params });
        const { data } = parseResponse(response);
        menus.value = ensureArray(data);
        
        // Auto-select logic
        if (menus.value.length > 0) {
            if (!selectedMenuId.value) {
                selectedMenuId.value = menus.value[0].id.toString();
            }
        } else {
            selectedMenuId.value = null;
        }
    } catch (error) {
        console.error('Failed to fetch menus:', error);
        toast.error.load(error);
    } finally {
        loading.value = false;
    }
};

const openCreateModal = () => {
    showCreateModal.value = true;
};

const handleMenuCreated = async (newMenu) => {
    showCreateModal.value = false;
    await fetchMenus();
    if (newMenu && newMenu.id) {
        selectedMenuId.value = newMenu.id.toString();
    }
    toast.success.create(t('features.menus.title'));
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
             await api.delete(`/admin/cms/menus/${selectedMenuId.value}/force-delete`);
             toast.success.action(t('common.messages.success.deleted', { item: 'Menu' }));
        } else {
            await api.delete(`/admin/cms/menus/${selectedMenuId.value}`);
            toast.success.delete(t('features.menus.title'));
        }
        selectedMenuId.value = null;
        await fetchMenus();
    } catch (error) {
        console.error('Error deleting menu:', error);
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
        await api.post(`/admin/cms/menus/${selectedMenuId.value}/restore`);
        toast.success.restore('Menu');
        await fetchMenus();
    } catch (error) {
         console.error('Error restoring menu:', error);
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
