<template>
    <Popover>
        <PopoverTrigger as-child>
            <button 
                class="w-8 h-8 rounded border border-border shadow-sm shrink-0 cursor-pointer transition-all hover:scale-105 focus:outline-none focus:ring-2 focus:ring-primary/50"
                :style="{ backgroundColor: modelValue || '#000000' }"
                :title="title || 'Pick a color'"
            >
            </button>
        </PopoverTrigger>
        <PopoverContent class="w-64 p-3" align="start">
            <div class="space-y-3">
                <!-- Hex Input -->
                <div class="flex gap-2 items-center">
                    <div 
                        class="w-10 h-10 rounded border-2 border-border shadow-sm shrink-0"
                        :style="{ backgroundColor: modelValue || '#000000' }"
                    ></div>
                    <Input 
                        :model-value="modelValue" 
                        @update:model-value="$emit('update:modelValue', $event)"
                        class="h-9 text-xs font-mono" 
                        placeholder="#000000"
                    />
                </div>

                <!-- Color Palette Grid -->
                <div class="space-y-2">
                    <p class="text-[10px] font-bold text-muted-foreground">Quick Colors</p>
                    <div class="grid grid-cols-10 gap-1.5">
                        <button
                            v-for="color in colorPalette"
                            :key="color"
                            class="w-6 h-6 rounded border border-border/50 transition-all hover:scale-110 focus:outline-none focus:ring-2 focus:ring-primary/50"
                            :class="{ 'ring-2 ring-primary': modelValue === color }"
                            :style="{ backgroundColor: color }"
                            @click="$emit('update:modelValue', color)"
                            :title="color"
                        >
                        </button>
                    </div>
                </div>

                <!-- Theme Colors (if provided) -->
                <div v-if="themeColors && themeColors.length > 0" class="space-y-2">
                    <p class="text-[10px] font-bold text-muted-foreground">Theme Colors</p>
                    <div class="flex flex-wrap gap-1.5">
                        <button
                            v-for="color in themeColors"
                            :key="color.variable"
                            class="w-6 h-6 rounded border border-border/50 transition-all hover:scale-110 focus:outline-none focus:ring-2 focus:ring-primary/50"
                            :class="{ 'ring-2 ring-primary': modelValue === color.variable }"
                            :style="{ backgroundColor: color.value }"
                            @click="$emit('update:modelValue', color.variable)"
                            :title="color.name"
                        >
                        </button>
                    </div>
                </div>

                <!-- Native Color Picker (Hidden, for advanced users) -->
                <div class="pt-2 border-t">
                    <label class="flex items-center gap-2 cursor-pointer hover:bg-muted/50 rounded p-1.5 transition-colors">
                        <input 
                            type="color" 
                            :value="modelValue || '#000000'"
                            @input="$emit('update:modelValue', $event.target.value)"
                            class="w-5 h-5 cursor-pointer"
                        />
                        <span class="text-[10px] text-muted-foreground">Advanced Picker</span>
                    </label>
                </div>
            </div>
        </PopoverContent>
    </Popover>
</template>

<script setup>
import { computed } from 'vue';
import Popover from '@/components/ui/popover.vue';
import PopoverTrigger from '@/components/ui/popover-trigger.vue';
import PopoverContent from '@/components/ui/popover-content.vue';
import Input from '@/components/ui/input.vue';

const props = defineProps({
    modelValue: { type: String, default: '#000000' },
    title: { type: String, default: '' },
    themeColors: { type: Array, default: () => [] }
});

defineEmits(['update:modelValue']);

// Comprehensive color palette (Material Design inspired)
const colorPalette = [
    // Grays
    '#000000', '#374151', '#6B7280', '#9CA3AF', '#D1D5DB', '#E5E7EB', '#F3F4F6', '#F9FAFB', '#FFFFFF',
    
    // Reds
    '#7F1D1D', '#991B1B', '#B91C1C', '#DC2626', '#EF4444', '#F87171', '#FCA5A5', '#FECACA', '#FEE2E2',
    
    // Oranges
    '#7C2D12', '#9A3412', '#C2410C', '#EA580C', '#F97316', '#FB923C', '#FDBA74', '#FED7AA', '#FFEDD5',
    
    // Yellows
    '#713F12', '#854D0E', '#A16207', '#CA8A04', '#EAB308', '#FACC15', '#FDE047', '#FEF08A', '#FEF9C3',
    
    // Greens
    '#14532D', '#166534', '#15803D', '#16A34A', '#22C55E', '#4ADE80', '#86EFAC', '#BBF7D0', '#DCFCE7',
    
    // Teals
    '#134E4A', '#115E59', '#0F766E', '#0D9488', '#14B8A6', '#2DD4BF', '#5EEAD4', '#99F6E4', '#CCFBF1',
    
    // Blues
    '#1E3A8A', '#1E40AF', '#1D4ED8', '#2563EB', '#3B82F6', '#60A5FA', '#93C5FD', '#BFDBFE', '#DBEAFE',
    
    // Purples
    '#581C87', '#6B21A8', '#7C3AED', '#8B5CF6', '#A78BFA', '#C4B5FD', '#DDD6FE', '#EDE9FE', '#F5F3FF',
    
    // Pinks
    '#831843', '#9F1239', '#BE123C', '#E11D48', '#F43F5E', '#FB7185', '#FDA4AF', '#FECDD3', '#FCE7F3',
];
</script>
