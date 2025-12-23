<template>
    <div>
        <div class="mb-6 flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-foreground">{{ $t('features.newsletter.title') }}</h1>
                <p class="mt-1 text-sm text-muted-foreground">{{ $t('features.newsletter.subtitle') }}</p>
            </div>
            <div class="flex gap-2">
                <button
                    @click="exportCsv"
                    class="px-4 py-2 bg-card border border-input rounded-lg text-sm font-medium text-foreground hover:bg-muted flex items-center"
                >
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    {{ $t('features.newsletter.actions.export') }}
                </button>
            </div>
        </div>

        <div class="bg-card shadow rounded-lg overflow-hidden">
            <!-- Filters & Search -->
            <div class="p-4 border-b border-border flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="flex items-center gap-2">
                    <select
                        v-model="filters.status"
                        @change="fetchSubscribers"
                        class="block w-full pl-3 pr-10 py-2 text-base border-input focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md"
                    >
                        <option value="">{{ $t('features.newsletter.filters.allStatus') }}</option>
                        <option value="subscribed">{{ $t('features.newsletter.filters.subscribed') }}</option>
                        <option value="unsubscribed">{{ $t('features.newsletter.filters.unsubscribed') }}</option>
                    </select>
                </div>
                <div class="relative max-w-xs w-full">
                    <input
                        v-model="filters.q"
                        @input="debounceSearch"
                        type="text"
                        :placeholder="$t('features.newsletter.filters.search')"
                        class="block w-full pl-10 pr-3 py-2 border border-input bg-card text-foreground rounded-md leading-5 bg-card placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                    >
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Table -->
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-border">
                    <thead class="bg-muted">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">
                                {{ $t('features.newsletter.table.subscriber') }}
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">
                                {{ $t('features.newsletter.table.status') }}
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">
                                {{ $t('features.newsletter.table.joinedAt') }}
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">
                                {{ $t('features.newsletter.table.source') }}
                            </th>
                            <th scope="col" class="relative px-6 py-3">
                                <span class="sr-only">{{ $t('features.newsletter.table.actions') }}</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-card divide-y divide-border">
                        <tr v-if="loading">
                            <td colspan="5" class="px-6 py-4 text-center text-sm text-muted-foreground">
                                {{ $t('features.newsletter.messages.loading') }}
                            </td>
                        </tr>
                        <tr v-else-if="subscribers.length === 0">
                            <td colspan="5" class="px-6 py-4 text-center text-sm text-muted-foreground">
                                {{ $t('features.newsletter.messages.empty') }}
                            </td>
                        </tr>
                        <tr v-for="subscriber in subscribers" :key="subscriber.id" class="hover:bg-muted">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10">
                                        <div class="h-10 w-10 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600 font-semibold">
                                            {{ (subscriber.name || subscriber.email).charAt(0).toUpperCase() }}
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-foreground">
                                            {{ subscriber.name || $t('features.newsletter.messages.noName') }}
                                        </div>
                                        <div class="text-sm text-muted-foreground">
                                            {{ subscriber.email }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span
                                    class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                                    :class="subscriber.status === 'subscribed' ? 'bg-green-500/20 text-green-400' : 'bg-red-500/20 text-red-400'"
                                >
                                    {{ $t(`features.newsletter.filters.${subscriber.status}`) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-muted-foreground">
                                {{ formatDate(subscriber.created_at) }}
                            </td>
                             <td class="px-6 py-4 whitespace-nowrap text-sm text-muted-foreground truncated max-w-xs overflow-hidden text-ellipsis" :title="subscriber.source">
                                {{ subscriber.source }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <button
                                    @click="deleteSubscriber(subscriber)"
                                    class="text-red-600 hover:text-red-900"
                                >
                                    {{ $t('features.newsletter.actions.delete') }}
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div
                v-if="pagination.total > 0"
                class="bg-card px-4 py-3 border-t border-border flex items-center justify-between sm:px-6"
            >
                <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                    <div>
                        <p class="text-sm text-foreground">
                            {{ $t('common.pagination.showing', { from: pagination.from, to: pagination.to, total: pagination.total }) }}
                        </p>
                    </div>
                    <div>
                        <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                            <button
                                @click="changePage(pagination.current_page - 1)"
                                :disabled="pagination.current_page === 1"
                                class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-input bg-card text-sm font-medium text-muted-foreground hover:bg-muted disabled:opacity-50"
                            >
                                <span class="sr-only">{{ $t('common.pagination.previous') }}</span>
                                <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                            </button>
                            <button
                                @click="changePage(pagination.current_page + 1)"
                                :disabled="pagination.current_page === pagination.last_page"
                                class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-input bg-card text-sm font-medium text-muted-foreground hover:bg-muted disabled:opacity-50"
                            >
                                <span class="sr-only">{{ $t('common.pagination.next') }}</span>
                                <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                                </svg>
                            </button>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import api from '../../../services/api';
import { parseResponse } from '../../../utils/responseParser';
import _ from 'lodash';

const { t } = useI18n();

const loading = ref(false);
const subscribers = ref([]);
const pagination = ref({});
const filters = ref({
    status: '',
    q: '',
    page: 1,
});

const fetchSubscribers = async () => {
    loading.value = true;
    try {
        const params = { ...filters.value };
        const response = await api.get('/admin/cms/newsletter/subscribers', { params });
        const { data, pagination: pag } = parseResponse(response);
        subscribers.value = data;
        if (pag) {
            pagination.value = pag;
        }
    } catch (error) {
        console.error('Failed to fetch subscribers:', error);
    } finally {
        loading.value = false;
    }
};

const debounceSearch = _.debounce(() => {
    filters.value.page = 1;
    fetchSubscribers();
}, 300);

const changePage = (page) => {
    if (page < 1 || page > pagination.value.last_page) return;
    filters.value.page = page;
    fetchSubscribers();
};

const deleteSubscriber = async (subscriber) => {
    if (!confirm(t('features.newsletter.messages.deleteConfirm', { email: subscriber.email }))) return;

    try {
        await api.delete(`/admin/cms/newsletter/subscribers/${subscriber.id}`);
        fetchSubscribers();
    } catch (error) {
        alert(t('features.newsletter.messages.deleteFailed'));
    }
};

const exportCsv = async () => {
    try {
        const response = await api.get('/admin/cms/newsletter/export', {
            params: { status: filters.value.status },
            responseType: 'blob',
        });
        
        const url = window.URL.createObjectURL(new Blob([response.data]));
        const link = document.createElement('a');
        link.href = url;
        link.setAttribute('download', `subscribers-${new Date().toISOString().split('T')[0]}.csv`);
        document.body.appendChild(link);
        link.click();
        link.remove();
    } catch (error) {
        alert(t('features.newsletter.messages.exportFailed'));
    }
};

const formatDate = (dateString) => {
    if (!dateString) return '-';
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

onMounted(() => {
    fetchSubscribers();
});
</script>
