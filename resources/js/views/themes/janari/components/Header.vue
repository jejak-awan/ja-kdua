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
                            class="relative group"
                        >
                            <button 
                                class="flex items-center gap-1.5 px-4 py-2 text-sm font-medium text-muted-foreground hover:text-primary transition-colors rounded-lg hover:bg-muted/50"
                            >
                                <component 
                                    v-if="item.icon" 
                                    :is="getIconComponent(item.icon)" 
                                    class="w-4 h-4" 
                                />
                                {{ item.title }}
                                <ChevronDown class="w-3.5 h-3.5 transition-transform group-hover:rotate-180" />
                            </button>
                            
                            <!-- Mega Menu Dropdown -->
                            <div class="absolute top-full left-0 pt-2 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-50">
                                <div 
                                    class="bg-card/95 backdrop-blur-xl border border-border rounded-xl shadow-2xl p-4 min-w-[280px]"
                                    :class="{ 'grid grid-cols-2 gap-4 min-w-[500px]': item.children.length > 3 }"
                                >
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
                                </div>
                            </div>
                        </div>
                        
                        <!-- Simple nav item (no children) -->
                        <router-link 
                            v-else
                            :to="item.url || '/'"
                            :target="item.open_in_new_tab ? '_blank' : null"
                            class="flex items-center gap-1.5 px-4 py-2 text-sm font-medium text-muted-foreground hover:text-primary transition-colors rounded-lg hover:bg-muted/50"
                            active-class="text-primary bg-muted/50"
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
import { ref, onMounted, computed } from 'vue';
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

onMounted(() => {
    fetchMenuByLocation('header');
    fetchMenuByLocation('header_top');
});

const navItems = computed(() => menus.value['header']?.items || []);
const headerTopItems = computed(() => menus.value['header_top']?.items || []);
</script>
