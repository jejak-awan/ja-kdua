<template>
    <footer class="bg-card text-card-foreground mt-auto border-t border-border">
        <div class="container mx-auto px-4 py-16">
            <div :class="['grid gap-12', isDesktop ? 'grid-cols-4' : 'grid-cols-1']">
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
                         <a v-if="getSetting('social_twitter')" :href="getSetting('social_twitter')" target="_blank" class="text-muted-foreground hover:text-primary transition-colors">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.84 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z" /></svg>
                         </a>
                         <a v-if="getSetting('social_github')" :href="getSetting('social_github')" target="_blank" class="text-muted-foreground hover:text-primary transition-colors">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 .297c-6.63 0-12 5.373-12 12 0 5.303 3.438 9.8 8.205 11.385.6.113.82-.258.82-.577 0-.285-.01-1.04-.015-2.04-3.338.724-4.042-1.61-4.042-1.61C4.422 18.07 3.633 17.7 3.633 17.7c-1.087-.744.084-.729.084-.729 1.205.084 1.838 1.236 1.838 1.236 1.07 1.835 2.809 1.305 3.495.998.108-.776.417-1.305.76-1.605-2.665-.3-5.466-1.332-5.466-5.93 0-1.31.465-2.38 1.235-3.22-.135-.303-.54-1.523.105-3.176 0 0 1.005-.322 3.3 1.23.96-.267 1.98-.399 3-.405 1.02.006 2.04.138 3 .405 2.28-1.552 3.285-1.23 3.285-1.23.645 1.653.24 2.873.12 3.176.765.84 1.23 1.91 1.23 3.22 0 4.61-2.805 5.625-5.475 5.92.43.372.823 1.102.823 2.222 0 1.606-.015 2.896-.015 3.286 0 .315.21.69.825.57C20.565 22.092 24 17.592 24 12.297c0-6.627-5.373-12-12-12" /></svg>
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
                        <div class="flex gap-2">
                            <input 
                                v-model="email"
                                type="email" 
                                :placeholder="$t('features.frontend.newsletter.placeholder')" 
                                class="flex-1 px-4 py-2 rounded-lg bg-background border border-input text-foreground placeholder-muted-foreground focus:outline-none focus:ring-1 focus:ring-ring transition-colors"
                                :class="{ 'border-destructive focus:ring-destructive': errors.email }"
                            >
                            <button 
                                type="submit" 
                                :disabled="loading"
                                class="p-2 bg-primary text-primary-foreground rounded-lg hover:bg-primary/90 transition-colors disabled:opacity-50"
                            >
                                <svg v-if="loading" class="w-5 h-5 animate-spin" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                </svg>
                            </button>
                        </div>
                        <p v-if="errors.email" class="text-xs text-destructive">{{ errors.email[0] }}</p>
                    </form>
                </div>
            </div>

            <div :class="['mt-16 pt-8 border-t border-border flex justify-between items-center gap-4', isDesktop ? 'flex-row' : 'flex-col']">
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

const { t } = useI18n()
const { getSetting } = useTheme()
const { menus, fetchMenuByLocation } = useMenu()
const device = useResponsiveDevice();
const toast = useToast()
const { errors, validateWithZod, setErrors, clearErrors } = useFormValidation(newsletterSchema)

const isDesktop = computed(() => device.value === 'desktop');
const loading = ref(false)
const email = ref('')

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

const brandingDisplay = computed(() => getSetting('branding_display', 'logo_only'));

// Dynamic Branding Fallbacks
const cmsStore = useCmsStore();
const siteSettings = computed(() => cmsStore.siteSettings);

const siteName = computed(() => (getSetting('site_title') as string) || siteSettings.value?.site_name || 'Janari');
const siteLogo = computed(() => (getSetting('brand_logo') as string) || siteSettings.value?.site_logo || '');
const siteVersion = computed(() => siteSettings.value?.site_version || 'v1.0 Janari');

const submitNewsletter = async () => {
    if (!validateWithZod({ email: email.value })) return

    loading.value = true
    clearErrors()
    
    try {
        // Mock API call - in a real app this would call the newsletter endpoint
        await new Promise(resolve => setTimeout(resolve, 1000))
        
        toast.success.action(t('features.frontend.newsletter.success'))
        email.value = ''
    } catch (error: any) {
        if (error.response?.status === 422) {
            setErrors(error.response.data.errors)
        } else {
            toast.error.action(error)
        }
    } finally {
        loading.value = false
    }
}
</script>

