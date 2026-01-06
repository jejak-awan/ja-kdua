<template>
    <div class="space-y-3">
        <!-- Font Family -->
        <div class="space-y-1.5">
            <label class="text-[10px] font-bold text-muted-foreground">Font Family</label>
            <select 
                :value="values.fontFamily"
                @change="updateValue('fontFamily', $event.target.value)"
                class="w-full h-8 px-2 bg-background border border-input rounded-md text-xs outline-none focus:ring-1 focus:ring-inset focus:ring-primary"
            >
                <option v-for="font in fontFamilyOptions" :key="font.value" :value="font.value">
                    {{ font.label }}
                </option>
            </select>
        </div>

        <!-- Size & Weight Row -->
        <div class="grid grid-cols-2 gap-2">
            <div class="space-y-1.5">
                <label class="text-[10px] font-bold text-muted-foreground">Size</label>
                <div class="flex items-center gap-1">
                    <input 
                        type="number"
                        :value="values.fontSize"
                        @input="updateValue('fontSize', Number($event.target.value))"
                        class="flex-1 h-8 px-2 text-xs font-mono bg-background border border-input rounded-md focus:ring-1 focus:ring-primary outline-none"
                        :min="8"
                        :max="120"
                    />
                    <span class="text-[10px] text-muted-foreground">px</span>
                </div>
            </div>
            <div class="space-y-1.5">
                <label class="text-[10px] font-bold text-muted-foreground">Weight</label>
                <select 
                    :value="values.fontWeight"
                    @change="updateValue('fontWeight', $event.target.value)"
                    class="w-full h-8 px-2 bg-background border border-input rounded-md text-xs outline-none focus:ring-1 focus:ring-inset focus:ring-primary"
                >
                    <option v-for="weight in fontWeightOptions" :key="weight.value" :value="weight.value">
                        {{ weight.label }}
                    </option>
                </select>
            </div>
        </div>

        <!-- Line Height & Letter Spacing Row -->
        <div class="grid grid-cols-2 gap-2">
            <div class="space-y-1.5">
                <label class="text-[10px] font-bold text-muted-foreground">Line Height</label>
                <input 
                    type="number"
                    :value="values.lineHeight"
                    @input="updateValue('lineHeight', Number($event.target.value))"
                    class="w-full h-8 px-2 text-xs font-mono bg-background border border-input rounded-md focus:ring-1 focus:ring-primary outline-none"
                    :min="0.8"
                    :max="3"
                    :step="0.1"
                />
            </div>
            <div class="space-y-1.5">
                <label class="text-[10px] font-bold text-muted-foreground">Letter Spacing</label>
                <div class="flex items-center gap-1">
                    <input 
                        type="number"
                        :value="values.letterSpacing"
                        @input="updateValue('letterSpacing', Number($event.target.value))"
                        class="flex-1 h-8 px-2 text-xs font-mono bg-background border border-input rounded-md focus:ring-1 focus:ring-primary outline-none"
                        :min="-5"
                        :max="20"
                        :step="0.5"
                    />
                    <span class="text-[10px] text-muted-foreground">px</span>
                </div>
            </div>
        </div>

        <!-- Text Align -->
        <div class="space-y-1.5">
            <label class="text-[10px] font-bold text-muted-foreground">Alignment</label>
            <div class="flex gap-1 bg-muted/20 p-1 rounded-md border">
                <button 
                    v-for="align in textAlignOptions" 
                    :key="align.value"
                    @click="updateValue('textAlign', align.value)"
                    class="flex flex-1 items-center justify-center py-1.5 rounded text-[10px] font-medium transition-all"
                    :class="values.textAlign === align.value 
                        ? 'bg-background text-foreground shadow-sm ring-1 ring-black/5' 
                        : 'text-muted-foreground hover:bg-background/50 hover:text-foreground'"
                    :title="align.label"
                >
                    <component :is="align.icon" class="w-3.5 h-3.5" />
                </button>
            </div>
        </div>

        <!-- Transform & Decoration Row -->
        <div class="grid grid-cols-2 gap-2">
            <div class="space-y-1.5">
                <label class="text-[10px] font-bold text-muted-foreground">Transform</label>
                <select 
                    :value="values.textTransform"
                    @change="updateValue('textTransform', $event.target.value)"
                    class="w-full h-8 px-2 bg-background border border-input rounded-md text-xs outline-none focus:ring-1 focus:ring-inset focus:ring-primary"
                >
                    <option v-for="transform in textTransformOptions" :key="transform.value" :value="transform.value">
                        {{ transform.label }}
                    </option>
                </select>
            </div>
            <div class="space-y-1.5">
                <label class="text-[10px] font-bold text-muted-foreground">Decoration</label>
                <select 
                    :value="values.textDecoration"
                    @change="updateValue('textDecoration', $event.target.value)"
                    class="w-full h-8 px-2 bg-background border border-input rounded-md text-xs outline-none focus:ring-1 focus:ring-inset focus:ring-primary"
                >
                    <option v-for="dec in textDecorationOptions" :key="dec.value" :value="dec.value">
                        {{ dec.label }}
                    </option>
                </select>
            </div>
        </div>

        <!-- Color -->
        <div class="space-y-1.5">
            <label class="text-[10px] font-bold text-muted-foreground">Text Color</label>
            <div class="flex gap-2 items-center">
                <ColorPicker 
                    :model-value="values.textColor"
                    @update:model-value="updateValue('textColor', $event)"
                />
                <Input 
                    :model-value="values.textColor"
                    @update:model-value="updateValue('textColor', $event)"
                    class="h-8 text-xs font-mono bg-background border-input flex-1" 
                    placeholder="#000000" 
                />
            </div>
        </div>

        <!-- Preview -->
        <div 
            class="p-4 rounded-lg border border-border bg-muted/20 min-h-[60px] flex items-center justify-center overflow-hidden"
        >
            <span 
                class="transition-all duration-200 max-w-full truncate"
                :style="previewStyle"
            >
                Preview Text
            </span>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue';
import { AlignLeft, AlignCenter, AlignRight, AlignJustify } from 'lucide-vue-next';
import ColorPicker from '@/components/ui/color-picker.vue';
import Input from '@/components/ui/input.vue';

const props = defineProps({
    modelValue: {
        type: Object,
        default: () => ({})
    }
});

const emit = defineEmits(['update:modelValue']);

const fontFamilyOptions = [
    { label: 'System', value: 'inherit' },
    { label: 'Inter', value: "'Inter', sans-serif" },
    { label: 'Roboto', value: "'Roboto', sans-serif" },
    { label: 'Open Sans', value: "'Open Sans', sans-serif" },
    { label: 'Poppins', value: "'Poppins', sans-serif" },
    { label: 'Montserrat', value: "'Montserrat', sans-serif" },
    { label: 'Lato', value: "'Lato', sans-serif" },
    { label: 'Playfair Display', value: "'Playfair Display', serif" },
    { label: 'Merriweather', value: "'Merriweather', serif" },
    { label: 'Georgia', value: "Georgia, serif" },
    { label: 'Monospace', value: "'JetBrains Mono', monospace" }
];

const fontWeightOptions = [
    { label: 'Light', value: '300' },
    { label: 'Normal', value: '400' },
    { label: 'Medium', value: '500' },
    { label: 'Semi', value: '600' },
    { label: 'Bold', value: '700' },
    { label: 'Black', value: '900' }
];

const textAlignOptions = [
    { label: 'Left', value: 'left', icon: AlignLeft },
    { label: 'Center', value: 'center', icon: AlignCenter },
    { label: 'Right', value: 'right', icon: AlignRight },
    { label: 'Justify', value: 'justify', icon: AlignJustify }
];

const textTransformOptions = [
    { label: 'None', value: 'none' },
    { label: 'Uppercase', value: 'uppercase' },
    { label: 'Lowercase', value: 'lowercase' },
    { label: 'Capitalize', value: 'capitalize' }
];

const textDecorationOptions = [
    { label: 'None', value: 'none' },
    { label: 'Underline', value: 'underline' },
    { label: 'Line-through', value: 'line-through' }
];

const defaults = {
    fontFamily: 'inherit',
    fontSize: 16,
    fontWeight: '400',
    lineHeight: 1.5,
    letterSpacing: 0,
    textAlign: 'left',
    textTransform: 'none',
    textDecoration: 'none',
    textColor: 'inherit'
};

const values = computed({
    get() {
        return { ...defaults, ...props.modelValue };
    },
    set(newValues) {
        emit('update:modelValue', newValues);
    }
});

const updateValue = (key, value) => {
    values.value = { ...values.value, [key]: value };
};

const previewStyle = computed(() => ({
    fontFamily: values.value.fontFamily,
    fontSize: `${values.value.fontSize}px`,
    fontWeight: values.value.fontWeight,
    lineHeight: values.value.lineHeight,
    letterSpacing: `${values.value.letterSpacing}px`,
    textAlign: values.value.textAlign,
    textTransform: values.value.textTransform,
    textDecoration: values.value.textDecoration,
    color: values.value.textColor === 'inherit' ? 'currentColor' : values.value.textColor
}));
</script>
