<template>
  <div class="min-h-screen flex flex-col">
    <div v-if="loading" class="flex items-center justify-center min-h-[50vh]">
        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary"></div>
    </div>
    
    <template v-else>
        <BlockRenderer 
            v-if="pageData && pageData.blocks && pageData.blocks.length > 0" 
            :blocks="pageData.blocks"
            :context="{ page: pageData }"
        />
        
        <!-- Fallback if no content found - Keep original minimal fallback or just empty -->
        <div v-else class="min-h-screen bg-background py-12">
             <div class="container mx-auto px-4 text-center">
                 <h1 class="text-4xl font-bold mb-4">Our Blog</h1>
                 <p class="text-muted-foreground">No content configured.</p>
             </div>
        </div>
    </template>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import api from '@/services/api'
import BlockRenderer from '@/components/builder/blocks/BlockRenderer.vue'

const pageData = ref(null)
const loading = ref(true)

onMounted(async () => {
  try {
    // Fetch blog page content
    const response = await api.get('/cms/contents/blog')
    pageData.value = response.data.data
  } catch (error) {
    console.error('Failed to fetch blog page:', error)
  } finally {
    loading.value = false
  }
})
</script>
