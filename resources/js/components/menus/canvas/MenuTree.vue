<template>
    <div class="menu-tree">
        <!-- Empty State -->
        <div 
            v-if="items.length === 0" 
            class="flex flex-col items-center justify-center py-12 px-6 border-2 border-dashed border-border rounded-lg bg-muted/20"
        >
            <Menu class="w-12 h-12 text-muted-foreground mb-4" />
            <h3 class="text-lg font-medium text-foreground mb-1">{{ t('features.menus.messages.empty') }}</h3>
            <p class="text-sm text-muted-foreground text-center max-w-sm">
                {{ t('features.menus.messages.emptyHint') }}
            </p>
        </div>

        <!-- Tree -->
        <draggable
            v-else
            :list="items"
            :group="{ name: 'menu' }"
            :item-key="getItemKey"
            class="space-y-3 min-h-[100px]"
            handle=".drag-handle"
            ghost-class="ghost"
            @change="handleChange"
        >
            <template #item="{ element }">
                <MenuItemWrapper :item="element">
                    <template #children>
                        <MenuTreeRecursive 
                            :items="element.children || []" 
                            :depth="1"
                            @update="handleChildUpdate(element, $event)"
                        />
                    </template>
                </MenuItemWrapper>
            </template>
        </draggable>
    </div>
</template>

<script setup>
import { useI18n } from 'vue-i18n';
import draggable from 'vuedraggable';
import { useMenuContext } from '../../../composables/useMenu';
import MenuItemWrapper from './MenuItemWrapper.vue';
import MenuTreeRecursive from './MenuTreeRecursive.vue';

import { Menu } from 'lucide-vue-next';

const { t } = useI18n();
const menuContext = useMenuContext();

defineProps({
    items: {
        type: Array,
        default: () => []
    }
});

const getItemKey = (item) => item.id || item._temp_id || Math.random();

const handleChange = () => {
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
