<template>
    <footer class="bg-card text-card-foreground mt-auto border-t border-border">
        <div class="container mx-auto px-4 py-16">
            <div :class="['grid gap-12', gridClass]">
                <!-- Brand -->
                <div class="space-y-4">
                    <div class="flex items-center gap-3">
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
                                class="w-8 h-8 rounded-lg bg-primary text-primary-foreground flex items-center justify-center font-bold"
                            >
                                {{ siteName.substring(0, 2).toUpperCase() }}
                            </div>
                        </div>

                        <!-- Site Name -->
                        <div 
                            v-if="brandingDisplay !== 'logo_only'"
                            class="flex flex-col"
                        >
                            <span class="text-xl font-bold leading-none">{{ siteName }}</span>
                            <span v-if="brandingDisplay === 'both'" class="text-[10px] font-medium text-muted-foreground mt-0.5">
                                {{ siteVersion }}
                            </span>
                        </div>
                    </div>
                    <p class="text-muted-foreground text-sm">
                        {{ getSetting('site_description', 'Modern Content Management System built with Laravel and Vue.js') }}
                    </p>
                    <!-- Social Links -->
                    <div class="flex gap-4">
                         <a v-if="getSetting('social_twitter')" :href="(getSetting('social_twitter') as string)" target="_blank" class="text-muted-foreground hover:text-primary transition-colors">
                            <Twitter class="w-5 h-5" />
                         </a>
                         <a v-if="getSetting('social_github')" :href="(getSetting('social_github') as string)" target="_blank" class="text-muted-foreground hover:text-primary transition-colors">
                            <Github class="w-5 h-5" />
                         </a>
                    </div>
                </div>

                <!-- Dynamic Footer Column 1 -->
                <div v-if="(footerCol1Items?.length || 0) > 0" class="space-y-4">
                    <h5 class="font-semibold text-foreground">{{ menus['footer_col_1']?.name || 'Links' }}</h5>
                    <ul class="space-y-2">
                        <li v-for="item in footerCol1Items" :key="String(item.id || item.title)">
                            <router-link 
                                :to="item.url || '/'" 
                                class="text-muted-foreground hover:text-foreground text-sm transition-colors"
                            >
                                {{ item.title }}
                            </router-link>
                        </li>
                    </ul>
                </div>

                <!-- Dynamic Footer Column 2 -->
                <div v-if="(footerCol2Items?.length || 0) > 0" class="space-y-4">
                    <h5 class="font-semibold text-foreground">{{ menus['footer_col_2']?.name || 'Resources' }}</h5>
                    <ul class="space-y-2">
                        <li v-for="item in footerCol2Items" :key="String(item.id || item.title)">
                            <router-link 
                                :to="item.url || '/'" 
                                class="text-muted-foreground hover:text-foreground text-sm transition-colors"
                            >
                                {{ item.title }}
                            </router-link>
                        </li>
                    </ul>
                </div>

                <!-- Newsletter -->
                <div class="space-y-4">
                    <h5 class="font-semibold text-foreground">{{ $t('features.frontend.newsletter.title') }}</h5>
                    <p class="text-muted-foreground text-sm">
                        {{ $t('features.frontend.newsletter.description') }}
                    </p>
                    <form class="flex flex-col gap-2" @submit.prevent="submitNewsletter">
                        <div class="flex flex-wrap gap-2">
                            <input 
                                v-model="email"
                                type="email" 
                                :placeholder="$t('features.frontend.newsletter.placeholder')" 
                                class="flex-1 min-w-0 px-4 py-2 rounded-lg bg-background border border-input text-foreground placeholder-muted-foreground focus:outline-none focus:ring-1 focus:ring-ring transition-colors"
                                :class="{ 'border-destructive focus:ring-destructive': errors.email }"
                            >
                            <button 
                                type="submit" 
                                :disabled="loading"
                                class="p-2 bg-primary text-primary-foreground rounded-lg hover:bg-primary/90 transition-colors disabled:opacity-50 shrink-0"
                            >
                                <Loader2 v-if="loading" class="w-5 h-5 animate-spin" />
                                <ArrowRight v-else class="w-5 h-5" />
                            </button>
                        </div>
                        <p v-if="errors.email" class="text-xs text-destructive">{{ errors.email[0] }}</p>
                    </form>
                </div>
            </div>

            <div :class="['mt-16 pt-8 border-t border-border flex justify-between items-center gap-4', (isDesktop || isTablet) ? 'flex-row' : 'flex-col']">
                <p class="text-muted-foreground text-sm">
                    {{ getSetting('footer_text', `© ${new Date().getFullYear()} Janari. All rights reserved.`) }}
                </p>
                <div class="flex gap-6">
                    <router-link 
                        v-for="item in footerItems" 
                        :key="String(item.id || item.title)" 
                        :to="item.url || '/'"
                        class="text-muted-foreground hover:text-foreground text-sm transition-colors"
                    >
                        {{ item.title }}
                    </router-link>
                    <template v-if="footerItems.length === 0">
                        <a href="#" class="text-muted-foreground hover:text-foreground text-sm transition-colors">{{ $t('features.frontend.footer.privacy') }}</a>
                        <a href="#" class="text-muted-foreground hover:text-foreground text-sm transition-colors">{{ $t('features.frontend.footer.terms') }}</a>
                    </template>
                </div>
            </div>
        </div>

    </footer>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useI18n } from 'vue-i18n'
import { useTheme } from '@/composables/useTheme'
import { useMenu } from '@/composables/useMenu'
import { useCmsStore } from '@/stores/cms'
import { useToast } from '@/composables/useToast'
import { useFormValidation } from '@/composables/useFormValidation'
import { useResponsiveDevice } from '@/shared/utils/useResponsiveDevice';
import { newsletterSchema } from '@/schemas'
import type { MenuItem } from '@/types/cms';
import Twitter from 'lucide-vue-next/dist/esm/icons/twitter.js';
import Github from 'lucide-vue-next/dist/esm/icons/github.js';
import Loader2 from 'lucide-vue-next/dist/esm/icons/loader-circle.js';
import ArrowRight from 'lucide-vue-next/dist/esm/icons/arrow-right.js';
import { onUnmounted } from 'vue'

const { t } = useI18n()
const { getSetting } = useTheme()
const { menus, fetchMenuByLocation } = useMenu()
const device = useResponsiveDevice();
const toast = useToast()
const { errors, validateWithZod, setErrors, clearErrors } = useFormValidation(newsletterSchema)

const isDesktop = computed(() => device.value === 'desktop');
const isTablet = computed(() => device.value === 'tablet');

// Grid logic: Desktop (4), Tablet (2), Mobile (1)
const gridClass = computed(() => {
    if (isDesktop.value) return 'grid-cols-4';
    if (isTablet.value) return 'grid-cols-2';
    return 'grid-cols-1';
});

const loading = ref(false)
const email = ref('')

const brandingDisplay = computed(() => getSetting('branding_display', 'logo_only'));

// Dynamic Branding Fallbacks
const cmsStore = useCmsStore();
const siteSettings = computed(() => cmsStore.siteSettings);

const siteName = computed(() => (getSetting('site_title') as string) || siteSettings.value?.site_name || 'Janari');
const siteLogo = computed(() => (getSetting('brand_logo') as string) || siteSettings.value?.site_logo || '');
const siteVersion = computed(() => siteSettings.value?.site_version || 'v1.0 Janari');

// Fetch footer menu columns
onMounted(() => {
    fetchMenuByLocation('footer_col_1')
    fetchMenuByLocation('footer_col_2')
    fetchMenuByLocation('footer')
})

const defaultCol1Items: Partial<MenuItem>[] = [
    { id: 'fb-about', title: 'About', url: '/about' },
    { id: 'fb-blog', title: 'Blog', url: '/blog' },
    { id: 'fb-contact', title: 'Contact', url: '/contact' },
];

const defaultCol2Items: Partial<MenuItem>[] = [
    { id: 'fb-docs', title: 'Documentation', url: '/docs' },
    { id: 'fb-help', title: 'Help Center', url: '/help' },
];

const footerCol1Items = computed(() => (menus.value['footer_col_1']?.items?.length || 0) > 0 ? menus.value['footer_col_1'].items : defaultCol1Items);
const footerCol2Items = computed(() => (menus.value['footer_col_2']?.items?.length || 0) > 0 ? menus.value['footer_col_2'].items : defaultCol2Items);
const footerItems = computed(() => menus.value['footer']?.items || []);

const submitNewsletter = async () => {
    if (!validateWithZod({ email: email.value })) return

    loading.value = true
    clearErrors()
    
    try {
        // Mock API call - in a real app this would call the newsletter endpoint
        await new Promise(resolve => setTimeout(resolve, 1000))
        
        toast.success.action(t('features.frontend.newsletter.success'))
        email.value = ''
    } catch (error: unknown) {
        if (typeof error === 'object' && error !== null && 'response' in error) {
            const err = error as { response: { status: number; data: { errors: Record<string, string[]> } } };
            if (err.response?.status === 422) {
                setErrors(err.response.data.errors)
            } else {
                toast.error.action(error)
            }
        } else {
            toast.error.action(error)
        }
    } finally {
        loading.value = false
    }
}
</script>

