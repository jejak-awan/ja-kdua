<template>
    <Card class="border-border shadow-sm">
        <CardHeader class="pb-3">
            <div class="flex items-center justify-between">
                <CardTitle class="text-base">
                    {{ selectedItem ? 'Edit Item' : 'Properties' }}
                </CardTitle>
                <div class="flex items-center gap-1">
                    <Button 
                        variant="ghost" 
                        size="icon" 
                        class="h-8 w-8"
                        @click="$emit('collapse')"
                        title="Collapse Panel"
                    >
                        <PanelRightClose class="w-4 h-4" />
                    </Button>
                    <Button 
                        v-if="selectedItem"
                        variant="ghost" 
                        size="icon" 
                        class="h-8 w-8"
                        @click="menuContext.clearSelection()"
                        title="Deselect Item"
                    >
                        <X class="w-4 h-4" />
                    </Button>
                </div>
            </div>
        </CardHeader>
        <CardContent>
            <!-- No Selection -->
            <div v-if="!selectedItem" class="flex flex-col items-center justify-center py-8 text-center">
                <MousePointer class="w-8 h-8 text-muted-foreground mb-3" />
                <p class="text-sm text-muted-foreground">
                    Select an item to edit its properties
                </p>
            </div>

            <!-- Properties Form -->
            <div v-else class="space-y-4">
                <!-- Type Badge -->
                <div class="flex items-center gap-2 pb-2 border-b border-border">
                    <component :is="iconComponent" class="w-4 h-4" :class="iconColorClass" />
                    <Badge variant="outline">{{ typeLabel }}</Badge>
                </div>

                <!-- Dynamic Fields -->
                <div v-for="setting in settingsSchema" :key="setting.key" class="space-y-1.5">
                    <!-- Skip grouped fields, render them in groups -->
                    <template v-if="!setting.group">
                        <ItemPropertyField 
                            :setting="setting"
                            :value="selectedItem[setting.key]"
                            @update="updateField(setting.key, $event)"
                        />
                    </template>
                </div>
                
                <!-- Parent Selector -->
                <div class="space-y-1.5 pt-2 border-t border-border">
                    <Label class="text-xs text-muted-foreground">{{ t('features.menus.form.parentItem') }}</Label>
                    <Select :model-value="currentParentId" @update:model-value="handleParentChange">
                        <SelectTrigger class="h-8 text-sm">
                            <SelectValue :placeholder="t('features.menus.form.placeholders.selectParent') || 'Select Parent'" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="root">{{ t('features.menus.form.rootItem') }}</SelectItem>
                            <SelectItem 
                                v-for="p in validParents" 
                                :key="p.id || p._temp_id" 
                                :value="(p.id || p._temp_id).toString()"
                            >
                                <span :style="{ paddingLeft: (p._depth * 12) + 'px' }">
                                    {{ p.title || 'Untitled' }}
                                </span>
                            </SelectItem>
                        </SelectContent>
                    </Select>
                </div>

                <!-- Grouped Fields -->
                <Accordion type="multiple" class="w-full" v-if="groupedSettings.length > 0">
                    <AccordionItem v-for="group in groupedSettings" :key="group.name" :value="group.name">
                        <AccordionTrigger class="py-2 text-sm">
                            {{ formatGroupName(group.name) }}
                        </AccordionTrigger>
                        <AccordionContent class="pt-2 pb-4 space-y-4">
                            <ItemPropertyField 
                                v-for="setting in group.settings" 
                                :key="setting.key"
                                :setting="setting"
                                :value="selectedItem[setting.key]"
                                @update="updateField(setting.key, $event)"
                            />
                        </AccordionContent>
                    </AccordionItem>
                </Accordion>

                <!-- Quick Actions -->
                <div class="flex gap-2 pt-4 border-t border-border">
                    <Button 
                        size="sm" 
                        variant="outline" 
                        class="flex-1"
                        @click="handleDuplicate"
                    >
                        <Copy class="w-3.5 h-3.5 mr-2" />
                        Duplicate
                    </Button>
                    <Button 
                        size="sm" 
                        variant="destructive" 
                        class="flex-1"
                        @click="handleDelete"
                    >
                        <Trash2 class="w-3.5 h-3.5 mr-2" />
                        Delete
                    </Button>
                </div>
            </div>
        </CardContent>
    </Card>
</template>

<script setup>
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { useMenuContext } from '../../../composables/useMenu';
import { menuItemRegistry } from '../registry';

// UI Components
import Card from '../../ui/card.vue';
import CardHeader from '../../ui/card-header.vue';
import CardTitle from '../../ui/card-title.vue';
import CardContent from '../../ui/card-content.vue';
import Badge from '../../ui/badge.vue';
import Button from '../../ui/button.vue';
import Accordion from '../../ui/accordion.vue';
import AccordionContent from '../../ui/accordion-content.vue';
import AccordionItem from '../../ui/accordion-item.vue';
import AccordionTrigger from '../../ui/accordion-trigger.vue';
import Select from '../../ui/select.vue';
import SelectTrigger from '../../ui/select-trigger.vue';
import SelectValue from '../../ui/select-value.vue';
import SelectContent from '../../ui/select-content.vue';
import SelectItem from '../../ui/select-item.vue';
import Label from '../../ui/label.vue';
import ItemPropertyField from './ItemPropertyField.vue';

import { 
    X, MousePointer, Copy, Trash2,
    FileText, File, Tag, Link as LinkIcon, Columns,
    PanelRightClose
} from 'lucide-vue-next';

defineEmits(['collapse']);

const { t } = useI18n();
const menuContext = useMenuContext();

const selectedItem = computed(() => menuContext.selectedItem.value);

// Flatten items with depth for select options
const validParents = computed(() => {
    if (!selectedItem.value) return [];
    
    const currentId = selectedItem.value.id || selectedItem.value._temp_id;
    const result = [];
    
    // Recursive flatten
    const traverse = (nodes, depth) => {
        nodes.forEach(node => {
            const nodeId = node.id || node._temp_id;
            
            // Exclude self
            if (nodeId === currentId) return;
            
            // Exclude descendants of self (if self was in the list, but we excluded it)
            // But wait, if we are traversing normally from root, 
            // if we hit 'self', we skip it. But we must also skip its children!
            // So if nodeId === currentId, we do NOT traverse its children.
            
            if (nodeId === currentId) {
                return; // Skip this node and its subtree
            }
            
            result.push({ ...node, _depth: depth });
            
            if (node.children && node.children.length > 0) {
                traverse(node.children, depth + 1);
            }
        });
    };
    
    traverse(menuContext.items.value, 0);
    return result;
});

const currentParentId = computed(() => {
    if (!selectedItem.value) return 'root';
    const parent = menuContext.findParent(selectedItem.value.id || selectedItem.value._temp_id);
    return parent ? (parent.id || parent._temp_id).toString() : 'root';
});

const handleParentChange = (val) => {
    if (!selectedItem.value) return;
    const itemId = selectedItem.value.id || selectedItem.value._temp_id;
    const newParentId = val === 'root' ? null : val;
    menuContext.moveItem(itemId, newParentId);
};

const typeDefinition = computed(() => {
    if (!selectedItem.value) return null;
    return menuItemRegistry.get(selectedItem.value.type);
});

const typeLabel = computed(() => {
    return typeDefinition.value?.label || selectedItem.value?.type || 'Unknown';
});

const iconComponent = computed(() => {
    switch (selectedItem.value?.type) {
        case 'page': return FileText;
        case 'post': return File;
        case 'category': return Tag;
        case 'column_group': return Columns;
        default: return LinkIcon;
    }
});

const iconColorClass = computed(() => {
    const color = typeDefinition.value?.color || 'gray';
    return `text-${color}-500`;
});

const settingsSchema = computed(() => {
    return typeDefinition.value?.settings || [];
});

const groupedSettings = computed(() => {
    const groups = {};
    settingsSchema.value.forEach(setting => {
        if (setting.group) {
            if (!groups[setting.group]) {
                groups[setting.group] = [];
            }
            groups[setting.group].push(setting);
        }
    });
    return Object.entries(groups).map(([name, settings]) => ({ name, settings }));
});

const formatGroupName = (name) => {
    return name.split('_').map(w => w.charAt(0).toUpperCase() + w.slice(1)).join(' ');
};

const updateField = (key, value) => {
    const itemId = selectedItem.value.id || selectedItem.value._temp_id;
    menuContext.updateItem(itemId, { [key]: value });
};

const handleDuplicate = () => {
    const itemId = selectedItem.value.id || selectedItem.value._temp_id;
    menuContext.duplicateItem(itemId);
};

const handleDelete = () => {
    const itemId = selectedItem.value.id || selectedItem.value._temp_id;
    menuContext.deleteItem(itemId);
};
</script>
