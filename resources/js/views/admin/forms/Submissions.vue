<template>
    <div class="fixed inset-0 bg-background/80 backdrop-blur-sm overflow-y-auto h-full w-full z-50" @click.self="close">
        <div class="relative top-10 mx-auto p-5 border border-border w-full max-w-6xl rounded-md bg-card max-h-[90vh] overflow-y-auto">
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h2 class="text-2xl font-bold text-foreground">{{ t('features.forms.submissions.title') }}</h2>
                    <p class="text-sm text-muted-foreground mt-1">{{ form.name }}</p>
                </div>
                <Button variant="ghost" size="icon" @click="close">
                    <X class="w-5 h-5" />
                </Button>
            </div>

            <!-- Statistics -->
            <div v-if="statistics" class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                <Card class="p-4 bg-blue-500/10 border-blue-500/20">
                    <p class="text-2xl font-bold text-blue-500">{{ statistics.total || 0 }}</p>
                    <p class="text-sm text-blue-500/70">{{ t('features.forms.stats.total') }}</p>
                </Card>
                <Card class="p-4 bg-green-500/10 border-green-500/20">
                    <p class="text-2xl font-bold text-green-500">{{ statistics.new || 0 }}</p>
                    <p class="text-sm text-green-500/70">{{ t('features.forms.stats.new') }}</p>
                </Card>
                <Card class="p-4 bg-yellow-500/10 border-yellow-500/20">
                    <p class="text-2xl font-bold text-yellow-500">{{ statistics.read || 0 }}</p>
                    <p class="text-sm text-yellow-500/70">{{ t('features.forms.stats.read') }}</p>
                </Card>
                <Card class="p-4">
                    <p class="text-2xl font-bold text-muted-foreground">{{ statistics.archived || 0 }}</p>
                    <p class="text-sm text-muted-foreground">{{ t('features.forms.stats.archived') }}</p>
                </Card>
            </div>

            <!-- Filters -->
            <Card class="p-4 mb-4">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <Input
                        v-model="search"
                        type="text"
                        :placeholder="t('features.forms.submissions.search')"
                    />
                    <Select v-model="statusFilter">
                        <SelectTrigger>
                            <SelectValue :placeholder="t('features.forms.filters.status')" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">{{ t('features.forms.filters.status') }}</SelectItem>
                            <SelectItem value="new">{{ t('features.forms.stats.new') }}</SelectItem>
                            <SelectItem value="read">{{ t('features.forms.stats.read') }}</SelectItem>
                            <SelectItem value="archived">{{ t('features.forms.stats.archived') }}</SelectItem>
                        </SelectContent>
                    </Select>
                    <Input
                        v-model="dateFrom"
                        type="date"
                    />
                    <Input
                        v-model="dateTo"
                        type="date"
                    />
                </div>
                <div class="mt-4 flex justify-end">
                    <Button variant="outline" @click="exportSubmissions">
                        <Download class="w-4 h-4 mr-2" />
                        {{ t('features.forms.actions.export') }}
                    </Button>
                </div>
            </Card>

            <!-- Submissions List -->
            <div v-if="loading" class="text-center py-12">
                <Loader2 class="w-8 h-8 mx-auto animate-spin text-muted-foreground" />
                <p class="text-muted-foreground mt-2">{{ t('features.forms.messages.loading') }}</p>
            </div>

            <div v-else-if="submissions.length === 0" class="text-center py-12">
                <FileText class="mx-auto h-12 w-12 text-muted-foreground opacity-50" />
                <p class="mt-4 text-muted-foreground">{{ t('features.forms.submissions.empty') }}</p>
            </div>

            <div v-else class="space-y-3">
                <Card
                    v-for="submission in submissions"
                    :key="submission.id"
                    :class="[
                        'p-4 cursor-pointer hover:shadow-md transition-shadow',
                        submission.status === 'new' ? 'border-blue-500/50 bg-blue-500/5' : ''
                    ]"
                    @click="viewSubmission(submission)"
                >
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <div class="flex items-center space-x-2 mb-2">
                                <Badge
                                    :variant="submission.status === 'new' ? 'default' : submission.status === 'read' ? 'secondary' : 'outline'"
                                    :class="{
                                        'bg-blue-500/20 text-blue-500 border-blue-500/30': submission.status === 'new',
                                        'bg-yellow-500/20 text-yellow-500 border-yellow-500/30': submission.status === 'read',
                                    }"
                                >
                                    {{ submission.status === 'new' ? t('features.forms.stats.new') : submission.status === 'read' ? t('features.forms.stats.read') : t('features.forms.stats.archived') }}
                                </Badge>
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
                        <div class="flex items-center space-x-1 ml-4">
                            <Button
                                v-if="submission.status === 'new'"
                                @click.stop="markAsRead(submission)"
                                variant="ghost"
                                size="icon"
                                class="h-8 w-8 text-yellow-500 hover:text-yellow-600 hover:bg-yellow-500/10"
                            >
                                <Check class="w-4 h-4" />
                            </Button>
                            <Button
                                @click.stop="deleteSubmission(submission)"
                                variant="ghost"
                                size="icon"
                                class="h-8 w-8 text-destructive hover:text-destructive hover:bg-destructive/10"
                            >
                                <Trash2 class="w-4 h-4" />
                            </Button>
                        </div>
                    </div>
                </Card>
            </div>

            <!-- Pagination -->
            <Pagination
                v-if="pagination && pagination.total > 0"
                :current-page="pagination.current_page"
                :total-items="pagination.total"
                :per-page="Number(pagination.per_page || 15)"
                :show-page-numbers="true"
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
                <Card class="relative top-20 mx-auto p-6 w-full max-w-3xl max-h-[90vh] overflow-y-auto">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-xl font-bold text-foreground">{{ t('features.forms.submissions.detailTitle') }}</h3>
                        <Button variant="ghost" size="icon" @click="selectedSubmission = null">
                            <X class="w-5 h-5" />
                        </Button>
                    </div>

                    <div class="space-y-4">
                        <div class="grid grid-cols-2 gap-4 text-sm">
                            <div>
                                <span class="font-medium text-foreground">{{ t('features.forms.submissions.status') }}:</span>
                                <Badge class="ml-2" :variant="selectedSubmission.status === 'new' ? 'default' : 'secondary'">
                                    {{ selectedSubmission.status === 'new' ? t('features.forms.stats.new') : selectedSubmission.status === 'read' ? t('features.forms.stats.read') : t('features.forms.stats.archived') }}
                                </Badge>
                            </div>
                            <div>
                                <span class="font-medium text-foreground">{{ t('features.forms.submissions.submitted') }}:</span>
                                <span class="ml-2 text-muted-foreground">{{ formatDate(selectedSubmission.created_at) }}</span>
                            </div>
                            <div>
                                <span class="font-medium text-foreground">{{ t('features.forms.submissions.ipAddress') }}:</span>
                                <span class="ml-2 text-muted-foreground">{{ selectedSubmission.ip_address }}</span>
                            </div>
                            <div v-if="selectedSubmission.user">
                                <span class="font-medium text-foreground">{{ t('features.forms.submissions.user') }}:</span>
                                <span class="ml-2 text-muted-foreground">{{ selectedSubmission.user.name || selectedSubmission.user.email }}</span>
                            </div>
                        </div>

                        <div class="border-t border-border pt-4">
                            <h4 class="font-semibold text-foreground mb-3">{{ t('features.forms.submissions.formData') }}</h4>
                            <dl class="space-y-3">
                                <div v-for="(value, key) in selectedSubmission.data" :key="key" class="border-b border-border pb-2">
                                    <dt class="text-sm font-medium text-foreground">{{ key }}</dt>
                                    <dd class="mt-1 text-sm text-muted-foreground">{{ formatValue(value) }}</dd>
                                </div>
                            </dl>
                        </div>
                    </div>
                </Card>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, watch, onMounted } from 'vue';
import { useI18n } from 'vue-i18n';
import api from '../../../services/api';
import Pagination from '../../../components/ui/pagination.vue';
import Button from '../../../components/ui/button.vue';
import Input from '../../../components/ui/input.vue';
import Card from '../../../components/ui/card.vue';
import Badge from '../../../components/ui/badge.vue';
import Select from '../../../components/ui/select.vue';
import SelectContent from '../../../components/ui/select-content.vue';
import SelectItem from '../../../components/ui/select-item.vue';
import SelectTrigger from '../../../components/ui/select-trigger.vue';
import SelectValue from '../../../components/ui/select-value.vue';
import { X, Download, Loader2, FileText, Check, Trash2 } from 'lucide-vue-next';

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
const statusFilter = ref('all');
const dateFrom = ref('');
const dateTo = ref('');
const selectedSubmission = ref(null);

const fetchSubmissions = async (page = 1) => {
    try {
        loading.value = true;
        const params = {
            page,
            per_page: pagination.value?.per_page || 15,
            ...(statusFilter.value && statusFilter.value !== 'all' && { status: statusFilter.value }),
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
        statistics.value = response.data?.data || response.data;
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
        alert(t('features.forms.messages.exportFailed'));
    }
};

const exportSubmissions = async () => {
    try {
        const params = new URLSearchParams({
            format: 'csv',
            ...(dateFrom.value && { date_from: dateFrom.value }),
            ...(dateTo.value && { date_to: dateTo.value })
        });

        const baseUrl = import.meta.env.VITE_API_URL || '';
        const exportUrl = `${baseUrl}/api/v1/admin/cms/forms/${props.form.id}/submissions/export?${params.toString()}`;
        
        const link = document.createElement('a');
        link.href = exportUrl;
        link.setAttribute('download', '');
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    } catch (error) {
        console.error('Error exporting submissions:', error);
        alert(t('features.forms.messages.exportFailed'));
    }
};

const formatDate = (date) => {
    if (!date) return '-';
    const parsed = new Date(date);
    if (isNaN(parsed.getTime())) return '-';
    return parsed.toLocaleString();
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
