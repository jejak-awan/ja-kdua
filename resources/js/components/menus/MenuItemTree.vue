<template>
    <draggable
        :list="items"
        group="menu"
        item-key="id"
        class="space-y-2 min-h-[10px] pb-2"
        handle=".drag-handle"
        ghost-class="ghost"
        @change="$emit('change', items)"
    >
        <template #item="{ element }">
            <div class="border border-border rounded-lg bg-card relative">
                <!-- Item Content -->
                <div class="flex items-center justify-between p-3 bg-card rounded-lg hover:bg-muted/50 transition-colors">
                    <div class="flex items-center space-x-3 flex-1">
                        <div class="drag-handle cursor-move text-muted-foreground hover:text-foreground p-1 rounded-md hover:bg-muted transition-colors">
                            <GripVertical class="w-4 h-4" />
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-sm font-medium text-foreground">{{ element.title || element.label }}</span>
                            <Badge variant="secondary" class="text-[10px] px-1.5 py-0 h-4 capitalize">
                                {{ element.type }}
                            </Badge>
                            <Badge v-if="!element.is_active" variant="destructive" class="text-[10px] px-1.5 py-0 h-4">
                                Inactive
                            </Badge>
                        </div>
                    </div>
                    <div class="flex items-center gap-1">
                        <Button
                            variant="ghost"
                            size="sm"
                            @click="$emit('edit', element)"
                            class="h-8 px-2 text-primary hover:text-primary hover:bg-primary/10"
                        >
                            <Pencil class="w-3.5 h-3.5 mr-1.5" />
                            {{ t('common.actions.edit') }}
                        </Button>
                        <Button
                            variant="ghost"
                            size="sm"
                            @click="$emit('delete', element)"
                            class="h-8 px-2 text-destructive hover:text-destructive hover:bg-destructive/10"
                        >
                            <Trash2 class="w-3.5 h-3.5 mr-1.5" />
                            {{ t('common.actions.delete') }}
                        </Button>
                    </div>
                </div>

                <!-- Nested Children -->
                <div class="pl-8 pr-2">
                    <MenuItemTree
                        :items="element.children"
                        @edit="$emit('edit', $event)"
                        @delete="$emit('delete', $event)"
                        @change="$emit('change', $event)"
                    />
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
import { GripVertical, Pencil, Trash2 } from 'lucide-vue-next';

const { t } = useI18n();

const props = defineProps({
    items: {
        type: Array,
        required: true,
    },
});

defineEmits(['edit', 'delete', 'change']);
</script>

<style scoped>
.ghost {
    opacity: 0.5;
    background: #f3f4f6;
    border: 1px dashed #9ca3af;
}
</style>

