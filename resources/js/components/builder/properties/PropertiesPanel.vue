<template>
    <div 
        class="border-l border-border bg-sidebar flex flex-col shrink-0 transition-all duration-300 ease-in-out relative overflow-hidden h-full z-10"
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
                    :class="builder.activeRightSidebarTab.value === 'properties' ? 'bg-background shadow-sm text-foreground' : 'text-muted-foreground hover:text-foreground'"
                    @click="setTab('properties')"
                    :title="t('features.builder.properties.title')"
                >
                    <Settings2 class="w-4 h-4" />
                </Button>
                <Button 
                    variant="ghost" 
                    size="icon" 
                    class="h-8 w-8 rounded-md transition-colors"
                    :class="builder.activeRightSidebarTab.value === 'layers' ? 'bg-background shadow-sm text-foreground' : 'text-muted-foreground hover:text-foreground'"
                    @click="setTab('layers')"
                    :title="t('features.builder.properties.layers')"
                >
                    <Layers class="w-4 h-4" />
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
        <div v-show="builder.isRightSidebarOpen.value" class="flex-1 overflow-y-auto custom-scrollbar bg-sidebar p-4">
            
            <!-- PROPERTIES TAB -->
            <div v-if="builder.activeRightSidebarTab.value === 'properties'" class="space-y-4">
                <div v-if="selectedBlock" class="space-y-4">
                    <!-- Selected Block Info & Device Toggles -->
                    <div class="flex flex-col gap-2 p-3 bg-sidebar-accent/30 rounded-lg border border-sidebar-border">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-md bg-background flex items-center justify-center border border-border shadow-sm text-primary">
                                <component :is="builder.getBlockComponent(selectedBlock.type)?.icon" class="w-4 h-4" />
                            </div>
                            <div class="overflow-hidden flex-1">
                                <h3 class="font-bold text-xs text-foreground truncate">{{ builder.getBlockLabel(selectedBlock.type) }}</h3>
                                <p class="text-[10px] text-muted-foreground font-mono truncate">#{{ selectedBlock.id.slice(0, 8) }}</p>
                            </div>
                        </div>

                        <!-- Responsive Controls -->
                        <div class="flex items-center justify-between pt-2 border-t border-sidebar-border/50">
                            <span class="text-[10px] font-medium text-muted-foreground uppercase">Mode</span>
                            <div class="flex items-center gap-0.5 bg-background rounded-md border border-border p-0.5">
                                <button 
                                    v-for="mode in ['desktop', 'tablet', 'mobile']" 
                                    :key="mode"
                                    class="p-1 rounded hover:bg-muted transition-colors"
                                    :class="builder.deviceMode.value === mode ? 'bg-muted text-foreground' : 'text-muted-foreground'"
                                    @click="builder.deviceMode.value = mode"
                                    :title="mode"
                                >
                                    <component 
                                        :is="mode === 'mobile' ? Smartphone : mode === 'tablet' ? Tablet : Monitor" 
                                        class="w-3.5 h-3.5" 
                                    />
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Accordion Settings -->
                    <Accordion type="multiple" class="w-full" :defaultValue="['content']">
                        <AccordionItem value="content" class="border-sidebar-border">
                            <AccordionTrigger class="text-xs font-bold uppercase tracking-widest text-sidebar-foreground py-3 hover:no-underline hover:bg-sidebar-accent/50 px-2 -mx-2 rounded-md transition-colors">
                                {{ t('features.builder.properties.tabs.content') }}
                            </AccordionTrigger>
                            <AccordionContent class="space-y-4 pt-4 pb-2">
                                <template v-for="field in getBlockDefinition(selectedBlock.type).settings" :key="field.key">
                                    <div v-if="!['color', 'select'].includes(field.type) || field.key === 'alignment'" class="space-y-1.5">
                                        <label class="text-[10px] font-bold uppercase text-muted-foreground">{{ field.label }}</label>
                                        
                                        <div v-if="field.type === 'text'" class="flex gap-2">
                                            <Input v-model="selectedBlock.settings[field.key]" class="h-8 text-xs bg-background border-input" :disabled="isDynamic(selectedBlock, field.key)" :placeholder="getDynamicLabel(selectedBlock, field.key)" />
                                            <Popover>
                                                <PopoverTrigger asChild>
                                                    <Button 
                                                        variant="outline" 
                                                        size="icon" 
                                                        class="h-8 w-8 shrink-0" 
                                                        :class="{ 'text-primary border-primary bg-primary/5': isDynamic(selectedBlock, field.key) }"
                                                        title="Dynamic Content"
                                                    >
                                                        <Database class="w-3.5 h-3.5" />
                                                    </Button>
                                                </PopoverTrigger>
                                                <PopoverContent class="w-60 p-2" align="end">
                                                    <div class="space-y-2">
                                                        <h4 class="font-medium text-xs text-muted-foreground uppercase">Connect to...</h4>
                                                        <div class="grid gap-1">
                                                            <Button 
                                                                v-for="source in dynamicSources" 
                                                                :key="source.id"
                                                                variant="ghost" 
                                                                size="sm" 
                                                                class="justify-start h-8 text-xs font-normal"
                                                                @click="setDynamic(selectedBlock, field.key, source.id)"
                                                            >
                                                                <component :is="sourceIcons[source.icon] || Database" class="w-3 h-3 mr-2 opacity-70" />
                                                                {{ source.label }}
                                                            </Button>
                                                            <Button 
                                                                v-if="isDynamic(selectedBlock, field.key)"
                                                                variant="ghost" 
                                                                size="sm" 
                                                                class="justify-start h-8 text-xs font-normal text-destructive hover:text-destructive"
                                                                @click="setDynamic(selectedBlock, field.key, null)"
                                                            >
                                                                <Unlink class="w-3 h-3 mr-2" />
                                                                Disconnect
                                                            </Button>
                                                        </div>
                                                    </div>
                                                </PopoverContent>
                                            </Popover>
                                        </div>
                                        <div v-if="field.type === 'textarea' || field.type === 'richtext'">
                                            <Textarea v-model="selectedBlock.settings[field.key]" class="min-h-[80px] text-xs bg-background border-input" />
                                        </div>

                                        <div v-if="field.type === 'image'">
                                            <div class="flex gap-2">
                                                <Input v-model="selectedBlock.settings[field.key]" class="h-8 text-xs bg-background border-input" placeholder="https://..." />
                                                <Button variant="outline" size="icon" class="h-8 w-8 shrink-0" @click="openMediaPickerFor(field.key)" title="Media Library">
                                                    <ImageIcon class="w-3.5 h-3.5" />
                                                </Button>
                                                <!-- Dynamic Trigger -->
                                                <Popover>
                                                    <PopoverTrigger asChild>
                                                        <Button 
                                                            variant="outline" 
                                                            size="icon" 
                                                            class="h-8 w-8 shrink-0" 
                                                            :class="{ 'text-primary border-primary bg-primary/5': isDynamic(selectedBlock, field.key) }"
                                                            title="Dynamic Content"
                                                        >
                                                            <Database class="w-3.5 h-3.5" />
                                                        </Button>
                                                    </PopoverTrigger>
                                                    <PopoverContent class="w-60 p-2" align="end">
                                                        <div class="space-y-2">
                                                            <h4 class="font-medium text-xs text-muted-foreground uppercase">Connect to...</h4>
                                                            <div class="grid gap-1">
                                                                <Button 
                                                                    v-for="source in dynamicSources" 
                                                                    :key="source.id"
                                                                    variant="ghost" 
                                                                    size="sm" 
                                                                    class="justify-start h-8 text-xs font-normal"
                                                                    @click="setDynamic(selectedBlock, field.key, source.id)"
                                                                >
                                                                    <component :is="sourceIcons[source.icon] || Database" class="w-3 h-3 mr-2 opacity-70" />
                                                                    {{ source.label }}
                                                                </Button>
                                                                <Button 
                                                                    v-if="isDynamic(selectedBlock, field.key)"
                                                                    variant="ghost" 
                                                                    size="sm" 
                                                                    class="justify-start h-8 text-xs font-normal text-destructive hover:text-destructive"
                                                                    @click="setDynamic(selectedBlock, field.key, null)"
                                                                >
                                                                    <Unlink class="w-3 h-3 mr-2" />
                                                                    Disconnect
                                                                </Button>
                                                            </div>
                                                        </div>
                                                    </PopoverContent>
                                                </Popover>
                                            </div>
                                        </div>

                                        <div v-if="field.type === 'repeater'" class="space-y-2">
                                            <div v-for="(item, idx) in selectedBlock.settings[field.key]" :key="idx" class="p-2 border rounded-md bg-muted/20">
                                                <div class="flex justify-between items-center mb-2">
                                                    <span class="text-[10px] font-bold">{{ field.itemLabel }} {{ idx + 1 }}</span>
                                                    <Button variant="ghost" size="icon" class="h-5 w-5 text-destructive" @click="selectedBlock.settings[field.key].splice(idx, 1)"><Trash2 class="w-3 h-3" /></Button>
                                                </div>
                                                <div class="space-y-2">
                                                    <template v-for="subField in field.fields" :key="subField.key">
                                                        <div class="space-y-1">
                                                            <label class="text-[9px] uppercase text-muted-foreground">{{ subField.label }}</label>
                                                            <Input v-if="subField.type === 'text'" v-model="item[subField.key]" class="h-7 text-xs" />
                                                            <Textarea v-if="subField.type === 'textarea'" v-model="item[subField.key]" class="min-h-[40px] text-xs" />
                                                             <div v-if="subField.type === 'image'" class="flex gap-1">
                                                                <Input v-model="item[subField.key]" class="h-7 text-xs" />
                                                            </div>
                                                        </div>
                                                    </template>
                                                </div>
                                            </div>
                                            <Button variant="outline" size="sm" class="w-full text-xs" @click="selectedBlock.settings[field.key].push(createDefaultItem(field))">
                                                Add {{ field.itemLabel }}
                                            </Button>
                                        </div>

                                        <!-- Boolean / Toggle Field -->
                                        <div v-if="field.type === 'boolean'" class="flex items-center justify-between">
                                            <Switch 
                                                :checked="selectedBlock.settings[field.key]" 
                                                @update:checked="selectedBlock.settings[field.key] = $event"
                                            />
                                        </div>
                                    </div>
                                </template>
                            </AccordionContent>
                        </AccordionItem>

                        <AccordionItem value="style" class="border-sidebar-border">
                            <AccordionTrigger class="text-xs font-bold uppercase tracking-widest text-sidebar-foreground py-3 hover:no-underline hover:bg-sidebar-accent/50 px-2 -mx-2 rounded-md transition-colors">
                                {{ t('features.builder.properties.tabs.style') }}
                            </AccordionTrigger>
                            <AccordionContent class="space-y-4 pt-4 pb-2">
                                <template v-for="field in getBlockDefinition(selectedBlock.type).settings" :key="field.key">
                                    <div v-if="['color', 'select'].includes(field.type) && field.key !== 'alignment'" class="space-y-1.5">
                                        <label class="text-[10px] font-bold uppercase text-muted-foreground">{{ field.label }}</label>
                                        
                                        <div v-if="field.type === 'color'" class="space-y-2">
                                            <!-- Quick Palette -->
                                            <div v-if="themeColors.length > 0" class="flex flex-wrap gap-1.5 p-1.5 bg-muted/30 rounded-md border border-border/50">
                                                <button 
                                                    v-for="color in themeColors" 
                                                    :key="color.variable"
                                                    class="w-5 h-5 rounded-full border border-border shadow-sm transition-transform hover:scale-110 focus:outline-none focus:ring-2 focus:ring-primary/50"
                                                    :style="{ backgroundColor: color.value }"
                                                    :title="color.name"
                                                    @click="selectedBlock.settings[field.key] = color.variable"
                                                >
                                                </button>
                                            </div>
                                            
                                            <div class="flex gap-2">
                                                <div class="w-8 h-8 rounded border border-border shadow-sm shrink-0" :style="{ backgroundColor: selectedBlock.settings[field.key] }"></div>
                                                <Input v-model="selectedBlock.settings[field.key]" class="h-8 text-xs font-mono bg-background border-input" />
                                            </div>
                                        </div>

                                        <div v-if="field.type === 'select'">
                                            <select v-model="selectedBlock.settings[field.key]" class="w-full h-8 px-2 bg-background border border-input rounded-md text-xs outline-none focus:ring-1 focus:ring-primary/20 text-foreground">
                                                <option v-for="opt in field.options" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
                                            </select>
                                        </div>
                                    </div>
                                </template>
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

            <!-- LAYERS TAB -->
            <div v-else-if="builder.activeRightSidebarTab.value === 'layers'" class="space-y-2">
                 <h3 class="text-[10px] font-bold uppercase tracking-widest text-muted-foreground mb-4 px-2">{{ t('features.builder.properties.layers') }}</h3>
                
                 <draggable
                    v-model="builder.blocks.value"
                    group="layers"
                    item-key="id"
                    handle=".drag-handle"
                    class="space-y-1"
                >
                    <template #item="{ element: block, index }">
                        <div 
                            class="group flex items-center gap-2 p-2 rounded-md border text-sm transition-all cursor-pointer hover:border-primary/50"
                            :class="builder.editingIndex.value === index ? 'bg-primary/5 border-primary text-primary' : 'bg-sidebar-accent/20 border-transparent hover:bg-sidebar-accent text-sidebar-foreground'"
                            @click="selectBlock(index)"
                        >
                            <GripVertical class="w-4 h-4 text-muted-foreground opacity-0 group-hover:opacity-50 cursor-move drag-handle" />
                            <component :is="builder.getBlockComponent(block.type)?.icon" class="w-3.5 h-3.5 shrink-0 opacity-70" />
                            <span class="truncate font-medium flex-1">{{ builder.getBlockLabel(block.type) }}</span>
                            <span v-if="builder.editingIndex.value === index" class="w-1.5 h-1.5 rounded-full bg-primary"></span>
                        </div>
                    </template>
                </draggable>
            </div>
        </div>
    </div>
</template>

<script setup>
import { inject, computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { 
    PanelRightClose, 
    Settings2, 
    Layers, 
    Image as ImageIcon, 
    AlignLeft, 
    AlignCenter, 
    AlignRight, 
    MousePointerClick,
    GripVertical,
    Trash2,
    Smartphone,
    Tablet,
    Monitor
} from 'lucide-vue-next';
import Button from '@/components/ui/button.vue';
import Input from '@/components/ui/input.vue';
import Textarea from '@/components/ui/textarea.vue';
import Badge from '@/components/ui/badge.vue';
import Accordion from '@/components/ui/accordion.vue';
import AccordionContent from '@/components/ui/accordion-content.vue';
import AccordionItem from '@/components/ui/accordion-item.vue';
import AccordionTrigger from '@/components/ui/accordion-trigger.vue';
import Switch from '@/components/ui/switch.vue';
import draggable from 'vuedraggable';
import { blockRegistry } from '../BlockRegistry';
import { useTheme } from '@/composables/useTheme';

const builder = inject('builder');
const { t } = useI18n();
const { activeTheme, getSetting } = useTheme();

const selectedBlock = computed(() => {
    if (builder.editingIndex.value === null) return null;
    return builder.blocks.value[builder.editingIndex.value];
});

const selectBlock = (index) => {
    builder.editingIndex.value = index;
    // Auto switch to properties when selecting from layers (optional, but good UX)
    // builder.activeRightSidebarTab.value = 'properties'; 
};

const setTab = (tab) => {
    builder.activeRightSidebarTab.value = tab;
    if (!builder.isRightSidebarOpen.value) {
        builder.isRightSidebarOpen.value = true;
    }
};

const openMediaPickerFor = (key) => {
    builder.activeMediaField.value = key;
    builder.activeBlockId.value = selectedBlock.value.id;
    builder.showMediaPicker.value = true;
};

const themeColors = computed(() => {
    if (!activeTheme.value?.manifest?.settings_schema) return [];
    
    return Object.entries(activeTheme.value.manifest.settings_schema)
        .filter(([_, setting]) => setting.type === 'color')
        .map(([key, setting]) => ({
            name: setting.label || key,
            value: getSetting(key),
            variable: `var(--theme-${key.replace(/_/g, '-')})` // Assumes standard naming convention from useTheme
        }));
});

const getBlockDefinition = (type) => {
    return blockRegistry.get(type) || { settings: [] };
};

const createDefaultItem = (field) => {
    if (!field.fields) return {};
    const item = {};
    field.fields.forEach(f => {
        item[f.key] = f.default || '';
    });
    return item;
};

// Dynamic Content Logic
import { dynamicContent } from '@/services/DynamicContentService';
import Popover from '@/components/ui/popover.vue';
import PopoverTrigger from '@/components/ui/popover-trigger.vue';
import PopoverContent from '@/components/ui/popover-content.vue';
import { Database, Unlink, FileText, Calendar, User, Globe } from 'lucide-vue-next';

const dynamicSources = dynamicContent.getSources();
const sourceIcons = { FileText, Calendar, User, Globe, AlignLeft };

const isDynamic = (block, key) => {
    return !!(block.dynamicSettings && block.dynamicSettings[key]);
};

const getDynamicLabel = (block, key) => {
    const sourceId = block.dynamicSettings?.[key];
    const source = dynamicSources.find(s => s.id === sourceId);
    return source ? `Dynamic: ${source.label}` : '';
};

const setDynamic = (block, key, sourceId) => {
    if (!block.dynamicSettings) {
        block.dynamicSettings = {};
    }
    
    if (sourceId) {
        block.dynamicSettings[key] = sourceId;
    } else {
        delete block.dynamicSettings[key];
    }
};
</script>
