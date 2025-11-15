<template>
    <div class="min-h-screen bg-gray-50">
        <!-- Header -->
        <header class="bg-white shadow-sm">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
                <div class="flex justify-between items-center">
                    <h1 class="text-2xl font-bold text-gray-900">JA-CMS</h1>
                    <nav class="flex space-x-4 items-center">
                        <router-link
                            v-if="!authStore.isAuthenticated"
                            to="/login"
                            class="px-4 py-2 text-gray-600 hover:text-gray-900 transition-colors"
                        >
                            Login
                        </router-link>
                        <router-link
                            v-if="!authStore.isAuthenticated"
                            to="/register"
                            class="px-4 py-2 text-gray-600 hover:text-gray-900 transition-colors"
                        >
                            Register
                        </router-link>
                        <router-link
                            v-if="authStore.isAuthenticated"
                            to="/admin"
                            class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors font-medium"
                        >
                            Go to Admin Dashboard
                        </router-link>
                    </nav>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div v-if="loading" class="text-center py-12">
                <p class="text-gray-500">Loading...</p>
            </div>

            <div v-else>
                <h2 class="text-3xl font-bold text-gray-900 mb-6">Latest Contents</h2>
                
                <div v-if="contents.length === 0" class="text-center py-12">
                    <p class="text-gray-500">No contents available yet.</p>
                </div>

                <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <article
                        v-for="content in contents"
                        :key="content.id"
                        class="bg-white rounded-lg shadow hover:shadow-lg transition-shadow cursor-pointer"
                        @click="$router.push(`/content/${content.slug}`)"
                    >
                        <div v-if="content.featured_image" class="h-48 bg-gray-200 rounded-t-lg overflow-hidden">
                            <img :src="content.featured_image" :alt="content.title" class="w-full h-full object-cover" />
                        </div>
                        <div class="p-6">
                            <div class="flex items-center space-x-2 text-xs text-gray-500 mb-2">
                                <span v-if="content.category">{{ content.category.name }}</span>
                                <span>{{ formatDate(content.published_at || content.created_at) }}</span>
                            </div>
                            <h3 class="text-xl font-semibold text-gray-900 mb-2">{{ content.title }}</h3>
                            <p class="text-gray-600 text-sm line-clamp-3">{{ content.excerpt }}</p>
                        </div>
                    </article>
                </div>
            </div>
        </main>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useCmsStore } from '../stores/cms';
import { useAuthStore } from '../stores/auth';

const cmsStore = useCmsStore();
const authStore = useAuthStore();

const contents = ref([]);
const loading = ref(true);

// Initialize auth store (but don't require authentication for home page)
authStore.initAuth();

const formatDate = (date) => {
    if (!date) return '';
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
};

onMounted(async () => {
    try {
        loading.value = true;
        const data = await cmsStore.fetchContents({ per_page: 9 });
        contents.value = data?.data || data || [];
    } catch (error) {
        console.error('Failed to fetch contents:', error);
        contents.value = [];
    } finally {
        loading.value = false;
    }
});
</script>

