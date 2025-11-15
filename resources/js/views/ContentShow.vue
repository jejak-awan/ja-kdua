<template>
    <div class="min-h-screen bg-gray-50">
        <div class="max-w-4xl mx-auto px-4 py-8">
            <div v-if="loading" class="text-center py-12">
                <p class="text-gray-500">Loading...</p>
            </div>

            <template v-else-if="content">
                <article class="bg-white rounded-lg shadow-lg p-8">
                    <h1 class="text-4xl font-bold text-gray-900 mb-4">{{ content.title }}</h1>
                    
                    <div class="flex items-center space-x-4 text-sm text-gray-500 mb-6">
                        <span v-if="content.category">{{ content.category.name }}</span>
                        <span>{{ formatDate(content.published_at || content.created_at) }}</span>
                        <span v-if="content.author">By {{ content.author.name }}</span>
                    </div>

                    <div v-if="content.featured_image" class="mb-6">
                        <img :src="content.featured_image" :alt="content.title" class="w-full rounded-lg" />
                    </div>

                    <div class="prose max-w-none" v-html="formatBody(content.body)"></div>

                    <div v-if="content.tags && content.tags.length > 0" class="mt-8 pt-6 border-t">
                        <div class="flex flex-wrap gap-2">
                            <span
                                v-for="tag in content.tags"
                                :key="tag.id"
                                class="px-3 py-1 bg-gray-100 text-gray-700 rounded-full text-sm"
                            >
                                {{ tag.name }}
                            </span>
                        </div>
                    </div>
                    
                    <div class="mt-8 pt-6 border-t">
                        <router-link to="/" class="text-indigo-600 hover:text-indigo-800">
                            ← Back to Home
                        </router-link>
                    </div>
                </article>
            </template>

            <div v-else class="text-center py-12">
                <p class="text-gray-500">Content not found</p>
                <router-link to="/" class="text-indigo-600 hover:text-indigo-800 mt-4 inline-block">
                    Go to Home
                </router-link>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import { useCmsStore } from '../stores/cms';

const route = useRoute();
const cmsStore = useCmsStore();

const content = ref(null);
const loading = ref(true);

const formatDate = (date) => {
    if (!date) return '';
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    });
};

const formatBody = (body) => {
    if (!body) return '';
    // Simple HTML rendering - in production, use a proper sanitizer
    return body;
};

onMounted(async () => {
    try {
        loading.value = true;
        const data = await cmsStore.fetchContent(route.params.slug);
        content.value = data;
    } catch (error) {
        console.error('Failed to fetch content:', error);
        content.value = null;
    } finally {
        loading.value = false;
    }
});
</script>
