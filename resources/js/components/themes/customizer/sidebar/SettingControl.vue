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
                    @input="$emit('update:modelValue', $event.target.value)"
                    @change="$emit('change')"
                >
                <div 
                    class="w-full h-full"
                    :style="{ backgroundColor: modelValue }"
                ></div>
            </div>
            <input
                type="text"
                :value="modelValue"
                @input="$emit('update:modelValue', $event.target.value)"
                @change="$emit('change')"
                class="flex-1 h-10 px-3 py-2 bg-background border rounded-lg text-sm font-mono focus:ring-1 focus:ring-inset focus:ring-primary focus:border-primary outline-none transition-all"
            >
        </div>

        <!-- Select -->
        <div v-else-if="setting.type === 'select'" class="relative">
            <select
                :value="modelValue"
                @change="handleInput($event.target.value); $emit('change')"
                class="w-full h-9 pl-3 pr-8 bg-background border rounded-lg text-sm appearance-none focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all cursor-pointer"
            >
                <option v-for="opt in setting.options" :key="opt.value" :value="opt.value">
                    {{ opt.label }}
                </option>
            </select>
            <div class="absolute inset-y-0 right-3 flex items-center pointer-events-none text-muted-foreground">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4-4 4 4m0 6l-4 4-4-4" /></svg>
            </div>
        </div>
        
        <!-- Range Slider -->
        <div v-else-if="setting.type === 'range'" class="flex items-center gap-3">
            <input 
                type="range"
                :min="setting.min || 0"
                :max="setting.max || 100"
                :step="setting.step || 1"
                :value="modelValue"
                @input="handleInput($event.target.value)"
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
            :value="modelValue"
            @input="handleInput($event.target.value)"
            @change="$emit('change')"
            rows="3"
            class="w-full p-3 bg-background border rounded-lg text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all resize-y min-h-[80px]"
        ></textarea>

        <!-- Toggle Switch -->
        <label v-else-if="setting.type === 'checkbox'" class="flex items-center cursor-pointer gap-3 p-2 border rounded-lg bg-muted/30 hover:bg-muted/50 transition-colors">
            <div class="relative inline-flex h-5 w-9 items-center rounded-full transition-colors focus-within:ring-2 focus-within:ring-primary/20 focus-within:ring-offset-2" :class="modelValue ? 'bg-primary' : 'bg-input'">
                <input 
                    type="checkbox" 
                    class="sr-only" 
                    :checked="modelValue"
                    @change="handleInput($event.target.checked); $emit('change')"
                >
                <span class="translate-x-1 inline-block h-3 w-3 transform rounded-full bg-background shadow-sm transition-transform" :class="modelValue ? 'translate-x-5' : 'translate-x-1'"></span>
            </div>
            <span class="text-sm font-medium text-foreground select-none">{{ modelValue ? 'Enabled' : 'Disabled' }}</span>
        </label>

        <!-- Media Picker -->
        <div v-else-if="setting.type === 'media'" class="space-y-2">
            <div v-if="modelValue" class="relative group h-32 bg-muted/50 rounded-lg overflow-hidden border shadow-sm">
                <img :src="modelValue" class="w-full h-full object-contain p-2" alt="Preview">
                <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-2">
                    <button @click="$emit('pick-media')" class="p-2 bg-white/20 hover:bg-white/40 rounded-full text-white backdrop-blur-sm transition-colors" title="Change Image">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg>
                    </button>
                    <button @click="handleInput(''); $emit('change')" class="p-2 bg-white/20 hover:bg-white/40 rounded-full text-white backdrop-blur-sm transition-colors" title="Remove">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                    </button>
                </div>
            </div>
            <button 
                v-else
                @click="$emit('pick-media')"
                class="w-full h-20 border-2 border-dashed rounded-lg flex flex-col items-center justify-center gap-1 text-muted-foreground hover:text-primary hover:border-primary/50 transition-all bg-muted/10 hover:bg-muted/20"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                <span class="text-[10px] font-medium">Select Media</span>
            </button>
        </div>

        <!-- Default Input (Text/URL/etc) -->
        <input
            v-else
            :type="setting.type || 'text'"
            :value="modelValue"
            @input="handleInput($event.target.value)"
            @change="$emit('change')"
            :placeholder="setting.placeholder"
            class="w-full h-9 px-3 bg-background border rounded-lg text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all"
        >
        
        <p v-if="setting.description" class="text-[10px] text-muted-foreground leading-snug">
            {{ setting.description }}
        </p>
    </div>
</template>

<script setup>
defineProps({
    setting: { type: Object, required: true },
    modelValue: { required: true },
});

const emit = defineEmits(['update:modelValue', 'change', 'pick-media']);

const handleInput = (val) => {
    emit('update:modelValue', val);
};
</script>
