<template>
    <draggable
        :list="items"
        :group="{ name: 'menu' }"
        :item-key="getItemKey"
        class="transition-all"
        :class="items.length === 0 ? 'min-h-[12px]' : 'space-y-2 min-h-[40px]'"
        handle=".drag-handle"
        ghost-class="ghost"
        @change="handleChange"
    >
        <template #item="{ element }">
            <MenuItemWrapper :item="element">
                <template #children>
                    <MenuTreeRecursive 
                        v-if="depth < maxDepth"
                        :items="element.children || []" 
                        :depth="depth + 1"
                        :max-depth="maxDepth"
                        @update="handleChildUpdate(element, $event)"
                    />
                </template>
            </MenuItemWrapper>
        </template>
    </draggable>
</template>

<script setup>
import draggable from 'vuedraggable';
import { useMenuContext } from '../../../composables/useMenu';
import MenuItemWrapper from './MenuItemWrapper.vue';

const menuContext = useMenuContext();

const props = defineProps({
    items: {
        type: Array,
        default: () => []
    },
    depth: {
        type: Number,
        default: 0
    },
    maxDepth: {
        type: Number,
        default: 5
    }
});

const emit = defineEmits(['update']);

const getItemKey = (item) => item.id || item._temp_id || Math.random();

const handleChange = () => {
    emit('update', props.items);
    menuContext.takeSnapshot();
};

const handleChildUpdate = (parent, newChildren) => {
    parent.children = newChildren;
    menuContext.takeSnapshot();
};
</script>

<style scoped>
.ghost {
    opacity: 0.5;
    background: hsl(var(--primary) / 0.1);
    border: 2px dashed hsl(var(--primary));
}
</style>
