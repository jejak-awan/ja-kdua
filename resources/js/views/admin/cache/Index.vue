<template>
    <div>
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900">Cache Management</h1>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div class="bg-white shadow rounded-lg p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-8 w-8 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Cache Status</p>
                        <p class="text-2xl font-semibold text-gray-900">
                            {{ cacheStats.status || 'Active' }}
                        </p>
                    </div>
                </div>
            </div>
            <div class="bg-white shadow rounded-lg p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-8 w-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Cache Hits</p>
                        <p class="text-2xl font-semibold text-gray-900">
                            {{ cacheStats.hits || 0 }}
                        </p>
                    </div>
                </div>
            </div>
            <div class="bg-white shadow rounded-lg p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-8 w-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Cache Misses</p>
                        <p class="text-2xl font-semibold text-gray-900">
                            {{ cacheStats.misses || 0 }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white shadow rounded-lg p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Cache Actions</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <button
                    @click="clearAllCache"
                    :disabled="clearing"
                    class="px-4 py-3 bg-red-600 text-white rounded-md hover:bg-red-700 disabled:opacity-50 text-left"
                >
                    <div class="font-medium">Clear All Cache</div>
                    <div class="text-sm opacity-90">Remove all cached data</div>
                </button>
                <button
                    @click="clearContentCache"
                    :disabled="clearing"
                    class="px-4 py-3 bg-yellow-600 text-white rounded-md hover:bg-yellow-700 disabled:opacity-50 text-left"
                >
                    <div class="font-medium">Clear Content Cache</div>
                    <div class="text-sm opacity-90">Remove content-related cache</div>
                </button>
                <button
                    @click="warmUpCache"
                    :disabled="warming"
                    class="px-4 py-3 bg-green-600 text-white rounded-md hover:bg-green-700 disabled:opacity-50 text-left"
                >
                    <div class="font-medium">Warm Up Cache</div>
                    <div class="text-sm opacity-90">Preload frequently used data</div>
                </button>
            </div>
        </div>

        <div v-if="cacheStats.details" class="mt-6 bg-white shadow rounded-lg p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Cache Statistics</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Key
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Value
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-for="(value, key) in cacheStats.details" :key="key">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                {{ key }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ value }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import api from '../../../services/api';

const cacheStats = ref({});
const clearing = ref(false);
const warming = ref(false);

const fetchCacheStats = async () => {
    try {
        // Try to get cache stats if endpoint exists
        // For now, we'll set default values
        cacheStats.value = {
            status: 'Active',
            hits: 0,
            misses: 0,
        };
    } catch (error) {
        console.error('Failed to fetch cache stats:', error);
    }
};

const clearAllCache = async () => {
    if (!confirm('Are you sure you want to clear all cache? This may affect performance temporarily.')) {
        return;
    }

    clearing.value = true;
    try {
        await api.post('/admin/cms/cache/clear');
        alert('All cache cleared successfully');
        await fetchCacheStats();
    } catch (error) {
        console.error('Failed to clear cache:', error);
        alert('Failed to clear cache');
    } finally {
        clearing.value = false;
    }
};

const clearContentCache = async () => {
    if (!confirm('Are you sure you want to clear content cache?')) {
        return;
    }

    clearing.value = true;
    try {
        await api.post('/admin/cms/cache/clear-content');
        alert('Content cache cleared successfully');
        await fetchCacheStats();
    } catch (error) {
        console.error('Failed to clear content cache:', error);
        alert('Failed to clear content cache');
    } finally {
        clearing.value = false;
    }
};

const warmUpCache = async () => {
    warming.value = true;
    try {
        await api.post('/admin/cms/cache/warm-up');
        alert('Cache warmed up successfully');
        await fetchCacheStats();
    } catch (error) {
        console.error('Failed to warm up cache:', error);
        alert('Failed to warm up cache');
    } finally {
        warming.value = false;
    }
};

onMounted(() => {
    fetchCacheStats();
});
</script>

