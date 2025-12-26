<template>
    <div>
        <div class="mb-6 flex items-center justify-between">
            <div class="flex items-center gap-4">
                <Button
                    variant="ghost"
                    size="icon"
                    @click="router.push({ name: 'menus.index' })"
                >
                    <ChevronLeft class="w-5 h-5" />
                </Button>
                <div>
                    <h1 class="text-2xl font-bold text-foreground">{{ t('features.menus.form.editTitle') }}</h1>
                    <p v-if="menu" class="text-sm text-muted-foreground">{{ menu.name }}</p>
                </div>
            </div>
            <Button
                @click="addMenuItem"
            >
                <Plus class="w-4 h-4 mr-2" />
                {{ t('features.menus.actions.createItem') }}
            </Button>
        </div>

        <div v-if="loading" class="flex flex-col items-center justify-center py-24">
            <Loader2 class="w-10 h-10 animate-spin text-muted-foreground mb-4" />
            <p class="text-muted-foreground">{{ t('features.menus.messages.loading') }}</p>
        </div>

        <div v-else class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Menu Items Tree -->
            <div class="lg:col-span-2">
                <Card>
                    <CardHeader>
                        <CardTitle>{{ t('features.menus.form.items') }}</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div v-if="nestedItems.length === 0" class="text-center py-12">
                            <MenuSquare class="w-12 h-12 mx-auto text-muted-foreground/20 mb-4" />
                            <p class="text-muted-foreground">{{ t('features.menus.messages.emptyItems') }}</p>
                        </div>
                        <div v-else>
                            <MenuItemTree
                                :items="nestedItems"
                                @edit="editMenuItem"
                                @delete="deleteMenuItem"
                                @change="handleTreeChange"
                            />
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Menu Settings -->
            <div class="lg:col-span-1">
                <Card>
                    <CardHeader>
                        <CardTitle>{{ t('features.menus.form.settings') }}</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <form @submit.prevent="saveMenu" class="space-y-4">
                            <div class="space-y-2">
                                <Label>
                                    {{ t('features.menus.form.name') }} <span class="text-red-500">*</span>
                                </Label>
                                <Input
                                    v-model="menuForm.name"
                                    type="text"
                                    required
                                />
                            </div>
                            <div class="space-y-2">
                                <Label>
                                    {{ t('features.menus.form.location') }}
                                </Label>
                                <Input
                                    v-model="menuForm.location"
                                    type="text"
                                    :placeholder="t('features.menus.form.placeholders.location')"
                                />
                            </div>
                            <Button
                                type="submit"
                                :disabled="saving"
                                class="w-full mt-4"
                            >
                                <Loader2 v-if="saving" class="w-4 h-4 mr-2 animate-spin" />
                                <Save v-else class="w-4 h-4 mr-2" />
                                {{ saving ? t('features.menus.actions.saving') : t('features.menus.actions.save') }}
                            </Button>
                        </form>
                    </CardContent>
                </Card>
            </div>
        </div>

        <!-- Menu Item Modal -->
        <MenuItemModal
            v-if="showItemModal"
            @close="showItemModal = false"
            @saved="handleItemSaved"
            :item="editingItem"
            :menu-id="menuId"
        />
    </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useI18n } from 'vue-i18n';
import api from '../../../services/api';
import Button from '../../../components/ui/button.vue';
import Input from '../../../components/ui/input.vue';
import Label from '../../../components/ui/label.vue';
import Card from '../../../components/ui/card.vue';
import CardHeader from '../../../components/ui/card-header.vue';
import CardTitle from '../../../components/ui/card-title.vue';
import CardContent from '../../../components/ui/card-content.vue';
import { 
    ChevronLeft, Plus, Save, 
    Loader2, MenuSquare 
} from 'lucide-vue-next';

const { t } = useI18n();
import { parseResponse, ensureArray, parseSingleResponse } from '../../../utils/responseParser';
import MenuItemTree from '../../../components/menus/MenuItemTree.vue';
import MenuItemModal from '../../../components/menus/MenuItemModal.vue';

const route = useRoute();
const router = useRouter();
const menuId = route.params.id;

const menu = ref(null);
const loading = ref(false);
const saving = ref(false);
const showItemModal = ref(false);
const editingItem = ref(null);
const nestedItems = ref([]);

const menuForm = ref({
    name: '',
    location: '',
});

const fetchMenu = async () => {
    loading.value = true;
    try {
        const response = await api.get(`/admin/cms/menus/${menuId}`);
        menu.value = parseSingleResponse(response) || {};
        menuForm.value = {
            name: menu.value.name || '',
            location: menu.value.location || '',
        };
        
        // Fetch menu items and build tree
        const itemsResponse = await api.get(`/admin/cms/menus/${menuId}/items`);
        const { data } = parseResponse(itemsResponse);
        const flatItems = ensureArray(data);
        nestedItems.value = buildTree(flatItems);
    } catch (error) {
        console.error('Failed to fetch menu:', error);
    } finally {
        loading.value = false;
    }
};

const buildTree = (items, parentId = null) => {
    return items
        .filter(item => item.parent_id === parentId)
        .sort((a, b) => a.sort_order - b.sort_order)
        .map(item => ({
            ...item,
            children: buildTree(items, item.id)
        }));
};

const flattenTree = (items, parentId = null) => {
    let result = [];
    items.forEach((item, index) => {
        result.push({
            id: item.id,
            parent_id: parentId,
            sort_order: index
        });
        if (item.children && item.children.length > 0) {
            result = result.concat(flattenTree(item.children, item.id));
        }
    });
    return result;
};

const saveMenu = async () => {
    saving.value = true;
    try {
        // Save menu details
        await api.put(`/admin/cms/menus/${menuId}`, menuForm.value);
        
        // Save menu items order
        const reordered = flattenTree(nestedItems.value);
        await api.post(`/admin/cms/menus/${menuId}/reorder`, { items: reordered });
        
        alert(t('features.menus.actions.saved'));
        await fetchMenu();
    } catch (error) {
        console.error('Failed to save menu:', error);
        alert(t('features.menus.messages.saveFailed'));
    } finally {
        saving.value = false;
    }
};

const addMenuItem = () => {
    editingItem.value = null;
    showItemModal.value = true;
};

const editMenuItem = (item) => {
    editingItem.value = item;
    showItemModal.value = true;
};

const deleteMenuItem = async (item) => {
    if (!confirm(t('features.menus.messages.deleteItemConfirm', { title: item.title || item.label }))) {
        return;
    }

    try {
        await api.delete(`/admin/cms/menu-items/${item.id}`);
        // Remove from local tree to avoid full reload flicker
        removeItemFromTree(nestedItems.value, item.id);
    } catch (error) {
        console.error('Failed to delete menu item:', error);
        alert(t('features.menus.messages.deleteItemFailed'));
    }
};

const removeItemFromTree = (items, id) => {
    const index = items.findIndex(i => i.id === id);
    if (index > -1) {
        items.splice(index, 1);
        return true;
    }
    for (const item of items) {
        if (item.children && removeItemFromTree(item.children, id)) {
            return true;
        }
    }
    return false;
};

const handleItemSaved = () => {
    fetchMenu();
    showItemModal.value = false;
};

// Handle changes from MenuItemTree (drag-drop)
const handleTreeChange = (newItems) => {
    nestedItems.value = newItems;
};

onMounted(() => {
    fetchMenu();
});
</script>

