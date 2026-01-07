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
            :is-preview="true"
        />
        
        <!-- Fallback if no content found (keep original hardcoded as temporary fallback) -->
        <div v-else class="min-h-screen bg-background flex items-center justify-center">
            <div class="bg-card p-8 rounded-lg shadow-lg max-w-2xl w-full border border-border">
                <h1 class="text-4xl font-bold text-foreground mb-6">About Us</h1>
                <p class="text-muted-foreground mb-4 text-lg leading-relaxed">
                    Welcome to JA-CMS, a modern content management system designed for speed, flexibility, and ease of use.
                    Our mission is to empower creators and developers to build amazing web experiences.
                </p>
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
    // Fetch about page content
    const response = await api.get('/cms/contents/about')
    pageData.value = response.data.data
  } catch (error) {
    console.error('Failed to fetch about page:', error)
  } finally {
    loading.value = false
  }
})
</script>
