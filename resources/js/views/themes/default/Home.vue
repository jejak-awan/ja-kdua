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
        <div v-else class="container mx-auto py-20 text-center">
            <h1 class="text-2xl font-bold">Welcome to JA-CMS</h1>
            <p class="mt-4 text-muted-foreground">Please configure your home page content in the admin panel.</p>
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
    // Fetch home page content
    const response = await api.get('/cms/contents/home')
    pageData.value = response.data.data
  } catch (error) {
    console.error('Failed to fetch home page:', error)
  } finally {
    loading.value = false
  }
})
</script>
