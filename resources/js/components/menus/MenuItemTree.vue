<template>
    <div class="border border-gray-200 rounded-lg p-4">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-2 flex-1">
                <svg class="w-5 h-5 text-gray-400 cursor-move" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16" />
                </svg>
                <span class="text-sm font-medium text-gray-900">{{ item.label }}</span>
                <span class="text-xs text-gray-500">({{ item.type }})</span>
            </div>
            <div class="flex items-center space-x-2">
                <button
                    @click="$emit('edit', item)"
                    class="text-indigo-600 hover:text-indigo-900 text-sm"
                >
                    Edit
                </button>
                <button
                    @click="$emit('delete', item)"
                    class="text-red-600 hover:text-red-900 text-sm"
                >
                    Delete
                </button>
            </div>
        </div>
        <div v-if="children.length > 0" class="mt-2 ml-6 space-y-2">
            <MenuItemTree
                v-for="child in children"
                :key="child.id"
                :item="child"
                :items="items"
                @edit="$emit('edit', $event)"
                @delete="$emit('delete', $event)"
                @move="$emit('move', $event)"
            />
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
    item: {
        type: Object,
        required: true,
    },
    items: {
        type: Array,
        required: true,
    },
});

defineEmits(['edit', 'delete', 'move']);

const children = computed(() => {
    return props.items.filter(i => i.parent_id === props.item.id);
});
</script>

