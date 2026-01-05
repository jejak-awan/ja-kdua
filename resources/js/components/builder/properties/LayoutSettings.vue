<template>
    <div class="px-4 py-4 space-y-6 overflow-y-auto h-[calc(100vh-8rem)] custom-scrollbar">
        <!-- Global Layout Settings -->
        <div class="space-y-4">
            <h3 class="text-xs font-bold text-muted-foreground uppercase tracking-wider">Global Grid</h3>
            
            <div class="grid gap-2">
                <label class="text-sm font-medium">Container Max Width</label>
                <div class="flex items-center gap-2">
                    <input 
                        v-model="bgMaxWidth" 
                        class="flex h-9 w-full rounded-md border border-input bg-background px-3 py-1 text-sm shadow-sm transition-colors placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring"
                        placeholder="e.g. max-w-7xl mx-auto px-4"
                        @change="updateSetting('container_max_width', bgMaxWidth)"
                    />
                </div>
                <p class="text-[10px] text-muted-foreground">Tailwind classes for the main page container.</p>
            </div>
            
            <div class="grid gap-2">
                <label class="text-sm font-medium">Default Block Margin</label>
                <div class="flex items-center gap-2">
                    <input 
                        v-model="bgBlockMargin" 
                        class="flex h-9 w-full rounded-md border border-input bg-background px-3 py-1 text-sm shadow-sm transition-colors placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring"
                        placeholder="e.g. mb-4"
                        @change="updateSetting('block_spacing', bgBlockMargin)"
                    />
                </div>
            </div>
        </div>

        <div class="h-px bg-border"></div>

        <!-- Add more global settings here -->
    </div>
</template>

<script setup>
import { ref, inject, onMounted } from 'vue';

const builder = inject('builder');

const bgMaxWidth = ref('');
const bgBlockMargin = ref('');

onMounted(() => {
    bgMaxWidth.value = builder.getGlobalSetting('container_max_width') || 'max-w-7xl mx-auto px-4';
    bgBlockMargin.value = builder.getGlobalSetting('block_spacing') || 'mb-4';
});

const updateSetting = (key, value) => {
    builder.setGlobalSetting(key, value);
};
</script>
