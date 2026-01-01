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
                    <!-- Selected Block Info -->
                    <div class="flex items-center gap-3 p-3 bg-sidebar-accent/30 rounded-lg border border-sidebar-border">
                        <div class="w-8 h-8 rounded-md bg-background flex items-center justify-center border border-border shadow-sm text-primary">
                            <component :is="builder.getBlockComponent(selectedBlock.type)?.icon" class="w-4 h-4" />
                        </div>
                        <div class="overflow-hidden">
                            <h3 class="font-bold text-xs text-foreground truncate">{{ builder.getBlockLabel(selectedBlock.type) }}</h3>
                            <p class="text-[10px] text-muted-foreground font-mono truncate">#{{ selectedBlock.id.slice(0, 8) }}</p>
                        </div>
                    </div>

                    <!-- Accordion Settings -->
                    <Accordion type="multiple" class="w-full" :defaultValue="['content']">
                        <!-- Content Section -->
                        <AccordionItem value="content" class="border-sidebar-border">
                            <AccordionTrigger class="text-xs font-bold uppercase tracking-widest text-sidebar-foreground py-3 hover:no-underline hover:bg-sidebar-accent/50 px-2 -mx-2 rounded-md transition-colors">
                                {{ t('features.builder.properties.tabs.content') }}
                            </AccordionTrigger>
                            <AccordionContent class="space-y-4 pt-4 pb-2">
                                <template v-for="(value, key) in selectedBlock.settings" :key="key">
                                    <template v-if="['content', 'description', 'title', 'subtitle', 'buttonText', 'label', 'placeholder'].some(k => key.toLowerCase().includes(k)) && !key.toLowerCase().includes('color')">
                                        <div class="space-y-1.5">
                                            <label class="text-[10px] font-bold uppercase text-muted-foreground">{{ key.replace(/([A-Z])/g, ' $1').trim() }}</label>
                                            <div v-if="['content', 'description'].some(k => key.toLowerCase().includes(k))">
                                                 <Textarea v-model="selectedBlock.settings[key]" class="min-h-[80px] text-xs bg-background border-input" />
                                            </div>
                                            <Input v-else v-model="selectedBlock.settings[key]" class="h-8 text-xs bg-background border-input" />
                                        </div>
                                    </template>
                                     <template v-else-if="(key.toLowerCase().includes('url') || key.toLowerCase().includes('image') || key === 'src') && !key.includes('bg')">
                                        <div class="space-y-1.5">
                                            <label class="text-[10px] font-bold uppercase text-muted-foreground">{{ key.replace(/([A-Z])/g, ' $1').trim() }}</label>
                                            <div class="flex gap-2">
                                                <Input v-model="selectedBlock.settings[key]" class="h-8 text-xs bg-background border-input" />
                                                <Button variant="outline" size="icon" class="h-8 w-8 shrink-0" @click="openMediaPickerFor(key)">
                                                    <ImageIcon class="w-3.5 h-3.5" />
                                                </Button>
                                            </div>
                                        </div>
                                    </template>
                                </template>
                            </AccordionContent>
                        </AccordionItem>

                        <!-- Style Section -->
                        <AccordionItem value="style" class="border-sidebar-border">
                            <AccordionTrigger class="text-xs font-bold uppercase tracking-widest text-sidebar-foreground py-3 hover:no-underline hover:bg-sidebar-accent/50 px-2 -mx-2 rounded-md transition-colors">
                                {{ t('features.builder.properties.tabs.style') }}
                            </AccordionTrigger>
                            <AccordionContent class="space-y-4 pt-4 pb-2">
                                <!-- Background Color -->
                                <div v-if="'bgColor' in selectedBlock.settings" class="space-y-1.5">
                                    <label class="text-[10px] font-bold uppercase text-muted-foreground">{{ t('features.builder.properties.fields.backgroundColor') }}</label>
                                    <div class="flex gap-2">
                                        <div class="w-8 h-8 rounded border border-border shadow-sm shrink-0" :style="{ backgroundColor: selectedBlock.settings.bgColor }"></div>
                                        <Input v-model="selectedBlock.settings.bgColor" class="h-8 text-xs font-mono bg-background border-input" />
                                    </div>
                                </div>
                                
                                <!-- Background Image -->
                                <div v-if="'bgImage' in selectedBlock.settings" class="space-y-1.5">
                                    <label class="text-[10px] font-bold uppercase text-muted-foreground">{{ t('features.builder.properties.fields.backgroundImage') }}</label>
                                    <div class="flex gap-2">
                                        <Input v-model="selectedBlock.settings.bgImage" class="h-8 text-xs bg-background border-input" placeholder="https://..." />
                                        <Button variant="outline" size="icon" class="h-8 w-8 shrink-0" @click="openMediaPickerFor('bgImage')">
                                            <ImageIcon class="w-3.5 h-3.5" />
                                        </Button>
                                    </div>
                                </div>

                                <!-- Alignment -->
                                <div v-if="'alignment' in selectedBlock.settings" class="space-y-1.5">
                                    <label class="text-[10px] font-bold uppercase text-muted-foreground">{{ t('features.builder.properties.fields.contentAlignment') }}</label>
                                    <div class="flex p-0.5 bg-muted rounded-md border border-border">
                                        <Button 
                                            v-for="align in [{id:'left',icon:AlignLeft}, {id:'center',icon:AlignCenter}, {id:'right',icon:AlignRight}]" 
                                            :key="align.id"
                                            variant="ghost" 
                                            size="sm"
                                            class="flex-1 h-6 rounded-sm"
                                            :class="selectedBlock.settings.alignment === 'text-' + align.id ? 'bg-background shadow-xs text-foreground' : 'text-muted-foreground'"
                                            @click="selectedBlock.settings.alignment = 'text-' + align.id"
                                        >
                                            <component :is="align.icon" class="w-3.5 h-3.5" />
                                        </Button>
                                    </div>
                                </div>
                            </AccordionContent>
                        </AccordionItem>

                        <!-- Advanced Section -->
                        <AccordionItem value="advanced" class="border-sidebar-border">
                            <AccordionTrigger class="text-xs font-bold uppercase tracking-widest text-sidebar-foreground py-3 hover:no-underline hover:bg-sidebar-accent/50 px-2 -mx-2 rounded-md transition-colors">
                                {{ t('features.builder.properties.tabs.advanced') }}
                            </AccordionTrigger>
                            <AccordionContent class="space-y-4 pt-4 pb-2">
                                <div v-if="'padding' in selectedBlock.settings" class="space-y-1.5">
                                    <label class="text-[10px] font-bold uppercase text-muted-foreground">{{ t('features.builder.properties.fields.verticalPadding') }}</label>
                                    <select v-model="selectedBlock.settings.padding" class="w-full h-8 px-2 bg-background border border-input rounded-md text-xs outline-none focus:ring-1 focus:ring-primary/20 text-foreground">
                                        <option value="py-0">{{ t('features.builder.properties.fields.padding.none') }}</option>
                                        <option value="py-16">{{ t('features.builder.properties.fields.padding.medium') }}</option>
                                        <option value="py-32">{{ t('features.builder.properties.fields.padding.large') }}</option>
                                    </select>
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
    Image as ImageIcon, 
    AlignLeft, 
    AlignCenter, 
    AlignRight, 
    MousePointerClick,
    GripVertical 
} from 'lucide-vue-next';
import Button from '@/components/ui/button.vue';
import Input from '@/components/ui/input.vue';
import Textarea from '@/components/ui/textarea.vue';
import Badge from '@/components/ui/badge.vue';
import Accordion from '@/components/ui/accordion.vue';
import AccordionContent from '@/components/ui/accordion-content.vue';
import AccordionItem from '@/components/ui/accordion-item.vue';
import AccordionTrigger from '@/components/ui/accordion-trigger.vue';
import draggable from 'vuedraggable';

const builder = inject('builder');
const { t } = useI18n();

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
</script>
