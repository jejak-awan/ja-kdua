<template>
    <div class="fixed inset-0 bg-background/80 backdrop-blur-sm overflow-y-auto h-full w-full z-50" @click.self="close">
        <div class="relative top-10 mx-auto p-5 border w-full max-w-6xl rounded-md bg-card max-h-[90vh] overflow-y-auto">
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h2 class="text-2xl font-bold text-foreground">{{ t('features.forms.submissions.title') }}</h2>
                    <p class="text-sm text-muted-foreground mt-1">{{ form.name }}</p>
                </div>
                <button
                    @click="close"
                    class="text-muted-foreground hover:text-muted-foreground"
                >
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Statistics -->
            <div v-if="statistics" class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-blue-500/20 rounded-lg p-4">
                    <div class="text-2xl font-bold text-blue-900">{{ statistics.total || 0 }}</div>
                    <div class="text-sm text-blue-700">{{ t('features.forms.stats.total') }}</div>
                </div>
                <div class="bg-green-500/20 rounded-lg p-4">
                    <div class="text-2xl font-bold text-green-900">{{ statistics.new || 0 }}</div>
                    <div class="text-sm text-green-700">{{ t('features.forms.stats.new') }}</div>
                </div>
                <div class="bg-yellow-500/20 rounded-lg p-4">
                    <div class="text-2xl font-bold text-yellow-900">{{ statistics.read || 0 }}</div>
                    <div class="text-sm text-yellow-700">{{ t('features.forms.stats.read') }}</div>
                </div>
                <div class="bg-muted rounded-lg p-4">
                    <div class="text-2xl font-bold text-foreground">{{ statistics.archived || 0 }}</div>
                    <div class="text-sm text-foreground">{{ t('features.forms.stats.archived') }}</div>
                </div>
            </div>

            <!-- Filters -->
            <div class="bg-muted rounded-lg p-4 mb-4">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <input
                        v-model="search"
                        type="text"
                        :placeholder="t('features.forms.submissions.search')"
                        class="px-3 py-2 border border-input bg-card text-foreground rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                    >
                    <select
                        v-model="statusFilter"
                        class="px-3 py-2 border border-input bg-card text-foreground rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                    >
                        <option value="">{{ t('features.forms.filters.status') }}</option>
                        <option value="new">{{ t('features.forms.stats.new') }}</option>
                        <option value="read">{{ t('features.forms.stats.read') }}</option>
                        <option value="archived">{{ t('features.forms.stats.archived') }}</option>
                    </select>
                    <input
                        v-model="dateFrom"
                        type="date"
                        class="px-3 py-2 border border-input bg-card text-foreground rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                    >
                    <input
                        v-model="dateTo"
                        type="date"
                        class="px-3 py-2 border border-input bg-card text-foreground rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                    >
                </div>
                <div class="mt-4 flex justify-end">
                    <button
                        @click="exportSubmissions"
                        class="inline-flex items-center px-4 py-2 border border-input bg-card text-foreground rounded-md text-sm font-medium text-foreground bg-card hover:bg-muted"
                    >
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        {{ t('features.forms.actions.export') }}
                    </button>
                </div>
            </div>

            <!-- Submissions List -->
            <div v-if="loading" class="text-center py-12">
                <p class="text-muted-foreground">{{ t('features.forms.messages.loading') }}</p>
            </div>

            <div v-else-if="submissions.length === 0" class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-muted-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <p class="mt-4 text-muted-foreground">{{ t('features.forms.submissions.empty') }}</p>
            </div>

            <div v-else class="space-y-3">
                <div
                    v-for="submission in submissions"
                    :key="submission.id"
                    :class="[
                        'bg-card border rounded-lg p-4 hover:shadow-md transition-shadow cursor-pointer',
                        submission.status === 'new' ? 'border-blue-300 bg-blue-50' : 'border-border'
                    ]"
                    @click="viewSubmission(submission)"
                >
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <div class="flex items-center space-x-2 mb-2">
                                <span
                                    :class="[
                                        'px-2 py-1 text-xs font-medium rounded-full',
                                        submission.status === 'new' ? 'bg-blue-500/20 text-blue-400' :
                                        submission.status === 'read' ? 'bg-yellow-500/20 text-yellow-400' :
                                        'bg-secondary text-secondary-foreground'
                                    ]"
                                >
                                    {{ submission.status === 'new' ? t('features.forms.stats.new') : submission.status === 'read' ? t('features.forms.stats.read') : t('features.forms.stats.archived') }}
                                </span>
                                <span class="text-sm text-muted-foreground">
                                    {{ formatDate(submission.created_at) }}
                                </span>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-2 text-sm">
                                <div v-for="(value, key) in submission.data" :key="key" class="truncate">
                                    <span class="font-medium text-foreground">{{ key }}:</span>
                                    <span class="text-muted-foreground ml-1">{{ formatValue(value) }}</span>
                                </div>
                            </div>
                            <div v-if="submission.user" class="mt-2 text-xs text-muted-foreground">
                                {{ t('features.forms.submissions.submittedBy') }} {{ submission.user.name || submission.user.email }}
                            </div>
                        </div>
                        <div class="flex items-center space-x-2 ml-4">
                            <button
                                v-if="submission.status === 'new'"
                                @click.stop="markAsRead(submission)"
                                class="p-2 text-yellow-600 hover:text-yellow-800 hover:bg-yellow-500/20 rounded transition-colors"
                                title="Mark as read"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                            </button>
                            <button
                                @click.stop="deleteSubmission(submission)"
                                class="p-2 text-red-600 hover:text-red-800 hover:bg-red-500/20 rounded transition-colors"
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
            <Pagination
                v-if="pagination && pagination.total > 0"
                :current-page="pagination.current_page"
                :total-items="pagination.total"
                :per-page="Number(pagination.per_page || 15)"
                @page-change="loadPage"
                @update:per-page="(val) => { if(pagination) { pagination.per_page = val; loadPage(1); } }"
                class="border-none shadow-none mt-6"
            />

            <!-- Submission Detail Modal -->
            <div
                v-if="selectedSubmission"
                class="fixed inset-0 bg-background/80 backdrop-blur-sm overflow-y-auto h-full w-full z-50"
                @click.self="selectedSubmission = null"
            >
                <div class="relative top-20 mx-auto p-5 border w-full max-w-3xl rounded-md bg-card max-h-[90vh] overflow-y-auto">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-xl font-bold text-foreground">{{ t('features.forms.submissions.detailTitle') }}</h3>
                        <button
                            @click="selectedSubmission = null"
                            class="text-muted-foreground hover:text-muted-foreground"
                        >
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <div class="space-y-4">
                        <div class="grid grid-cols-2 gap-4 text-sm">
                            <div>
                                <span class="font-medium text-foreground">{{ t('features.forms.submissions.status') }}:</span>
                                <span class="ml-2">{{ selectedSubmission.status }}</span>
                            </div>
                            <div>
                                <span class="font-medium text-foreground">{{ t('features.forms.submissions.submitted') }}:</span>
                                <span class="ml-2">{{ formatDate(selectedSubmission.created_at) }}</span>
                            </div>
                            <div>
                                <span class="font-medium text-foreground">{{ t('features.forms.submissions.ipAddress') }}:</span>
                                <span class="ml-2">{{ selectedSubmission.ip_address }}</span>
                            </div>
                            <div v-if="selectedSubmission.user">
                                <span class="font-medium text-foreground">{{ t('features.forms.submissions.user') }}:</span>
                                <span class="ml-2">{{ selectedSubmission.user.name || selectedSubmission.user.email }}</span>
                            </div>
                        </div>

                        <div class="border-t border-border pt-4">
                            <h4 class="font-semibold text-foreground mb-3">{{ t('features.forms.submissions.formData') }}</h4>
                            <dl class="space-y-3">
                                <div v-for="(value, key) in selectedSubmission.data" :key="key" class="border-b border-border pb-2">
                                    <dt class="text-sm font-medium text-foreground">{{ key }}</dt>
                                    <dd class="mt-1 text-sm text-foreground">{{ formatValue(value) }}</dd>
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
import { useI18n } from 'vue-i18n';
import api from '../../../services/api';
import Pagination from '../../../components/ui/pagination.vue';

const { t } = useI18n();

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
            per_page: pagination.value?.per_page || 15,
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
    if (!confirm(t('features.forms.messages.submissionDeleteConfirm'))) {
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
        // Build query params for filtering
        const params = new URLSearchParams({
            format: 'csv',
            ...(dateFrom.value && { date_from: dateFrom.value }),
            ...(dateTo.value && { date_to: dateTo.value })
        });

        // Use window.open for direct file download
        const baseUrl = import.meta.env.VITE_API_URL || '';
        const exportUrl = `${baseUrl}/api/v1/admin/cms/forms/${props.form.id}/submissions/export?${params.toString()}`;
        
        // Create a link and trigger download
        const link = document.createElement('a');
        link.href = exportUrl;
        link.setAttribute('download', '');
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
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

