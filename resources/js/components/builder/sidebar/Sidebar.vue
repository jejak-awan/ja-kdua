<template>
    <div 
        class="border-r border-border bg-sidebar flex flex-col shrink-0 transition-all duration-300 ease-in-out relative overflow-hidden"
        :class="builder.isSidebarOpen.value ? 'w-72' : 'w-14'"
    >
        <div class="p-4 border-b border-sidebar-border bg-sidebar-accent/10 flex items-center justify-between h-14 shrink-0">
            <h3 v-if="builder.isSidebarOpen.value" class="font-bold text-sm tracking-tight text-sidebar-foreground truncate">{{ t('features.builder.sidebar.title') }}</h3>
            <Button 
                variant="ghost" 
                size="icon" 
                class="h-6 w-6 rounded-lg text-sidebar-foreground hover:bg-sidebar-accent ml-auto" 
                @click="builder.isSidebarOpen.value = !builder.isSidebarOpen.value"
            >
                <PanelLeftClose class="w-4 h-4 transition-transform duration-300" :class="!builder.isSidebarOpen.value ? 'rotate-180' : ''" />
            </Button>
        </div>
        
        <!-- Search Widgets (Only visible when open) -->
        <div v-if="builder.isSidebarOpen.value" class="p-4 pt-2 pb-0 opacity-100 transition-opacity duration-300 delay-100">
            <div class="relative">
                <SearchIcon class="absolute left-2.5 top-2.5 h-4 w-4 text-muted-foreground" />
                <Input 
                    v-model="builder.widgetSearch.value" 
                    :placeholder="t('features.builder.sidebar.searchPlaceholder')" 
                    class="pl-9 h-9 text-xs bg-sidebar-accent/50 border-sidebar-border focus-visible:ring-1 focus-visible:ring-primary/20 text-sidebar-foreground placeholder:text-muted-foreground" 
                />
            </div>
        </div>
        
        <div class="flex-1 overflow-y-auto p-2 custom-scrollbar bg-sidebar">
             <!-- Elements Badge/Header -->
            <div v-if="builder.isSidebarOpen.value" class="px-2 pb-2 mt-4 flex items-center justify-between">
                 <Badge variant="outline" class="text-[10px] font-bold uppercase tracking-widest px-1.5 h-5 bg-background text-foreground border-border">{{ t('features.builder.sidebar.elements') }}</Badge>
            </div>

            <draggable
                :list="builder.availableBlocks"
                :group="{ name: 'blocks', pull: 'clone', put: false }"
                :sort="false"
                :clone="builder.cloneBlock"
                @end="onDragEnd"
                item-key="name"
                class="grid gap-2"
                :class="builder.isSidebarOpen.value ? 'grid-cols-2 p-2' : 'grid-cols-1'"
            >
                <template #item="{ element: type }">
                    <div 
                        v-show="!builder.widgetSearch.value || type.label.toLowerCase().includes(builder.widgetSearch.value.toLowerCase())"
                        class="p-2 border border-transparent rounded-lg hover:bg-sidebar-accent hover:text-sidebar-accent-foreground cursor-grab active:cursor-grabbing transition-all flex flex-col items-center gap-2 text-center group relative"
                        :class="!builder.isSidebarOpen.value ? 'justify-center py-3' : 'border-sidebar-border bg-sidebar-accent/20'"
                    >
                        <div 
                            class="w-8 h-8 rounded-md flex items-center justify-center transition-colors"
                            :class="builder.isSidebarOpen.value ? 'bg-background border border-border/50 group-hover:bg-primary/10 group-hover:text-primary text-sidebar-foreground' : 'text-muted-foreground group-hover:text-foreground'"
                        >
                            <component :is="type.icon" class="w-4 h-4" />
                        </div>
                        
                        <span v-if="builder.isSidebarOpen.value" class="text-[10px] font-medium leading-none truncate w-full group-hover:text-primary transition-colors text-sidebar-foreground">{{ type.label }}</span>
                        
                        <!-- Tooltip for minimized state -->
                        <div v-if="!builder.isSidebarOpen.value" class="absolute left-full top-1/2 -translate-y-1/2 ml-2 px-2 py-1 bg-popover text-popover-foreground text-xs rounded shadow-md opacity-0 group-hover:opacity-100 pointer-events-none whitespace-nowrap z-50 border border-border">
                            {{ type.label }}
                        </div>
                    </div>
                </template>
            </draggable>
        </div>
        
        <!-- Sidebar Footer -->
        <div class="p-3 border-t border-sidebar-border bg-sidebar shrink-0">
            <Button 
                variant="ghost" 
                size="sm" 
                class="w-full text-sidebar-foreground hover:bg-sidebar-accent h-9" 
                :class="!builder.isSidebarOpen.value ? 'px-0 justify-center' : 'justify-start'"
                @click="builder.showTemplateLibrary.value = true"
            >
                <LayoutTemplate class="w-4 h-4" :class="builder.isSidebarOpen.value ? 'mr-2' : ''" />
                <span v-if="builder.isSidebarOpen.value" class="text-[10px] font-bold uppercase tracking-widest">{{ t('features.builder.sidebar.layoutLibrary') }}</span>
            </Button>
        </div>

        <!-- Template Library Modal -->
        <TemplateLibrary />
    </div>
</template>

<script setup>
import { inject } from 'vue';
import { useI18n } from 'vue-i18n';
import draggable from 'vuedraggable';
import { Search as SearchIcon, LayoutTemplate, PanelLeftClose } from 'lucide-vue-next';
import Button from '@/components/ui/button.vue';
import Input from '@/components/ui/input.vue';
import Badge from '@/components/ui/badge.vue';
import TemplateLibrary from './TemplateLibrary.vue';

const builder = inject('builder');
const { t } = useI18n();

const onDragEnd = (evt) => {
    // Logic handled by draggable clone, but we can hook here if needed
};
</script>
