<template>
  <div class="min-h-screen flex flex-col">
    <div v-if="loading" class="flex items-center justify-center min-h-[50vh]">
        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary"></div>
    </div>
    
    <template v-else>
        <BlockRenderer 
            v-if="pageData && pageData.blocks" 
            :blocks="pageData.blocks"
            :context="{ page: pageData }"
        />
        
        <!-- Fallback if no content found -->
        <div v-else class="min-h-screen bg-background py-16 px-4">
             <div class="max-w-4xl mx-auto bg-card rounded-xl shadow-lg overflow-hidden border border-border">
                <div class="p-8 text-center">
                    <h1 class="text-3xl font-bold mb-4">{{ $t('features.frontend.contact.title') }}</h1>
                    <p class="text-muted-foreground">{{ $t('features.frontend.contact.subtitle') }}</p>
                </div>
             </div>
        </div>
    </template>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useI18n } from 'vue-i18n'
import api from '@/services/api'
import BlockRenderer from '@/components/builder/blocks/BlockRenderer.vue'

const { t } = useI18n()
const pageData = ref(null)
const loading = ref(true)

onMounted(async () => {
  try {
    // Fetch contact page content
    const response = await api.get('/cms/contents/contact')
    pageData.value = response.data.data
  } catch (error) {
    console.error('Failed to fetch contact page:', error)
  } finally {
    loading.value = false
  }
})
</script>
