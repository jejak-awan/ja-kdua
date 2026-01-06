<template>
    <div 
        class="border-r border-border bg-sidebar flex flex-col shrink-0 transition-[width] duration-200 ease-in-out relative overflow-hidden"
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
        
        <div 
            ref="scrollContainer"
            @scroll="handleScroll"
            class="flex-1 overflow-y-auto p-2 pb-16 custom-scrollbar bg-sidebar"
        >
            <!-- Categorized Blocks (when sidebar is expanded) -->
            <template v-if="builder.isSidebarOpen.value">
                <div v-for="category in categorizedBlocks" :key="category.name" class="mb-4">
                    <!-- Category Header -->
                    <button 
                        @click="toggleCategory(category.name)"
                        class="w-full flex items-center justify-between px-2 py-2 text-[10px] font-bold text-muted-foreground hover:text-foreground transition-colors"
                    >
                        <span class="flex items-center gap-2">
                            <component :is="category.icon" class="w-3.5 h-3.5" />
                            {{ category.label }}
                        </span>
                        <ChevronDown 
                            class="w-3.5 h-3.5 transition-transform duration-200" 
                            :class="collapsedCategories[category.name] ? '-rotate-90' : ''" 
                        />
                    </button>
                    
                    <!-- Category Blocks -->
                    <div v-show="!collapsedCategories[category.name]" class="space-y-1">
                        <draggable
                            :list="category.blocks"
                            :group="{ name: 'blocks', pull: 'clone', put: false }"
                            :sort="false"
                            :clone="builder.cloneBlock"
                            item-key="name"
                            class="grid grid-cols-2 gap-2 p-2"
                        >
                            <template #item="{ element: type }">
                                <div 
                                    class="p-2 border border-sidebar-border bg-sidebar-accent/20 rounded-lg hover:bg-sidebar-accent hover:text-sidebar-accent-foreground cursor-grab active:cursor-grabbing transition-colors flex flex-col items-center gap-2 text-center group"
                                >
                                    <div class="w-8 h-8 rounded-md flex items-center justify-center transition-colors bg-sidebar-accent/50 border border-sidebar-border group-hover:bg-primary/10 group-hover:text-primary text-sidebar-foreground">
                                        <component :is="type.icon" class="w-4 h-4" />
                                    </div>
                                    <span class="text-[10px] font-medium leading-none truncate w-full group-hover:text-primary transition-colors text-sidebar-foreground">{{ type.label }}</span>
                                </div>
                            </template>
                        </draggable>
                    </div>
                </div>
            </template>
            
            <!-- Minimized View (icons only) -->
            <template v-else>
                <draggable
                    :list="builder.availableBlocks"
                    :group="{ name: 'blocks', pull: 'clone', put: false }"
                    :sort="false"
                    :clone="builder.cloneBlock"
                    item-key="name"
                    class="grid grid-cols-1 gap-2"
                >
                    <template #item="{ element: type }">
                        <div 
                            class="p-2 border border-transparent rounded-lg hover:bg-sidebar-accent cursor-grab active:cursor-grabbing transition-colors flex flex-col items-center justify-center py-3 group relative"
                        >
                            <div class="w-8 h-8 rounded-md flex items-center justify-center text-muted-foreground group-hover:text-foreground transition-colors">
                                <component :is="type.icon" class="w-4 h-4" />
                            </div>
                            <!-- Tooltip -->
                            <div class="absolute left-full top-1/2 -translate-y-1/2 ml-2 px-2 py-1 bg-popover text-popover-foreground text-xs rounded shadow-md opacity-0 group-hover:opacity-100 pointer-events-none whitespace-nowrap z-50 border border-border">
                                {{ type.label }}
                            </div>
                        </div>
                    </template>
                </draggable>
            </template>
        </div>
        
        <!-- Sidebar Footer -->
        <div class="p-3 border-t border-sidebar-border bg-sidebar shrink-0 space-y-2">
            <Button 
                variant="ghost" 
                size="sm" 
                class="w-full text-sidebar-foreground hover:bg-sidebar-accent h-9" 
                :class="!builder.isSidebarOpen.value ? 'px-0 justify-center' : 'justify-start'"
                @click="builder.showTemplateLibrary.value = true"
            >
                <LayoutTemplate class="w-4 h-4" :class="builder.isSidebarOpen.value ? 'mr-2' : ''" />
                <span v-if="builder.isSidebarOpen.value" class="text-[10px] font-bold">{{ t('features.builder.sidebar.layoutLibrary') }}</span>
            </Button>
            
            <!-- Keyboard Shortcuts Help -->
            <Button 
                v-if="builder.isSidebarOpen.value"
                variant="ghost" 
                size="sm" 
                class="w-full text-muted-foreground hover:bg-sidebar-accent h-8 justify-start text-[10px]"
                @click="showShortcuts = !showShortcuts"
            >
                <Keyboard class="w-3.5 h-3.5 mr-2" />
                Keyboard Shortcuts
            </Button>
        </div>

        <!-- Keyboard Shortcuts Modal -->
        <div 
            v-if="showShortcuts" 
            class="absolute inset-0 bg-background/95 backdrop-blur-sm z-50 p-4 overflow-y-auto"
        >
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-bold text-sm">{{ t('features.builder.sidebar.keyboardShortcuts.title') }}</h3>
                <Button variant="ghost" size="icon" class="h-6 w-6" @click="showShortcuts = false">
                    <X class="w-4 h-4" />
                </Button>
            </div>
            <div class="space-y-3 text-xs">
                <div class="flex justify-between items-center py-1 border-b border-border">
                    <span class="text-muted-foreground">{{ t('features.builder.sidebar.keyboardShortcuts.undo') }}</span>
                    <kbd class="px-2 py-0.5 bg-muted rounded text-[10px] font-mono">Ctrl + Z</kbd>
                </div>
                <div class="flex justify-between items-center py-1 border-b border-border">
                    <span class="text-muted-foreground">{{ t('features.builder.sidebar.keyboardShortcuts.redo') }}</span>
                    <kbd class="px-2 py-0.5 bg-muted rounded text-[10px] font-mono">Ctrl + Y</kbd>
                </div>
                <div class="flex justify-between items-center py-1 border-b border-border">
                    <span class="text-muted-foreground">{{ t('features.builder.sidebar.keyboardShortcuts.copyBlock') }}</span>
                    <kbd class="px-2 py-0.5 bg-muted rounded text-[10px] font-mono">Ctrl + C</kbd>
                </div>
                <div class="flex justify-between items-center py-1 border-b border-border">
                    <span class="text-muted-foreground">{{ t('features.builder.sidebar.keyboardShortcuts.cutBlock') }}</span>
                    <kbd class="px-2 py-0.5 bg-muted rounded text-[10px] font-mono">Ctrl + X</kbd>
                </div>
                <div class="flex justify-between items-center py-1 border-b border-border">
                    <span class="text-muted-foreground">{{ t('features.builder.sidebar.keyboardShortcuts.pasteBlock') }}</span>
                    <kbd class="px-2 py-0.5 bg-muted rounded text-[10px] font-mono">Ctrl + V</kbd>
                </div>
                <div class="flex justify-between items-center py-1 border-b border-border">
                    <span class="text-muted-foreground">{{ t('features.builder.sidebar.keyboardShortcuts.duplicateBlock') }}</span>
                    <kbd class="px-2 py-0.5 bg-muted rounded text-[10px] font-mono">Ctrl + D</kbd>
                </div>
                <div class="flex justify-between items-center py-1 border-b border-border">
                    <span class="text-muted-foreground">{{ t('features.builder.sidebar.keyboardShortcuts.deleteBlock') }}</span>
                    <kbd class="px-2 py-0.5 bg-muted rounded text-[10px] font-mono">Delete</kbd>
                </div>
                <div class="flex justify-between items-center py-1 border-b border-border">
                    <span class="text-muted-foreground">{{ t('features.builder.sidebar.keyboardShortcuts.moveUp') }}</span>
                    <kbd class="px-2 py-0.5 bg-muted rounded text-[10px] font-mono">Alt + ↑</kbd>
                </div>
                <div class="flex justify-between items-center py-1 border-b border-border">
                    <span class="text-muted-foreground">{{ t('features.builder.sidebar.keyboardShortcuts.moveDown') }}</span>
                    <kbd class="px-2 py-0.5 bg-muted rounded text-[10px] font-mono">Alt + ↓</kbd>
                </div>
                <div class="flex justify-between items-center py-1">
                    <span class="text-muted-foreground">{{ t('features.builder.sidebar.keyboardShortcuts.deselect') }}</span>
                    <kbd class="px-2 py-0.5 bg-muted rounded text-[10px] font-mono">Escape</kbd>
                </div>
            </div>
        </div>

        <!-- Template Library Modal -->
        <TemplateLibrary />
        
        <BackToTop 
            :show="showBackToTop && builder.isSidebarOpen.value" 
            positionClass="bottom-32 left-1/2 -translate-x-1/2"
            @click="scrollToTop" 
        />
    </div>
</template>

<script setup>
import { inject, ref, computed, reactive, watch, onMounted, onUnmounted } from 'vue';
import { useI18n } from 'vue-i18n';
import draggable from 'vuedraggable';
import BackToTop from '@/components/ui/back-to-top.vue';
import { useScrollToTop } from '@/composables/useScrollToTop';
import { useSidebar } from '@/composables/useSidebar';
import { 
    Search as SearchIcon, 
    LayoutTemplate, 
    PanelLeftClose,
    ChevronDown,
    Keyboard,
    X,
    // Category Icons
    Type,
    ImageIcon,
    FileText,
    Columns3,
    Zap,
    Database,
    ShoppingBag,
    LayoutPanelTop
} from 'lucide-vue-next';
import Button from '@/components/ui/button.vue';
import Input from '@/components/ui/input.vue';
import Badge from '@/components/ui/badge.vue';
import TemplateLibrary from './TemplateLibrary.vue';

const builder = inject('builder');
const { sidebarMinimized, sidebarOpen, toggleSidebarMinimize, toggleSidebarOpen, closeSidebar } = useSidebar();
const { t } = useI18n();

const scrollContainer = ref(null);
const { showBackToTop, handleScroll, scrollToTop } = useScrollToTop(scrollContainer);

const showShortcuts = ref(false);

// Category definitions with block name mappings
const categoryDefinitions = [
    {
        name: 'structure',
        label: 'Structure',
        icon: LayoutPanelTop,
        blocks: ['section', 'columns']
    },
    {
        name: 'basic',
        label: 'Basic',
        icon: Type,
        blocks: ['heading', 'text', 'button', 'icon', 'divider', 'spacer']
    },
    {
        name: 'media',
        label: 'Media',
        icon: ImageIcon,
        blocks: ['image', 'gallery', 'video', 'audio', 'slider', 'map']
    },
    {
        name: 'content',
        label: 'Content',
        icon: FileText,
        blocks: ['hero', 'cta', 'feature_grid', 'quote', 'testimonial', 'blurb', 'person', 'alert', 'icon_list', 'social_links', 'code']
    },
    {
        name: 'layout',
        label: 'Layout',
        icon: Columns3,
        blocks: ['accordion', 'tabs', 'toggle', 'menu', 'sidebar']
    },
    {
        name: 'interactive',
        label: 'Interactive',
        icon: Zap,
        blocks: ['contact_form', 'email_optin', 'login', 'search', 'counter', 'circle_counter', 'progress_bar', 'countdown', 'pricing', 'star_rating', 'comments']
    },
    {
        name: 'dynamic',
        label: 'Dynamic',
        icon: Database,
        blocks: ['blog-grid', 'post_nav', 'post_slider', 'post_title', 'post_content', 'portfolio', 'PostCarousel', 'TabbedPosts']
    },
    {
        name: 'commerce',
        label: 'Shop',
        icon: ShoppingBag,
        blocks: ['shop', 'woo_title', 'woo_price', 'woo_add_to_cart', 'woo_images', 'woo_tabs', 'Cart']
    },
    {
        name: 'magazine',
        label: 'Magazine',
        icon: FileText,
        blocks: ['PostCarousel', 'TabbedPosts']
    }
];

// Collapse state per category
const collapsedCategories = reactive({});

const toggleCategory = (name) => {
    // If clicking already open category, close it
    if (!collapsedCategories[name]) {
        // Close all other categories first (single collapsible mode)
        Object.keys(collapsedCategories).forEach(key => {
            collapsedCategories[key] = true;
        });
    }
    // Toggle clicked category
    collapsedCategories[name] = !collapsedCategories[name];
};

// Group blocks by category
const categorizedBlocks = computed(() => {
    const allBlocks = builder.availableBlocks;
    const search = builder.widgetSearch.value.toLowerCase();
    
    return categoryDefinitions.map(cat => {
        const blocks = cat.blocks
            .map(blockName => allBlocks.find(b => b.name === blockName))
            .filter(Boolean)
            .filter(b => !search || b.label.toLowerCase().includes(search));
            
        return {
            ...cat,
            blocks
        };
    }).filter(cat => cat.blocks.length > 0);
});

// Watch search to expand all categories when searching
watch(() => builder.widgetSearch.value, (newSearch) => {
    if (newSearch) {
        // Expand all categories that have results
        categorizedBlocks.value.forEach(cat => {
            collapsedCategories[cat.name] = false;
        });
    }
});

// Initialize collapsed state (all collapsed except first category or those with results)
onMounted(() => {
    categorizedBlocks.value.forEach((cat, index) => {
        if (collapsedCategories[cat.name] === undefined) {
            collapsedCategories[cat.name] = index !== 0; // false for first, true for rest
        }
    });
});
</script>
