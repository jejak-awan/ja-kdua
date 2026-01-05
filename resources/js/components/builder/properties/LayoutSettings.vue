<template>
    <div class="px-4 py-4 space-y-6 overflow-y-auto h-[calc(100vh-8rem)] custom-scrollbar">
        <!-- Global Layout Settings -->
        <div class="space-y-6">
            <div class="space-y-1">
                <h3 class="text-xs font-bold text-muted-foreground uppercase tracking-wider">Global Grid</h3>
                <p class="text-[10px] text-muted-foreground">Adjust the main structure of your page.</p>
            </div>
            
            <!-- Container Width -->
            <div class="space-y-3">
                <label class="text-sm font-semibold flex items-center gap-2">
                    <Maximize2 class="w-3.5 h-3.5" />
                    Container Max Width
                </label>
                
                <div class="grid grid-cols-2 gap-2">
                    <Button 
                        v-for="preset in widthPresets" 
                        :key="preset.value"
                        variant="outline"
                        size="sm"
                        class="text-[11px] h-8"
                        :class="{ 'border-primary bg-primary/5 text-primary': bgMaxWidth === preset.value }"
                        @click="updateSetting('container_max_width', preset.value)"
                    >
                        {{ preset.label }}
                    </Button>
                </div>

                <div class="pt-1">
                    <input 
                        v-model="bgMaxWidth" 
                        class="flex h-8 w-full rounded-md border border-input bg-background px-3 py-1 text-[11px] shadow-sm transition-colors placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring"
                        placeholder="Custom classes (e.g. max-w-7xl mx-auto)"
                        @blur="updateSetting('container_max_width', bgMaxWidth)"
                    />
                </div>
            </div>

            <div class="h-px bg-border/50"></div>
            
            <!-- Block Spacing -->
            <div class="space-y-3">
                <label class="text-sm font-semibold flex items-center gap-2">
                    <GripHorizontal class="w-3.5 h-3.5" />
                    Default Block Spacing
                </label>
                
                <div class="grid grid-cols-3 gap-2">
                    <Button 
                        v-for="preset in spacingPresets" 
                        :key="preset.value"
                        variant="outline"
                        size="sm"
                        class="text-[11px] h-8"
                        :class="{ 'border-primary bg-primary/5 text-primary': bgBlockMargin === preset.value }"
                        @click="updateSetting('block_spacing', preset.value)"
                    >
                        {{ preset.label }}
                    </Button>
                </div>

                <div class="pt-1">
                    <input 
                        v-model="bgBlockMargin" 
                        class="flex h-8 w-full rounded-md border border-input bg-background px-3 py-1 text-[11px] shadow-sm transition-colors placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring"
                        placeholder="Custom margin (e.g. mb-12)"
                        @blur="updateSetting('block_spacing', bgBlockMargin)"
                    />
                </div>
            </div>
        </div>

        <div class="h-px bg-border"></div>
        
        <div class="p-3 bg-muted/30 rounded-lg border border-border/50">
            <p class="text-[10px] text-muted-foreground flex gap-2">
                <Info class="w-3 h-3 shrink-0" />
                These settings apply to the root layout container and affect all root-level blocks.
            </p>
        </div>
    </div>
</template>

<script setup>
import { ref, inject, onMounted, watch } from 'vue';
import { Maximize2, GripHorizontal, Info } from 'lucide-vue-next';
import Button from '@/components/ui/button.vue';

const builder = inject('builder');

const bgMaxWidth = ref('');
const bgBlockMargin = ref('');

const widthPresets = [
    { label: 'Full', value: 'max-w-full px-4' },
    { label: 'Wide (7XL)', value: 'max-w-7xl mx-auto px-4' },
    { label: 'Medium (5XL)', value: 'max-w-5xl mx-auto px-4' },
    { label: 'Narrow (3XL)', value: 'max-w-3xl mx-auto px-4' },
];

const spacingPresets = [
    { label: 'None', value: 'space-y-0' },
    { label: 'Small', value: 'space-y-4' },
    { label: 'Medium', value: 'space-y-8' },
    { label: 'Large', value: 'space-y-12' },
    { label: 'X-Large', value: 'space-y-20' },
];

const syncFromBuilder = () => {
    bgMaxWidth.value = builder.globalSettings.value.container_max_width;
    bgBlockMargin.value = builder.globalSettings.value.block_spacing;
};

onMounted(syncFromBuilder);

// Watch for external changes (Undo/Redo)
watch(() => builder.globalSettings.value, syncFromBuilder, { deep: true });

const updateSetting = (key, value) => {
    if (key === 'container_max_width') bgMaxWidth.value = value;
    if (key === 'block_spacing') bgBlockMargin.value = value;
    builder.setGlobalSetting(key, value);
};
</script>
