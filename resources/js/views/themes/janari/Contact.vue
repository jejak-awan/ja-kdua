<template>
  <div class="min-h-screen bg-background transition-colors duration-300 overflow-x-hidden">
    <!-- Main Content Area -->
    <div v-if="loading" class="flex flex-col items-center justify-center min-h-[60vh] gap-4">
        <Loader2 class="w-10 h-10 animate-spin text-primary/50" />
        <p class="text-sm font-medium text-muted-foreground animate-pulse">Loading Contact Page...</p>
    </div>

    <template v-else>
        <!-- Render Dynamic Blocks from Database (Hero, Content, etc.) -->
        <main v-if="pageData && pageData.blocks && pageData.blocks.length" class="animate-fade">
            <BlockRenderer :blocks="pageData.blocks" />
        </main>

        <!-- Fallback Design (Matches design system if seeder fails) -->
        <main v-else class="container mx-auto px-6 py-20 animate-fade">
            <div class="grid grid-cols-1 md:grid-cols-12 gap-12 items-start">
                <!-- Info Column -->
                <div class="lg:col-span-4 space-y-6">
                    <div class="mb-8">
                        <h1 class="text-4xl font-extrabold tracking-tight mb-4">Contact Us</h1>
                        <p class="text-lg text-muted-foreground">We'd love to hear from you. Reach out via any of the channels below.</p>
                    </div>

                    <Card class="p-6 space-y-6">
                        <div class="flex items-start gap-4">
                            <div class="w-10 h-10 rounded-lg bg-primary/10 flex items-center justify-center text-primary shrink-0">
                                <Mail class="w-5 h-5" />
                            </div>
                            <div>
                                <h3 class="font-bold text-sm uppercase tracking-wider text-muted-foreground mb-1">Email</h3>
                                <a :href="'mailto:' + (siteSettings.contact_email || 'hello@ja-cms.com')" class="font-bold hover:text-primary transition-colors">
                                    {{ siteSettings.contact_email || 'hello@ja-cms.com' }}
                                </a>
                            </div>
                        </div>

                        <div v-if="siteSettings.contact_phone" class="flex items-start gap-4">
                            <div class="w-10 h-10 rounded-lg bg-primary/10 flex items-center justify-center text-primary shrink-0">
                                <Phone class="w-5 h-5" />
                            </div>
                            <div>
                                <h3 class="font-bold text-sm uppercase tracking-wider text-muted-foreground mb-1">Phone</h3>
                                <p class="font-bold">{{ siteSettings.contact_phone }}</p>
                            </div>
                        </div>

                        <div v-if="siteSettings.contact_address" class="flex items-start gap-4">
                            <div class="w-10 h-10 rounded-lg bg-primary/10 flex items-center justify-center text-primary shrink-0">
                                <MapPin class="w-5 h-5" />
                            </div>
                            <div>
                                <h3 class="font-bold text-sm uppercase tracking-wider text-muted-foreground mb-1">Office</h3>
                                <p class="font-bold text-sm leading-relaxed">{{ siteSettings.contact_address }}</p>
                            </div>
                        </div>
                    </Card>
                </div>

                <!-- Form Column -->
                <div class="lg:col-span-8">
                    <Card class="p-8 md:p-10">
                        <ContactFormBlock 
                            :module="fallbackModule" 
                            :nested-blocks="fallbackNestedBlocks"
                        />
                    </Card>
                </div>
            </div>
        </main>
    </template>
  </div>
</template>

<script setup lang="ts">
import { logger } from '@/utils/logger';
import { ref, onMounted, computed } from 'vue'
import api from '@/services/api'
import { useCmsStore } from '@/stores/cms'
import BlockRenderer from '@/components/content-renderer/BlockRenderer.vue'
import ContactFormBlock from '@/shared/blocks/ContactFormBlock.vue'
import { Card } from '@/components/ui'
import Mail from 'lucide-vue-next/dist/esm/icons/mail.js';
import Phone from 'lucide-vue-next/dist/esm/icons/phone.js';
import MapPin from 'lucide-vue-next/dist/esm/icons/map-pin.js';
import Loader2 from 'lucide-vue-next/dist/esm/icons/loader-circle.js';interface ContactField {
    label: string;
    type: string;
    required: boolean;
    width: string;
}

import type { Content } from '@/types/cms'

interface PageData extends Content {
    title: string;
    blocks?: any[];
}

const loading = ref(true)
const pageData = ref<PageData | null>(null)
const cmsStore = useCmsStore()
const siteSettings = computed(() => cmsStore.siteSettings)

const fallbackModule = computed(() => ({
    id: 'fallback-contact-form',
    type: 'contactform',
    settings: {
        title: 'Drop us a line',
        description: 'Tell us about your project or just say hi!'
    }
}))

const fallbackNestedBlocks = computed(() => [
    { 
        id: 'f1', 
        type: 'contactfield', 
        settings: { label: 'First Name', type: 'text', required: true, fullWidth: false } 
    },
    { 
        id: 'f2', 
        type: 'contactfield', 
        settings: { label: 'Last Name', type: 'text', required: true, fullWidth: false } 
    },
    { 
        id: 'f3', 
        type: 'contactfield', 
        settings: { label: 'Email Address', type: 'email', required: true, fullWidth: true } 
    },
    { 
        id: 'f4', 
        type: 'contactfield', 
        settings: { label: 'Message', type: 'textarea', required: true, fullWidth: true } 
    }
])

onMounted(async () => {

  try {
    // 1. Fetch site settings for global info
    await cmsStore.fetchPublicSettings()

    // 2. Fetch specific page content
    const response = await api.get('/cms/contents/contact')
    pageData.value = response.data.data
  } catch (error) {
    logger.warning('[Contact] Static fallback mode engaged due to fetch error:', error)
  } finally {
    loading.value = false
  }
})
</script>

