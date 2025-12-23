<template>
    <div>
        <div class="mb-6 flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-foreground">Content Revisions</h1>
                <p class="text-sm text-muted-foreground mt-1">{{ contentTitle }}</p>
            </div>
            <router-link
                :to="{ name: 'contents.edit', params: { id: contentId } }"
                class="text-muted-foreground hover:text-foreground"
            >
                ← Back to Content
            </router-link>
        </div>

        <div v-if="loading" class="bg-card shadow rounded-lg p-12 text-center">
            <p class="text-muted-foreground">Loading revisions...</p>
        </div>

        <div v-else-if="revisions.length === 0" class="bg-card shadow rounded-lg p-12 text-center">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <p class="mt-4 text-muted-foreground">No revisions found</p>
        </div>

        <div v-else class="bg-card shadow rounded-lg overflow-hidden">
            <table class="min-w-full divide-y divide-border">
                <thead class="bg-muted">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">
                            Version
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">
                            Author
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">
                            Date
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">
                            Changes
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-muted-foreground uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-card divide-y divide-border">
                    <tr v-for="revision in revisions" :key="revision.id" class="hover:bg-muted">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <span
                                    v-if="revision.is_current"
                                    class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-500/20 text-green-400 mr-2"
                                >
                                    Current
                                </span>
                                <div class="text-sm font-medium text-foreground">v{{ revision.version }}</div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-foreground">{{ revision.author?.name || 'System' }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-muted-foreground">{{ formatDate(revision.created_at) }}</div>
                            <div class="text-xs text-gray-400">{{ formatTime(revision.created_at) }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm text-muted-foreground">
                                {{ revision.changes_summary || 'No changes summary' }}
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <div class="flex justify-end space-x-2">
                                <button
                                    @click="viewRevision(revision)"
                                    class="text-indigo-600 hover:text-indigo-900"
                                >
                                    View
                                </button>
                                <button
                                    v-if="!revision.is_current"
                                    @click="restoreRevision(revision)"
                                    class="text-green-600 hover:text-green-900"
                                >
                                    Restore
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Revision Detail Modal -->
        <div
            v-if="viewingRevision"
            class="fixed inset-0 z-50 overflow-y-auto bg-black bg-opacity-50"
            @click.self="viewingRevision = null"
        >
            <div class="flex items-center justify-center min-h-screen px-4">
                <div class="bg-card rounded-lg shadow-xl max-w-4xl w-full max-h-[90vh] overflow-y-auto">
                    <div class="flex items-center justify-between p-6 border-b sticky top-0 bg-card">
                        <h3 class="text-lg font-semibold">
                            Revision v{{ viewingRevision.version }}
                        </h3>
                        <button
                            @click="viewingRevision = null"
                            class="text-gray-400 hover:text-muted-foreground"
                        >
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                    <div class="p-6">
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-foreground mb-1">Title</label>
                                <div class="text-sm text-foreground">{{ viewingRevision.data?.title || '-' }}</div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-foreground mb-1">Content</label>
                                <div
                                    class="text-sm text-foreground prose max-w-none"
                                    v-html="viewingRevision.data?.content || '-'"
                                />
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-foreground mb-1">Status</label>
                                    <div class="text-sm text-foreground">{{ viewingRevision.data?.status || '-' }}</div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-foreground mb-1">Type</label>
                                    <div class="text-sm text-foreground">{{ viewingRevision.data?.type || '-' }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center justify-end space-x-3 p-6 border-t sticky bottom-0 bg-card">
                        <button
                            @click="viewingRevision = null"
                            class="px-4 py-2 border border-input bg-card text-foreground rounded-md text-foreground hover:bg-muted"
                        >
                            Close
                        </button>
                        <button
                            v-if="!viewingRevision.is_current"
                            @click="restoreRevision(viewingRevision)"
                            class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700"
                        >
                            Restore This Revision
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import api from '../../../services/api';

const route = useRoute();
const router = useRouter();
const contentId = route.params.id;
const revisions = ref([]);
const loading = ref(false);
const contentTitle = ref('');
const viewingRevision = ref(null);

const fetchRevisions = async () => {
    loading.value = true;
    try {
        const response = await api.get(`/admin/cms/contents/${contentId}/revisions`);
        revisions.value = response.data.data || response.data;
        
        // Get content title from first revision or fetch content
        if (revisions.value.length > 0 && revisions.value[0].data?.title) {
            contentTitle.value = revisions.value[0].data.title;
        } else {
            try {
                const contentResponse = await api.get(`/admin/cms/contents/${contentId}`);
                contentTitle.value = contentResponse.data.data?.title || contentResponse.data.title || 'Content';
            } catch (error) {
                contentTitle.value = 'Content';
            }
        }
    } catch (error) {
        console.error('Failed to fetch revisions:', error);
    } finally {
        loading.value = false;
    }
};

const viewRevision = async (revision) => {
    try {
        const response = await api.get(`/admin/cms/contents/${contentId}/revisions/${revision.id}`);
        viewingRevision.value = response.data.data || response.data;
    } catch (error) {
        console.error('Failed to fetch revision detail:', error);
        viewingRevision.value = revision;
    }
};

const restoreRevision = async (revision) => {
    if (!confirm(`Are you sure you want to restore revision v${revision.version}? This will replace the current content.`)) {
        return;
    }

    try {
        await api.post(`/admin/cms/contents/${contentId}/revisions/${revision.id}/restore`);
        alert('Revision restored successfully');
        router.push({ name: 'contents.edit', params: { id: contentId } });
    } catch (error) {
        console.error('Failed to restore revision:', error);
        alert(error.response?.data?.message || 'Failed to restore revision');
    }
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString();
};

const formatTime = (date) => {
    return new Date(date).toLocaleTimeString();
};

onMounted(() => {
    fetchRevisions();
});
</script>

