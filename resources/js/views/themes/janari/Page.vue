<template>
  <div class="min-h-screen">
    <!-- Loading State -->
    <div v-if="loading" class="flex items-center justify-center min-h-[50vh]">
      <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary"></div>
    </div>
    
    <!-- Page Content -->
    <template v-else-if="pageData">
      <!-- Builder Blocks -->
      <BlockRenderer 
        v-if="pageData.blocks && pageData.blocks.length > 0" 
        :blocks="pageData.blocks"
        :context="{ page: pageData }"
        :is-preview="true"
      />
      
      <!-- Classic Content Fallback -->
      <div v-else-if="pageData.body" class="container mx-auto px-4 py-16">
        <h1 class="text-4xl font-bold mb-8">{{ pageData.title }}</h1>
        <div class="prose prose-lg max-w-none" v-html="pageData.body"></div>
      </div>
      
<!-- Empty State -->
      <div v-else class="min-h-screen flex items-center justify-center bg-background p-4">
        <div class="text-center max-w-md mx-auto">
                 <div class="w-24 h-24 bg-muted/50 rounded-full flex items-center justify-center mx-auto mb-8">
                     <Archive class="w-10 h-10 text-muted-foreground" />
                 </div>
             <h1 class="text-3xl font-bold mb-4">{{ pageData.title }}</h1>
             <p class="text-muted-foreground leading-relaxed mb-8">
                This page is currently being built. Check back soon for updates.
             </p>
             <router-link to="/" class="inline-flex items-center justify-center rounded-lg bg-primary px-4 py-2 text-sm font-medium text-primary-foreground shadow transition-colors hover:bg-primary/90">
                Return Home
             </router-link>
        </div>
      </div>
    </template>
    
    <!-- Not Found -->
    <div v-else class="container mx-auto py-20 text-center">
      <h1 class="text-2xl font-bold text-foreground">Page Not Found</h1>
      <router-link to="/" class="text-primary hover:text-primary/80 mt-4 inline-block">
        Return Home
      </router-link>
    </div>
  </div>
</template>

<script setup lang="ts">
import { logger } from '@/utils/logger';
import { ref, onMounted, watch } from 'vue';
import { useRoute } from 'vue-router';
import api from '@/services/api';
import BlockRenderer from '@/components/content-renderer/BlockRenderer.vue';
import Archive from 'lucide-vue-next/dist/esm/icons/archive.js';

import type { Content } from '@/types/cms'
import type { BlockInstance } from '@/types/builder'

interface PageData extends Content {
    // Add specific page extension properties if any, otherwise this interface might be redundant
    blocks?: BlockInstance[]; 
}

const route = useRoute();
const pageData = ref<PageData | null>(null);
const loading = ref(true);

const fetchPage = async () => {
  const slug = route.params.slug as string;
  
  // Guard: Don't fetch if slug is undefined
  if (!slug) {
    loading.value = false;
    pageData.value = null;
    return;
  }
  
  loading.value = true;
  try {
    const response = await api.get(`/cms/contents/${slug}`);
    pageData.value = response.data.data || response.data;
  } catch (error) {
    logger.error('Failed to load page:', error);
    pageData.value = null;
  } finally {
    loading.value = false;
  }
};

onMounted(fetchPage);

// Re-fetch when slug changes
watch(() => route.params.slug, fetchPage);
</script>

