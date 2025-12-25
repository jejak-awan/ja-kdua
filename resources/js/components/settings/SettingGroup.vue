<template>
    <div class="bg-muted/20 rounded-xl border border-border p-5">
        <!-- Group Header -->
        <div class="flex items-center gap-3 mb-4 pb-3 border-b border-border">
            <div class="p-2 rounded-lg bg-primary/10">
                <component :is="icon" class="w-5 h-5 text-primary" />
            </div>
            <div>
                <h3 class="text-base font-semibold text-foreground">{{ title }}</h3>
                <p class="text-xs text-muted-foreground">{{ description }}</p>
            </div>
        </div>
        
        <!-- Group Settings Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <slot />
        </div>

        <!-- Group Footer Action -->
        <div v-if="$slots.footer" class="mt-4 pt-4 border-t border-border">
            <slot name="footer" />
        </div>
    </div>
</template>

<script setup>
defineProps({
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
    }
})
</script>
