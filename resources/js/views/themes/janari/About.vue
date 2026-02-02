<template>
  <div class="min-h-screen flex flex-col">
    <!-- Loading State -->
    <div v-if="loading" class="flex-1 flex items-center justify-center min-h-[60vh]">
        <div class="flex flex-col items-center gap-4">
            <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-primary"></div>
            <span class="text-muted-foreground text-sm">Loading...</span>
        </div>
    </div>
    
    <template v-else>
        <BlockRenderer 
            v-if="pageData && pageData.blocks" 
            :blocks="pageData.blocks"
            :context="{ page: pageData }"
            :is-preview="true"
        />
        
        <!-- Fallback if no content found (keep original hardcoded as temporary fallback) -->
<!-- Fallback Premium About Layout -->
        <div v-else class="flex-1">
            <!-- Header -->
            <section class="py-24 bg-muted/30 border-b border-border/50">
                <div class="container mx-auto px-4 text-center">
                    <span class="text-primary font-semibold tracking-wider uppercase text-sm mb-4 block">Our Story</span>
                    <h1 class="text-4xl md:text-6xl font-bold mb-6 text-foreground">We are Janari</h1>
                    <p class="text-xl text-muted-foreground max-w-2xl mx-auto leading-relaxed">
                        Building the future of content management with modern tools and premium design.
                    </p>
                </div>
            </section>

            <!-- Mission/Content -->
            <section class="py-20">
                <div class="container mx-auto px-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-16 items-center">
                        <div class="space-y-6">
                            <h2 class="text-3xl font-bold text-foreground">Driven by Innovation</h2>
                            <p class="text-muted-foreground text-lg leading-relaxed">
                                Our mission is to empower creators and developers to build amazing web experiences without compromise. 
                                We believe in clean code, beautiful design, and intuitive user interfaces.
                            </p>
                            <p class="text-muted-foreground text-lg leading-relaxed">
                                Founded in 2024, Janari has grown from a simple idea into a robust platform used by creators worldwide.
                            </p>
                            
                            <!-- Stats -->
                            <div class="grid grid-cols-3 gap-8 pt-6 border-t border-border">
                                <div>
                                    <div class="text-3xl font-bold text-primary">100+</div>
                                    <div class="text-sm text-muted-foreground mt-1">Components</div>
                                </div>
                                <div>
                                    <div class="text-3xl font-bold text-primary">24/7</div>
                                    <div class="text-sm text-muted-foreground mt-1">Support</div>
                                </div>
                                <div>
                                    <div class="text-3xl font-bold text-primary">10k+</div>
                                    <div class="text-sm text-muted-foreground mt-1">Users</div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Image Layout -->
                        <div class="relative">
                            <div class="absolute inset-0 bg-gradient-to-tr from-primary/20 to-transparent rounded-2xl transform rotate-3"></div>
                            <img 
                                src="https://images.unsplash.com/photo-1522071820081-009f0129c71c?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80" 
                                alt="Team working" 
                                class="relative rounded-2xl shadow-2xl object-cover w-full h-[500px]"
                            >
                        </div>
                    </div>
                </div>
            </section>

             <!-- Team Section -->
             <section class="py-20 bg-muted/50 dark:bg-muted/20">
                <div class="container mx-auto px-4 text-center">
                    <h2 class="text-3xl font-bold mb-12 text-foreground">Meet the Team</h2>
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-8">
                        <div v-for="member in teamMembers" :key="member.name" class="group">
                             <div class="w-full aspect-[4/5] bg-slate-200 dark:bg-slate-800 rounded-xl mb-4 overflow-hidden relative border border-border">
                                <img 
                                    :src="member.image" 
                                    :alt="member.name"
                                    class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                                >
                                <div class="absolute inset-0 bg-gradient-to-t from-black/50 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
                             </div>
                             <h3 class="font-bold text-lg text-foreground">{{ member.name }}</h3>
                             <p class="text-sm text-muted-foreground">{{ member.role }}</p>
                        </div>
                    </div>
                </div>
             </section>
        </div>
    </template>
  </div>
</template>

<script setup lang="ts">
import { logger } from '@/utils/logger';
import { ref, onMounted } from 'vue'
import api from '@/services/api'
import BlockRenderer from '@/components/content-renderer/BlockRenderer.vue'

interface TeamMember {
    name: string;
    role: string;
    image: string;
}

import type { Content } from '@/types/cms'

interface PageData extends Content {
    title: string;
    blocks?: any[];
}

const pageData = ref<PageData | null>(null)
const loading = ref(true)

// Team members data
const teamMembers: TeamMember[] = [
    { name: 'Ari Nurcahya', role: 'Lead Developer', image: '/images/fallback/team-1.png' },
    { name: 'Sarah Amira', role: 'Frontend Engineer', image: '/images/fallback/team-2.png' },
    { name: 'Budi Santoso', role: 'Backend Engineer', image: '/images/fallback/team-3.png' },
    { name: 'Maya Putri', role: 'UI/UX Designer', image: '/images/fallback/team-4.png' }
]

onMounted(async () => {
  try {
    // Fetch about page content
    const response = await api.get('/cms/contents/about')
    pageData.value = response.data.data
  } catch (error) {
    logger.error('Failed to fetch about page:', error)
  } finally {
    loading.value = false
  }
})
</script>

