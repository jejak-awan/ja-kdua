<template>
    <div class="space-y-2">
        <div class="flex items-center justify-between">
            <label class="text-xs font-medium text-foreground tracking-wide">
                {{ setting.label }}
            </label>
            <span v-if="setting.required" class="text-[10px] text-destructive">*</span>
        </div>

        <!-- Color Picker -->
        <div v-if="setting.type === 'color'" class="flex gap-2">
            <div class="relative w-10 h-10 rounded-lg overflow-hidden border shadow-sm shrink-0 group cursor-pointer">
                <input
                    type="color"
                    :value="modelValue"
                    class="absolute inset-0 w-[150%] h-[150%] -top-[25%] -left-[25%] p-0 m-0 opacity-0 cursor-pointer"
                    @input="$emit('update:modelValue', ($event.target as HTMLInputElement).value)"
                    @change="$emit('change')"
                >
                <div 
                    class="w-full h-full"
                    :style="{ backgroundColor: (modelValue as string) }"
                ></div>
            </div>
            <input
                type="text"
                :value="modelValue"
                @input="$emit('update:modelValue', ($event.target as HTMLInputElement).value)"
                @change="$emit('change')"
                class="flex-1 h-10 px-3 py-2 bg-background border rounded-lg text-sm font-mono focus:ring-1 focus:ring-inset focus:ring-primary focus:border-primary outline-none transition-colors"
            >
        </div>

        <!-- Select -->
        <div v-else-if="setting.type === 'select'" class="relative">
            <Select 
                :model-value="String(modelValue)" 
                @update:model-value="(val) => { handleInput(val); $emit('change'); }"
            >
                <SelectTrigger class="h-9">
                    <SelectValue :placeholder="setting.placeholder || 'Select option'" />
                </SelectTrigger>
                <SelectContent>
                    <SelectItem v-for="opt in setting.options" :key="String(opt.value)" :value="String(opt.value)">
                        {{ opt.label }}
                    </SelectItem>
                </SelectContent>
            </Select>
        </div>
        
        <!-- Range Slider -->
        <div v-else-if="setting.type === 'range'" class="flex items-center gap-3">
            <input 
                type="range"
                :min="setting.min || 0"
                :max="setting.max || 100"
                :step="setting.step || 1"
                :value="(modelValue as number)"
                @input="handleInput(($event.target as HTMLInputElement).value)"
                @change="$emit('change')"
                class="flex-1 h-2 bg-secondary rounded-lg appearance-none cursor-pointer accent-primary"
            >
            <span class="text-xs font-mono bg-muted px-2 py-1 rounded text-muted-foreground min-w-[3ch] text-center">
                {{ modelValue }}
            </span>
        </div>

        <!-- Textarea -->
        <textarea 
            v-else-if="setting.type === 'textarea'"
            :value="(modelValue as string)"
            @input="handleInput(($event.target as HTMLTextAreaElement).value)"
            @change="$emit('change')"
            rows="3"
            class="w-full p-3 bg-background border rounded-lg text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-colors resize-y min-h-[80px]"
        ></textarea>

        <!-- Toggle Switch -->
        <div v-else-if="setting.type === 'checkbox'" class="flex items-center justify-between p-3 border rounded-lg bg-muted/20 hover:bg-muted/30 transition-colors">
            <span class="text-sm font-medium text-foreground select-none">{{ modelValue ? 'Enabled' : 'Disabled' }}</span>
            <Switch 
                :checked="(modelValue as boolean)"
                @update:checked="(val) => { handleInput(val); $emit('change'); }"
            />
        </div>

        <!-- Media Picker -->
        <div v-else-if="setting.type === 'media'" class="space-y-2">
            <div v-if="modelValue" class="relative group h-32 bg-muted/50 rounded-lg overflow-hidden border shadow-sm">
                <img :src="(modelValue as string)" class="w-full h-full object-contain p-2" alt="Preview">
                <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-2">
                    <button @click="$emit('pick-media')" class="p-2 bg-white/20 hover:bg-white/40 rounded-full text-white backdrop-blur-sm transition-colors" title="Change Image">
                        <Pencil class="w-4 h-4" />
                    </button>
                    <button @click="handleInput(''); $emit('change')" class="p-2 bg-white/20 hover:bg-white/40 rounded-full text-white backdrop-blur-sm transition-colors" title="Remove">
                        <Trash2 class="w-4 h-4" />
                    </button>
                </div>
            </div>
            <button 
                v-else
                @click="$emit('pick-media')"
                class="w-full h-20 border-2 border-dashed rounded-lg flex flex-col items-center justify-center gap-1 text-muted-foreground hover:text-primary hover:border-primary/50 transition-colors bg-muted/10 hover:bg-muted/20"
            >
                <Image class="w-5 h-5" />
                <span class="text-[10px] font-medium">Select Media</span>
            </button>
        </div>

        <!-- Default Input (Text/URL/etc) -->
        <input
            v-else
            :type="setting.type || 'text'"
            :value="(modelValue as string)"
            @input="handleInput(($event.target as HTMLInputElement).value)"
            @change="$emit('change')"
            :placeholder="setting.placeholder"
            class="w-full h-9 px-3 bg-background border rounded-lg text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-colors"
        >
        
        <p v-if="setting.description" class="text-[10px] text-muted-foreground leading-snug">
            {{ setting.description }}
        </p>
    </div>
</template>

<script setup lang="ts">
import type { ThemeSetting } from '@/types/theme';
import Pencil from 'lucide-vue-next/dist/esm/icons/pencil.js';
import Trash2 from 'lucide-vue-next/dist/esm/icons/trash-2.js';
import Image from 'lucide-vue-next/dist/esm/icons/image.js';
import { 
    Select, 
    SelectTrigger, 
    SelectValue, 
    SelectContent, 
    SelectItem,
    Switch
} from '@/components/ui';

defineProps<{
    setting: ThemeSetting;
    modelValue: unknown;
}>();

const emit = defineEmits<{
    (e: 'update:modelValue', value: unknown): void;
    (e: 'change'): void;
    (e: 'pick-media'): void;
}>();

const handleInput = (val: unknown) => {
    emit('update:modelValue', val);
};
</script>
