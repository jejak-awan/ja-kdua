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
<!-- Header -->
            <header class="relative py-20 md:py-32 mb-12 overflow-hidden border-b border-border/40">
                <div class="absolute inset-0 bg-gradient-to-b from-primary/5 to-background z-0"></div>
                <div class="container mx-auto px-4 text-center max-w-4xl relative z-10">
                    <div class="flex items-center justify-center gap-3 mb-8">
                        <span v-if="post.category" class="px-4 py-1.5 bg-primary text-primary-foreground rounded-full text-xs font-bold tracking-wider uppercase shadow-sm">
                            {{ post.category.name }}
                        </span>
                        <span class="text-muted-foreground text-sm font-medium flex items-center gap-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            {{ new Date(post.published_at).toLocaleDateString(undefined, { year: 'numeric', month: 'long', day: 'numeric' }) }}
                        </span>
                    </div>
                    <h1 class="text-4xl md:text-6xl font-bold text-foreground mb-8 leading-tight tracking-tight text-balance">
                        {{ post.title }}
                    </h1>
                    <div class="flex items-center justify-center gap-4">
                        <div class="w-12 h-12 bg-muted rounded-full overflow-hidden ring-2 ring-background shadow-md">
                             <!-- Author Avatar placeholder -->
                             <svg class="w-full h-full text-muted-foreground bg-muted" fill="currentColor" viewBox="0 0 24 24"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>
                        </div>
                        <div class="text-left">
                            <p class="text-sm font-bold text-foreground">{{ post.author?.name || 'Janari Team' }}</p>
                            <p class="text-xs text-muted-foreground uppercase tracking-wider">Editor</p>
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
                <div v-if="post.blocks?.length > 0" class="space-y-0">
                    <BlockRenderer :blocks="post.blocks" :is-preview="true" />
                </div>
                
                <!-- If using Classic Editor -->
                <div v-else ref="contentRef" class="prose prose-lg prose-indigo mx-auto" v-html="post.body"></div>
                
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
import { ref, onMounted, computed, nextTick, watch } from 'vue';
import { useRoute } from 'vue-router';
import { useI18n } from 'vue-i18n';
import api from '@/services/api';
import BlockRenderer from '@/components/content-renderer/BlockRenderer.vue';
import { useIconHydration } from '@/composables/useIconHydration';

const route = useRoute();
const { t } = useI18n();
const { hydrateIcons } = useIconHydration();

const post = ref(null);
const loading = ref(true);
const contentRef = ref(null);

watch(() => post.value, () => {
    nextTick(() => {
        if (contentRef.value) {
            hydrateIcons(contentRef.value);
        }
    });
}, { deep: true });

onMounted(async () => {
    try {
        const slug = route.params.slug;
        const response = await api.get(`/cms/contents/${slug}`);
        post.value = response.data.data || response.data;
    } catch (error) {
        console.error('Failed to load post:', error);
    } finally {
        loading.value = false;
        nextTick(() => {
            if (contentRef.value) {
                hydrateIcons(contentRef.value);
            }
        });
    }
});
</script>
