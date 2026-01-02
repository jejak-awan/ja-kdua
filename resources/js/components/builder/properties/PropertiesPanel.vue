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
                            <AccordionTrigger class="text-xs font-bold uppercase tracking-widest text-sidebar-foreground py-3 hover:no-underline hover:bg-sidebar-accent/50 px-2 -mx-2 rounded-md transition-colors">
                                {{ t('features.builder.properties.tabs.style') }}
                            </AccordionTrigger>
                            <AccordionContent class="space-y-4 pt-4 pb-2">
                                <template v-for="field in getBlockDefinition(selectedBlock.type).settings" :key="field.key">
                                    <div v-if="['color', 'select'].includes(field.type) && field.key !== 'alignment'">
                                         <PropertyField 
                                            :field="field" 
                                            :block="selectedBlock"
                                            v-model="selectedBlock.settings[field.key]"
                                        />
                                    </div>
                                </template>
                            </AccordionContent>
                        </AccordionItem>

                        <AccordionItem value="advanced" class="border-sidebar-border">
                            <AccordionTrigger class="text-xs font-bold uppercase tracking-widest text-sidebar-foreground py-3 hover:no-underline hover:bg-sidebar-accent/50 px-2 -mx-2 rounded-md transition-colors">
                                Advanced
                            </AccordionTrigger>
                            <AccordionContent class="space-y-4 pt-4 pb-2">
                                <div class="space-y-3">
                                    <div class="space-y-1.5">
                                        <label class="text-[10px] font-bold uppercase text-muted-foreground">CSS ID</label>
                                        <Input v-model="selectedBlock.settings._css_id" placeholder="my-custom-id" class="h-8 text-xs bg-background border-input font-mono" />
                                    </div>
                                    <div class="space-y-1.5">
                                        <label class="text-[10px] font-bold uppercase text-muted-foreground">CSS Classes</label>
                                        <Input v-model="selectedBlock.settings._css_class" placeholder="p-4 bg-red-500" class="h-8 text-xs bg-background border-input font-mono" />
                                        <p class="text-[9px] text-muted-foreground">Space separated classes (Tailwind supported)</p>
                                    </div>
                                    <div class="space-y-1.5">
                                        <label class="text-[10px] font-bold uppercase text-muted-foreground">Custom CSS</label>
                                        <Textarea v-model="selectedBlock.settings._custom_css" placeholder="border: 1px solid red;" class="min-h-[80px] text-xs bg-background border-input font-mono" />
                                    </div>
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
    MousePointerClick,
    GripVertical,
    Smartphone,
    Tablet,
    Monitor
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

const builder = inject('builder');
const { t } = useI18n();

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
</script>
