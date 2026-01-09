<template>
    <node-view-wrapper 
        as="span" 
        class="inline-block align-middle select-none mx-0.5 leading-none"
        :class="{ 'ring-1 ring-primary rounded-sm': selected }"
    >
        <component 
            :is="iconComponent" 
            :size="node.attrs.size"
            :stroke-width="2"
            class="transition-all"
            :style="{ 
                color: node.attrs.color,
                width: node.attrs.size,
                height: node.attrs.size
            }"
        />
    </node-view-wrapper>
</template>

<script setup>
import { computed } from 'vue';
import { NodeViewWrapper } from '@tiptap/vue-3';
import * as LucideIcons from 'lucide-vue-next';

const props = defineProps({
    node: {
        type: Object,
        required: true,
    },
    selected: {
        type: Boolean,
        default: false,
    }
});

const iconComponent = computed(() => {
    const name = props.node.attrs.name;
    return LucideIcons[name] || LucideIcons.Circle;
});
</script>
