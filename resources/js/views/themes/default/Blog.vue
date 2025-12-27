<template>
    <div class="min-h-screen bg-background py-12">
        <div class="container mx-auto px-4">
            <div class="text-center mb-12">
                <h1 class="text-4xl font-bold text-gray-900 mb-4">Our Blog</h1>
                <p class="text-gray-600 max-w-2xl mx-auto">
                    Insights, tutorials, and updates from the JA-CMS team.
                </p>
            </div>

            <div v-if="loading" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <div v-for="n in 6" :key="n" class="animate-pulse bg-card rounded-xl h-96 shadow-sm border border-border"></div>
            </div>

            <div v-else-if="posts.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <PostCard 
                    v-for="post in posts" 
                    :key="post.id" 
                    :post="post"
                />
            </div>

            <div v-else class="text-center py-20 bg-card rounded-xl shadow-sm border border-border">
                <h3 class="text-xl font-medium text-gray-900 mb-2">No posts found</h3>
                <p class="text-gray-500">Check back later for new content.</p>
            </div>
            
            <!-- Pagination (Simple Implementation) -->
            <div v-if="posts.length > 0" class="mt-12 flex justify-center gap-2">
                <button class="px-4 py-2 bg-card border border-border rounded-lg text-muted-foreground hover:bg-accent disabled:opacity-50">Previous</button>
                <button class="px-4 py-2 bg-primary text-primary-foreground rounded-lg hover:bg-primary/90">1</button>
                <button class="px-4 py-2 bg-card border border-border rounded-lg text-muted-foreground hover:bg-accent">2</button>
                <button class="px-4 py-2 bg-card border border-border rounded-lg text-muted-foreground hover:bg-accent">Next</button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import api from '@/services/api';
import PostCard from './components/PostCard.vue';

const posts = ref([]);
const loading = ref(true);

onMounted(async () => {
    try {
        // Fetch Posts
        const response = await api.get('/cms/contents', {
             params: {
                status: 'published',
                type: 'post',
                sort: '-published_at',
                limit: 12
            }
        });
        posts.value = response.data.data || [];
    } catch (error) {
        console.error('Failed to load blog:', error);
    } finally {
        loading.value = false;
    }
});
</script>
