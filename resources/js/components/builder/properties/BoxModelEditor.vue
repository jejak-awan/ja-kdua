<template>
    <div class="space-y-3">
        <!-- Mode Toggle (Linked/Unlinked) -->
        <div class="flex items-center justify-between">
            <span class="text-[10px] font-bold text-muted-foreground uppercase tracking-wide">
                {{ mode === 'padding' ? 'Padding' : 'Margin' }}
            </span>
            <Button 
                variant="ghost" 
                size="icon" 
                class="h-6 w-6"
                :class="isLinked ? 'text-primary' : 'text-muted-foreground'"
                @click="toggleLinked"
                :title="isLinked ? 'Unlink values' : 'Link all values'"
            >
                <Link v-if="isLinked" class="w-3.5 h-3.5" />
                <Unlink v-else class="w-3.5 h-3.5" />
            </Button>
        </div>

        <!-- Visual Box Model -->
        <div class="relative bg-muted/30 rounded-lg p-4 border border-border">
            <!-- Outer Box (Margin representation) -->
            <div 
                v-if="mode === 'margin'" 
                class="absolute inset-0 rounded-lg border-2 border-dashed border-orange-400/30 pointer-events-none"
            ></div>

            <!-- Top Input -->
            <div class="flex justify-center mb-2">
                <div class="flex items-center gap-1">
                    <input 
                        type="number"
                        :value="parseValue(values.top)"
                        @input="updateValue('top', $event.target.value)"
                        class="w-12 h-6 text-center text-[10px] font-mono bg-background border border-input rounded focus:border-primary focus:ring-1 focus:ring-primary outline-none"
                        :placeholder="mode === 'padding' ? 'T' : 'T'"
                    />
                    <select 
                        :value="getUnit(values.top)"
                        @change="updateUnit('top', $event.target.value)"
                        class="h-6 text-[9px] bg-background border border-input rounded px-1"
                    >
                        <option value="px">px</option>
                        <option value="rem">rem</option>
                        <option value="%">%</option>
                    </select>
                </div>
            </div>

            <!-- Middle Row (Left, Center Box, Right) -->
            <div class="flex items-center justify-between gap-2">
                <!-- Left Input -->
                <div class="flex items-center gap-1">
                    <input 
                        type="number"
                        :value="parseValue(values.left)"
                        @input="updateValue('left', $event.target.value)"
                        class="w-12 h-6 text-center text-[10px] font-mono bg-background border border-input rounded focus:border-primary focus:ring-1 focus:ring-primary outline-none"
                        :placeholder="mode === 'padding' ? 'L' : 'L'"
                    />
                    <select 
                        :value="getUnit(values.left)"
                        @change="updateUnit('left', $event.target.value)"
                        class="h-6 text-[9px] bg-background border border-input rounded px-1"
                    >
                        <option value="px">px</option>
                        <option value="rem">rem</option>
                        <option value="%">%</option>
                    </select>
                </div>

                <!-- Center Visual Box -->
                <div 
                    class="flex-1 h-12 rounded border-2 flex items-center justify-center text-[9px] font-bold text-muted-foreground uppercase"
                    :class="mode === 'padding' ? 'border-blue-400/50 bg-blue-400/10' : 'border-orange-400/50 bg-orange-400/10'"
                >
                    {{ mode }}
                </div>

                <!-- Right Input -->
                <div class="flex items-center gap-1">
                    <input 
                        type="number"
                        :value="parseValue(values.right)"
                        @input="updateValue('right', $event.target.value)"
                        class="w-12 h-6 text-center text-[10px] font-mono bg-background border border-input rounded focus:border-primary focus:ring-1 focus:ring-primary outline-none"
                        :placeholder="mode === 'padding' ? 'R' : 'R'"
                    />
                    <select 
                        :value="getUnit(values.right)"
                        @change="updateUnit('right', $event.target.value)"
                        class="h-6 text-[9px] bg-background border border-input rounded px-1"
                    >
                        <option value="px">px</option>
                        <option value="rem">rem</option>
                        <option value="%">%</option>
                    </select>
                </div>
            </div>

            <!-- Bottom Input -->
            <div class="flex justify-center mt-2">
                <div class="flex items-center gap-1">
                    <input 
                        type="number"
                        :value="parseValue(values.bottom)"
                        @input="updateValue('bottom', $event.target.value)"
                        class="w-12 h-6 text-center text-[10px] font-mono bg-background border border-input rounded focus:border-primary focus:ring-1 focus:ring-primary outline-none"
                        :placeholder="mode === 'padding' ? 'B' : 'B'"
                    />
                    <select 
                        :value="getUnit(values.bottom)"
                        @change="updateUnit('bottom', $event.target.value)"
                        class="h-6 text-[9px] bg-background border border-input rounded px-1"
                    >
                        <option value="px">px</option>
                        <option value="rem">rem</option>
                        <option value="%">%</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Quick Presets -->
        <div class="flex flex-wrap gap-1">
            <button 
                v-for="preset in presets" 
                :key="preset.label"
                @click="applyPreset(preset.value)"
                class="px-2 py-1 text-[9px] font-medium rounded border border-border bg-muted/30 hover:bg-muted hover:border-primary/50 transition-colors"
            >
                {{ preset.label }}
            </button>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import { Link, Unlink } from 'lucide-vue-next';
import Button from '@/components/ui/button.vue';

const props = defineProps({
    modelValue: {
        type: Object,
        default: () => ({ top: '0', right: '0', bottom: '0', left: '0' })
    },
    mode: {
        type: String,
        default: 'padding',
        validator: (val) => ['padding', 'margin'].includes(val)
    }
});

const emit = defineEmits(['update:modelValue']);

const isLinked = ref(false);

const values = computed({
    get() {
        const defaults = { top: '0', right: '0', bottom: '0', left: '0' };
        if (typeof props.modelValue === 'object' && props.modelValue !== null) {
            return { ...defaults, ...props.modelValue };
        }
        // Handle string format "10px 20px 10px 20px"
        if (typeof props.modelValue === 'string') {
            const parts = props.modelValue.split(' ');
            if (parts.length === 4) {
                return { top: parts[0], right: parts[1], bottom: parts[2], left: parts[3] };
            } else if (parts.length === 1) {
                return { top: parts[0], right: parts[0], bottom: parts[0], left: parts[0] };
            }
        }
        return defaults;
    },
    set(newValues) {
        emit('update:modelValue', newValues);
    }
});

const presets = [
    { label: '0', value: { top: '0', right: '0', bottom: '0', left: '0' } },
    { label: '8px', value: { top: '8px', right: '8px', bottom: '8px', left: '8px' } },
    { label: '16px', value: { top: '16px', right: '16px', bottom: '16px', left: '16px' } },
    { label: '24px', value: { top: '24px', right: '24px', bottom: '24px', left: '24px' } },
    { label: '32px', value: { top: '32px', right: '32px', bottom: '32px', left: '32px' } }
];

const parseValue = (val) => {
    if (!val) return 0;
    const num = parseFloat(val);
    return isNaN(num) ? 0 : num;
};

const getUnit = (val) => {
    if (!val) return 'px';
    const match = String(val).match(/(px|rem|%|em|vh|vw)$/);
    return match ? match[1] : 'px';
};

const updateValue = (side, newValue) => {
    const unit = getUnit(values.value[side]);
    const formatted = `${newValue}${unit}`;
    
    if (isLinked.value) {
        values.value = {
            top: formatted,
            right: formatted,
            bottom: formatted,
            left: formatted
        };
    } else {
        values.value = {
            ...values.value,
            [side]: formatted
        };
    }
};

const updateUnit = (side, newUnit) => {
    const num = parseValue(values.value[side]);
    const formatted = `${num}${newUnit}`;
    
    if (isLinked.value) {
        values.value = {
            top: formatted,
            right: formatted,
            bottom: formatted,
            left: formatted
        };
    } else {
        values.value = {
            ...values.value,
            [side]: formatted
        };
    }
};

const toggleLinked = () => {
    isLinked.value = !isLinked.value;
    if (isLinked.value) {
        // Set all to top value when linking
        const topValue = values.value.top;
        values.value = {
            top: topValue,
            right: topValue,
            bottom: topValue,
            left: topValue
        };
    }
};

const applyPreset = (preset) => {
    values.value = { ...preset };
};
</script>
