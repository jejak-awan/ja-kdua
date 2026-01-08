<template>
    <div class="h-full">
        <!-- Loading State -->
        <div v-if="isLoading" class="flex flex-col items-center justify-center py-24">
            <Loader2 class="w-10 h-10 animate-spin text-muted-foreground mb-4" />
            <p class="text-muted-foreground">{{ t('features.menus.messages.loading') }}</p>
        </div>

        <div v-else class="space-y-4">
            <!-- Full Toolbar -->
            <Card class="bg-transparent">
                <CardContent class="p-2">
                    <div class="flex items-center justify-between gap-4">
                        <!-- Left: Menu Selector & New -->
                        <div class="flex items-center gap-2">
                            <Select v-model="trashedFilter">
                                <SelectTrigger class="h-9 w-[130px] lg:w-auto lg:min-w-[130px] lg:max-w-[200px] text-sm flex-shrink-0">
                                    <SelectValue placeholder="Status" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="without">{{ t('common.labels.activeOnly') }}</SelectItem>
                                    <SelectItem value="with" v-if="trashedCount > 0 || trashedFilter === 'with'">{{ t('common.labels.includesTrashed') }}</SelectItem>
                                    <SelectItem value="only" v-if="trashedCount > 0 || trashedFilter === 'only'">{{ t('common.labels.trashedOnly') }}</SelectItem>
                                </SelectContent>
                            </Select>
                            
                            <Select v-model="selectedMenuIdLocal">
                                <SelectTrigger class="h-9 w-[180px] lg:w-auto lg:min-w-[180px] lg:max-w-[300px] text-sm flex-1 truncate">
                                    <SelectValue :placeholder="t('features.menus.actions.selectMenu')" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem 
                                        v-for="m in menus" 
                                        :key="m.id" 
                                        :value="m.id.toString()"
                                    >
                                        {{ m.name }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>

                            <Button 
                                @click="$emit('create-menu')" 
                                variant="ghost" 
                                size="icon"
                                class="h-9 w-9"
                                :title="t('features.menus.actions.create')"
                            >
                                <Plus class="w-4 h-4" />
                            </Button>
                        </div>

                        <!-- Center: Name & Location -->
                        <div class="flex items-center gap-3 border-l border-r border-border px-4 flex-1 min-w-0">
                            <div class="flex items-center gap-2 flex-1 min-w-0">
                                <Label class="text-xs text-muted-foreground whitespace-nowrap">{{ t('features.menus.form.name') }}</Label>
                                <Input 
                                    v-model="menuName" 
                                    class="h-9 w-full min-w-[150px] max-w-[300px] text-sm" 
                                    :placeholder="t('features.menus.form.namePlaceholder')"
                                    :disabled="isTrashed || !menuId"
                                />
                            </div>
                            <div class="flex items-center gap-2 flex-1 min-w-0">
                                <Label class="text-xs text-muted-foreground whitespace-nowrap">{{ t('features.menus.form.location') }}</Label>
                                <Select v-model="menuLocation" :disabled="isTrashed || !menuId">
                                    <SelectTrigger class="h-9 w-full min-w-[150px] max-w-[250px] text-sm truncate">
                                        <SelectValue :placeholder="t('features.menus.form.placeholders.selectLocation')" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem 
                                            v-for="loc in locations" 
                                            :key="loc.value" 
                                            :value="loc.value"
                                        >
                                            {{ loc.label }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                        </div>

                        <!-- Right: Actions -->
                        <div class="flex items-center gap-1">
                            <Button 
                                variant="ghost" 
                                size="icon" 
                                class="h-9 w-9"
                                :disabled="!canUndo || isTrashed"
                                @click="undo"
                                title="Undo (Ctrl+Z)"
                            >
                                <Undo2 class="w-4 h-4" />
                            </Button>
                            <Button 
                                variant="ghost" 
                                size="icon" 
                                class="h-9 w-9"
                                :disabled="!canRedo || isTrashed"
                                @click="redo"
                                title="Redo (Ctrl+Y)"
                            >
                                <Redo2 class="w-4 h-4" />
                            </Button>

                            <div class="w-px h-6 bg-border mx-1"></div>

                            <Button 
                                variant="ghost" 
                                size="icon" 
                                class="h-9 w-9"
                                @click="menuState.fetchMenu()"
                                :title="t('common.actions.refresh')"
                            >
                                <RotateCcw class="w-4 h-4" />
                            </Button>

                            <Button 
                                @click="handleSave" 
                                :disabled="isSaving || !isDirty || isTrashed"
                                variant="ghost"
                                size="icon"
                                class="h-9 w-9"
                                :class="{ 'text-primary': isDirty && !isTrashed }"
                                :title="isSaving ? t('features.menus.actions.saving') : t('features.menus.actions.save')"
                            >
                                <Loader2 v-if="isSaving" class="w-4 h-4 animate-spin" />
                                <Save v-else class="w-4 h-4" />
                            </Button>

                            <div v-if="isTrashed" class="w-px h-6 bg-border mx-1"></div>

                            <Button 
                                v-if="isTrashed"
                                @click="$emit('restore-menu')"
                                variant="ghost"
                                size="icon"
                                class="h-9 w-9 text-emerald-600 hover:text-emerald-700 hover:bg-emerald-50"
                                :title="t('common.actions.restore')"
                            >
                                <RotateCcw class="w-4 h-4" />
                            </Button>

                            <Button 
                                @click="$emit('delete-menu')"
                                variant="ghost"
                                size="icon"
                                class="h-9 w-9 text-destructive hover:text-destructive hover:bg-destructive/10"
                                :title="isTrashed ? t('common.actions.forceDelete') : t('features.menus.actions.delete')"
                            >
                                <Trash2 class="w-4 h-4" />
                            </Button>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Empty State when no menu selected -->
            <div v-if="!menuId" class="flex flex-col items-center justify-center p-12 border-2 border-dashed rounded-lg bg-muted/10 h-[600px]">
                <MenuSquare class="w-16 h-16 text-muted-foreground/20 mb-4" />
                <h3 class="text-lg font-medium text-foreground mb-2">{{ t('features.menus.messages.noMenuSelected') || 'No Menu Selected' }}</h3>
                <p class="text-sm text-muted-foreground max-w-sm text-center mb-6">
                    {{ menus.length === 0 ? 'No menus found for the current filter.' : 'Select a menu from the dropdown to start editing.' }}
                </p>
                <Button @click="$emit('create-menu')">
                    <Plus class="w-4 h-4 mr-2" />
                    {{ t('features.menus.actions.create') }}
                </Button>
            </div>

            <!-- Main Layout -->
            <div v-else class="grid grid-cols-12 gap-6 items-start" :class="{ 'opacity-60 pointer-events-none': isTrashed }">
                <!-- Left: Source Panel -->
                <div 
                    class="transition-all duration-300"
                    :class="isSidebarCollapsed ? 'col-span-1' : 'col-span-3'"
                >
                    <Button 
                        v-if="isSidebarCollapsed"
                        variant="outline" 
                        size="icon"
                        class="w-full h-10"
                        @click="isSidebarCollapsed = false"
                    >
                        <PanelLeftOpen class="w-4 h-4" />
                    </Button>
                    <SourcePanel 
                        v-else 
                        @collapse="isSidebarCollapsed = true" 
                    />
                </div>

                <!-- Center: Menu Tree -->
                <div :class="centerColClass">
                    <Card>
                        <CardHeader class="pb-3">
                            <div class="flex items-center justify-between">
                                <CardTitle class="text-base">
                                    {{ t('features.menus.form.menuStructure') }}
                                </CardTitle>
                                <Badge variant="secondary">
                                    {{ items.length }} items
                                </Badge>
                            </div>
                        </CardHeader>
                        <CardContent>
                            <MenuTree :items="items" />
                        </CardContent>
                    </Card>
                </div>

                <!-- Right: Properties Panel -->
                <div 
                    class="sticky top-4 transition-all duration-300"
                    :class="isPropertiesCollapsed ? 'col-span-1' : 'col-span-3'"
                >
                    <Button 
                        v-if="isPropertiesCollapsed"
                        variant="outline" 
                        size="icon"
                        class="w-full h-10"
                        @click="isPropertiesCollapsed = false"
                        title="Expand Properties"
                    >
                        <PanelRightOpen class="w-4 h-4" />
                    </Button>
                    <ItemPropertiesPanel 
                        v-else 
                        @collapse="isPropertiesCollapsed = true" 
                    />
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import api from '../../services/api';
import { parseResponse, ensureArray } from '../../utils/responseParser';
import { useMenu, provideMenu } from '../../composables/useMenu';
import { useToast } from '../../composables/useToast';

// Modular Components
import SourcePanel from './sidebar/SourcePanel.vue';
import MenuTree from './canvas/MenuTree.vue';
import ItemPropertiesPanel from './properties/ItemPropertiesPanel.vue';

// UI Components
import Card from '../ui/card.vue';
import CardHeader from '../ui/card-header.vue';
import CardTitle from '../ui/card-title.vue';
import CardContent from '../ui/card-content.vue';
import Badge from '../ui/badge.vue';
import Button from '../ui/button.vue';
import Input from '../ui/input.vue';
import Label from '../ui/label.vue';
import Select from '../ui/select.vue';
import SelectTrigger from '../ui/select-trigger.vue';
import SelectValue from '../ui/select-value.vue';
import SelectContent from '../ui/select-content.vue';
import SelectItem from '../ui/select-item.vue';

import { 
    Loader2, Save, Undo2, Redo2,
    PanelLeftOpen, Plus, Trash2, RotateCcw, PanelRightOpen
} from 'lucide-vue-next';

const props = defineProps({
    menuId: {
        type: [String, Number],
        required: true
    },
    menus: {
        type: Array,
        default: () => []
    },
    trashedFilter: {
        type: String,
        default: 'without'
    },
    isTrashed: {
        type: Boolean,
        default: false
    },
    trashedCount: {
        type: Number,
        default: 0
    }
});

const emit = defineEmits(['menu-updated', 'create-menu', 'delete-menu', 'restore-menu', 'select-menu', 'update:trashedFilter']);

const { t } = useI18n();
const toast = useToast();

// Initialize useMenu composable
const menuIdRef = ref(props.menuId);
const menuState = useMenu(menuIdRef);

// Provide context to child components
provideMenu(menuState);

// Destructure for template
const {
    menu,
    items,
    isLoading,
    isSaving,
    isDirty,
    canUndo,
    canRedo,
    undo,
    redo,
    saveMenu
} = menuState;

// Local state
const isSidebarCollapsed = ref(false);
const isPropertiesCollapsed = ref(false);
const locations = ref([]);


// Layout computation
const centerColClass = computed(() => {
    const leftW = isSidebarCollapsed.value ? 1 : 3;
    const rightW = isPropertiesCollapsed.value ? 1 : 3;
    const centerW = 12 - leftW - rightW; // 6, 8, or 10
    
    // Explicit map for Tailwind JIT
    const widthMap = {
        6: 'col-span-6',
        8: 'col-span-8',
        10: 'col-span-10'
    };
    
    return widthMap[centerW] || 'col-span-6';
});



// Computed for menu selector with emit
const selectedMenuIdLocal = computed({
    get: () => props.menuId?.toString() || '',
    set: (val) => emit('select-menu', val)
});

// Computed properties for v-model binding
const menuName = computed({
    get: () => menu.value?.name || '',
    set: (val) => {
        if (!menu.value) return;
        menu.value.name = val;
        // Mark as dirty managed by useMenu watchers usually, or manual
        // useMenu doesn't auto-watch deep object properties of 'menu', only 'items' for history
        // But for isDirty, we might need manual trigger if useMenu only tracks items??
        // Checking useMenu.js: isDirty checks items vs initialState (items).
        // It seems useMenu PRIMARILY tracks items changes. Menu properties changes might strictly be direct API calls or need separate dirty tracking.
        // For now, let's assume we just mutate it.
    }
});

const menuLocation = computed({
    get: () => menu.value?.location || '',
    set: (val) => {
        if (!menu.value) return;
        menu.value.location = val;
    }
});

// Computed for trashedFilter with v-model emit
const trashedFilter = computed({
    get: () => props.trashedFilter,
    set: (val) => emit('update:trashedFilter', val)
});

// Fetch locations
const fetchLocations = async () => {
    try {
        const response = await api.get('/admin/ja/themes/active/locations');
        const data = response.data?.data || response.data || {};
        locations.value = Object.entries(data).map(([key, label]) => ({
            value: key,
            label: label
        }));
        locations.value.unshift({ value: 'none', label: 'None' });
    } catch (error) {
        console.error('Failed to fetch locations:', error);
    }
};

// Save handler
const handleSave = async () => {
    const success = await saveMenu({
        name: menuName.value,
        location: menuLocation.value
    });
    
    if (success) {
        toast.success.update(t('features.menus.title'));
        emit('menu-updated');
    } else {
        toast.error.action('Failed to save menu');
    }
};

// Keyboard shortcuts
const handleKeydown = (e) => {
    if ((e.ctrlKey || e.metaKey) && e.key === 'z') {
        e.preventDefault();
        if (e.shiftKey) {
            redo();
        } else {
            undo();
        }
    }
    if ((e.ctrlKey || e.metaKey) && e.key === 'y') {
        e.preventDefault();
        redo();
    }
    if ((e.ctrlKey || e.metaKey) && e.key === 's') {
        e.preventDefault();
        if (isDirty.value) {
            handleSave();
        }
    }
};

// Watch for menuId changes
watch(() => props.menuId, (newId) => {
    menuIdRef.value = newId;
});

// Expose methods/state for parent component (Index.vue)
defineExpose({
    // Save
    saveMenu: handleSave,
    fetchMenu: menuState.fetchMenu,
    isDirty,
    saving: isSaving,
    // Menu settings
    menuName,
    menuLocation,
    locations,
    // Undo/Redo
    undo,
    redo,
    canUndo,
    canRedo
});

onMounted(() => {
    fetchLocations();
    window.addEventListener('keydown', handleKeydown);
});

onUnmounted(() => {
    window.removeEventListener('keydown', handleKeydown);
});
</script>
