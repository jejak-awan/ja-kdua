<template>
    <div class="h-full">
        <div v-if="loading" class="flex flex-col items-center justify-center py-24">
            <Loader2 class="w-10 h-10 animate-spin text-muted-foreground mb-4" />
            <p class="text-muted-foreground">{{ t('features.menus.messages.loading') }}</p>
        </div>

        <div v-else class="space-y-6">
            
            <!-- Settings Card -->
            <Card>
                <CardHeader class="pb-3">
                    <CardTitle class="text-base">{{ t('features.menus.form.settings') }}</CardTitle>
                </CardHeader>
                <CardContent>
                    <form @submit.prevent="saveMenu" class="grid grid-cols-1 md:grid-cols-2 gap-6 items-start">
                        <div class="space-y-1.5">
                            <Label>
                                {{ t('features.menus.form.name') }} <span class="text-red-500">*</span>
                            </Label>
                            <Input
                                v-model="menuForm.name"
                                type="text"
                                required
                                :class="{ 'border-destructive focus-visible:ring-destructive': errors.name }"
                            />
                        </div>
                        <div class="space-y-1.5">
                            <Label>
                                {{ t('features.menus.form.location') }}
                            </Label>
                            <Select v-model="menuForm.location">
                                <SelectTrigger>
                                    <SelectValue :placeholder="t('features.menus.form.placeholders.location')" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem 
                                        v-for="loc in locationOptions" 
                                        :key="loc.value" 
                                        :value="loc.value"
                                    >
                                        {{ loc.label }}
                                    </SelectItem>
                                    <SelectItem value="none">{{ t('features.menus.form.placeholders.none') }}</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                    </form>
                </CardContent>
            </Card>

            <!-- Builder Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
                
                <!-- Left Column: Source Items (Sticky) -->
                <div class="lg:col-span-4 lg:sticky lg:top-4 space-y-4">
                    <Card class="border-t-4 border-t-primary/20">
                        <CardHeader class="pb-3">
                            <CardTitle class="text-base">{{ t('features.menus.form.addItems') }}</CardTitle>
                        </CardHeader>
                        <CardContent class="p-0">
                            <Accordion type="multiple" class="w-full" :default-value="['pages']">
                                <!-- Pages -->
                                <AccordionItem value="pages">
                                    <AccordionTrigger class="px-4 py-3 hover:no-underline hover:bg-muted/50">
                                        <div class="flex items-center gap-2">
                                            <FileText class="w-4 h-4 text-primary" />
                                            <span>{{ t('features.menus.form.types.page') }}</span>
                                            <Badge variant="secondary" class="ml-2">{{ pages.length }}</Badge>
                                        </div>
                                    </AccordionTrigger>
                                    <AccordionContent class="px-4 pb-4">
                                        <div class="max-h-[300px] overflow-y-auto pr-2 custom-scrollbar">
                                            <draggable
                                                :list="pages"
                                                :group="{ name: 'menu', pull: 'clone', put: false }"
                                                :clone="(item) => cloneSourceItem(item, 'page')"
                                                item-key="id"
                                                class="space-y-2"
                                            >
                                                <template #item="{ element }">
                                                    <div class="flex items-center gap-3 p-3 text-sm bg-card border border-border rounded-md cursor-move hover:border-primary/50 hover:shadow-sm transition-shadow group">
                                                        <GripVertical class="w-4 h-4 text-muted-foreground group-hover:text-foreground" />
                                                        <span class="font-medium truncate">{{ element.title }}</span>
                                                    </div>
                                                </template>
                                            </draggable>
                                            <div v-if="pages.length === 0" class="text-center py-4 text-muted-foreground text-xs">
                                                {{ t('features.menus.form.noPages') }}
                                            </div>
                                        </div>
                                    </AccordionContent>
                                </AccordionItem>

                                <!-- Posts -->
                                <AccordionItem value="posts">
                                    <AccordionTrigger class="px-4 py-3 hover:no-underline hover:bg-muted/50">
                                        <div class="flex items-center gap-2">
                                            <File class="w-4 h-4 text-orange-500" />
                                            <span>{{ t('features.menus.form.types.post') }}</span>
                                            <Badge variant="secondary" class="ml-2">{{ posts.length }}</Badge>
                                        </div>
                                    </AccordionTrigger>
                                    <AccordionContent class="px-4 pb-4">
                                        <div class="max-h-[300px] overflow-y-auto pr-2 custom-scrollbar">
                                            <draggable
                                                :list="posts"
                                                :group="{ name: 'menu', pull: 'clone', put: false }"
                                                :clone="(item) => cloneSourceItem(item, 'post')"
                                                item-key="id"
                                                class="space-y-2"
                                            >
                                                <template #item="{ element }">
                                                    <div class="flex items-center gap-3 p-3 text-sm bg-card border border-border rounded-md cursor-move hover:border-orange-500/50 dark:hover:border-orange-500/30 hover:shadow-sm transition-shadow group">
                                                        <GripVertical class="w-4 h-4 text-muted-foreground group-hover:text-foreground" />
                                                        <span class="font-medium truncate">{{ element.title }}</span>
                                                    </div>
                                                </template>
                                            </draggable>
                                            <div v-if="posts.length === 0" class="text-center py-4 text-muted-foreground text-xs">
                                                {{ t('features.menus.form.noPosts') }}
                                            </div>
                                        </div>
                                    </AccordionContent>
                                </AccordionItem>

                                <!-- Categories -->
                                <AccordionItem value="categories">
                                    <AccordionTrigger class="px-4 py-3 hover:no-underline hover:bg-muted/50">
                                        <div class="flex items-center gap-2">
                                            <Tag class="w-4 h-4 text-blue-500" />
                                            <span>{{ t('features.menus.form.types.category') }}</span>
                                            <Badge variant="secondary" class="ml-2">{{ categories.length }}</Badge>
                                        </div>
                                    </AccordionTrigger>
                                    <AccordionContent class="px-4 pb-4">
                                        <div class="max-h-[300px] overflow-y-auto pr-2 custom-scrollbar">
                                            <draggable
                                                :list="categories"
                                                :group="{ name: 'menu', pull: 'clone', put: false }"
                                                :clone="(item) => cloneSourceItem(item, 'category')"
                                                item-key="id"
                                                class="space-y-2"
                                            >
                                                <template #item="{ element }">
                                                    <div class="flex items-center gap-3 p-3 text-sm bg-card border border-border rounded-md cursor-move hover:border-blue-500/50 dark:hover:border-blue-500/30 hover:shadow-sm transition-shadow group">
                                                        <GripVertical class="w-4 h-4 text-muted-foreground group-hover:text-foreground" />
                                                        <span class="font-medium truncate">{{ element.name }}</span>
                                                    </div>
                                                </template>
                                            </draggable>
                                            <div v-if="categories.length === 0" class="text-center py-4 text-muted-foreground text-xs">
                                                {{ t('features.menus.form.noCategories') }}
                                            </div>
                                        </div>
                                    </AccordionContent>
                                </AccordionItem>

                                <!-- Custom Link -->
                                <AccordionItem value="custom">
                                    <AccordionTrigger class="px-4 py-3 hover:no-underline hover:bg-muted/50">
                                        <div class="flex items-center gap-2">
                                            <LinkIcon class="w-4 h-4 text-emerald-500" />
                                            <span>{{ t('features.menus.form.customLink') }}</span>
                                        </div>
                                    </AccordionTrigger>
                                    <AccordionContent class="px-4 pb-4 pt-2">
                                        <div class="space-y-3">
                                            <div class="space-y-1.5">
                                                <Label class="text-xs">{{ t('features.menus.form.url') }}</Label>
                                                <Input v-model="customLink.url" class="h-8" placeholder="https://" />
                                            </div>
                                            <div class="space-y-1.5">
                                                <Label class="text-xs">{{ t('features.menus.form.linkText') }}</Label>
                                                <Input v-model="customLink.title" class="h-8" :placeholder="t('features.menus.form.labelPlaceholder')" />
                                            </div>
                                            <Button size="sm" class="w-full mt-2" @click="addCustomLink" :disabled="!customLink.title">
                                                <PlusCircle class="w-3.5 h-3.5 mr-2" />
                                                {{ t('features.menus.actions.addToMenu') }}
                                            </Button>
                                        </div>
                                    </AccordionContent>
                                </AccordionItem>
                            </Accordion>
                        </CardContent>
                    </Card>
                </div>

                <!-- Right Column: Menu Structure -->
                <div class="lg:col-span-8 space-y-4">
                    <Card class="min-h-[600px] flex flex-col">
                        <CardHeader class="flex flex-row items-center justify-between border-b pb-4">
                            <span class="text-xs text-muted-foreground mr-2">
                                {{ t('features.menus.messages.dragHelp') }}
                            </span>
                             <Button
                                size="sm"
                                variant="outline"
                                @click="addMenuItem"
                            >
                                <Plus class="w-4 h-4 mr-2" />
                                {{ t('features.menus.actions.createItem') }}
                            </Button>
                        </CardHeader>
                        <CardContent class="flex-1 bg-muted/10 p-6 relative">
                            <div v-if="nestedItems.length === 0" class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none z-0">
                                <div class="bg-background p-4 rounded-full mb-4 shadow-sm">
                                    <MenuSquare class="w-10 h-10 text-muted-foreground/40" />
                                </div>
                                <p class="text-muted-foreground font-medium mb-2">{{ t('features.menus.messages.emptyItems') }}</p>
                                <p class="text-xs text-muted-foreground">{{ t('features.menus.form.dragStart') }}</p>
                            </div>
                            
                            <MenuItemTree
                                :items="nestedItems"
                                :all-items="flatItemsList"
                                @delete="deleteMenuItem"
                                @change="handleTreeChange"
                                @parent-change="handleParentChange"
                                class="min-h-[300px] w-full z-10 relative"
                            />
                        </CardContent>
                    </Card>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, computed, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import api from '../../services/api'; // adjusted import path
import { useToast } from '../../composables/useToast'; // adjusted import path
import { useConfirm } from '../../composables/useConfirm'; // adjusted import path
import { useFormValidation } from '../../composables/useFormValidation'; // adjusted import path
import { menuSchema } from '../../schemas'; // adjusted import path
import Button from '../../components/ui/button.vue'; // adjusted import path
import Input from '../../components/ui/input.vue'; // adjusted import path
import Label from '../../components/ui/label.vue'; // adjusted import path
import Select from '../../components/ui/select.vue'; // adjusted import path
import SelectTrigger from '../../components/ui/select-trigger.vue'; // adjusted import path
import SelectValue from '../../components/ui/select-value.vue'; // adjusted import path
import SelectContent from '../../components/ui/select-content.vue'; // adjusted import path
import SelectItem from '../../components/ui/select-item.vue'; // adjusted import path
import Card from '../../components/ui/card.vue'; // adjusted import path
import CardHeader from '../../components/ui/card-header.vue'; // adjusted import path
import CardTitle from '../../components/ui/card-title.vue'; // adjusted import path
import CardContent from '../../components/ui/card-content.vue'; // adjusted import path
import { 
    Plus, Save, RotateCcw,
    Loader2, MenuSquare,
    FileText, Tag, Link as LinkIcon, 
    GripVertical, PlusCircle, File
} from 'lucide-vue-next';
import { parseResponse, ensureArray, parseSingleResponse } from '../../utils/responseParser'; // adjusted import path
import MenuItemTree from '../../components/menus/MenuItemTree.vue'; // adjusted import path
import draggable from 'vuedraggable';
import Accordion from '../../components/ui/accordion.vue'; // adjusted import path
import AccordionContent from '../../components/ui/accordion-content.vue'; // adjusted import path
import AccordionItem from '../../components/ui/accordion-item.vue'; // adjusted import path
import AccordionTrigger from '../../components/ui/accordion-trigger.vue'; // adjusted import path
import Badge from '../../components/ui/badge.vue'; // adjusted import path

const { t } = useI18n();
const { confirm } = useConfirm();
const toast = useToast();
const { errors, validateWithZod, setErrors, clearErrors } = useFormValidation(menuSchema);

const props = defineProps({
    menuId: {
        type: [String, Number],
        required: true
    }
});

const emit = defineEmits(['menu-updated']);

const menu = ref(null);
const loading = ref(false);
const saving = ref(false);
const nestedItems = ref([]);

const menuForm = ref({
    name: '',
    location: '',
});

const pages = ref([]);
const posts = ref([]);
const categories = ref([]);
const customLink = ref({ title: '', url: 'http://' });

const initialMenuForm = ref(null);
const initialNestedItems = ref([]);

const isDirty = computed(() => {
    if (!initialMenuForm.value) return false;
    const formChanged = JSON.stringify(menuForm.value) !== JSON.stringify(initialMenuForm.value);
    const itemsChanged = JSON.stringify(nestedItems.value) !== JSON.stringify(initialNestedItems.value);
    return formChanged || itemsChanged;
});

const fetchMenu = async () => {
    if (!props.menuId) return;

    loading.value = true;
    try {
        const response = await api.get(`/admin/ja/menus/${props.menuId}`);
        menu.value = parseSingleResponse(response) || {};
        menuForm.value = {
            name: menu.value.name || '',
            location: menu.value.location || 'none',
        };
        initialMenuForm.value = JSON.parse(JSON.stringify(menuForm.value));
        
        // Fetch menu items and build tree
        const itemsResponse = await api.get(`/admin/ja/menus/${props.menuId}/items`);
        const { data } = parseResponse(itemsResponse);
        const flatItems = ensureArray(data);
        nestedItems.value = buildTree(flatItems);
        initialNestedItems.value = JSON.parse(JSON.stringify(nestedItems.value));
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
            sort_order: index,
            title: item.title,
            url: item.url,
            icon: item.icon || null,
            css_class: item.css_class || null,
            description: item.description || null,
            badge: item.badge || null,
            badge_color: item.badge_color || 'primary',
            mega_menu_layout: item.mega_menu_layout || 'default',
            mega_menu_column: item.mega_menu_column || 0,
            open_in_new_tab: item.open_in_new_tab || false,
        });
        if (item.children && item.children.length > 0) {
            result = result.concat(flattenTree(item.children, item.id));
        }
    });
    return result;
};

const saveMenu = async () => {
    if (!validateWithZod(menuForm.value)) return;

    saving.value = true;
    clearErrors();
    try {
        const payload = {
            ...menuForm.value,
            location: menuForm.value.location === 'none' ? '' : menuForm.value.location
        };
        await api.put(`/admin/ja/menus/${props.menuId}`, payload);
        await saveTree(nestedItems.value, null);
        
        initialMenuForm.value = JSON.parse(JSON.stringify(menuForm.value));
        toast.success.update(t('features.menus.title'));
        emit('menu-updated', { ...menu.value, ...menuForm.value });
        await fetchMenu();
    } catch (error) {
        if (error.response?.status === 422) {
            setErrors(error.response.data.errors || {});
        } else {
            toast.error.fromResponse(error);
        }
    } finally {
        saving.value = false;
    }
};

const saveTree = async (items, parentId) => {
    for (let i = 0; i < items.length; i++) {
        const item = items[i];
        
        if (!item.id || item.id.toString().startsWith('temp_')) {
            const payload = {
                menu_id: props.menuId,
                parent_id: parentId, 
                title: item.title,
                type: item.type,
                target_id: item.target_id,
                url: item.url,
                icon: item.icon || null,
                css_class: item.css_class || null,
                description: item.description || null,
                badge: item.badge || null,
                badge_color: item.badge_color || 'primary',
                open_in_new_tab: item.open_in_new_tab || false,
                is_active: item.is_active || 1,
                image: item.image || null,
                mega_menu_layout: item.mega_menu_layout || 'default',
                mega_menu_column: item.mega_menu_column || 0,
                sort_order: i, 
            };
            
            const response = await api.post(`/admin/ja/menus/${props.menuId}/items`, payload);
            const newItem = response.data?.data || response.data;
            item.id = newItem.id; 
        }
        
        if (item.children && item.children.length > 0) {
            await saveTree(item.children, item.id);
        }
    }
    const reordered = flattenTree(nestedItems.value);
    await api.post(`/admin/ja/menus/${props.menuId}/reorder`, { items: reordered });
};

const addMenuItem = () => {
    // Add a new draft item
    const newItem = {
        id: null,
        title: t('features.menus.form.newLink'),
        type: 'custom',
        url: '#',
        children: [],
        target: null,
        css_class: '',
        is_active: 1,
        _isEditing: true, // Auto open
        _temp_id: 'temp_' + Date.now()
    };
    nestedItems.value.push(newItem);
};

const deleteMenuItem = async (item) => {
    const confirmed = await confirm({
        title: t('features.menus.actions.deleteItem'),
        message: t('features.menus.messages.deleteItemConfirm', { title: item.title || item.label }),
        variant: 'danger',
        confirmText: t('common.actions.delete'),
    });

    if (!confirmed) return;

    try {
        if (item.id && !item.id.toString().startsWith('temp_')) {
            await api.delete(`/admin/ja/menus/${props.menuId}/items/${item.id}`);
        }
        removeItemFromTree(nestedItems.value, item.id || item._temp_id);
        toast.success.action(t('features.menus.messages.itemDeleted'));
    } catch (error) {
        console.error('Failed to delete menu item:', error);
        toast.error.fromResponse(error);
    }
};

const removeItemFromTree = (items, id) => {
    const index = items.findIndex(i => (i.id === id) || (i._temp_id === id));
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

const handleTreeChange = (newItems) => {
    nestedItems.value = newItems;
};

// Flat list of all items for parent selector
const flatItemsList = computed(() => {
    const flatten = (items, depth = 0) => {
        let result = [];
        for (const item of items) {
            result.push({ ...item, _depth: depth });
            if (item.children && item.children.length > 0) {
                result = result.concat(flatten(item.children, depth + 1));
            }
        }
        return result;
    };
    return flatten(nestedItems.value);
});

// Handle parent change from dropdown
const handleParentChange = ({ item, newParentId }) => {
    const itemId = item.id || item._temp_id;
    
    // Remove item from current position
    removeItemFromTree(nestedItems.value, itemId);
    
    if (!newParentId || newParentId === 'root') {
        // Move to root
        nestedItems.value.push(item);
    } else {
        // Find new parent and add as child
        const addToParent = (items) => {
            for (const i of items) {
                if ((i.id && i.id.toString() === newParentId.toString()) || 
                    (i._temp_id && i._temp_id === newParentId)) {
                    if (!i.children) i.children = [];
                    i.children.push(item);
                    return true;
                }
                if (i.children && addToParent(i.children)) {
                    return true;
                }
            }
            return false;
        };
        addToParent(nestedItems.value);
    }
};

const locationOptions = ref([]);

const fetchLocations = async () => {
    try {
        const response = await api.get('/admin/ja/themes/active/locations');
        const data = response.data?.data || response.data || {};
        
        locationOptions.value = Object.entries(data).map(([key, label]) => ({
            value: key,
            label: label
        }));
    } catch (error) {
        console.error('Failed to fetch menu locations:', error);
    }
};

const fetchPages = async () => {
    try {
        // Updated to use same API logic as Edit.vue
        const response = await api.get('/admin/ja/contents?type=page&status=published');
        const { data } = parseResponse(response);
        pages.value = ensureArray(data);
    } catch (error) {
        console.error('Failed to fetch pages:', error);
    }
};

const fetchPosts = async () => {
    try {
        const response = await api.get('/admin/ja/contents?type=post&status=published');
        const { data } = parseResponse(response);
        posts.value = ensureArray(data);
    } catch (error) {
        console.error('Failed to fetch posts:', error);
    }
};

const fetchCategories = async () => {
    try {
        const response = await api.get('/admin/ja/categories');
        const { data } = parseResponse(response);
        categories.value = ensureArray(data);
    } catch (error) {
        console.error('Failed to fetch categories:', error);
    }
};

const cloneSourceItem = (item, type) => {
    return {
        id: null,
        title: item.title || item.name || item.label,
        type: type,
        target_id: item.id || null, 
        url: item.url || null,
        url: item.url || null,
        children: [],
        mega_menu_layout: 'default',
        mega_menu_column: 0,
        image: null,
        is_active: 1, 
        _temp_id: 'temp_' + Date.now() + Math.random().toString(36).substr(2, 9)
    };
};

const addCustomLink = () => {
    if (!customLink.value.title || !customLink.value.url) {
        toast.error.validation(t('features.menus.messages.enterUrlLabel'));
        return;
    }
    
    const newItem = cloneSourceItem({
        title: customLink.value.title,
        url: customLink.value.url,
        id: null
    }, 'custom');
    
    nestedItems.value.push(newItem);
    
    customLink.value = { title: '', url: 'http://' };
    toast.success.action(t('features.menus.messages.customLinkAdded'));
};

watch(() => props.menuId, (newId) => {
    if (newId) fetchMenu();
}, { immediate: true });

onMounted(() => {
    fetchLocations();
    fetchPages();
    fetchPosts();
    fetchCategories();
});

defineExpose({
    saveMenu,
    fetchMenu,
    saving,
    isDirty
});
</script>

<style scoped>
.custom-scrollbar {
  scrollbar-width: thin;
  scrollbar-color: hsl(var(--border)) transparent;
}
.custom-scrollbar::-webkit-scrollbar {
  width: 4px;
}
.custom-scrollbar::-webkit-scrollbar-track {
  background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background-color: hsl(var(--border));
  border-radius: 4px;
}
</style>
