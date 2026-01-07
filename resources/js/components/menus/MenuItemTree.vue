<template>
    <draggable
        :list="items"
        group="menu"
        :item-key="getItemKey"
        class="space-y-3 min-h-[50px] pb-2"
        handle=".drag-handle"
        ghost-class="ghost"
        @change="handleChange"
    >
        <template #item="{ element }">
            <div class="border border-border rounded-lg bg-card relative shadow-sm transition-all hover:shadow-md">
                <!-- Item Header (Always Visible) -->
                <div class="flex items-center justify-between p-3 bg-card rounded-lg hover:bg-muted/30 transition-colors">
                    <div class="flex items-center space-x-3 flex-1 overflow-hidden">
                        <!-- Drag Handle -->
                        <div class="drag-handle cursor-move text-muted-foreground hover:text-foreground p-1.5 rounded-md hover:bg-muted transition-colors">
                            <GripVertical class="w-4 h-4" />
                        </div>
                        
                        <!-- Icon Display -->
                        <div v-if="element.icon" class="w-8 h-8 rounded-lg bg-primary/10 flex items-center justify-center shrink-0">
                            <component :is="getIconComponent(element.icon)" class="w-4 h-4 text-primary" />
                        </div>
                        
                        <!-- Title & Badges -->
                        <div class="flex flex-col gap-0.5 overflow-hidden">
                            <div class="font-medium text-sm truncate flex items-center gap-2">
                                {{ element.title || element.label || t('common.labels.untitled') }}
                                <Badge variant="outline" class="text-[10px] px-1.5 py-0 h-4 capitalize font-normal border-muted-foreground/30">
                                    {{ element.type }}
                                </Badge>
                                <Badge 
                                    v-if="element.badge" 
                                    :variant="element.badge_color || 'default'"
                                    class="text-[10px] px-1.5 py-0 h-4"
                                >
                                    {{ element.badge }}
                                </Badge>
                            </div>
                            <div v-if="element.description" class="text-[10px] text-muted-foreground truncate max-w-[200px]">
                                {{ element.description }}
                            </div>
                            <div v-else-if="element.url && element.type === 'custom'" class="text-[10px] text-muted-foreground truncate">
                                {{ element.url }}
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center gap-1">
                        <Badge v-if="element.children && element.children.length > 0" variant="secondary" class="text-[10px] mr-1">
                            {{ element.children.length }} sub
                        </Badge>
                        <Button
                            variant="ghost"
                            size="sm"
                            @click="toggleEdit(element)"
                            class="h-8 w-8 p-0"
                            :class="{ 'bg-muted text-foreground': element._isEditing }"
                        >
                            <ChevronDown 
                                class="w-4 h-4 transition-transform duration-200"
                                :class="{ 'rotate-180': element._isEditing }"
                            />
                        </Button>
                        <Button
                            variant="ghost"
                            size="sm"
                            @click="$emit('delete', element)"
                            class="h-8 w-8 p-0 text-destructive hover:text-destructive hover:bg-destructive/10"
                        >
                            <Trash2 class="w-4 h-4" />
                        </Button>
                    </div>
                </div>

                <!-- Inline Edit Form (Accordion Body) -->
                <div v-if="element._isEditing" class="border-t border-border p-4 bg-muted/10 space-y-4">
                    <!-- Parent Selector -->
                    <div class="p-3 bg-primary/5 rounded-lg border border-primary/20">
                        <div class="flex items-center gap-4">
                            <div class="flex-1 space-y-1.5">
                                <Label class="text-xs font-medium flex items-center gap-2">
                                    <FolderTree class="w-3.5 h-3.5" />
                                    Parent Item
                                </Label>
                                <Select 
                                    :model-value="getCurrentParentId(element)" 
                                    @update:model-value="(val) => changeParent(element, val)"
                                >
                                    <SelectTrigger class="h-8 bg-background">
                                        <SelectValue placeholder="Select parent..." />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="root">
                                            <span class="flex items-center gap-2">
                                                <Home class="w-3.5 h-3.5" />
                                                Root Level (No Parent)
                                            </span>
                                        </SelectItem>
                                        <template v-for="parentItem in getAvailableParents(element)" :key="parentItem.id || parentItem._temp_id">
                                            <SelectItem :value="(parentItem.id || parentItem._temp_id).toString()">
                                                <span class="flex items-center gap-2">
                                                    <span :style="{ paddingLeft: (parentItem._depth || 0) * 12 + 'px' }">
                                                        {{ '└ '.repeat(parentItem._depth || 0) }}{{ parentItem.title }}
                                                    </span>
                                                </span>
                                            </SelectItem>
                                        </template>
                                    </SelectContent>
                                </Select>
                            </div>
                        </div>
                    </div>

                    <!-- Row 1: Basic Info -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="space-y-1.5">
                            <Label class="text-xs font-medium">{{ t('features.menus.form.label') }}</Label>
                            <Input v-model="element.title" class="h-8 bg-background" />
                        </div>
                        
                        <div class="space-y-1.5" v-if="element.type === 'custom'">
                            <Label class="text-xs font-medium">{{ t('features.menus.form.url') }}</Label>
                            <Input v-model="element.url" class="h-8 bg-background" />
                        </div>

                        <div class="space-y-1.5">
                            <Label class="text-xs font-medium">Icon</Label>
                            <IconPicker v-model="element.icon" placeholder="Choose icon..." />
                        </div>
                    </div>

                    <!-- Row 2: Mega Menu Fields -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="space-y-1.5">
                            <Label class="text-xs font-medium">Description (Mega Menu)</Label>
                            <Textarea 
                                v-model="element.description" 
                                class="min-h-[60px] bg-background text-sm"
                                placeholder="Optional subtitle for mega menu dropdown..."
                            />
                        </div>
                        
                        <div class="space-y-4">
                            <div class="grid grid-cols-2 gap-4">
                                <div class="space-y-1.5">
                                    <Label class="text-xs font-medium">Badge Text</Label>
                                    <Input v-model="element.badge" class="h-8 bg-background" placeholder="e.g. NEW" />
                                </div>
                                <div class="space-y-1.5">
                                    <Label class="text-xs font-medium">Badge Color</Label>
                                    <Select v-model="element.badge_color">
                                        <SelectTrigger class="h-8">
                                            <SelectValue placeholder="Color" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="default">Default</SelectItem>
                                            <SelectItem value="primary">Primary</SelectItem>
                                            <SelectItem value="secondary">Secondary</SelectItem>
                                            <SelectItem value="destructive">Red</SelectItem>
                                            <SelectItem value="success">Green</SelectItem>
                                            <SelectItem value="warning">Yellow</SelectItem>
                                        </SelectContent>
                                    </Select>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div class="space-y-1.5">
                                    <Label class="text-xs font-medium">{{ t('features.menus.form.cssClasses') }}</Label>
                                    <Input v-model="element.css_class" class="h-8 bg-background" :placeholder="t('features.menus.form.placeholders.cssClasses')" />
                                </div>
                                <div class="space-y-1.5">
                                    <Label class="text-xs font-medium">{{ t('features.menus.form.openInNewTab') }}</Label>
                                    <div class="flex items-center h-8">
                                        <Switch 
                                            :checked="element.open_in_new_tab" 
                                            @update:checked="(val) => element.open_in_new_tab = val" 
                                        />
                                        <span class="ml-2 text-xs text-muted-foreground">{{ element.open_in_new_tab ? t('common.labels.yes') : t('common.labels.no') }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ALWAYS show nested children drop zone -->
                <div class="pl-6 pr-2 pb-2">
                    <div 
                        class="border-l-2 pl-3 min-h-[40px] transition-colors"
                        :class="element.children && element.children.length > 0 ? 'border-primary/30' : 'border-dashed border-muted-foreground/20'"
                    >
                        <!-- Nested draggable for children -->
                        <draggable
                            :list="ensureChildren(element)"
                            group="menu"
                            :item-key="getItemKey"
                            class="min-h-[36px] rounded-lg transition-colors"
                            :class="(!element.children || element.children.length === 0) ? 'bg-muted/20 border border-dashed border-muted-foreground/20 flex items-center justify-center' : 'space-y-2'"
                            handle=".drag-handle"
                            ghost-class="ghost"
                            @change="handleChange"
                        >
                            <template #item="{ element: child }">
                                <!-- Recursive child item -->
                                <div class="border border-border rounded-lg bg-card shadow-sm">
                                    <div class="flex items-center justify-between p-2.5 bg-card rounded-lg hover:bg-muted/30 transition-colors">
                                        <div class="flex items-center space-x-2 flex-1 overflow-hidden">
                                            <div class="drag-handle cursor-move text-muted-foreground hover:text-foreground p-1 rounded hover:bg-muted transition-colors">
                                                <GripVertical class="w-3.5 h-3.5" />
                                            </div>
                                            <div v-if="child.icon" class="w-6 h-6 rounded bg-primary/10 flex items-center justify-center shrink-0">
                                                <component :is="getIconComponent(child.icon)" class="w-3.5 h-3.5 text-primary" />
                                            </div>
                                            <div class="flex flex-col gap-0.5 overflow-hidden">
                                                <div class="font-medium text-xs truncate flex items-center gap-1.5">
                                                    {{ child.title || child.label }}
                                                    <Badge variant="outline" class="text-[9px] px-1 py-0 h-3.5 capitalize font-normal">{{ child.type }}</Badge>
                                                    <Badge v-if="child.badge" :variant="child.badge_color || 'default'" class="text-[9px] px-1 py-0 h-3.5">{{ child.badge }}</Badge>
                                                </div>
                                                <div v-if="child.description" class="text-[9px] text-muted-foreground truncate">{{ child.description }}</div>
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-0.5">
                                            <Button variant="ghost" size="sm" @click="toggleEdit(child)" class="h-7 w-7 p-0" :class="{ 'bg-muted': child._isEditing }">
                                                <ChevronDown class="w-3.5 h-3.5" :class="{ 'rotate-180': child._isEditing }" />
                                            </Button>
                                            <Button variant="ghost" size="sm" @click="$emit('delete', child)" class="h-7 w-7 p-0 text-destructive hover:bg-destructive/10">
                                                <Trash2 class="w-3.5 h-3.5" />
                                            </Button>
                                        </div>
                                    </div>
                                    <!-- Child Edit Panel -->
                                    <div v-if="child._isEditing" class="border-t p-3 bg-muted/10 space-y-3">
                                        <div class="grid grid-cols-2 gap-3">
                                            <div class="space-y-1">
                                                <Label class="text-[10px]">Label</Label>
                                                <Input v-model="child.title" class="h-7 text-xs" />
                                            </div>
                                            <div class="space-y-1" v-if="child.type === 'custom'">
                                                <Label class="text-[10px]">URL</Label>
                                                <Input v-model="child.url" class="h-7 text-xs" />
                                            </div>
                                            <div class="space-y-1">
                                                <Label class="text-[10px]">Icon</Label>
                                                <IconPicker v-model="child.icon" placeholder="Icon..." />
                                            </div>
                                            <div class="space-y-1">
                                                <Label class="text-[10px]">Description</Label>
                                                <Input v-model="child.description" class="h-7 text-xs" placeholder="Subtitle..." />
                                            </div>
                                        </div>
                                        <div class="grid grid-cols-3 gap-3">
                                            <div class="space-y-1">
                                                <Label class="text-[10px]">Badge</Label>
                                                <Input v-model="child.badge" class="h-7 text-xs" placeholder="NEW" />
                                            </div>
                                            <div class="space-y-1">
                                                <Label class="text-[10px]">Badge Color</Label>
                                                <Select v-model="child.badge_color">
                                                    <SelectTrigger class="h-7 text-xs"><SelectValue placeholder="Color" /></SelectTrigger>
                                                    <SelectContent>
                                                        <SelectItem value="default">Default</SelectItem>
                                                        <SelectItem value="primary">Primary</SelectItem>
                                                        <SelectItem value="destructive">Red</SelectItem>
                                                        <SelectItem value="success">Green</SelectItem>
                                                    </SelectContent>
                                                </Select>
                                            </div>
                                            <div class="space-y-1">
                                                <Label class="text-[10px]">New Tab</Label>
                                                <div class="flex items-center h-7">
                                                    <Switch :checked="child.open_in_new_tab" @update:checked="(val) => child.open_in_new_tab = val" />
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </draggable>
                        <!-- Empty state hint -->
                        <div v-if="!element.children || element.children.length === 0" class="text-[10px] text-muted-foreground py-2 text-center select-none">
                            <CornerDownRight class="w-3 h-3 inline mr-1 opacity-50" />
                            Drop here to nest
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </draggable>
</template>

<script setup>
import { defineProps, defineEmits } from 'vue';
import { useI18n } from 'vue-i18n';
import draggable from 'vuedraggable';
import Button from '../ui/button.vue';
import Badge from '../ui/badge.vue';
import Input from '../ui/input.vue';
import Label from '../ui/label.vue';
import Switch from '../ui/switch.vue';
import Textarea from '../ui/textarea.vue';
import Select from '../ui/select.vue';
import SelectTrigger from '../ui/select-trigger.vue';
import SelectValue from '../ui/select-value.vue';
import SelectContent from '../ui/select-content.vue';
import SelectItem from '../ui/select-item.vue';
import IconPicker from '../ui/icon-picker.vue';
import { GripVertical, Trash2, ChevronDown, CornerDownRight, FolderTree, Home } from 'lucide-vue-next';
import * as LucideIcons from 'lucide-vue-next';

const { t } = useI18n();

const props = defineProps({
    items: {
        type: Array,
        required: true,
    },
    allItems: {
        type: Array,
        default: () => [],
    },
});

const emit = defineEmits(['delete', 'change', 'parent-change']);

const getItemKey = (item) => {
    return item.id || item._temp_id || `temp_${Math.random()}`;
};

const getIconComponent = (iconName) => {
    return LucideIcons[iconName] || LucideIcons.Circle;
};

const ensureChildren = (element) => {
    if (!element.children) {
        element.children = [];
    }
    return element.children;
};

const handleChange = () => {
    emit('change', props.items);
};

const toggleEdit = (element) => {
    if (typeof element._isEditing === 'undefined') {
        element._isEditing = false;
    }
    element._isEditing = !element._isEditing;
};

// Parent selector helpers
const getCurrentParentId = (element) => {
    // Check if this element is nested (has a parent)
    const findParent = (items, targetId, parent = null) => {
        for (const item of items) {
            const itemId = item.id || item._temp_id;
            if (itemId === targetId) {
                return parent ? (parent.id || parent._temp_id).toString() : 'root';
            }
            if (item.children && item.children.length > 0) {
                const found = findParent(item.children, targetId, item);
                if (found !== null) return found;
            }
        }
        return null;
    };
    const targetId = element.id || element._temp_id;
    return findParent(props.items, targetId) || 'root';
};

const getAvailableParents = (element) => {
    const elementId = element.id || element._temp_id;
    // Return all items except self and descendants
    const isDescendant = (item, targetId) => {
        if ((item.id || item._temp_id) === targetId) return true;
        if (item.children) {
            for (const child of item.children) {
                if (isDescendant(child, targetId)) return true;
            }
        }
        return false;
    };
    return props.allItems.filter(item => {
        const itemId = item.id || item._temp_id;
        return itemId !== elementId && !isDescendant(element, itemId);
    });
};

const changeParent = (element, newParentId) => {
    emit('parent-change', { item: element, newParentId });
};
</script>

<style scoped>
.ghost {
    opacity: 0.5;
    background: hsl(var(--muted));
    border: 1px dashed hsl(var(--foreground));
}
</style>
