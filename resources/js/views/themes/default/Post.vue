<template>
    <div class="min-h-screen bg-background">
        <!-- Loading State -->
        <div v-if="loading" class="container mx-auto px-4 py-12">
            <div class="animate-pulse space-y-8 max-w-3xl mx-auto">
                <div class="h-8 bg-muted rounded w-3/4"></div>
                <div class="h-4 bg-muted rounded w-1/2"></div>
                <div class="h-96 bg-muted rounded-xl"></div>
                <div class="space-y-4">
                    <div class="h-4 bg-muted rounded"></div>
                    <div class="h-4 bg-muted rounded"></div>
                </div>
            </div>
        </div>

        <!-- Post Content -->
        <article v-else-if="post" class="pb-20">
            <!-- Header -->
            <header class="py-16 md:py-24 bg-muted mb-12">
                <div class="container mx-auto px-4 text-center max-w-4xl">
                    <div class="flex items-center justify-center gap-2 mb-6">
                        <span v-if="post.category" class="px-3 py-1 bg-secondary text-secondary-foreground rounded-full text-sm font-medium">
                            {{ post.category.name }}
                        </span>
                        <span class="text-muted-foreground text-sm">
                            {{ new Date(post.published_at).toLocaleDateString() }}
                        </span>
                    </div>
                    <h1 class="text-4xl md:text-5xl font-bold text-foreground mb-6 leading-tight">
                        {{ post.title }}
                    </h1>
                    <div class="flex items-center justify-center gap-3">
                        <div class="w-10 h-10 bg-muted rounded-full overflow-hidden">
                             <!-- Author Avatar placeholder -->
                             <svg class="w-full h-full text-muted-foreground" fill="currentColor" viewBox="0 0 24 24"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>
                        </div>
                        <div class="text-left">
                            <p class="text-sm font-medium text-foreground">{{ post.author?.name || 'Unknown Author' }}</p>
                            <p class="text-xs text-muted-foreground">Editor</p>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Featured Image -->
            <div v-if="post.featured_image" class="container mx-auto px-4 mb-12 max-w-5xl">
                <img :src="post.featured_image" :alt="post.title" class="w-full h-[500px] object-cover rounded-xl shadow-lg">
            </div>

            <!-- Content -->
            <div class="container mx-auto px-4 max-w-3xl">
                <!-- If using Page Builder -->
                <div v-if="post.blocks && post.blocks.length > 0" class="space-y-0">
                    <BlockRenderer :blocks="post.blocks" />
                </div>
                
                <!-- If using Classic Editor -->
                <div v-else class="prose prose-lg prose-indigo mx-auto" v-html="post.body"></div>
                
                <!-- Tags -->
                <div v-if="post.tags && post.tags.length > 0" class="mt-12 pt-8 border-t border-border">
                    <div class="flex flex-wrap gap-2">
                        <span v-for="tag in post.tags" :key="tag.id" class="text-sm text-muted-foreground px-3 py-1 bg-muted rounded-lg">
                            #{{ tag.name }}
                        </span>
                    </div>
                </div>
            </div>
        </article>
        
        <!-- Not Found -->
        <div v-else class="text-center py-20">
            <h1 class="text-2xl font-bold text-foreground">{{ $t('features.frontend.post.notFound') }}</h1>
            <router-link to="/blog" class="text-primary hover:text-primary/80 mt-4 inline-block">{{ $t('features.frontend.post.backToBlog') }}</router-link>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useRoute } from 'vue-router';
import { useI18n } from 'vue-i18n';
import api from '@/services/api';
import BlockRenderer from '@/components/builder/blocks/BlockRenderer.vue';

const route = useRoute();
const { t } = useI18n();
const post = ref(null);
const loading = ref(true);

onMounted(async () => {
    try {
        const slug = route.params.slug;
        // In real app, might fetch by slug or ID depending on router setup.
        // Assuming route /blog/:slug
        const response = await api.get(`/cms/contents/${slug}`); // Or specialized endpoint
        post.value = response.data.data || response.data;
    } catch (error) {
        console.error('Failed to load post:', error);
    } finally {
        loading.value = false;
    }
});
</script>
