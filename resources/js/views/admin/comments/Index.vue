<template>
    <div>
        <!-- Header -->
        <div class="mb-6 flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Comments</h1>
                <p class="mt-1 text-sm text-gray-500">Manage and moderate comments</p>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white shadow rounded-lg p-4 mb-4">
            <div class="flex items-center space-x-4">
                <input
                    v-model="search"
                    type="text"
                    placeholder="Search comments..."
                    class="flex-1 px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                />
                <select
                    v-model="statusFilter"
                    class="px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                >
                    <option value="">All Status</option>
                    <option value="pending">Pending</option>
                    <option value="approved">Approved</option>
                    <option value="rejected">Rejected</option>
                    <option value="spam">Spam</option>
                </select>
            </div>
        </div>

        <!-- Comments List -->
        <div v-if="loading" class="bg-white shadow rounded-lg p-12 text-center">
            <p class="text-gray-500">Loading comments...</p>
        </div>

        <div v-else-if="comments.length === 0" class="bg-white shadow rounded-lg p-12 text-center">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
            </svg>
            <p class="mt-4 text-gray-500">No comments found</p>
        </div>

        <div v-else class="space-y-4">
            <div
                v-for="comment in comments"
                :key="comment.id"
                class="bg-white shadow rounded-lg p-6"
            >
                <!-- Comment Header -->
                <div class="flex items-start justify-between mb-4">
                    <div class="flex items-start space-x-3 flex-1">
                        <div class="flex-shrink-0">
                            <div class="h-10 w-10 rounded-full bg-indigo-100 flex items-center justify-center">
                                <span class="text-indigo-600 font-medium text-sm">
                                    {{ (comment.user?.name || comment.name || 'U').charAt(0).toUpperCase() }}
                                </span>
                            </div>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center space-x-2">
                                <p class="text-sm font-medium text-gray-900">
                                    {{ comment.user?.name || comment.name || 'Anonymous' }}
                                </p>
                                <span
                                    class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                                    :class="{
                                        'bg-yellow-100 text-yellow-800': comment.status === 'pending',
                                        'bg-green-100 text-green-800': comment.status === 'approved',
                                        'bg-red-100 text-red-800': comment.status === 'rejected',
                                        'bg-gray-100 text-gray-800': comment.status === 'spam',
                                    }"
                                >
                                    {{ comment.status }}
                                </span>
                            </div>
                            <p class="text-xs text-gray-500 mt-1">
                                {{ comment.user?.email || comment.email || 'No email' }}
                            </p>
                            <p class="text-xs text-gray-500">
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
                            @click="deleteComment(comment)"
                            class="text-red-600 hover:text-red-800 text-sm font-medium"
                        >
                            Delete
                        </button>
                    </div>
                </div>

                <!-- Comment Body -->
                <div class="mb-4">
                    <p class="text-sm text-gray-700 whitespace-pre-wrap">{{ comment.body }}</p>
                </div>

                <!-- Comment Meta -->
                <div class="flex items-center justify-between text-xs text-gray-500 pt-4 border-t">
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
                <div v-if="comment.replies && comment.replies.length > 0" class="mt-4 pl-8 border-l-2 border-gray-200">
                    <div
                        v-for="reply in comment.replies"
                        :key="reply.id"
                        class="mb-4 last:mb-0"
                    >
                        <div class="flex items-start space-x-3">
                            <div class="flex-shrink-0">
                                <div class="h-8 w-8 rounded-full bg-gray-100 flex items-center justify-center">
                                    <span class="text-gray-600 font-medium text-xs">
                                        {{ (reply.user?.name || reply.name || 'U').charAt(0).toUpperCase() }}
                                    </span>
                                </div>
                            </div>
                            <div class="flex-1">
                                <div class="flex items-center space-x-2 mb-1">
                                    <p class="text-xs font-medium text-gray-900">
                                        {{ reply.user?.name || reply.name || 'Anonymous' }}
                                    </p>
                                    <span
                                        class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                                        :class="{
                                            'bg-yellow-100 text-yellow-800': reply.status === 'pending',
                                            'bg-green-100 text-green-800': reply.status === 'approved',
                                            'bg-red-100 text-red-800': reply.status === 'rejected',
                                        }"
                                    >
                                        {{ reply.status }}
                                    </span>
                                </div>
                                <p class="text-xs text-gray-700 whitespace-pre-wrap">{{ reply.body }}</p>
                                <p class="text-xs text-gray-500 mt-1">{{ formatDate(reply.created_at) }}</p>
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
            <div v-if="pagination && pagination.last_page > 1" class="bg-white shadow rounded-lg p-4 flex items-center justify-between">
                <div class="text-sm text-gray-700">
                    Showing {{ pagination.from }} to {{ pagination.to }} of {{ pagination.total }} results
                </div>
                <div class="flex space-x-2">
                    <button
                        @click="changePage(pagination.current_page - 1)"
                        :disabled="pagination.current_page === 1"
                        class="px-3 py-2 border border-gray-300 rounded-md text-sm disabled:opacity-50"
                    >
                        Previous
                    </button>
                    <button
                        @click="changePage(pagination.current_page + 1)"
                        :disabled="pagination.current_page === pagination.last_page"
                        class="px-3 py-2 border border-gray-300 rounded-md text-sm disabled:opacity-50"
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
import api from '../../../services/api';

const loading = ref(false);
const comments = ref([]);
const search = ref('');
const statusFilter = ref('pending');
const pagination = ref(null);

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
        comments.value = response.data.data || [];
        pagination.value = {
            current_page: response.data.current_page,
            last_page: response.data.last_page,
            from: response.data.from,
            to: response.data.to,
            total: response.data.total,
        };
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
        alert('Failed to approve comment');
    }
};

const rejectComment = async (comment) => {
    try {
        await api.put(`/admin/cms/comments/${comment.id}/reject`);
        await fetchComments();
    } catch (error) {
        console.error('Failed to reject comment:', error);
        alert('Failed to reject comment');
    }
};

const deleteComment = async (comment) => {
    if (!confirm(`Are you sure you want to delete this comment?`)) {
        return;
    }

    try {
        await api.delete(`/admin/cms/comments/${comment.id}`);
        await fetchComments();
    } catch (error) {
        console.error('Failed to delete comment:', error);
        alert('Failed to delete comment');
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
});
</script>

