<template>
    <div class="space-y-1.5">
        <div class="flex items-center justify-between">
            <label class="text-[10px] font-bold text-muted-foreground flex items-center gap-2">
                {{ field.label }}
                <!-- Responsive Indicator/Toggle -->
                <div class="flex items-center gap-2">
                    <button 
                        v-if="supportsResponsive"
                        class="opacity-50 hover:opacity-100 transition-opacity"
                        :class="{ 'text-primary opacity-100': isResponsive }"
                        @click="toggleResponsive"
                        :title="isResponsive ? 'Responsive Mode Active' : 'Enable Responsive Mode'"
                    >
                        <component :is="currentDeviceIcon" class="w-3 h-3" />
                    </button>
                    <!-- Global Dynamic Data Trigger -->
                    <DynamicTrigger v-if="supportsDynamic" :field="field" :block="block" />
                </div>
            </label>
        </div>
        
        <!-- Field Input (Main Wrapper) -->
        <div :class="{ 'relative': isResponsive }">
            <!-- Responsive overlay indicator -->
             <div v-if="isResponsive" class="absolute -top-1.5 -right-1 z-10 pointer-events-none">
                <span class="text-[8px] bg-primary text-primary-foreground px-1 rounded-full shadow-sm">
                    {{ builder.deviceMode.value }}
                </span>
            </div>

            <!-- Text Input -->
            <div v-if="field.type === 'text'">
                <Input v-model="proxyValue" class="h-8 text-xs bg-background border-input" :disabled="isDynamic" :placeholder="dynamicLabel" />
            </div>

            <!-- Textarea -->
            <div v-if="field.type === 'textarea' || field.type === 'richtext'">
                <Textarea v-model="proxyValue" class="min-h-[80px] text-xs bg-background border-input" :disabled="isDynamic" :placeholder="dynamicLabel" />
            </div>

            <!-- Image -->
            <div v-if="field.type === 'image'">
                <div class="flex gap-2">
                    <Input v-model="proxyValue" class="h-8 text-xs bg-background border-input" placeholder="https://..." :disabled="isDynamic" />
                    <Button variant="outline" size="icon" class="h-8 w-8 shrink-0" @click="openMediaPicker" :title="t('features.builder.properties.tooltips.mediaLibrary')" :disabled="isDynamic">
                        <ImageIcon class="w-3.5 h-3.5" />
                    </Button>
                </div>
            </div>

            <!-- Color -->
            <div v-if="field.type === 'color'" class="space-y-2">
                <!-- Quick Palette (Only show if not responsive or if we want to support it per device) -->
                <div v-if="themeColors.length > 0" class="flex flex-wrap gap-1.5 p-1.5 bg-muted/30 rounded-md border border-border/50">
                    <button 
                        v-for="color in themeColors" 
                        :key="color.variable"
                        class="w-5 h-5 rounded-full border border-border shadow-sm transition-transform hover:scale-110 focus:outline-none focus:ring-2 focus:ring-primary/50"
                        :style="{ backgroundColor: color.value }"
                        :title="color.name"
                        @click="proxyValue = color.variable"
                    >
                    </button>
                </div>
                
                <div class="flex gap-2">
                    <!-- Native Color Picker -->
                    <input 
                        type="color" 
                        v-model="proxyValue" 
                        class="w-8 h-8 rounded border border-border shadow-sm shrink-0 cursor-pointer"
                        :title="field.label"
                    />
                    <!-- Hex Input -->
                    <Input v-model="proxyValue" class="h-8 text-xs font-mono bg-background border-input" placeholder="#000000" />
                </div>
            </div>

            <!-- Select -->
            <div v-if="field.type === 'select'">
                <select v-model="proxyValue" class="w-full h-8 px-2 bg-background border border-input rounded-md text-xs outline-none focus:ring-1 focus:ring-inset focus:ring-primary text-foreground">
                    <option v-for="opt in field.options" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
                </select>
            </div>

            <!-- Data Select (Dynamic) -->
            <div v-if="field.type === 'data_select'">
                <div class="relative">
                    <select 
                        v-model="proxyValue" 
                        class="w-full h-8 px-2 bg-background border border-input rounded-md text-xs outline-none focus:ring-1 focus:ring-inset focus:ring-primary text-foreground"
                        :disabled="fetchingData"
                    >
                        <option :value="null">Select...</option>
                        <option v-for="opt in dynamicOptions" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
                    </select>
                    <div v-if="fetchingData" class="absolute right-8 top-2">
                        <Loader2 class="w-4 h-4 animate-spin text-muted-foreground" />
                    </div>
                </div>
            </div>

            <!-- Boolean -->
             <div v-if="field.type === 'boolean'" class="flex items-center justify-between">
                <Switch 
                    :checked="proxyValue" 
                    @update:checked="proxyValue = $event"
                />
            </div>

            <!-- Number -->
             <div v-if="field.type === 'number'" class="flex gap-2 items-center">
                <Input 
                    type="number" 
                    :model-value="proxyValue"
                    @input="proxyValue = Number($event.target.value)"
                    :min="field.min"
                    :max="field.max"
                    :step="field.step || 1"
                    class="h-8 text-xs bg-background border-input w-24"
                />
                <span v-if="field.unit" class="text-xs text-muted-foreground">{{ field.unit }}</span>
            </div>

            <!-- Slider/Range -->
            <div v-if="field.type === 'slider' || field.type === 'range'" class="space-y-2">
                <div class="flex items-center gap-3">
                    <input 
                        type="range"
                        :value="proxyValue"
                        @input="proxyValue = Number($event.target.value)"
                        :min="field.min || 0"
                        :max="field.max || 100"
                        :step="field.step || 1"
                        class="flex-1 h-2 bg-muted rounded-full appearance-none cursor-pointer accent-primary"
                    />
                    <span class="text-xs font-mono text-muted-foreground w-12 text-right">
                        {{ proxyValue }}{{ field.unit || '' }}
                    </span>
                </div>
            </div>
            
            <!-- Repeater -->
            <div v-if="field.type === 'repeater'" class="space-y-2">
                <div v-for="(item, idx) in proxyValue" :key="idx" class="p-2 border rounded-md bg-muted/20">
                    <div class="flex justify-between items-center mb-2">
                        <span class="text-[10px] font-bold">{{ field.itemLabel }} {{ idx + 1 }}</span>
                        <Button variant="ghost" size="icon" class="h-5 w-5 text-destructive" @click="proxyValue.splice(idx, 1)"><Trash2 class="w-3 h-3" /></Button>
                    </div>
                    <div class="space-y-2">
                         <!-- Recursive usage? Or simple rendering? Simple rendering for now to avoid complexity without dynamic context mismatch -->
                        <template v-for="subField in field.fields" :key="subField.key">
                            <div class="space-y-1">
                                <label class="text-[9px] text-muted-foreground">{{ subField.label }}</label>
                                <!-- Direct Input components for Repeater sub-fields (Simple types only usually) -->
                                <Input v-if="subField.type === 'text'" v-model="item[subField.key]" class="h-7 text-xs" />
                                <Textarea v-if="subField.type === 'textarea'" v-model="item[subField.key]" class="min-h-[40px] text-xs" />
                                <div v-if="subField.type === 'image'" class="flex gap-1">
                                    <Input v-model="item[subField.key]" class="h-7 text-xs" />
                                </div>
                                <select v-if="subField.type === 'select'" v-model="item[subField.key]" class="w-full h-7 px-2 bg-background border border-input rounded-md text-xs">
                                     <option v-for="opt in subField.options" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
                                </select>

                                <!-- List Type (Array of Strings) -->
                                <div v-if="subField.type === 'list'" class="space-y-1.5 pt-1">
                                    <div v-for="(str, sIdx) in item[subField.key]" :key="sIdx" class="flex gap-1 items-center">
                                        <Input v-model="item[subField.key][sIdx]" class="h-6 text-[10px] bg-background" />
                                        <Button variant="ghost" size="icon" class="h-6 w-6 shrink-0 text-muted-foreground hover:text-destructive" @click="item[subField.key].splice(sIdx, 1)">
                                            <Trash2 class="w-2.5 h-2.5" />
                                        </Button>
                                    </div>
                                    <Button variant="outline" size="sm" class="h-6 w-full text-[9px] border-dashed" @click="(item[subField.key] = item[subField.key] || []).push('')">
                                        <Plus class="w-2.5 h-2.5 mr-1" /> Add Feature
                                    </Button>
                                </div>
                             </div>
                        </template>
                    </div>
                </div>
                <Button variant="outline" size="sm" class="w-full text-xs" @click="proxyValue.push(createDefaultItem(field))">
                    Add {{ field.itemLabel }}
                </Button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, watch, inject, onMounted } from 'vue';
import { useI18n } from 'vue-i18n';
import { useTheme } from '@/composables/useTheme';
import Input from '@/components/ui/input.vue';
import Textarea from '@/components/ui/textarea.vue';
import Button from '@/components/ui/button.vue';
import Switch from '@/components/ui/switch.vue';
import DynamicTrigger from './DynamicTrigger.vue';
import { Image as ImageIcon, Smartphone, Tablet, Monitor, Trash2, Plus, Loader2 } from 'lucide-vue-next';
import api from '@/services/api';
import { parseResponse, ensureArray } from '@/utils/responseParser';

const props = defineProps({
    field: { type: Object, required: true },
    block: { type: Object, required: true },
    modelValue: { type: [String, Number, Boolean, Object, Array], default: '' }
});

const emit = defineEmits(['update:modelValue']);
const builder = inject('builder');
const { activeTheme, getSetting } = useTheme();
const { t } = useI18n();

const dynamicOptions = ref([]);
const fetchingData = ref(false);

const fetchDynamicData = async () => {
    if (props.field.type !== 'data_select' || !props.field.source) return;

    fetchingData.value = true;
    try {
        const response = await api.get(props.field.source);
        const { data } = parseResponse(response);
        const items = ensureArray(data);
        
        // Map to { label, value }
        dynamicOptions.value = items.map(item => ({
            label: item.name || item.title || item.label || `ID: ${item.id}`,
            value: item.id
        }));
    } catch (error) {
        console.error('Failed to fetch dynamic options:', error);
    } finally {
        fetchingData.value = false;
    }
};

onMounted(fetchDynamicData);

const createDefaultItem = (field) => {
    if (!field.fields) return {};
    const item = {};
    field.fields.forEach(f => {
        item[f.key] = f.default || '';
    });
    return item;
};

// Helper to determine active icon
const currentDeviceIcon = computed(() => {
    switch (builder.deviceMode.value) {
        case 'mobile': return Smartphone;
        case 'tablet': return Tablet;
        default: return Monitor;
    }
});

// Responsive Logic
const isResponsive = computed(() => {
    return typeof props.modelValue === 'object' && props.modelValue !== null && 'desktop' in props.modelValue;
});

const supportsResponsive = computed(() => {
    // Only allow responsive for specific types that are likely class-based or logic-handled
    // Text/Image content should genericly not be responsive (unless supported by component)
    // Color is complex (needs wrapper class knowledge).
    return ['select', 'slider', 'range', 'boolean'].includes(props.field.type);
});

const supportsDynamic = computed(() => {
    return ['text', 'textarea', 'richtext', 'image'].includes(props.field.type);
});

const toggleResponsive = () => {
    if (isResponsive.value) {
        // Confirm flatten? Or just take Desktop value?
        // Let's flatten to desktop value.
        emit('update:modelValue', props.modelValue.desktop);
    } else {
        // Convert scalar to object
        const val = props.modelValue;
        emit('update:modelValue', {
            desktop: val,
            tablet: val,
            mobile: val
        });
    }
};

const proxyValue = computed({
    get() {
        if (isResponsive.value) {
            return props.modelValue[builder.deviceMode.value];
        }
        return props.modelValue;
    },
    set(val) {
        if (isResponsive.value) {
            const newVal = { ...props.modelValue };
            newVal[builder.deviceMode.value] = val;
            emit('update:modelValue', newVal);
        } else {
            emit('update:modelValue', val);
        }
    }
});

// Theme Colors
const themeColors = computed(() => {
    if (!activeTheme.value?.manifest?.settings_schema) return [];
    return Object.entries(activeTheme.value.manifest.settings_schema)
        .filter(([_, setting]) => setting.type === 'color')
        .map(([key, setting]) => ({
            name: setting.label || key,
            value: getSetting(key),
            variable: `var(--theme-${key.replace(/_/g, '-')})`
        }));
});

const openMediaPicker = () => {
    builder.activeMediaField.value = props.field.key;
    builder.activeBlockId.value = props.block.id;
    builder.showMediaPicker.value = true;
};

const isDynamic = computed(() => {
    return !!(props.block.dynamicSettings && props.block.dynamicSettings[props.field.key]);
});

const dynamicLabel = computed(() => {
    if (!isDynamic.value) return '';
    const sourceId = props.block.dynamicSettings[props.field.key];
    const source = dynamicContent.getSources().find(s => s.id === sourceId);
    return source ? `Connected to ${source.label}` : 'Connected to dynamic content';
});
</script>
