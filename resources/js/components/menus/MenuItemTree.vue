<template>
    <draggable
        :list="items"
        group="menu"
        item-key="id"
        class="space-y-3 min-h-[50px] pb-2"
        handle=".drag-handle"
        ghost-class="ghost"
        @change="$emit('change', items)"
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
                        
                        <!-- Title & Badges -->
                        <div class="flex flex-col gap-1 overflow-hidden">
                            <div class="font-medium text-sm truncate flex items-center gap-2">
                                {{ element.title || element.label || t('common.labels.untitled') }}
                                <Badge variant="outline" class="text-[10px] px-1.5 py-0 h-4 capitalize font-normal border-muted-foreground/30">
                                    {{ element.type }}
                                </Badge>
                            </div>
                            <div class="text-[10px] text-muted-foreground truncate" v-if="element.url && element.type === 'custom'">
                                {{ element.url }}
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center gap-1">
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
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="space-y-1.5">
                            <Label class="text-xs font-medium">{{ t('features.menus.form.label') }}</Label>
                            <Input v-model="element.title" class="h-8 bg-background" />
                        </div>
                        
                        <div class="space-y-1.5" v-if="element.type === 'custom'">
                            <Label class="text-xs font-medium">{{ t('features.menus.form.url') }}</Label>
                            <Input v-model="element.url" class="h-8 bg-background" />
                        </div>

                        <div class="space-y-1.5">
                            <Label class="text-xs font-medium">{{ t('features.menus.form.cssClasses') }}</Label>
                            <Input v-model="element.css_class" class="h-8 bg-background" :placeholder="t('features.menus.form.placeholders.cssClasses')" />
                        </div>
                         <div class="space-y-1.5">
                            <Label class="text-xs font-medium">{{ t('features.menus.form.openInNewTab') }}</Label>
                            <div class="flex items-center h-8">
                                <Switch 
                                    :checked="!!element.target" 
                                    @update:checked="(val) => element.target = val ? '_blank' : null" 
                                />
                                <span class="ml-2 text-xs text-muted-foreground">{{ element.target === '_blank' ? t('common.labels.yes') : t('common.labels.no') }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Nested Children -->
                <div class="pl-6 pr-2 pb-2" v-if="element.children && element.children.length > 0">
                    <div class="border-l-2 border-border/50 pl-2">
                        <MenuItemTree
                            :items="element.children"
                            @delete="$emit('delete', $event)"
                            @change="$emit('change', $event)"
                        />
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
import { GripVertical, Pencil, Trash2, ChevronDown } from 'lucide-vue-next';

const { t } = useI18n();

const props = defineProps({
    items: {
        type: Array,
        required: true,
    },
});

const emit = defineEmits(['delete', 'change']);

const toggleEdit = (element) => {
    // Add reactivity if missing
    if (typeof element._isEditing === 'undefined') {
        element._isEditing = false;
    }
    element._isEditing = !element._isEditing;
};
</script>

<style scoped>
.ghost {
    opacity: 0.5;
    background: hsl(var(--muted));
    border: 1px dashed hsl(var(--foreground));
}
</style>
