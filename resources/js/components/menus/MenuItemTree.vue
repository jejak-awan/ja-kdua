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
                <div class="flex items-center justify-between p-3 bg-card rounded-lg hover:bg-muted">
                    <div class="flex items-center space-x-3 flex-1">
                        <div class="drag-handle cursor-move text-muted-foreground hover:text-muted-foreground p-1">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16" />
                            </svg>
                        </div>
                        <div>
                            <span class="text-sm font-medium text-foreground">{{ element.title || element.label }}</span>
                            <span class="ml-2 text-xs text-muted-foreground bg-secondary px-2 py-0.5 rounded-full capitalize">
                                {{ element.type }}
                            </span>
                            <span v-if="!element.is_active" class="ml-2 text-xs text-red-500 bg-red-500/20 px-2 py-0.5 rounded-full">
                                Inactive
                            </span>
                        </div>
                    </div>
                    <div class="flex items-center space-x-2">
                        <button
                            @click="$emit('edit', element)"
                            class="text-indigo-600 hover:text-indigo-900 text-sm px-2 py-1"
                        >
                            Edit
                        </button>
                        <button
                            @click="$emit('delete', element)"
                            class="text-red-600 hover:text-red-900 text-sm px-2 py-1"
                        >
                            Delete
                        </button>
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
import draggable from 'vuedraggable';

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

