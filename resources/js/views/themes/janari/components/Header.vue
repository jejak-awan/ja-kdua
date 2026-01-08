<template>
    <header 
        :class="[
            headerSticky ? 'sticky top-0 z-40' : 'relative z-40',
            headerStyleClasses
        ]"
    >
        <!-- Header Top -->
        <div v-if="headerTopItems.length > 0" class="bg-primary/5 border-b border-border/50 py-2 hidden md:block">
            <div class="container mx-auto px-4">
                <div class="flex justify-between items-center text-xs">
                    <div class="flex items-center gap-6">
                        <router-link 
                            v-for="item in headerTopItems" 
                            :key="item.id" 
                            :to="item.url || '/'"
                            class="text-muted-foreground hover:text-primary transition-colors"
                        >
                            {{ item.title }}
                        </router-link>
                    </div>
                    <div class="flex items-center gap-4">
                        <a v-if="getSetting('social_twitter')" :href="getSetting('social_twitter')" target="_blank" class="text-muted-foreground hover:text-primary">Twitter</a>
                        <a v-if="getSetting('social_github')" :href="getSetting('social_github')" target="_blank" class="text-muted-foreground hover:text-primary">GitHub</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="container mx-auto px-4">
            <div class="flex items-center justify-between h-16">
                <!-- Logo & Branding -->
                <router-link to="/" class="flex items-center gap-3 group">
                    <!-- Logo / Stylized Box -->
                    <div 
                        v-if="brandingDisplay !== 'text_only'"
                        class="relative flex items-center justify-center overflow-hidden"
                    >
                        <img 
                            v-if="siteLogo" 
                            :src="siteLogo" 
                            class="h-8 w-auto object-contain" 
                            :alt="siteName"
                        >
                        <div 
                            v-else
                            class="w-8 h-8 rounded-lg bg-primary flex items-center justify-center text-primary-foreground font-bold text-lg group-hover:bg-primary/90 transition-colors"
                        >
                            {{ siteName.substring(0, 2).toUpperCase() }}
                        </div>
                    </div>

                    <!-- Site Name -->
                    <div 
                        v-if="brandingDisplay !== 'logo_only'"
                        class="flex flex-col"
                    >
                        <span class="text-xl font-bold text-foreground leading-none">
                            {{ siteName }}
                        </span>
                        <span v-if="brandingDisplay === 'both'" class="text-[10px] font-medium text-muted-foreground mt-0.5">
                            {{ siteVersion }}
                        </span>
                    </div>
                </router-link>

                <!-- Desktop Nav with Mega Menu Support -->
                <nav class="hidden md:flex items-center gap-1">
                    <template v-for="item in navItems" :key="item.id">
                        <!-- Item with children = Dropdown/Mega Menu -->
                        <div 
                            v-if="item.children && item.children.length > 0"
                            class="group"
                            :class="item.mega_menu_layout === 'full' ? 'static' : 'relative'"
                        >
                            <button :class="getNavItemClasses(isParentActive(item))">
                                <component 
                                    v-if="item.icon" 
                                    :is="getIconComponent(item.icon)" 
                                    class="w-4 h-4" 
                                />
                                {{ item.title }}
                                <ChevronDown class="w-3.5 h-3.5 transition-transform group-hover:rotate-180" />
                            </button>
                            
                            <!-- Mega Menu Dropdown -->
                            <div 
                                class="absolute top-full pt-2 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-50"
                                :class="getDropdownPositionClasses(item.mega_menu_layout)"
                            >
                                <div 
                                    class="bg-card/95 backdrop-blur-xl border border-border rounded-xl shadow-2xl p-4"
                                    :class="getMegaMenuLayoutClasses(item)"
                                >
                                    <!-- CASE A: Explicit Columns (Grid/Full) -->
                                    <template v-if="item.mega_menu_layout && item.mega_menu_layout !== 'default'">
                                        <div 
                                            v-for="(colItems, colIndex) in groupItemsByColumn(item.children, item.mega_menu_layout)" 
                                            :key="colIndex" 
                                            class="flex flex-col gap-2"
                                        >
                                            <template v-for="child in colItems" :key="child.id">
                                                <!-- Group Item (Level 1 has children) -->
                                                <div v-if="child.children && child.children.length > 0" class="mb-6 last:mb-0">
                                                    <div v-if="!child.hide_label" class="flex items-center gap-2 mb-2 px-2">
                                                        <component v-if="child.icon" :is="getIconComponent(child.icon)" class="w-4 h-4 text-primary" />
                                                        <span class="text-xs font-bold uppercase tracking-wider text-muted-foreground">{{ child.heading || child.title }}</span>
                                                    </div>
                                                    <div class="flex flex-col gap-1">
                                                        <router-link
                                                            v-for="subChild in child.children"
                                                            :key="subChild.id"
                                                            :to="subChild.url || '/'"
                                                            :target="subChild.open_in_new_tab ? '_blank' : null"
                                                            class="flex items-center gap-3 p-2.5 rounded-lg hover:bg-muted/50 transition-colors group/subitem"
                                                        >
                                                            <div 
                                                                v-if="subChild.icon"
                                                                class="w-8 h-8 rounded-lg bg-primary/10 flex items-center justify-center shrink-0 group-hover/subitem:bg-primary/20 transition-colors"
                                                            >
                                                                <component :is="getIconComponent(subChild.icon)" class="w-4 h-4 text-primary" />
                                                            </div>
                                                            <div class="flex-1 min-w-0">
                                                                <div class="flex items-center gap-2">
                                                                    <span class="font-medium text-sm text-foreground">{{ subChild.title }}</span>
                                                                    <span v-if="subChild.badge" class="px-1.5 py-0.5 text-[10px] font-medium rounded-full" :class="getBadgeClasses(subChild.badge_color)">{{ subChild.badge }}</span>
                                                                </div>
                                                                <p v-if="subChild.description" class="text-xs text-muted-foreground line-clamp-1">{{ subChild.description }}</p>
                                                            </div>
                                                        </router-link>
                                                    </div>
                                                </div>
                                                
                                                <!-- Direct Link Item (No children) -->
                                                <router-link
                                                    v-else
                                                    :to="child.url || '/'"
                                                    :target="child.open_in_new_tab ? '_blank' : null"
                                                    class="flex items-center gap-4 p-3 rounded-lg hover:bg-muted/50 transition-colors group/item"
                                                >
                                                    <div 
                                                        v-if="child.icon"
                                                        class="w-10 h-10 rounded-lg bg-primary/10 flex items-center justify-center shrink-0 group-hover/item:bg-primary/20 transition-colors"
                                                    >
                                                        <component :is="getIconComponent(child.icon)" class="w-5 h-5 text-primary" />
                                                    </div>
                                                    <div class="flex-1 min-w-0">
                                                        <div class="flex items-center gap-2">
                                                            <span class="font-medium text-sm text-foreground">{{ child.heading || child.title }}</span>
                                                            <span v-if="child.badge" class="px-1.5 py-0.5 text-[10px] font-medium rounded-full" :class="getBadgeClasses(child.badge_color)">{{ child.badge }}</span>
                                                        </div>
                                                        <p v-if="child.description" class="text-xs text-muted-foreground mt-0.5 line-clamp-2">{{ child.description }}</p>
                                                    </div>
                                                </router-link>
                                            </template>
                                        </div>

                                        <!-- Promotional Images in Grid Layout -->
                                        <div 
                                            v-if="hasPromotionalImages(item.children)" 
                                            class="col-span-full mt-4 pt-4 border-t border-border"
                                        >
                                            <div class="grid grid-cols-2 gap-4">
                                                <template v-for="promoChild in getPromotionalItems(item.children)" :key="promoChild.id + '-promo-grid'">
                                                    <router-link 
                                                        :to="promoChild.url || '/'"
                                                        class="relative group/promo rounded-xl overflow-hidden block shadow-sm hover:shadow-md transition-all ring-1 ring-border/50 hover:ring-primary/50"
                                                    >
                                                        <img 
                                                            :src="promoChild.image" 
                                                            :alt="promoChild.title"
                                                            class="w-full object-cover transition-transform duration-500 group-hover/promo:scale-110"
                                                            :class="getImageSizeClasses(promoChild.image_size)"
                                                        />
                                                        <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent opacity-80 group-hover/promo:opacity-100 transition-opacity duration-300"></div>
                                                        <div class="absolute bottom-3 left-4 right-4 translate-y-0 group-hover/promo:-translate-y-1 transition-transform duration-300">
                                                            <span class="text-white font-bold text-base block drop-shadow-md">{{ promoChild.title }}</span>
                                                            <span v-if="promoChild.description" class="text-white/80 text-xs line-clamp-1 mt-1">{{ promoChild.description }}</span>
                                                        </div>
                                                        <span 
                                                            v-if="promoChild.badge" 
                                                            class="absolute top-3 right-3 px-2 py-1 text-[10px] font-bold tracking-wide uppercase rounded bg-primary text-primary-foreground shadow-sm"
                                                        >
                                                            {{ promoChild.badge }}
                                                        </span>
                                                    </router-link>
                                                </template>
                                            </div>
                                        </div>
                                    </template>

                                    <!-- CASE B: Default Flat List -->
                                    <template v-else>
                                        <router-link
                                            v-for="child in item.children"
                                            :key="child.id"
                                            :to="child.url || '/'"
                                            :target="child.open_in_new_tab ? '_blank' : null"
                                            class="flex items-start gap-3 p-3 rounded-lg hover:bg-muted/50 transition-colors group/item"
                                        >
                                            <div 
                                                v-if="child.icon"
                                                class="w-10 h-10 rounded-lg bg-primary/10 flex items-center justify-center shrink-0 group-hover/item:bg-primary/20 transition-colors"
                                            >
                                                <component :is="getIconComponent(child.icon)" class="w-5 h-5 text-primary" />
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <div class="flex items-center gap-2">
                                                    <span class="font-medium text-sm text-foreground">{{ child.title }}</span>
                                                    <span 
                                                        v-if="child.badge"
                                                        class="px-1.5 py-0.5 text-[10px] font-medium rounded-full"
                                                        :class="getBadgeClasses(child.badge_color)"
                                                    >
                                                        {{ child.badge }}
                                                    </span>
                                                </div>
                                                <p v-if="child.description" class="text-xs text-muted-foreground mt-0.5 line-clamp-2">
                                                    {{ child.description }}
                                                </p>
                                            </div>
                                        </router-link>
                                    </template>

                                    <!-- Promotional Image Banner -->
                                    <div 
                                        v-if="hasPromotionalImages(item.children)" 
                                        class="col-span-full mt-4 pt-4 border-t border-border/50"
                                    >
                                        <div class="grid grid-cols-2 gap-4">
                                            <template v-for="child in getPromotionalItems(item.children)" :key="child.id + '-promo'">
                                                <router-link 
                                                    :to="child.url || '/'"
                                                    class="relative group/promo rounded-xl overflow-hidden shadow-sm hover:shadow-md transition-all ring-1 ring-border/50 hover:ring-primary/50"
                                                >
                                                    <img 
                                                        :src="child.image" 
                                                        :alt="child.title"
                                                        class="w-full object-cover transition-transform duration-500 group-hover/promo:scale-110"
                                                        :class="getImageSizeClasses(child.image_size)"
                                                    />
                                                    <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent opacity-80 group-hover/promo:opacity-100 transition-opacity duration-300"></div>
                                                    <div class="absolute bottom-3 left-4 right-4 translate-y-0 group-hover/promo:-translate-y-1 transition-transform duration-300">
                                                        <span class="text-white font-semibold text-sm drop-shadow-md">{{ child.title }}</span>
                                                        <span v-if="child.badge" class="ml-2 px-1.5 py-0.5 text-[10px] font-bold tracking-wide uppercase rounded bg-primary text-primary-foreground shadow-sm">
                                                            {{ child.badge }}
                                                        </span>
                                                    </div>
                                                </router-link>
                                            </template>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Simple nav item (no children) -->
                        <router-link 
                            v-else
                            :to="item.url || '/'"
                            :target="item.open_in_new_tab ? '_blank' : null"
                            :class="getNavItemClasses(false)"
                            :active-class="getNavItemClasses(true)"
                        >
                            <component 
                                v-if="item.icon" 
                                :is="getIconComponent(item.icon)" 
                                class="w-4 h-4" 
                            />
                            {{ item.title }}
                            <span 
                                v-if="item.badge"
                                class="px-1.5 py-0.5 text-[10px] font-medium rounded-full"
                                :class="getBadgeClasses(item.badge_color)"
                            >
                                {{ item.badge }}
                            </span>
                        </router-link>
                    </template>
                </nav>

                <!-- Actions -->
                <div class="hidden md:flex items-center gap-3">
                    <!-- Language Switcher -->
                    <LanguageSwitcher />
                    
                    <!-- Dark Mode Toggle -->
                    <ThemeToggle />
                    
                    <router-link 
                        to="/login"
                        class="px-4 py-2 text-sm font-medium text-primary hover:text-primary/80 transition-colors"
                    >
                        {{ $t('features.frontend.nav.signIn') }}
                    </router-link>
                    <router-link 
                        :to="headerCtaUrl"
                        class="px-4 py-2 text-sm font-medium text-primary-foreground bg-primary rounded-full hover:bg-primary/90 transition-all shadow-lg transform hover:-translate-y-0.5"
                    >
                        {{ headerCtaText }}
                    </router-link>
                </div>

                <!-- Mobile Menu Button -->
                <button 
                    @click="isOpen = !isOpen"
                    class="md:hidden p-2 text-muted-foreground hover:bg-accent rounded-lg transition-colors"
                >
                    <svg v-if="!isOpen" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                    <svg v-else class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>

        <!-- Mobile Menu -->
        <transition
            enter-active-class="transition duration-200 ease-out"
            enter-from-class="transform -translate-y-4 opacity-0"
            enter-to-class="transform translate-y-0 opacity-100"
            leave-active-class="transition duration-150 ease-in"
            leave-from-class="transform translate-y-0 opacity-100"
            leave-to-class="transform -translate-y-4 opacity-0"
        >
            <div v-if="isOpen" class="md:hidden bg-background border-b border-border absolute w-full left-0 top-16 shadow-xl">
                <div class="container mx-auto px-4 py-4 space-y-2">
                    <router-link 
                        v-for="item in navItems" 
                        :key="item.id" 
                        :to="item.url || '/'"
                        class="block px-4 py-2.5 text-base font-medium text-muted-foreground hover:text-primary hover:bg-accent rounded-lg transition-colors"
                        active-class="text-primary bg-accent"
                        @click="isOpen = false"
                    >
                        {{ item.title }}
                    </router-link>
                    <div class="h-px bg-border my-4"></div>
                    <div class="flex flex-col gap-2">
                        <router-link 
                            to="/login"
                            class="w-full px-4 py-2.5 text-center text-sm font-medium text-muted-foreground hover:bg-accent rounded-lg transition-colors"
                        >
                            {{ $t('features.frontend.nav.signIn') }}
                        </router-link>
                        <router-link 
                            :to="headerCtaUrl"
                            class="w-full px-4 py-2.5 text-center text-sm font-medium text-primary-foreground bg-primary rounded-lg hover:bg-primary/90 transition-colors shadow-md"
                        >
                            {{ headerCtaText }}
                        </router-link>
                    </div>
                </div>
            </div>
        </transition>
    </header>
</template>

<script setup>
import { ref, onMounted, computed, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import { useTheme } from '../../../../composables/useTheme';
import { useMenu } from '../../../../composables/useMenu';
import { useCmsStore } from '../../../../stores/cms';
import ThemeToggle from '../../../../components/ThemeToggle.vue';
import LanguageSwitcher from '../../../../components/LanguageSwitcher.vue';
import { ChevronDown } from 'lucide-vue-next';
import * as LucideIcons from 'lucide-vue-next';

const { t } = useI18n();
const { getSetting } = useTheme();
const { menus, fetchMenuByLocation } = useMenu();

const isOpen = ref(false);

const headerSticky = computed(() => getSetting('header_sticky', true));
const headerStyle = computed(() => getSetting('header_style', 'glass'));
const brandingDisplay = computed(() => getSetting('branding_display', 'logo_only'));

// Dynamic Branding Fallbacks
const cmsStore = useCmsStore();
const { siteSettings } = cmsStore;
const siteName = computed(() => getSetting('site_title') || siteSettings?.site_name || 'Janari');
const siteLogo = computed(() => getSetting('brand_logo') || siteSettings?.site_logo || '');
const siteVersion = computed(() => siteSettings?.site_version || 'v1.0 Janari');
const headerCtaText = computed(() => getSetting('header_cta_text', 'Get Started'));
const headerCtaUrl = computed(() => getSetting('header_cta_url', '/register'));

const headerStyleClasses = computed(() => {
    switch (headerStyle.value) {
        case 'solid':
            return 'bg-background border-b border-border';
        case 'transparent':
            return 'bg-transparent';
        case 'glass':
        default:
            return 'bg-background/80 backdrop-blur-md border-b border-border';
    }
});

// Navigation Style
const navStyle = computed(() => getSetting('nav_style', 'glass'));

const getNavItemClasses = (isActive) => {
    const baseClasses = 'flex items-center gap-1.5 px-4 py-2.5 text-sm font-medium transition-all duration-300 relative';
    
    const styles = {
        // Glass Morph - Modern blur with transparency (Default)
        glass: {
            base: `${baseClasses} text-foreground/70 hover:text-foreground rounded-xl hover:bg-white/10 hover:backdrop-blur-sm hover:shadow-[0_4px_20px_-4px_rgba(0,0,0,0.1)] dark:hover:bg-white/5`,
            active: 'text-foreground bg-white/15 backdrop-blur-sm rounded-xl shadow-[0_4px_20px_-4px_rgba(0,0,0,0.1)] font-semibold'
        },
        // Spotlight - Focus highlight like Apple
        spotlight: {
            base: `${baseClasses} text-muted-foreground hover:text-foreground rounded-lg nav-spotlight`,
            active: 'text-foreground font-semibold nav-spotlight-active'
        },
        // Magnetic - Hover attraction effect
        magnetic: {
            base: `${baseClasses} text-muted-foreground hover:text-primary rounded-lg nav-magnetic hover:scale-105 hover:-translate-y-0.5`,
            active: 'text-primary font-semibold scale-105'
        },
        // Floating - 3D shadow depth
        floating: {
            base: `${baseClasses} text-muted-foreground hover:text-foreground rounded-xl hover:bg-card hover:shadow-[0_10px_40px_-10px_rgba(0,0,0,0.15)] hover:-translate-y-1 dark:hover:shadow-[0_10px_40px_-10px_rgba(0,0,0,0.4)]`,
            active: 'text-foreground bg-card rounded-xl shadow-[0_10px_40px_-10px_rgba(0,0,0,0.15)] -translate-y-1 font-semibold'
        },
        // Slide - Text reveal animation
        slide: {
            base: `${baseClasses} text-muted-foreground hover:text-primary rounded-lg overflow-hidden nav-slide`,
            active: 'text-primary font-semibold nav-slide-active'
        },
        // Pill Modern - Soft gradient fill
        pill: {
            base: `${baseClasses} text-muted-foreground hover:text-primary-foreground rounded-full hover:bg-gradient-to-r hover:from-primary hover:to-primary/80 hover:shadow-lg hover:shadow-primary/25`,
            active: 'text-primary-foreground bg-gradient-to-r from-primary to-primary/80 rounded-full shadow-lg shadow-primary/25 font-semibold'
        },
        // Underline Grow - Expanding bottom border
        underline: {
            base: `${baseClasses} text-muted-foreground hover:text-foreground nav-underline-item`,
            active: 'text-foreground nav-underline-active font-semibold'
        },
        // Neon Glow - Cyberpunk style
        glow: {
            base: `${baseClasses} text-muted-foreground hover:text-primary rounded-lg hover:bg-primary/5 hover:shadow-[0_0_20px_rgba(var(--primary-rgb),0.4)] nav-glow`,
            active: 'text-primary bg-primary/10 rounded-lg shadow-[0_0_25px_rgba(var(--primary-rgb),0.5)] font-semibold'
        }
    };
    
    const currentStyle = styles[navStyle.value] || styles.glass;
    return isActive ? currentStyle.active : currentStyle.base;
};

// Mega Menu Helpers
const getIconComponent = (iconName) => {
    return LucideIcons[iconName] || null;
};

const getBadgeClasses = (color) => {
    const colorMap = {
        primary: 'bg-primary text-primary-foreground',
        secondary: 'bg-secondary text-secondary-foreground',
        destructive: 'bg-destructive text-destructive-foreground',
        success: 'bg-emerald-500 text-white',
        warning: 'bg-amber-500 text-white',
        default: 'bg-muted text-muted-foreground',
    };
    return colorMap[color] || colorMap.default;
};

// Mega Menu Layout Helpers
const getDropdownPositionClasses = (layout) => {
    if (layout === 'full') {
        return 'left-0 right-0 w-full fixed mt-2 px-4 container mx-auto transform -translate-x-1/2 left-1/2';
    }
    return 'left-0 min-w-[280px]';
};

const getMegaMenuLayoutClasses = (item) => {
    const layout = item.mega_menu_layout || 'default';
    const childCount = item.children?.length || 0;
    
    switch (layout) {
        case 'grid-2':
            return 'grid grid-cols-2 gap-4 min-w-[500px]';
        case 'grid-3':
            return 'grid grid-cols-3 gap-6 min-w-[750px]';
        case 'full':
            return 'grid grid-cols-4 gap-8 p-6 w-full max-w-7xl mx-auto';
        case 'default':
        default:
            // Auto grid for large items
            if (childCount > 5) return 'grid grid-cols-2 gap-4 min-w-[500px]';
            return 'flex flex-col gap-1 min-w-[280px]';
    }
};

// Promotional image helpers
const hasPromotionalImages = (items) => {
    if (!items) return false;
    return items.some(item => item.image);
};

const getPromotionalItems = (items) => {
    if (!items) return [];
    return items.filter(item => item.image);
};

const getImageSizeClasses = (size) => {
    switch (size) {
        case 'sm': return 'h-24'; // 100px approx
        case 'md': return 'h-36'; // 150px approx  
        case 'lg': return 'h-48'; // 200px approx
        case 'xl': return 'h-72'; // 300px approx
        case 'full': return 'h-auto min-h-[200px]';
        case 'auto':
        default: return 'h-32'; // default
    }
};

const groupItemsByColumn = (items, layout) => {
    // Determine max columns based on layout
    // grid-2 = 2, grid-3 = 3, full = 4 (assumed)
    let maxCols = 1;
    if (layout === 'grid-2') maxCols = 2;
    if (layout === 'grid-3') maxCols = 3;
    if (layout === 'full') maxCols = 4;
    
    // Create an array of arrays [[], [], ...]
    const groups = Array.from({length: maxCols}, () => []);
    
    items.forEach(item => {
        let colIndex = item.mega_menu_column; // 1-based index from DB
        
        // Handle Auto (0) or invalid
        if (!colIndex || colIndex < 1) {
            // For now, put unassigned items in Column 1 (or implement round-robin)
            colIndex = 1; 
        }
        
        // Cap at max columns
        if (colIndex > maxCols) colIndex = maxCols;
        
        // Push to group (convert 1-based to 0-based)
        groups[colIndex - 1].push(item);
    });
    
    return groups;
};

import { useRoute } from 'vue-router'; // Import useRoute manually since it might not be auto-imported

const route = useRoute();

// Dynamic Menu Location Logic
const currentMenuLocation = computed(() => {
    // 1. Priority: Route meta override
    if (route.meta?.menu_location) {
        return route.meta.menu_location;
    }
    
    // 2. Special Case: Homepage
    if (route.name === 'home' || route.path === '/') {
        // We will try to fetch 'header_home' first, but logic needs to handle fallback.
        // For simplicity in this implementation, we assume if user wants different menu for home,
        // they create a menu location 'header_home'.
        return 'header_home';
    }

    // 3. Default
    return 'header';
});

// Watch route/location changes to fetch appropriate menu
watch(currentMenuLocation, async (newLoc) => {
    // Attempt to fetch specific menu
    await fetchMenuByLocation(newLoc);
    
    // Fallback logic: If specific menu is empty/not found, fetch default 'header'
    // Note: useMenu store needs to handle empty response gracefully or we check here
    if (!menus.value[newLoc] || !menus.value[newLoc].items || menus.value[newLoc].items.length === 0) {
        if (newLoc !== 'header') {
            console.warn(`Menu location '${newLoc}' not found or empty. Falling back to 'header'.`);
            await fetchMenuByLocation('header');
        }
    }
}, { immediate: true });

onMounted(() => {
    // Initial fetch handled by watch immediate
    fetchMenuByLocation('header_top');
});

const navItems = computed(() => {
    const loc = currentMenuLocation.value;
    // Return specific menu if exists and has items, otherwise fallback to 'header'
    if (menus.value[loc] && menus.value[loc].items && menus.value[loc].items.length > 0) {
        return menus.value[loc].items;
    }
    return menus.value['header']?.items || [];
    return menus.value['header']?.items || [];
});

const isParentActive = (item) => {
    if (!item.children || item.children.length === 0) return false;
    // Check if any child URL matches current path (or is parent of current path)
    return item.children.some(child => {
        const childUrl = child.url || '/';
        const currentPath = route.path;
        
        // Skip home link unless we are exactly at home
        if (childUrl === '/' && currentPath !== '/') return false;
        
        // Exact match
        if (currentPath === childUrl) return true;
        
        // Prefix match (e.g. /category/design matches child /category)
        // Be careful with short paths
        if (childUrl.length > 1 && currentPath.startsWith(childUrl)) return true;
        
        return false;
    });
};

const headerTopItems = computed(() => menus.value['header_top']?.items || []);
</script>

<style scoped>
/* ====== MODERN NAV STYLES 2024-2026 ====== */

/* Underline Grow Animation */
.nav-underline-item::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 50%;
    width: 0;
    opacity: 0;
    height: 2px;
    background: linear-gradient(90deg, hsl(var(--primary)), hsl(var(--primary) / 0.7));
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    transform: translateX(-50%);
    border-radius: 2px;
}

.nav-underline-item:hover::after {
    width: 80%;
    opacity: 1;
}

.nav-underline-active::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 50%;
    width: 80%;
    opacity: 1;
    height: 2px;
    background: linear-gradient(90deg, hsl(var(--primary)), hsl(var(--primary) / 0.7));
    border-radius: 2px;
    transform: translateX(-50%);
}

/* Spotlight Effect - Radial gradient follows mouse conceptually */
.nav-spotlight {
    background: radial-gradient(circle at var(--mouse-x, 50%) var(--mouse-y, 50%), transparent 0%, transparent 100%);
}

.nav-spotlight:hover {
    background: radial-gradient(circle at 50% 50%, hsl(var(--primary) / 0.15) 0%, transparent 70%);
}

.nav-spotlight-active {
    background: radial-gradient(circle at 50% 50%, hsl(var(--primary) / 0.2) 0%, transparent 70%);
}

/* Magnetic Effect - Already handled by Tailwind transform classes */
.nav-magnetic {
    will-change: transform;
}

/* Slide Text Effect */
.nav-slide::before {
    content: attr(data-text);
    position: absolute;
    top: 100%;
    left: 0;
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: hsl(var(--primary));
    transition: top 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.nav-slide:hover {
    color: transparent;
}

.nav-slide:hover::before {
    top: 0;
}

.nav-slide-active {
    color: hsl(var(--primary));
}

/* Neon Glow Pulse Animation */
.nav-glow {
    --primary-rgb: 124, 58, 237; /* Default purple, will be overridden by theme */
}

.nav-glow:hover {
    animation: glow-pulse 2s ease-in-out infinite;
}

@keyframes glow-pulse {
    0%, 100% {
        box-shadow: 0 0 20px rgba(var(--primary-rgb), 0.4);
    }
    50% {
        box-shadow: 0 0 30px rgba(var(--primary-rgb), 0.6);
    }
}

/* Smooth transitions for all nav items */
nav a, nav button {
    will-change: transform, box-shadow, background-color;
}

/* Dark mode adjustments */
:root.dark .nav-spotlight:hover {
    background: radial-gradient(circle at 50% 50%, hsl(var(--primary) / 0.2) 0%, transparent 70%);
}
</style>
