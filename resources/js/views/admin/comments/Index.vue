<template>
    <div>
        <!-- Header -->
        <div class="mb-6 flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-foreground">{{ $t('features.comments.list.title') }}</h1>
                <p class="mt-1 text-sm text-muted-foreground">{{ $t('features.comments.list.subtitle') }}</p>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div v-if="statistics" class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-4">
            <div class="bg-card border border-border rounded-lg p-4">
                <p class="text-2xl font-bold text-foreground">{{ statistics.total }}</p>
                <p class="text-sm text-muted-foreground">{{ $t('features.comments.stats.total') }}</p>
            </div>
            <div class="bg-yellow-500/20 border border-border rounded-lg p-4 cursor-pointer" @click="statusFilter = 'pending'">
                <p class="text-2xl font-bold text-yellow-600">{{ statistics.pending }}</p>
                <p class="text-sm text-yellow-400">{{ $t('features.comments.stats.pending') }}</p>
            </div>
            <div class="bg-green-500/20 border border-border rounded-lg p-4 cursor-pointer" @click="statusFilter = 'approved'">
                <p class="text-2xl font-bold text-green-600">{{ statistics.approved }}</p>
                <p class="text-sm text-green-400">{{ $t('features.comments.stats.approved') }}</p>
            </div>
            <div class="bg-red-500/20 border border-border rounded-lg p-4 cursor-pointer" @click="statusFilter = 'rejected'">
                <p class="text-2xl font-bold text-red-600">{{ statistics.rejected }}</p>
                <p class="text-sm text-red-400">{{ $t('features.comments.stats.rejected') }}</p>
            </div>
            <div class="bg-muted border border-border rounded-lg p-4 cursor-pointer" @click="statusFilter = 'spam'">
                <p class="text-2xl font-bold text-muted-foreground">{{ statistics.spam }}</p>
                <p class="text-sm text-foreground">{{ $t('features.comments.stats.spam') }}</p>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-card border border-border rounded-lg p-4 mb-4">
            <div class="flex items-center space-x-4">
                <input
                    v-model="search"
                    type="text"
                    :placeholder="$t('features.comments.filter.searchPlaceholder')"
                    class="flex-1 px-4 py-2 border border-input bg-card text-foreground rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                >
                <select
                    v-model="statusFilter"
                    class="px-4 py-2 border border-input bg-card text-foreground rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                >
                    <option value="">{{ $t('features.comments.filter.allStatus') }}</option>
                    <option value="pending">{{ $t('features.comments.status.pending') }}</option>
                    <option value="approved">{{ $t('features.comments.status.approved') }}</option>
                    <option value="rejected">{{ $t('features.comments.status.rejected') }}</option>
                    <option value="spam">{{ $t('features.comments.status.spam') }}</option>
                </select>
            </div>
            <!-- Bulk Actions -->
            <div v-if="selectedIds.length > 0" class="mt-3 flex items-center space-x-2 pt-3 border-t">
                <span class="text-sm text-muted-foreground">{{ selectedIds.length }} selected:</span>
                <button @click="bulkAction('approve')" class="px-3 py-1 text-sm bg-green-100 text-green-700 rounded-md hover:bg-green-200">{{ $t('features.comments.actions.approveAll') }}</button>
                <button @click="bulkAction('reject')" class="px-3 py-1 text-sm bg-red-100 text-red-700 rounded-md hover:bg-red-200">{{ $t('features.comments.actions.rejectAll') }}</button>
                <button @click="bulkAction('spam')" class="px-3 py-1 text-sm bg-secondary text-foreground rounded-md hover:bg-muted">{{ $t('features.comments.actions.markSpam') }}</button>
                <button @click="bulkAction('delete')" class="px-3 py-1 text-sm bg-red-500 text-white rounded-md hover:bg-red-600">{{ $t('common.actions.delete') }}</button>
                <button @click="selectedIds = []" class="px-3 py-1 text-sm text-muted-foreground hover:text-foreground">{{ $t('common.actions.clear') }}</button>
            </div>
        </div>

        <!-- Comments List -->
        <div v-if="loading" class="bg-card border border-border rounded-lg p-12 text-center">
            <p class="text-muted-foreground">{{ $t('common.loading.default') }}</p>
        </div>

        <div v-else-if="comments.length === 0" class="bg-card border border-border rounded-lg p-12 text-center">
            <svg class="mx-auto h-12 w-12 text-muted-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
            </svg>
            <p class="mt-4 text-muted-foreground">{{ $t('features.comments.list.empty') }}</p>
        </div>

        <div v-else class="space-y-4">
            <div
                v-for="comment in comments"
                :key="comment.id"
                class="bg-card border border-border rounded-lg p-6"
            >
                <!-- Comment Header -->
                <div class="flex items-start justify-between mb-4">
                    <div class="flex items-start space-x-3 flex-1">
                        <!-- Checkbox for selection -->
                        <div class="flex-shrink-0 pt-1">
                            <input
                                type="checkbox"
                                :checked="selectedIds.includes(comment.id)"
                                @change="toggleSelection(comment.id)"
                                class="h-4 w-4 text-indigo-600 border-input rounded focus:ring-indigo-500"
                            >
                        </div>
                        <div class="flex-shrink-0">
                            <div class="h-10 w-10 rounded-full bg-indigo-500/20 flex items-center justify-center">
                                <span class="text-indigo-600 font-medium text-sm">
                                    {{ ((comment.user?.name || comment.name || 'U')?.charAt(0) || 'U').toUpperCase() }}
                                </span>
                            </div>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center space-x-2">
                                <p class="text-sm font-medium text-foreground">
                                    {{ comment.user?.name || comment.name || 'Anonymous' }}
                                </p>
                                <span
                                    class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                                    :class="{
                                        'bg-yellow-500/20 text-yellow-400': comment.status === 'pending',
                                        'bg-green-500/20 text-green-400': comment.status === 'approved',
                                        'bg-red-500/20 text-red-400': comment.status === 'rejected',
                                        'bg-secondary text-secondary-foreground': comment.status === 'spam',
                                    }"
                                >
                                    {{ comment.status }}
                                </span>
                            </div>
                            <p class="text-xs text-muted-foreground mt-1">
                                {{ comment.user?.email || comment.email || 'No email' }}
                            </p>
                            <p class="text-xs text-muted-foreground">
                                {{ formatDate(comment.created_at) }}
                            </p>
                        </div>
                    </div>
                    <div class="flex items-center space-x-2">
                        <button
                            v-if="comment.status === 'pending'"
                            @click="approveComment(comment)"
                            class="text-green-600 hover:text-green-800 text-sm font-medium"
                        >
                            Approve
                        </button>
                        <button
                            v-if="comment.status === 'pending'"
                            @click="rejectComment(comment)"
                            class="text-red-600 hover:text-red-800 text-sm font-medium"
                        >
                            Reject
                        </button>
                        <button
                            v-if="comment.status !== 'spam'"
                            @click="markAsSpam(comment)"
                            class="text-muted-foreground hover:text-foreground text-sm font-medium"
                        >
                            Spam
                        </button>
                        <button
                            @click="deleteComment(comment)"
                            class="text-red-600 hover:text-red-800 text-sm font-medium"
                        >
                            Delete
                        </button>
                    </div>
                </div>

                <!-- Comment Body -->
                <div class="mb-4">
                    <p class="text-sm text-foreground whitespace-pre-wrap">{{ comment.body }}</p>
                </div>

                <!-- Comment Meta -->
                <div class="flex items-center justify-between text-xs text-muted-foreground pt-4 border-t">
                    <div class="flex items-center space-x-4">
                        <span v-if="comment.content">
                            On: 
                            <router-link
                                :to="{ name: 'contents.edit', params: { id: comment.content.id } }"
                                class="text-indigo-600 hover:text-indigo-800"
                            >
                                {{ comment.content.title }}
                            </router-link>
                        </span>
                        <span v-if="comment.parent">
                            Reply to: {{ comment.parent.user?.name || comment.parent.name || 'Anonymous' }}
                        </span>
                    </div>
                    <div>
                        <span v-if="comment.replies_count > 0">
                            {{ comment.replies_count }} {{ comment.replies_count === 1 ? 'reply' : 'replies' }}
                        </span>
                    </div>
                </div>

                <!-- Replies (if any) -->
                <div v-if="comment.replies && comment.replies.length > 0" class="mt-4 pl-8 border-l-2 border-border">
                    <div
                        v-for="reply in comment.replies"
                        :key="reply.id"
                        class="mb-4 last:mb-0"
                    >
                        <div class="flex items-start space-x-3">
                            <div class="flex-shrink-0">
                                <div class="h-8 w-8 rounded-full bg-secondary flex items-center justify-center">
                                    <span class="text-muted-foreground font-medium text-xs">
                                        {{ ((reply.user?.name || reply.name || 'U')?.charAt(0) || 'U').toUpperCase() }}
                                    </span>
                                </div>
                            </div>
                            <div class="flex-1">
                                <div class="flex items-center space-x-2 mb-1">
                                    <p class="text-xs font-medium text-foreground">
                                        {{ reply.user?.name || reply.name || 'Anonymous' }}
                                    </p>
                                    <span
                                        class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                                        :class="{
                                            'bg-yellow-500/20 text-yellow-400': reply.status === 'pending',
                                            'bg-green-500/20 text-green-400': reply.status === 'approved',
                                            'bg-red-500/20 text-red-400': reply.status === 'rejected',
                                        }"
                                    >
                                        {{ reply.status }}
                                    </span>
                                </div>
                                <p class="text-xs text-foreground whitespace-pre-wrap">{{ reply.body }}</p>
                                <p class="text-xs text-muted-foreground mt-1">{{ formatDate(reply.created_at) }}</p>
                            </div>
                            <div class="flex items-center space-x-2">
                                <button
                                    v-if="reply.status === 'pending'"
                                    @click="approveComment(reply)"
                                    class="text-green-600 hover:text-green-800 text-xs"
                                >
                                    Approve
                                </button>
                                <button
                                    v-if="reply.status === 'pending'"
                                    @click="rejectComment(reply)"
                                    class="text-red-600 hover:text-red-800 text-xs"
                                >
                                    Reject
                                </button>
                                <button
                                    @click="deleteComment(reply)"
                                    class="text-red-600 hover:text-red-800 text-xs"
                                >
                                    Delete
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pagination -->
            <div v-if="pagination && pagination.last_page > 1" class="bg-card border border-border rounded-lg p-4 flex items-center justify-between">
                <div class="text-sm text-foreground">
                    Showing {{ pagination.from }} to {{ pagination.to }} of {{ pagination.total }} results
                </div>
                <div class="flex space-x-2">
                    <button
                        @click="changePage(pagination.current_page - 1)"
                        :disabled="pagination.current_page === 1"
                        class="px-3 py-2 border border-input bg-card text-foreground rounded-md text-sm disabled:opacity-50"
                    >
                        Previous
                    </button>
                    <button
                        @click="changePage(pagination.current_page + 1)"
                        :disabled="pagination.current_page === pagination.last_page"
                        class="px-3 py-2 border border-input bg-card text-foreground rounded-md text-sm disabled:opacity-50"
                    >
                        Next
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import api from '../../../services/api';
import { parseResponse, ensureArray } from '../../../utils/responseParser';

const { t } = useI18n();

const loading = ref(false);
const comments = ref([]);
const search = ref('');
const statusFilter = ref('pending');
const pagination = ref(null);
const statistics = ref(null);
const selectedIds = ref([]);

const fetchStatistics = async () => {
    try {
        const response = await api.get('/admin/cms/comments/statistics');
        statistics.value = response.data?.data || response.data;
    } catch (error) {
        console.error('Failed to fetch statistics:', error);
    }
};

const bulkAction = async (action) => {
    if (selectedIds.value.length === 0) return;
    
    const count = selectedIds.value.length;
    let confirmMsg = '';
    
    switch (action) {
        case 'delete':
            confirmMsg = t('features.comments.messages.bulkDeleteConfirm', { count });
            break;
        case 'approve':
            confirmMsg = t('features.comments.messages.bulkApproveConfirm', { count });
            break;
        case 'reject':
            confirmMsg = t('features.comments.messages.bulkRejectConfirm', { count });
            break;
        case 'spam':
            confirmMsg = t('features.comments.messages.bulkSpamConfirm', { count });
            break;
    }
    
    if (!confirm(confirmMsg)) return;
    
    try {
        await api.post('/admin/cms/comments/bulk', {
            ids: selectedIds.value,
            action: action
        });
        selectedIds.value = [];
        await fetchComments();
        await fetchStatistics();
    } catch (error) {
        console.error('Bulk action failed:', error);
        alert(t('features.comments.messages.bulkFailed'));
    }
};

const fetchComments = async () => {
    loading.value = true;
    try {
        const params = {
            page: pagination.value?.current_page || 1,
        };

        if (statusFilter.value) {
            params.status = statusFilter.value;
        }

        const response = await api.get('/admin/cms/comments', { params });
        const { data, pagination: paginationData } = parseResponse(response);
        comments.value = ensureArray(data);
        if (paginationData) {
            pagination.value = paginationData;
        }
    } catch (error) {
        console.error('Failed to fetch comments:', error);
    } finally {
        loading.value = false;
    }
};

const changePage = (page) => {
    if (pagination.value) {
        pagination.value.current_page = page;
        fetchComments();
    }
};

const approveComment = async (comment) => {
    try {
        await api.put(`/admin/cms/comments/${comment.id}/approve`);
        await fetchComments();
    } catch (error) {
        console.error('Failed to approve comment:', error);
        alert(t('features.comments.messages.approveFailed'));
    }
};

const rejectComment = async (comment) => {
    try {
        await api.put(`/admin/cms/comments/${comment.id}/reject`);
        await fetchComments();
        await fetchStatistics();
    } catch (error) {
        console.error('Failed to reject comment:', error);
        alert(t('features.comments.messages.rejectFailed'));
    }
};

const markAsSpam = async (comment) => {
    try {
        await api.put(`/admin/cms/comments/${comment.id}/spam`);
        await fetchComments();
        await fetchStatistics();
    } catch (error) {
        console.error('Failed to mark as spam:', error);
        alert(t('features.comments.messages.spamFailed'));
    }
};

const toggleSelection = (commentId) => {
    const index = selectedIds.value.indexOf(commentId);
    if (index > -1) {
        selectedIds.value.splice(index, 1);
    } else {
        selectedIds.value.push(commentId);
    }
};

const deleteComment = async (comment) => {
    if (!confirm(t('features.comments.messages.deleteConfirm'))) {
        return;
    }

    try {
        await api.delete(`/admin/cms/comments/${comment.id}`);
        await fetchComments();
    } catch (error) {
        console.error('Failed to delete comment:', error);
        alert(t('features.comments.messages.deleteFailed'));
    }
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

watch([statusFilter, search], () => {
    if (pagination.value) {
        pagination.value.current_page = 1;
    }
    fetchComments();
});

onMounted(() => {
    fetchComments();
    fetchStatistics();
});
</script>

