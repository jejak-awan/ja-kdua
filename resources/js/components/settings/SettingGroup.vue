<template>
    <div class="bg-muted/20 rounded-xl border border-border overflow-hidden">
        <!-- Group Header (Clickable for collapse) -->
        <button 
            type="button"
            class="w-full flex items-center gap-3 p-5 text-left transition-colors hover:bg-muted/30"
            :class="{ 'border-b border-border': isExpanded }"
            @click="toggle"
        >
            <div class="p-2 rounded-lg bg-primary/10">
                <component :is="icon" class="w-5 h-5 text-primary" />
            </div>
            <div class="flex-1">
                <h3 class="text-base font-semibold text-foreground">{{ title }}</h3>
                <p class="text-xs text-muted-foreground">{{ description }}</p>
            </div>
            <ChevronDown 
                class="w-5 h-5 text-muted-foreground transition-transform duration-200" 
                :class="{ 'rotate-180': isExpanded }"
            />
        </button>
        
        <!-- Collapsible Content -->
        <div 
            v-show="isExpanded" 
            class="p-5 pt-4"
        >
            <!-- Group Settings Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <slot />
            </div>

            <!-- Group Footer Action -->
            <div v-if="$slots.footer" class="mt-4 pt-4 border-t border-border">
                <slot name="footer" />
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue'
import { ChevronDown } from 'lucide-vue-next'

const props = defineProps({
    title: {
        type: String,
        required: true
    },
    description: {
        type: String,
        required: true
    },
    icon: {
        type: Object,
        required: true
    },
    defaultExpanded: {
        type: Boolean,
        default: true
    }
})

const isExpanded = ref(props.defaultExpanded)

const toggle = () => {
    isExpanded.value = !isExpanded.value
}
</script>
