<template>
    <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" @click.self="close">
        <div class="relative top-10 mx-auto p-5 border w-full max-w-6xl shadow-lg rounded-md bg-white max-h-[90vh] overflow-y-auto">
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">Form Submissions</h2>
                    <p class="text-sm text-gray-500 mt-1">{{ form.name }}</p>
                </div>
                <button
                    @click="close"
                    class="text-gray-400 hover:text-gray-600"
                >
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Statistics -->
            <div v-if="statistics" class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-blue-50 rounded-lg p-4">
                    <div class="text-2xl font-bold text-blue-900">{{ statistics.total || 0 }}</div>
                    <div class="text-sm text-blue-700">Total</div>
                </div>
                <div class="bg-green-50 rounded-lg p-4">
                    <div class="text-2xl font-bold text-green-900">{{ statistics.new || 0 }}</div>
                    <div class="text-sm text-green-700">New</div>
                </div>
                <div class="bg-yellow-50 rounded-lg p-4">
                    <div class="text-2xl font-bold text-yellow-900">{{ statistics.read || 0 }}</div>
                    <div class="text-sm text-yellow-700">Read</div>
                </div>
                <div class="bg-gray-50 rounded-lg p-4">
                    <div class="text-2xl font-bold text-gray-900">{{ statistics.archived || 0 }}</div>
                    <div class="text-sm text-gray-700">Archived</div>
                </div>
            </div>

            <!-- Filters -->
            <div class="bg-gray-50 rounded-lg p-4 mb-4">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <input
                        v-model="search"
                        type="text"
                        placeholder="Search submissions..."
                        class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                    >
                    <select
                        v-model="statusFilter"
                        class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                    >
                        <option value="">All Status</option>
                        <option value="new">New</option>
                        <option value="read">Read</option>
                        <option value="archived">Archived</option>
                    </select>
                    <input
                        v-model="dateFrom"
                        type="date"
                        class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                    >
                    <input
                        v-model="dateTo"
                        type="date"
                        class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                    >
                </div>
                <div class="mt-4 flex justify-end">
                    <button
                        @click="exportSubmissions"
                        class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50"
                    >
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Export
                    </button>
                </div>
            </div>

            <!-- Submissions List -->
            <div v-if="loading" class="text-center py-12">
                <p class="text-gray-500">Loading submissions...</p>
            </div>

            <div v-else-if="submissions.length === 0" class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <p class="mt-4 text-gray-500">No submissions found</p>
            </div>

            <div v-else class="space-y-3">
                <div
                    v-for="submission in submissions"
                    :key="submission.id"
                    :class="[
                        'bg-white border rounded-lg p-4 hover:shadow-md transition-shadow cursor-pointer',
                        submission.status === 'new' ? 'border-blue-300 bg-blue-50' : 'border-gray-200'
                    ]"
                    @click="viewSubmission(submission)"
                >
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <div class="flex items-center space-x-2 mb-2">
                                <span
                                    :class="[
                                        'px-2 py-1 text-xs font-medium rounded-full',
                                        submission.status === 'new' ? 'bg-blue-100 text-blue-800' :
                                        submission.status === 'read' ? 'bg-yellow-100 text-yellow-800' :
                                        'bg-gray-100 text-gray-800'
                                    ]"
                                >
                                    {{ submission.status }}
                                </span>
                                <span class="text-sm text-gray-500">
                                    {{ formatDate(submission.created_at) }}
                                </span>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-2 text-sm">
                                <div v-for="(value, key) in submission.data" :key="key" class="truncate">
                                    <span class="font-medium text-gray-700">{{ key }}:</span>
                                    <span class="text-gray-600 ml-1">{{ formatValue(value) }}</span>
                                </div>
                            </div>
                            <div v-if="submission.user" class="mt-2 text-xs text-gray-500">
                                Submitted by: {{ submission.user.name || submission.user.email }}
                            </div>
                        </div>
                        <div class="flex items-center space-x-2 ml-4">
                            <button
                                v-if="submission.status === 'new'"
                                @click.stop="markAsRead(submission)"
                                class="p-2 text-yellow-600 hover:text-yellow-800 hover:bg-yellow-50 rounded transition-colors"
                                title="Mark as read"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                            </button>
                            <button
                                @click.stop="deleteSubmission(submission)"
                                class="p-2 text-red-600 hover:text-red-800 hover:bg-red-50 rounded transition-colors"
                                title="Delete"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pagination -->
            <div v-if="pagination && pagination.last_page > 1" class="mt-6 flex justify-center">
                <div class="flex space-x-2">
                    <button
                        @click="loadPage(pagination.current_page - 1)"
                        :disabled="!pagination.prev_page_url"
                        class="px-3 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        Previous
                    </button>
                    <span class="px-3 py-2 text-sm text-gray-700">
                        Page {{ pagination.current_page }} of {{ pagination.last_page }}
                    </span>
                    <button
                        @click="loadPage(pagination.current_page + 1)"
                        :disabled="!pagination.next_page_url"
                        class="px-3 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        Next
                    </button>
                </div>
            </div>

            <!-- Submission Detail Modal -->
            <div
                v-if="selectedSubmission"
                class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50"
                @click.self="selectedSubmission = null"
            >
                <div class="relative top-20 mx-auto p-5 border w-full max-w-3xl shadow-lg rounded-md bg-white max-h-[90vh] overflow-y-auto">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-xl font-bold text-gray-900">Submission Details</h3>
                        <button
                            @click="selectedSubmission = null"
                            class="text-gray-400 hover:text-gray-600"
                        >
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <div class="space-y-4">
                        <div class="grid grid-cols-2 gap-4 text-sm">
                            <div>
                                <span class="font-medium text-gray-700">Status:</span>
                                <span class="ml-2">{{ selectedSubmission.status }}</span>
                            </div>
                            <div>
                                <span class="font-medium text-gray-700">Submitted:</span>
                                <span class="ml-2">{{ formatDate(selectedSubmission.created_at) }}</span>
                            </div>
                            <div>
                                <span class="font-medium text-gray-700">IP Address:</span>
                                <span class="ml-2">{{ selectedSubmission.ip_address }}</span>
                            </div>
                            <div v-if="selectedSubmission.user">
                                <span class="font-medium text-gray-700">User:</span>
                                <span class="ml-2">{{ selectedSubmission.user.name || selectedSubmission.user.email }}</span>
                            </div>
                        </div>

                        <div class="border-t border-gray-200 pt-4">
                            <h4 class="font-semibold text-gray-900 mb-3">Form Data</h4>
                            <dl class="space-y-3">
                                <div v-for="(value, key) in selectedSubmission.data" :key="key" class="border-b border-gray-100 pb-2">
                                    <dt class="text-sm font-medium text-gray-700">{{ key }}</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ formatValue(value) }}</dd>
                                </div>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, watch, onMounted } from 'vue';
import api from '../../../services/api';

const props = defineProps({
    form: {
        type: Object,
        required: true
    }
});

const emit = defineEmits(['close']);

const submissions = ref([]);
const loading = ref(true);
const statistics = ref(null);
const pagination = ref(null);
const search = ref('');
const statusFilter = ref('');
const dateFrom = ref('');
const dateTo = ref('');
const selectedSubmission = ref(null);

const fetchSubmissions = async (page = 1) => {
    try {
        loading.value = true;
        const params = {
            page,
            ...(statusFilter.value && { status: statusFilter.value }),
            ...(dateFrom.value && { date_from: dateFrom.value }),
            ...(dateTo.value && { date_to: dateTo.value })
        };
        const response = await api.get(`/admin/cms/forms/${props.form.id}/submissions`, { params });
        submissions.value = response.data.data || response.data;
        pagination.value = response.data;
    } catch (error) {
        console.error('Error fetching submissions:', error);
    } finally {
        loading.value = false;
    }
};

const fetchStatistics = async () => {
    try {
        const response = await api.get(`/admin/cms/forms/${props.form.id}/submissions/statistics`);
        statistics.value = response.data;
    } catch (error) {
        console.error('Error fetching statistics:', error);
    }
};

const loadPage = (page) => {
    fetchSubmissions(page);
};

const viewSubmission = async (submission) => {
    try {
        const response = await api.get(`/admin/cms/form-submissions/${submission.id}`);
        selectedSubmission.value = response.data;
        if (submission.status === 'new') {
            markAsRead(submission, false);
        }
    } catch (error) {
        console.error('Error fetching submission details:', error);
        selectedSubmission.value = submission;
    }
};

const markAsRead = async (submission, refresh = true) => {
    try {
        await api.put(`/admin/cms/form-submissions/${submission.id}/read`);
        if (refresh) {
            fetchSubmissions(pagination.value?.current_page || 1);
            fetchStatistics();
        } else {
            submission.status = 'read';
        }
    } catch (error) {
        console.error('Error marking submission as read:', error);
    }
};

const deleteSubmission = async (submission) => {
    if (!confirm('Are you sure you want to delete this submission?')) {
        return;
    }

    try {
        await api.delete(`/admin/cms/form-submissions/${submission.id}`);
        submissions.value = submissions.value.filter(s => s.id !== submission.id);
        fetchStatistics();
    } catch (error) {
        console.error('Error deleting submission:', error);
        alert('Failed to delete submission');
    }
};

const exportSubmissions = async () => {
    try {
        const response = await api.get(`/admin/cms/forms/${props.form.id}/submissions/export`);
        const data = response.data.data;
        
        // Convert to CSV
        const headers = Object.keys(data[0] || {});
        const csv = [
            headers.join(','),
            ...data.map(row => headers.map(header => {
                const value = row[header] || '';
                return `"${String(value).replace(/"/g, '""')}"`;
            }).join(','))
        ].join('\n');

        // Download
        const blob = new Blob([csv], { type: 'text/csv' });
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `${props.form.slug}-submissions-${new Date().toISOString().split('T')[0]}.csv`;
        a.click();
        window.URL.revokeObjectURL(url);
    } catch (error) {
        console.error('Error exporting submissions:', error);
        alert('Failed to export submissions');
    }
};

const formatDate = (date) => {
    return new Date(date).toLocaleString();
};

const formatValue = (value) => {
    if (Array.isArray(value)) {
        return value.join(', ');
    }
    if (typeof value === 'object') {
        return JSON.stringify(value);
    }
    return String(value);
};

const close = () => {
    emit('close');
};

watch([statusFilter, dateFrom, dateTo], () => {
    fetchSubmissions(1);
});

onMounted(() => {
    fetchSubmissions();
    fetchStatistics();
});
</script>

