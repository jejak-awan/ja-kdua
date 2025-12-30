<template>
    <div>
        <!-- Header with Back Button -->
        <div class="mb-6 flex items-center justify-between">
            <div class="flex items-center gap-4">
                <Button variant="ghost" size="icon" @click="$router.back()">
                    <ArrowLeft class="w-5 h-5" />
                </Button>
                <div>
                    <h1 class="text-2xl font-bold text-foreground">{{ $t('features.forms.submissions.title') }}</h1>
                    <p class="text-sm text-muted-foreground">{{ form?.name || '-' }}</p>
                </div>
            </div>
            <Button variant="outline" @click="exportSubmissions">
                <Download class="w-4 h-4 mr-2" />
                {{ $t('features.forms.actions.export') }}
            </Button>
        </div>

        <!-- Statistics Cards -->
        <div v-if="statistics" class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
            <Card 
                class="p-4 cursor-pointer hover:bg-primary/5 transition-colors" 
                @click="statusFilter = 'all'"
                :class="{ 'ring-2 ring-primary/50': statusFilter === 'all' }"
            >
                <p class="text-2xl font-bold text-primary">{{ statistics.total || 0 }}</p>
                <p class="text-sm text-muted-foreground">{{ $t('features.forms.stats.total') }}</p>
            </Card>
            <Card 
                class="p-4 cursor-pointer hover:bg-green-500/5 transition-colors border-green-500/20" 
                @click="statusFilter = 'new'"
                :class="{ 'ring-2 ring-green-500/50': statusFilter === 'new' }"
            >
                <p class="text-2xl font-bold text-green-500">{{ statistics.new || 0 }}</p>
                <p class="text-sm text-green-500/70">{{ $t('features.forms.stats.new') }}</p>
            </Card>
            <Card 
                class="p-4 cursor-pointer hover:bg-yellow-500/5 transition-colors border-yellow-500/20" 
                @click="statusFilter = 'read'"
                :class="{ 'ring-2 ring-yellow-500/50': statusFilter === 'read' }"
            >
                <p class="text-2xl font-bold text-yellow-500">{{ statistics.read || 0 }}</p>
                <p class="text-sm text-yellow-500/70">{{ $t('features.forms.stats.read') }}</p>
            </Card>
            <Card 
                class="p-4 cursor-pointer hover:bg-muted/50 transition-colors" 
                @click="statusFilter = 'archived'"
                :class="{ 'ring-2 ring-muted-foreground/50': statusFilter === 'archived' }"
            >
                <p class="text-2xl font-bold text-muted-foreground">{{ statistics.archived || 0 }}</p>
                <p class="text-sm text-muted-foreground">{{ $t('features.forms.stats.archived') }}</p>
            </Card>
        </div>

        <!-- Filters -->
        <Card class="p-4 mb-4">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="relative">
                    <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-muted-foreground" />
                    <Input
                        v-model="search"
                        type="text"
                        :placeholder="$t('features.forms.submissions.search')"
                        class="pl-9"
                    />
                </div>
                <Select v-model="statusFilter">
                    <SelectTrigger>
                        <SelectValue :placeholder="$t('features.forms.filters.status')" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="all">{{ $t('features.forms.filters.status') }}</SelectItem>
                        <SelectItem value="new">{{ $t('features.forms.stats.new') }}</SelectItem>
                        <SelectItem value="read">{{ $t('features.forms.stats.read') }}</SelectItem>
                        <SelectItem value="archived">{{ $t('features.forms.stats.archived') }}</SelectItem>
                    </SelectContent>
                </Select>
                <Input v-model="dateFrom" type="date" />
                <Input v-model="dateTo" type="date" />
            </div>
        </Card>

        <!-- Loading -->
        <div v-if="loading" class="bg-card border border-border rounded-lg p-12 text-center">
            <Loader2 class="w-8 h-8 mx-auto animate-spin text-muted-foreground" />
            <p class="text-muted-foreground mt-2">{{ $t('features.forms.messages.loading') }}</p>
        </div>

        <!-- Empty State -->
        <Card v-else-if="filteredSubmissions.length === 0" class="p-12 text-center">
            <FileText class="mx-auto h-12 w-12 text-muted-foreground opacity-50" />
            <p class="mt-4 text-muted-foreground">{{ $t('features.forms.submissions.empty') }}</p>
        </Card>

        <!-- Submissions Table -->
        <Card v-else class="overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-muted/50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-muted-foreground">Status</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-muted-foreground">{{ $t('features.forms.submissions.submitted') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-muted-foreground">{{ $t('features.forms.submissions.formData') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-muted-foreground">{{ $t('features.forms.submissions.ipAddress') }}</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-muted-foreground">{{ $t('common.actions.title') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border">
                        <tr 
                            v-for="submission in filteredSubmissions" 
                            :key="submission.id" 
                            class="hover:bg-muted/30 transition-colors cursor-pointer"
                            @click="viewSubmission(submission)"
                        >
                            <td class="px-4 py-4">
                                <Badge
                                    :class="{
                                        'bg-green-500/10 text-green-500 border-green-500/20': submission.status === 'new',
                                        'bg-yellow-500/10 text-yellow-500 border-yellow-500/20': submission.status === 'read',
                                        'bg-muted text-muted-foreground': submission.status === 'archived'
                                    }"
                                >
                                    {{ getStatusLabel(submission.status) }}
                                </Badge>
                            </td>
                            <td class="px-4 py-4 text-sm text-muted-foreground">
                                {{ formatDate(submission.created_at) }}
                            </td>
                            <td class="px-4 py-4">
                                <div class="flex flex-wrap gap-2 max-w-md">
                                    <span v-for="(value, key) in getFirstFields(submission.data)" :key="key" class="text-xs bg-muted px-2 py-1 rounded">
                                        <span class="font-medium">{{ key }}:</span> {{ formatValue(value) }}
                                    </span>
                                </div>
                            </td>
                            <td class="px-4 py-4 text-sm text-muted-foreground">
                                {{ submission.ip_address || '-' }}
                            </td>
                            <td class="px-4 py-4">
                                <div class="flex items-center justify-center space-x-1">
                                    <Button
                                        v-if="submission.status === 'new'"
                                        @click.stop="markAsRead(submission)"
                                        variant="ghost"
                                        size="icon"
                                        class="h-8 w-8 text-amber-500 hover:text-amber-600 hover:bg-amber-500/10"
                                        :title="$t('features.forms.stats.read')"
                                    >
                                        <Check class="w-4 h-4" />
                                    </Button>
                                    <Button
                                        v-if="submission.status !== 'archived'"
                                        @click.stop="archiveSubmission(submission)"
                                        variant="ghost"
                                        size="icon"
                                        class="h-8 w-8 text-purple-500 hover:text-purple-600 hover:bg-purple-500/10"
                                        :title="$t('common.actions.archive')"
                                    >
                                        <Archive class="w-4 h-4" />
                                    </Button>
                                    <Button
                                        @click.stop="deleteSubmission(submission)"
                                        variant="ghost"
                                        size="icon"
                                        class="h-8 w-8 text-destructive hover:text-destructive hover:bg-destructive/10"
                                        :title="$t('common.actions.delete')"
                                    >
                                        <Trash2 class="w-4 h-4" />
                                    </Button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </Card>

        <!-- Pagination -->
        <Pagination
            v-if="pagination && pagination.total > 0"
            :current-page="pagination.current_page"
            :total-items="pagination.total"
            :per-page="Number(pagination.per_page || 15)"
            :show-page-numbers="true"
            @page-change="loadPage"
            @update:per-page="(val) => { pagination.per_page = val; loadPage(1); }"
            class="mt-4"
        />

        <!-- Detail Dialog -->
        <Dialog v-model:open="showDetail">
            <DialogContent class="max-w-2xl max-h-[80vh] overflow-y-auto">
                <DialogHeader>
                    <DialogTitle>{{ $t('features.forms.submissions.detailTitle') }}</DialogTitle>
                </DialogHeader>
                <div v-if="selectedSubmission" class="space-y-4">
                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <span class="font-medium text-foreground">{{ $t('features.forms.submissions.status') }}:</span>
                            <Badge class="ml-2">{{ getStatusLabel(selectedSubmission.status) }}</Badge>
                        </div>
                        <div>
                            <span class="font-medium text-foreground">{{ $t('features.forms.submissions.submitted') }}:</span>
                            <span class="ml-2 text-muted-foreground">{{ formatDate(selectedSubmission.created_at) }}</span>
                        </div>
                        <div>
                            <span class="font-medium text-foreground">{{ $t('features.forms.submissions.ipAddress') }}:</span>
                            <span class="ml-2 text-muted-foreground">{{ selectedSubmission.ip_address || '-' }}</span>
                        </div>
                        <div v-if="selectedSubmission.user">
                            <span class="font-medium text-foreground">{{ $t('features.forms.submissions.user') }}:</span>
                            <span class="ml-2 text-muted-foreground">{{ selectedSubmission.user.name || selectedSubmission.user.email }}</span>
                        </div>
                    </div>
                    <div class="border-t border-border pt-4">
                        <h4 class="font-semibold text-foreground mb-3">{{ $t('features.forms.submissions.formData') }}</h4>
                        <dl class="space-y-3">
                            <div v-for="(value, key) in selectedSubmission.data" :key="key" class="border-b border-border pb-2">
                                <dt class="text-sm font-medium text-foreground">{{ key }}</dt>
                                <dd class="mt-1 text-sm text-muted-foreground">{{ formatValue(value) }}</dd>
                            </div>
                        </dl>
                    </div>
                </div>
            </DialogContent>
        </Dialog>
    </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import { useI18n } from 'vue-i18n';
import { useRoute } from 'vue-router';
import api from '../../../services/api';
import toast from '../../../services/toast';
import { useConfirm } from '../../../composables/useConfirm';
import Card from '../../../components/ui/card.vue';
import Button from '../../../components/ui/button.vue';
import Input from '../../../components/ui/input.vue';
import Badge from '../../../components/ui/badge.vue';
import Select from '../../../components/ui/select.vue';
import SelectContent from '../../../components/ui/select-content.vue';
import SelectItem from '../../../components/ui/select-item.vue';
import SelectTrigger from '../../../components/ui/select-trigger.vue';
import SelectValue from '../../../components/ui/select-value.vue';
import Pagination from '../../../components/ui/pagination.vue';
import Dialog from '../../../components/ui/dialog.vue';
import DialogContent from '../../../components/ui/dialog-content.vue';
import DialogHeader from '../../../components/ui/dialog-header.vue';
import DialogTitle from '../../../components/ui/dialog-title.vue';
import { ArrowLeft, Download, Search, Loader2, FileText, Check, Archive, Trash2 } from 'lucide-vue-next';

const { t } = useI18n();
const route = useRoute();
const router = useRouter();
const { confirm } = useConfirm();

const form = ref(null);
const submissions = ref([]);
const loading = ref(true);
const statistics = ref(null);
const pagination = ref(null);
const search = ref('');
const statusFilter = ref('all');
const dateFrom = ref('');
const dateTo = ref('');
const selectedSubmission = ref(null);
const showDetail = ref(false);

const formId = computed(() => route.params.id);

const filteredSubmissions = computed(() => {
    let result = submissions.value;
    if (search.value) {
        const query = search.value.toLowerCase();
        result = result.filter(s => {
            const dataStr = JSON.stringify(s.data).toLowerCase();
            return dataStr.includes(query);
        });
    }
    return result;
});

const fetchForm = async () => {
    try {
        const response = await api.get(`/admin/cms/forms/${formId.value}`);
        form.value = response.data?.data || response.data;
    } catch (error) {
        console.error('Error fetching form:', error);
    }
};

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
        const response = await api.get(`/admin/cms/forms/${formId.value}/submissions`, { params });
        // API returns: { success, message, data: { data: [...], current_page, total, per_page, ... } }
        const paginatedData = response.data?.data || response.data;
        submissions.value = paginatedData?.data || [];
        pagination.value = {
            current_page: paginatedData?.current_page || 1,
            total: paginatedData?.total || 0,
            per_page: paginatedData?.per_page || 15,
            last_page: paginatedData?.last_page || 1
        };
    } catch (error) {
        console.error('Error fetching submissions:', error);
    } finally {
        loading.value = false;
    }
};

const fetchStatistics = async () => {
    try {
        const response = await api.get(`/admin/cms/forms/${formId.value}/submissions/statistics`);
        statistics.value = response.data?.data || response.data;
    } catch (error) {
        console.error('Error fetching statistics:', error);
    }
};

const loadPage = (page) => {
    fetchSubmissions(page);
};

const viewSubmission = async (submission) => {
    selectedSubmission.value = submission;
    showDetail.value = true;
    if (submission.status === 'new') {
        await markAsRead(submission, false);
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
        console.error('Error marking as read:', error);
    }
};

const archiveSubmission = async (submission) => {
    try {
        await api.put(`/admin/cms/form-submissions/${submission.id}/archive`);
        fetchSubmissions(pagination.value?.current_page || 1);
        fetchStatistics();
    } catch (error) {
        console.error('Error archiving submission:', error);
    }
};

const deleteSubmission = async (submission) => {
    const confirmed = await confirm({
        title: t('features.forms.submissions.actions.delete'),
        message: t('features.forms.submissions.messages.deleteConfirm'),
        variant: 'danger',
        confirmText: t('common.actions.delete'),
    });

    if (!confirmed) return;

    try {
        await api.delete(`/admin/cms/form-submissions/${submission.id}`);
        submissions.value = submissions.value.filter(s => s.id !== submission.id);
        toast.success(t('features.forms.submissions.messages.deleteSuccess'));
        fetchStatistics();
    } catch (error) {
        console.error('Error deleting submission:', error);
        toast.error('Error', error.response?.data?.message || t('features.forms.submissions.messages.deleteFailed'));
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
        const url = `${baseUrl}/api/v1/admin/cms/forms/${formId.value}/submissions/export?${params.toString()}`;
        const link = document.createElement('a');
        link.href = url;
        link.setAttribute('download', `submissions-${formId.value}.csv`);
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        toast.success(t('features.forms.submissions.messages.exportSuccess'));
    } catch (error) {
        console.error('Error exporting submissions:', error);
        toast.error('Error', t('features.forms.submissions.messages.exportFailed'));
    }
};

const getStatusLabel = (status) => {
    const labels = {
        new: t('features.forms.stats.new'),
        read: t('features.forms.stats.read'),
        archived: t('features.forms.stats.archived')
    };
    return labels[status] || status;
};

const formatDate = (date) => {
    if (!date) return '-';
    const parsed = new Date(date);
    if (isNaN(parsed.getTime())) return '-';
    return parsed.toLocaleString();
};

const formatValue = (value) => {
    if (Array.isArray(value)) return value.join(', ');
    if (typeof value === 'object') return JSON.stringify(value);
    return String(value || '-');
};

const getFirstFields = (data) => {
    if (!data) return {};
    const entries = Object.entries(data);
    return Object.fromEntries(entries.slice(0, 3));
};

watch([statusFilter, dateFrom, dateTo], () => {
    fetchSubmissions(1);
});

onMounted(() => {
    fetchForm();
    fetchSubmissions();
    fetchStatistics();
});
</script>
