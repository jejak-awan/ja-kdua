<template>
    <div>
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-foreground">{{ t('features.search.title') }}</h1>
            <p v-if="query" class="text-sm text-muted-foreground mt-1">
                {{ t('features.search.resultsFor') }} <span class="font-medium">{{ query }}</span>
            </p>
        </div>

        <div class="mb-4">
            <div class="relative">
                <input
                    v-model="searchQuery"
                    @keyup.enter="performSearch"
                    type="text"
                    :placeholder="t('features.search.placeholder')"
                    class="w-full pl-10 pr-4 py-3 border border-input rounded-lg focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                >
                <svg class="absolute left-3 top-3.5 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </div>
        </div>

        <!-- Filters -->
        <div class="mb-4 flex items-center space-x-2">
            <span class="text-sm text-foreground">{{ t('features.search.filterBy') }}</span>
            <button
                v-for="type in availableTypes"
                :key="type"
                @click="toggleTypeFilter(type)"
                :class="[
                    'px-3 py-1 text-sm rounded-md transition-colors',
                    typeFilters.includes(type)
                        ? 'bg-indigo-600 text-white'
                        : 'bg-secondary text-foreground hover:bg-accent'
                ]"
            >
                {{ t(`features.search.types.${type}`) }}
            </button>
        </div>

        <div v-if="loading" class="text-center py-12">
            <p class="text-muted-foreground">{{ t('features.search.searching') }}</p>
        </div>

        <div v-else-if="results.length === 0 && query" class="text-center py-12">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
            <p class="mt-4 text-muted-foreground">{{ t('features.search.empty') }}</p>
        </div>

        <div v-else-if="results.length > 0" class="space-y-4">
            <!-- Group results by type -->
            <div
                v-for="type in groupedResults"
                :key="type"
                class="bg-card shadow rounded-lg p-6"
            >
                <h2 class="text-lg font-semibold text-foreground mb-4 capitalize">{{ t(`features.search.types.${type}`) }}</h2>
                <div class="space-y-3">
                    <div
                        v-for="result in groupedResults[type]"
                        :key="`${result.type}-${result.id}`"
                        @click="handleResultClick(result)"
                        class="p-4 border border-border rounded-lg hover:bg-muted cursor-pointer transition-colors"
                    >
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <h3 class="text-sm font-medium text-foreground">{{ result.title }}</h3>
                                <p v-if="result.description" class="text-sm text-muted-foreground mt-1">{{ result.description }}</p>
                                <div class="mt-2 flex items-center space-x-4 text-xs text-gray-400">
                                    <span v-if="result.created_at">{{ formatDate(result.created_at) }}</span>
                                    <span v-if="result.author">{{ result.author }}</span>
                                </div>
                            </div>
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div v-else class="text-center py-12">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
            <p class="mt-4 text-muted-foreground">{{ t('features.search.initial') }}</p>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useI18n } from 'vue-i18n';
import api from '../../../services/api';

const { t } = useI18n();
import { parseResponse, ensureArray } from '../../../utils/responseParser';

const route = useRoute();
const router = useRouter();

const query = ref(route.query.q || '');
const searchQuery = ref(route.query.q || '');
const results = ref([]);
const loading = ref(false);
const typeFilters = ref([]);

const availableTypes = ['content', 'category', 'user', 'media', 'page', 'tag'];

const groupedResults = computed(() => {
    const grouped = {};
    
    let filteredResults = results.value;
    if (typeFilters.value.length > 0) {
        filteredResults = filteredResults.filter(r => typeFilters.value.includes(r.type));
    }
    
    filteredResults.forEach(result => {
        if (!grouped[result.type]) {
            grouped[result.type] = [];
        }
        grouped[result.type].push(result);
    });
    
    return grouped;
});

const performSearch = async () => {
    if (!searchQuery.value || searchQuery.value.length < 2) {
        return;
    }
    
    query.value = searchQuery.value;
    loading.value = true;
    
    try {
        const response = await api.get('/admin/cms/search', {
            params: { q: query.value },
        });
        const { data } = parseResponse(response);
        results.value = ensureArray(data);
        
        // Update URL
        router.replace({ query: { q: query.value } });
    } catch (error) {
        console.error('Failed to search:', error);
        results.value = [];
    } finally {
        loading.value = false;
    }
};

const toggleTypeFilter = (type) => {
    const index = typeFilters.value.indexOf(type);
    if (index > -1) {
        typeFilters.value.splice(index, 1);
    } else {
        typeFilters.value.push(type);
    }
};

const handleResultClick = (result) => {
    // Navigate based on result type
    if (result.type === 'content') {
        router.push({ name: 'contents.edit', params: { id: result.id } });
    } else if (result.type === 'category') {
        router.push({ name: 'categories.edit', params: { id: result.id } });
    } else if (result.type === 'user') {
        router.push({ name: 'users.edit', params: { id: result.id } });
    } else if (result.type === 'media') {
        router.push({ name: 'media', query: { id: result.id } });
    } else if (result.url) {
        router.push(result.url);
    }
};

const formatDate = (date) => {
    if (!date) return '';
    return new Date(date).toLocaleDateString();
};

onMounted(() => {
    if (query.value) {
        performSearch();
    }
});
</script>

