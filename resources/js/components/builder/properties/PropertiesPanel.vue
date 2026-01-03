<template>
    <div 
        class="border-l border-border bg-sidebar flex flex-col shrink-0 transition-[width] duration-200 ease-in-out relative overflow-hidden h-full z-10"
        :class="builder.isRightSidebarOpen.value ? 'w-80' : 'w-14'"
    >
        <!-- Sidebar Header -->
        <div class="h-14 shrink-0 border-b border-sidebar-border bg-sidebar-accent/10 flex items-center justify-between px-3">
            <div class="flex items-center gap-1">
                <!-- Tab Switchers -->
                <Button 
                    variant="ghost" 
                    size="icon" 
                    class="h-8 w-8 rounded-md transition-colors"
                    :class="builder.activeRightSidebarTab.value === 'properties' ? 'bg-sidebar-accent shadow-sm text-foreground' : 'text-muted-foreground hover:text-foreground'"
                    @click="setTab('properties')"
                    :title="t('features.builder.properties.title')"
                >
                    <Settings2 class="w-4 h-4" />
                </Button>
                <Button 
                    variant="ghost" 
                    size="icon" 
                    class="h-8 w-8 rounded-md transition-colors"
                    :class="builder.activeRightSidebarTab.value === 'layers' ? 'bg-sidebar-accent shadow-sm text-foreground' : 'text-muted-foreground hover:text-foreground'"
                    @click="setTab('layers')"
                    :title="t('features.builder.properties.layers')"
                >
                    <Layers class="w-4 h-4" />
                </Button>
                <Button 
                    variant="ghost" 
                    size="icon" 
                    class="h-8 w-8 rounded-md transition-colors"
                    :class="builder.activeRightSidebarTab.value === 'presets' ? 'bg-sidebar-accent shadow-sm text-foreground' : 'text-muted-foreground hover:text-foreground'"
                    @click="setTab('presets')"
                    :title="t('features.builder.properties.presets.title')"
                >
                    <Sparkles class="w-4 h-4" />
                </Button>
                <Button 
                    variant="ghost" 
                    size="icon" 
                    class="h-8 w-8 rounded-md transition-colors"
                    :class="builder.activeRightSidebarTab.value === 'visibility' ? 'bg-sidebar-accent shadow-sm text-foreground' : 'text-muted-foreground hover:text-foreground'"
                    @click="setTab('visibility')"
                    :title="t('features.builder.properties.visibility.title')"
                >
                    <Eye class="w-4 h-4" />
                </Button>
            </div>

            <!-- Toggle Button -->
            <Button 
                variant="ghost" 
                size="icon" 
                class="h-6 w-6 rounded-lg text-sidebar-foreground hover:bg-sidebar-accent" 
                @click="builder.isRightSidebarOpen.value = !builder.isRightSidebarOpen.value"
            >
                <PanelRightClose class="w-4 h-4 transition-transform duration-300" :class="!builder.isRightSidebarOpen.value ? 'rotate-180' : ''" />
            </Button>
        </div>

        <!-- Main Content Area -->
        <div 
            ref="scrollContainer"
            v-show="builder.isRightSidebarOpen.value" 
            class="flex-1 overflow-y-auto custom-scrollbar bg-sidebar p-4 pb-16 relative"
            @scroll="handleScroll"
        >
            
            <!-- LAYERS TAB -->
            <div v-if="builder.activeRightSidebarTab.value === 'layers'" class="space-y-4">
                <div class="flex items-center justify-between pb-2 border-b border-sidebar-border">
                    <h3 class="font-bold text-xs text-foreground">Layers</h3>
                    <span class="text-[10px] text-muted-foreground">{{ builder.blocks.value.length }} Root Elements</span>
                </div>
                <LayersTree :blocks="builder.blocks.value" />
            </div>

            <!-- PROPERTIES TAB -->
            <div v-if="builder.activeRightSidebarTab.value === 'properties'" class="space-y-4">
                <div v-if="selectedBlock" class="space-y-4">
                    <!-- Selected Block Info & Device Toggles -->
                    <div class="flex flex-col gap-2 p-3 bg-sidebar-accent/30 rounded-lg border border-sidebar-border">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-md bg-sidebar-accent flex items-center justify-center border border-sidebar-border shadow-sm text-primary">
                                <component :is="builder.getBlockComponent(selectedBlock.type)?.icon" class="w-4 h-4" />
                            </div>
                            <div class="overflow-hidden flex-1">
                                <h3 class="font-bold text-xs text-foreground truncate">{{ builder.getBlockLabel(selectedBlock.type) }}</h3>
                                <p class="text-[10px] text-muted-foreground font-mono truncate">#{{ selectedBlock.id.slice(0, 8) }}</p>
                            </div>
                        </div>

                        <div class="flex flex-col gap-2 pt-2 border-t border-sidebar-border/50">
                            <div class="flex items-center justify-between">
                                <span class="text-[10px] font-medium text-muted-foreground">{{ t('features.builder.properties.advanced.state') }}</span>
                                <div class="flex items-center gap-0.5 bg-sidebar-accent rounded-md border border-sidebar-border p-0.5">
                                    <button 
                                        @click="stateMode = 'default'"
                                        class="px-2 py-0.5 text-[10px] font-bold rounded transition-all"
                                        :class="stateMode === 'default' ? 'bg-muted text-foreground' : 'text-muted-foreground opacity-50 hover:opacity-100'"
                                    >
                                        {{ t('features.builder.properties.advanced.stateDefault') }}
                                    </button>
                                    <button 
                                        @click="stateMode = 'hover'"
                                        class="px-2 py-0.5 text-[10px] font-bold rounded transition-all flex items-center gap-1.2"
                                        :class="stateMode === 'hover' ? 'bg-primary/10 text-primary' : 'text-muted-foreground opacity-50 hover:opacity-100'"
                                    >
                                        <MousePointer2 class="w-2.5 h-2.5" />
                                        {{ t('features.builder.properties.advanced.stateHover') }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Accordion Settings -->
                    <Accordion type="single" collapsible class="w-full" default-value="content">
                        <AccordionItem value="content" class="border-sidebar-border">
                            <AccordionTrigger class="text-xs font-bold text-sidebar-foreground py-3 hover:no-underline hover:bg-sidebar-accent/50 px-2 -mx-2 rounded-md transition-colors">
                                {{ t('features.builder.properties.tabs.content') }}
                            </AccordionTrigger>
                            <AccordionContent class="space-y-4 pt-4 pb-2">
                                <template v-for="field in getBlockDefinition(selectedBlock.type).settings" :key="field.key">
                                    <div v-if="!['color', 'select'].includes(field.type) || field.key === 'alignment'">
                                        <PropertyField 
                                            :field="field" 
                                            :block="selectedBlock"
                                            v-model="selectedBlock.settings[field.key]"
                                        />
                                    </div>
                                </template>
                            </AccordionContent>
                        </AccordionItem>

                        <AccordionItem value="style" class="border-sidebar-border">
                            <AccordionTrigger class="text-xs font-bold text-sidebar-foreground py-3 hover:no-underline hover:bg-sidebar-accent/50 px-2 -mx-2 rounded-md transition-colors">
                                {{ t('features.builder.properties.tabs.style') }}
                            </AccordionTrigger>
                            <AccordionContent class="space-y-4 pt-4 pb-2">
                                <template v-for="field in getBlockDefinition(selectedBlock.type).settings" :key="field.key">
                                    <div v-if="['color', 'select'].includes(field.type) && field.key !== 'alignment'">
                                         <PropertyField 
                                            :field="field" 
                                            :block="selectedBlock"
                                            v-model="activeSettings[field.key]"
                                        />
                                    </div>
                                </template>
                            </AccordionContent>
                        </AccordionItem>
                        
                        <!-- NEW: Advanced Design Suite -->
                        <AccordionItem value="spacing" class="border-sidebar-border">
                            <AccordionTrigger class="text-xs font-bold text-sidebar-foreground py-3 hover:no-underline hover:bg-sidebar-accent/50 px-2 -mx-2 rounded-md transition-colors">
                                Spacing (Margin)
                            </AccordionTrigger>
                            <AccordionContent class="space-y-4 pt-4 pb-2">
                                <template v-for="field in marginFields" :key="field.key">
                                    <PropertyField 
                                        :field="field" 
                                        :block="selectedBlock"
                                        v-model="activeSettings[field.key]"
                                    />
                                </template>
                            </AccordionContent>
                        </AccordionItem>

                        <AccordionItem value="filters" class="border-sidebar-border">
                            <AccordionTrigger class="text-xs font-bold text-sidebar-foreground py-3 hover:no-underline hover:bg-sidebar-accent/50 px-2 -mx-2 rounded-md transition-colors">
                                Filters
                            </AccordionTrigger>
                            <AccordionContent class="space-y-4 pt-4 pb-2">
                                <template v-for="field in filterFields" :key="field.key">
                                    <PropertyField 
                                        :field="field" 
                                        :block="selectedBlock"
                                        v-model="activeSettings[field.key]"
                                    />
                                </template>
                            </AccordionContent>
                        </AccordionItem>

                        <AccordionItem value="transform" class="border-sidebar-border">
                            <AccordionTrigger class="text-xs font-bold text-sidebar-foreground py-3 hover:no-underline hover:bg-sidebar-accent/50 px-2 -mx-2 rounded-md transition-colors">
                                Transform
                            </AccordionTrigger>
                            <AccordionContent class="space-y-4 pt-4 pb-2">
                                <template v-for="field in transformFields" :key="field.key">
                                    <PropertyField 
                                        :field="field" 
                                        :block="selectedBlock"
                                        v-model="activeSettings[field.key]"
                                    />
                                </template>
                            </AccordionContent>
                        </AccordionItem>

                        <AccordionItem value="shadow" class="border-sidebar-border">
                            <AccordionTrigger class="text-xs font-bold text-sidebar-foreground py-3 hover:no-underline hover:bg-sidebar-accent/50 px-2 -mx-2 rounded-md transition-colors">
                                Box Shadow
                            </AccordionTrigger>
                            <AccordionContent class="space-y-4 pt-4 pb-2">
                                <PropertyField 
                                    :field="shadowField" 
                                    :block="selectedBlock"
                                    v-model="activeSettings.shadow"
                                />
                            </AccordionContent>
                        </AccordionItem>

                        <AccordionItem value="animation" class="border-sidebar-border">
                            <AccordionTrigger class="text-xs font-bold text-sidebar-foreground py-3 hover:no-underline hover:bg-sidebar-accent/50 px-2 -mx-2 rounded-md transition-colors">
                                Animation
                            </AccordionTrigger>
                            <AccordionContent class="space-y-4 pt-4 pb-2">
                                <template v-for="field in animationFields" :key="field.key">
                                    <PropertyField 
                                        :field="field" 
                                        :block="selectedBlock"
                                        v-model="activeSettings[field.key]"
                                    />
                                </template>
                            </AccordionContent>
                        </AccordionItem>

                        <AccordionItem value="advanced" class="border-sidebar-border">
                            <AccordionTrigger class="text-xs font-bold text-sidebar-foreground py-3 hover:no-underline hover:bg-sidebar-accent/50 px-2 -mx-2 rounded-md transition-colors">
                                Advanced
                            </AccordionTrigger>
                            <AccordionContent class="space-y-4 pt-4 pb-2">
                                <div class="space-y-3">
                                    <div class="space-y-1.5">
                                        <label class="text-[10px] font-bold text-muted-foreground">CSS ID</label>
                                        <Input v-model="selectedBlock.settings._css_id" placeholder="my-custom-id" class="h-8 text-xs bg-background border-input font-mono" />
                                    </div>
                                    <div class="space-y-1.5">
                                        <label class="text-[10px] font-bold text-muted-foreground">CSS Classes</label>
                                        <Input v-model="selectedBlock.settings._css_class" placeholder="p-4 bg-red-500" class="h-8 text-xs bg-background border-input font-mono" />
                                        <p class="text-[9px] text-muted-foreground">{{ t('features.builder.properties.advanced.cssClassHelp') }}</p>
                                    </div>
                                    <div class="space-y-1.5">
                                        <label class="text-[10px] font-bold text-muted-foreground">{{ t('features.builder.properties.advanced.customCSS') }}</label>
                                        <Textarea v-model="selectedBlock.settings._custom_css" placeholder="border: 1px solid red;" class="min-h-[80px] text-xs bg-background border-input font-mono" />
                                    </div>

                                    <!-- NEW: Positioning Refinements -->
                                    <template v-for="field in positioningFields" :key="field.key">
                                        <PropertyField 
                                            :field="field" 
                                            :block="selectedBlock"
                                            v-model="selectedBlock.settings[field.key]"
                                        />
                                    </template>
                                </div>
                            </AccordionContent>
                        </AccordionItem>
                    </Accordion>
                </div>
                
                <!-- Empty State -->
                <div v-else class="flex flex-col items-center justify-center h-64 text-center px-4 space-y-3 opacity-60">
                    <MousePointerClick class="w-12 h-12 text-muted-foreground stroke-1" />
                    <p class="text-sm font-medium text-muted-foreground">{{ t('features.builder.properties.noSelection') }}</p>
                </div>
            </div>


            <!-- VISIBILITY TAB -->
            <div v-else-if="builder.activeRightSidebarTab.value === 'visibility'" class="space-y-6">
                <div class="px-2">
                    <h3 class="text-[10px] font-bold text-muted-foreground mb-4">{{ t('features.builder.properties.visibility.title') }}</h3>
                    
                    <div v-if="selectedBlock" class="space-y-4">
                        <div class="space-y-4">
                             <div class="flex items-center justify-between">
                                <label class="text-[10px] font-bold text-muted-foreground">{{ t('features.builder.properties.visibility.logicMode') }}</label>
                                <select v-model="selectedBlock.settings.visibility_mode" class="h-6 text-[10px] bg-background border rounded px-1">
                                    <option value="all">{{ t('features.builder.properties.visibility.matchAll') }}</option>
                                    <option value="any">{{ t('features.builder.properties.visibility.matchAny') }}</option>
                                </select>
                             </div>

                             <!-- Rules Repeater -->
                             <PropertyField 
                                :field="visibilityRulesField"
                                :block="selectedBlock"
                                v-model="selectedBlock.settings.visibility_rules"
                             />
                        </div>

                        <div class="p-3 bg-primary/5 border border-primary/10 rounded-lg">
                            <p class="text-[9px] text-primary/70 leading-relaxed italic">
                                Note: Hidden blocks remain visible in the builder as placeholders but will be completely removed in preview mode or on the live site if conditions aren't met.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- PRESETS TAB -->
            <div v-else-if="builder.activeRightSidebarTab.value === 'presets'" class="space-y-6">
                 <div class="px-2">
                    <h3 class="text-[10px] font-bold text-muted-foreground mb-4">{{ t('features.builder.properties.presets.title') }}</h3>
                    
                    <div v-if="selectedBlock" class="space-y-4">
                        <!-- Save New Preset -->
                        <div class="p-3 bg-sidebar-accent/30 rounded-lg border border-sidebar-border space-y-3">
                            <h4 class="text-[10px] font-bold text-foreground/80">{{ t('features.builder.properties.presets.saveTitle') }}</h4>
                            <div class="flex gap-2">
                                <Input v-model="newPresetName" :placeholder="t('features.builder.properties.presets.namePlaceholder')" class="h-8 text-xs bg-background" />
                                <Button size="sm" class="h-8 px-3 text-xs" @click="handleSavePreset">
                                    <Plus class="w-3.5 h-3.5 mr-1" /> Save
                                </Button>
                            </div>
                        </div>

                        <!-- Existing Presets -->
                        <div class="space-y-2">
                            <h4 class="text-[10px] font-bold text-muted-foreground ml-1">{{ t('features.builder.properties.presets.yourPresets') }}</h4>
                            <div v-if="currentTypePresets.length > 0" class="space-y-1">
                                <div 
                                    v-for="preset in currentTypePresets" 
                                    :key="preset.id"
                                    class="group flex items-center justify-between p-2 rounded-md border border-sidebar-border bg-background hover:border-primary/50 transition-all"
                                >
                                    <span class="text-xs font-medium truncate shrink-0 max-w-[120px]">{{ preset.name }}</span>
                                    <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                        <Button variant="ghost" size="icon" class="h-6 w-6 text-primary hover:bg-primary/10" @click="applyPreset(preset)" :title="t('features.builder.properties.tooltips.applyPreset')">
                                            <Check class="w-3 h-3" />
                                        </Button>
                                        <Button variant="ghost" size="icon" class="h-6 w-6 text-destructive hover:bg-destructive/10" @click="deletePreset(preset.id)" :title="t('features.builder.properties.tooltips.deletePreset')">
                                            <Trash2 class="w-3 h-3" />
                                        </Button>
                                    </div>
                                </div>
                            </div>
                            <div v-else class="text-center py-8 border rounded-lg border-dashed text-muted-foreground text-[10px]">
                                No presets saved for {{ builder.getBlockLabel(selectedBlock.type) }} blocks.
                            </div>
                        </div>
                    </div>
                    <div v-else class="flex flex-col items-center justify-center h-64 text-center opacity-60">
                        <MousePointerClick class="w-12 h-12 text-muted-foreground stroke-1 mb-2" />
                        <p class="text-sm font-medium text-muted-foreground">{{ t('features.builder.properties.presets.noSelection') }}</p>
                    </div>
                 </div>
            </div>

        </div>

        <!-- Back to Top Button (Floating Sticky) -->
        <BackToTop 
            :show="showBackToTop && builder.isRightSidebarOpen.value" 
            @click="scrollToTop" 
        />
    </div>
</template>

<script setup>
import { inject, computed, ref, onMounted, onUnmounted } from 'vue';
import { useI18n } from 'vue-i18n';
import { 
    PanelRightClose, 
    Settings2, 
    Layers, 
    MousePointerClick,
    GripVertical,
    Smartphone,
    Tablet,
    Monitor,
    Sparkles,
    Plus,
    Trash2,
    Check,
    Eye,
    MousePointer2
} from 'lucide-vue-next';
import Button from '@/components/ui/button.vue';
import Input from '@/components/ui/input.vue';
import Textarea from '@/components/ui/textarea.vue';
import Accordion from '@/components/ui/accordion.vue';
import AccordionContent from '@/components/ui/accordion-content.vue';
import AccordionItem from '@/components/ui/accordion-item.vue';
import AccordionTrigger from '@/components/ui/accordion-trigger.vue';
import draggable from 'vuedraggable';
import { blockRegistry } from '../BlockRegistry';
import PropertyField from './PropertyField.vue';
import LayersTree from '../layers/LayersTree.vue';
import BackToTop from '@/components/ui/back-to-top.vue';
import { useScrollToTop } from '@/composables/useScrollToTop';

const builder = inject('builder');
const { t } = useI18n();

// Back to Top functionality
const scrollContainer = ref(null);
const { showBackToTop, handleScroll, scrollToTop } = useScrollToTop(scrollContainer);

const selectedBlock = computed(() => {
    if (builder.activeBlockId.value) {
        // Recursive find helper
        const findBlock = (blocks, id) => {
            for (const block of blocks) {
                if (block.id === id) return block;
                if (block.settings && block.settings.columns) {
                    for (const column of block.settings.columns) {
                         const found = findBlock(column.blocks, id);
                         if (found) return found;
                    }
                }
            }
            return null;
        };
        const found = findBlock(builder.blocks.value, builder.activeBlockId.value);
        if (found) return found;
    }
    
    // Legacy fallback to index if ID search fails (or if using main editingIndex)
    if (builder.editingIndex.value !== null) {
        return builder.blocks.value[builder.editingIndex.value];
    }
    return null;
});

const stateMode = ref('default'); // 'default' | 'hover'

const activeSettings = computed(() => {
    if (!selectedBlock.value) return {};
    if (stateMode.value === 'hover') {
        if (!selectedBlock.value.hoverSettings) {
             selectedBlock.value.hoverSettings = {};
        }
        return selectedBlock.value.hoverSettings;
    }
    return selectedBlock.value.settings;
});

const selectBlock = (index) => {
    builder.editingIndex.value = index;
    const block = builder.blocks.value[index];
    if (block) builder.activeBlockId.value = block.id;
};

const setTab = (tab) => {
    builder.activeRightSidebarTab.value = tab;
    if (!builder.isRightSidebarOpen.value) {
        builder.isRightSidebarOpen.value = true;
    }
};

const getBlockDefinition = (type) => {
    return blockRegistry.get(type) || { settings: [] };
};

// Advanced Design Suite Field Definitions
const marginFields = [
    { key: 'margin_top', type: 'slider', label: 'Margin Top', min: 0, max: 20, step: 1, responsive: true },
    { key: 'margin_bottom', type: 'slider', label: 'Margin Bottom', min: 0, max: 20, step: 1, responsive: true },
    { key: 'margin_left', type: 'slider', label: 'Margin Left', min: 0, max: 20, step: 1, responsive: true },
    { key: 'margin_right', type: 'slider', label: 'Margin Right', min: 0, max: 20, step: 1, responsive: true },
];

const filterFields = [
    { key: 'blur', type: 'slider', label: 'Blur', min: 0, max: 20, step: 1, responsive: true },
    { key: 'brightness', type: 'slider', label: 'Brightness', min: 0, max: 200, step: 1, default: 100, responsive: true },
    { key: 'contrast', type: 'slider', label: 'Contrast', min: 0, max: 200, step: 1, default: 100, responsive: true },
    { key: 'saturate', type: 'slider', label: 'Saturation', min: 0, max: 200, step: 1, default: 100, responsive: true },
    { key: 'hue_rotate', type: 'slider', label: 'Hue Rotate', min: 0, max: 360, step: 1, responsive: true },
];

const transformFields = [
    { key: 'scale', type: 'slider', label: 'Scale', min: 0, max: 200, step: 1, default: 100, responsive: true },
    { key: 'rotate', type: 'slider', label: 'Rotate', min: 0, max: 360, step: 1, responsive: true },
    { key: 'skew_x', type: 'slider', label: 'Skew X', min: -45, max: 45, step: 1, responsive: true },
    { key: 'skew_y', type: 'slider', label: 'Skew Y', min: -45, max: 45, step: 1, responsive: true },
    { key: 'translate_x', type: 'slider', label: 'Translate X', min: -100, max: 100, step: 1, responsive: true },
    { key: 'translate_y', type: 'slider', label: 'Translate Y', min: -100, max: 100, step: 1, responsive: true },
];

const shadowField = {
    key: 'shadow',
    type: 'select',
    label: 'Box Shadow',
    options: [
        { label: 'None', value: 'none' },
        { label: 'Small', value: 'shadow-sm' },
        { label: 'Standard', value: 'shadow' },
        { label: 'Medium', value: 'shadow-md' },
        { label: 'Large', value: 'shadow-lg' },
        { label: 'Extra Large', value: 'shadow-xl' },
        { label: '2XL', value: 'shadow-2xl' },
        { label: 'Inner', value: 'shadow-inner' },
    ],
    responsive: true
};

const animationFields = [
    { 
        key: 'animation_effect', 
        type: 'select', 
        label: 'Animation Effect', 
        options: [
            { label: 'None', value: '' },
            { label: 'Fade In', value: 'animate-fade' },
            { label: 'Fade Up', value: 'animate-fade-up' },
            { label: 'Fade Down', value: 'animate-fade-down' },
            { label: 'Fade Left', value: 'animate-fade-left' },
            { label: 'Fade Right', value: 'animate-fade-right' },
            { label: 'Zoom In', value: 'animate-zoom' },
            { label: 'Bounce In', value: 'animate-bounce-in' },
            { label: 'Flip X', value: 'animate-flip-x' },
            { label: 'Slide Up', value: 'animate-slide-up' },
            { label: 'Slide Down', value: 'animate-slide-down' },
            { label: 'Slide Left', value: 'animate-slide-left' },
            { label: 'Slide Right', value: 'animate-slide-right' },
            { label: 'Rotate In', value: 'animate-rotate-in' }
        ] 
    },
    { key: 'animation_duration', type: 'slider', label: 'Duration (ms)', min: 100, max: 2000, step: 100, default: 600 },
    { key: 'animation_delay', type: 'slider', label: 'Delay (ms)', min: 0, max: 2000, step: 100, default: 0 },
    { 
        key: 'animation_repeat', 
        type: 'select', 
        label: 'Repeat', 
        options: [
            { label: 'Once', value: 'once' },
            { label: 'Loop', value: 'infinite' }
        ],
        default: 'once'
    }
];

const visibilityRulesField = {
    key: 'visibility_rules',
    type: 'repeater',
    label: 'Conditions',
    itemLabel: 'Rule',
    fields: [
        { 
            key: 'type', 
            type: 'select', 
            label: 'Type', 
            options: [
                { label: 'Authentication', value: 'auth' },
                { label: 'User Role', value: 'role' },
                { label: 'Date/Time', value: 'date' }
            ] 
        },
        { 
            key: 'condition', 
            type: 'select', 
            label: 'Condition', 
            options: [
                { label: 'Is Logged In', value: 'logged_in' },
                { label: 'Is Guest', value: 'guest' },
                { label: 'User has Role', value: 'is' },
                { label: 'User lacks Role', value: 'is_not' },
                { label: 'Is Before', value: 'before' },
                { label: 'Is After', value: 'after' }
            ] 
        },
        { key: 'value', type: 'text', label: 'Value (Role/Date)' }
    ]
};


const positioningFields = [
    { 
        key: '_z_index', 
        type: 'slider', 
        label: 'Z-Index', 
        min: 0, 
        max: 100, 
        step: 1, 
        default: 0 
    },
    { 
        key: '_position', 
        type: 'select', 
        label: 'Position', 
        options: [
            { label: 'Default (Static)', value: 'static' },
            { label: 'Relative', value: 'relative' },
            { label: 'Absolute', value: 'absolute' },
            { label: 'Fixed', value: 'fixed' },
            { label: 'Sticky', value: 'sticky' }
        ],
        default: 'static'
    },
    { 
        key: '_sticky_top', 
        type: 'slider', 
        label: 'Sticky Top Offset', 
        min: 0, 
        max: 200, 
        step: 1, 
        default: 0,
        condition: (settings) => settings._position === 'sticky' || settings._position === 'fixed'
    }
];

// Presets Logic
import { ref as vueRef } from 'vue'; // To avoid conflict with inject ref if any
import { blockPresets } from '@/services/BlockPresetService';

const newPresetName = vueRef('');
const currentTypePresets = computed(() => {
    if (!selectedBlock.value) return [];
    return blockPresets.getPresets(selectedBlock.value.type);
});

const handleSavePreset = () => {
    if (!selectedBlock.value || !newPresetName.value) return;
    blockPresets.savePreset(selectedBlock.value.type, newPresetName.value, selectedBlock.value.settings);
    newPresetName.value = '';
    // Optional: Toast or feedback
};

const applyPreset = (preset) => {
    if (!selectedBlock.value) return;
    // We want to MERGE settings or REPLACE?
    // Divi usually replaces style settings.
    // For now, let's replace all but keep current content if we knew which fields were content.
    // Simple approach for now: replace all.
    selectedBlock.value.settings = JSON.parse(JSON.stringify(preset.settings));
    builder.takeSnapshot(); // Record for undo
};

const deletePreset = (id) => {
    if (!selectedBlock.value) return;
    blockPresets.deletePreset(selectedBlock.value.type, id);
};
</script>
