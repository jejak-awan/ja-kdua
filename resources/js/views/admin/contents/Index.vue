<script setup>
import { ref, onMounted, watch, computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { useRoute, useRouter } from 'vue-router';
import { useHead } from '@vueuse/head';
import { 
    Plus, 
    Search, 
    Filter, 
    MoreHorizontal, 
    FileText, 
    Calendar,
    CheckCircle2,
    XCircle,
    Clock3,
    AlertCircle,
    FileEdit,
    Archive,
    RotateCcw,
    Trash2,
    Eye,
    Copy,
    LayoutTemplate,
    History,
    Edit2,
    FileX2,
    Loader2
} from 'lucide-vue-next';
import { useAuthStore } from '@/stores/auth';
import { useConfirm } from '@/composables/useConfirm';
import { useToast } from '@/composables/useToast';
import api from '@/services/api';
import { parseResponse, ensureArray } from '@/utils/responseParser';

import Button from '@/components/ui/button.vue';
import Input from '@/components/ui/input.vue';
import Badge from '@/components/ui/badge.vue';
import Card from '@/components/ui/card.vue';
import CardContent from '@/components/ui/card-content.vue';
import Checkbox from '@/components/ui/checkbox.vue';
import Switch from '@/components/ui/switch.vue';
import Pagination from '@/components/ui/pagination.vue';
import Select from '@/components/ui/select.vue';
import SelectContent from '@/components/ui/select-content.vue';
import SelectItem from '@/components/ui/select-item.vue';
import SelectTrigger from '@/components/ui/select-trigger.vue';
import SelectValue from '@/components/ui/select-value.vue';
import Table from '@/components/ui/table.vue';
import TableBody from '@/components/ui/table-body.vue';
import TableCell from '@/components/ui/table-cell.vue';
import TableHead from '@/components/ui/table-head.vue';
import TableHeader from '@/components/ui/table-header.vue';
import TableRow from '@/components/ui/table-row.vue';

const { t } = useI18n();
const route = useRoute();
const router = useRouter();
const authStore = useAuthStore();
const { confirm } = useConfirm();
const toast = useToast();

useHead({
    title: computed(() => `${t('features.content.list.title')} | ${t('app.name')}`)
});

const loading = ref(true);
const contents = ref([]);
const pagination = ref({});
const search = ref('');
const statusFilter = ref('all');
const perPage = ref('10');
const selectedContents = ref([]);
const bulkAction = ref('');

const stats = ref({
    total: 0,
    published: 0,
    draft: 0,
    pending: 0,
    archived: 0,
    trashed: 0
});

const fetchContents = async (page = 1) => {
    loading.value = true;
    try {
        const params = {
            page,
            per_page: perPage.value,
            sort: 'created_at',
            order: 'desc',
        };

        if (search.value) params.search = search.value;
        if (statusFilter.value && statusFilter.value !== 'all') {
            params.status = statusFilter.value;
        }

        const response = await api.get('/admin/cms/contents', { params });
        
        const { data, pagination: meta } = parseResponse(response);
        
        contents.value = ensureArray(data);
        pagination.value = meta || {};
        
    } catch (error) {
        console.error('Failed to fetch contents:', error);
        toast.error.action(error);
        contents.value = [];
        pagination.value = {};
    } finally {
        loading.value = false;
    }
};

const fetchStats = async () => {
    try {
        const response = await api.get('/admin/cms/contents/stats');
        stats.value = response.data.data || {
            total: 0,
            published: 0,
            draft: 0,
            pending: 0,
            archived: 0,
            trashed: 0
        };
    } catch (error) {
        console.error('Failed to fetch stats:', error);
    }
};

const allSelected = computed(() => {
    return contents.value.length > 0 && selectedContents.value.length === contents.value.length;
});

const toggleSelectAll = (checked) => {
    if (checked) {
        selectedContents.value = contents.value.map(c => c.id);
    } else {
        selectedContents.value = [];
    }
};

const toggleFeatured = async (content) => {
    const previousState = content.is_featured;
    content.is_featured = !content.is_featured;

    try {
        await api.patch(`/admin/cms/contents/${content.id}/toggle-featured`);
        toast.success.action(t('common.messages.success.updated'));
    } catch (error) {
        content.is_featured = previousState;
        toast.error.action(error);
    }
};

const getStatusBadgeClass = (status) => {
    switch (status) {
        case 'published': return 'bg-emerald-500/10 text-emerald-500 border-emerald-200 dark:border-emerald-500/20';
        case 'draft': return 'bg-slate-500/10 text-slate-500 border-slate-200 dark:border-slate-500/20';
        case 'pending': return 'bg-amber-500/10 text-amber-500 border-amber-200 dark:border-amber-500/20';
        case 'archived': return 'bg-rose-500/10 text-rose-500 border-rose-200 dark:border-rose-500/20';
        case 'trashed': return 'bg-destructive/10 text-destructive border-destructive/20';
        default: return 'bg-gray-500/10 text-gray-500 border-gray-200 dark:border-gray-500/20';
    }
};

const getUserInitials = (name) => {
    if (!name) return '??';
    return name.split(' ').map(n => n[0]).join('').toUpperCase().substring(0, 2);
};

const handleBulkAction = async (action) => {
    if (!action) return;
    
    const confirmConfig = {
        title: t('features.content.list.bulkActions'),
        message: t('common.messages.confirm.bulkAction', { action: action, count: selectedContents.value.length }),
    };
    
    if (action === 'delete') {
        confirmConfig.variant = 'destructive';
        confirmConfig.confirmText = t('common.actions.delete');
    } else if (action === 'approve') {
        confirmConfig.variant = 'success';
        confirmConfig.confirmText = t('features.content.actions.approve');
    } else if (action === 'reject') {
        confirmConfig.variant = 'destructive';
        confirmConfig.confirmText = t('features.content.actions.reject');
    }

    const confirmed = await confirm(confirmConfig);

    if (!confirmed) {
        bulkAction.value = '';
        return;
    }

    try {
        await api.post('/admin/cms/contents/bulk-action', {
            action: action,
            content_ids: selectedContents.value,
        });
        await fetchContents();
        await fetchStats();
        toast.success.update();
        bulkAction.value = '';
    } catch (error) {
        console.error('Failed to perform bulk action:', error);
        toast.error.action(error);
        bulkAction.value = '';
    }
};

const handleDelete = async (content) => {
    const confirmed = await confirm({
        title: t('common.actions.delete'),
        message: t('common.messages.confirm.delete', { item: content.title }),
        confirmText: t('common.actions.delete'),
        variant: 'destructive'
    });
    if (!confirmed) return;
    try {
        await api.delete(`/admin/cms/contents/${content.id}`);
        await fetchContents();
        await fetchStats();
    } catch (error) {
        console.error('Failed to delete content:', error);
        toast.error(error);
    }
};

const handleRestore = async (content) => {
    const confirmed = await confirm({
        title: t('common.actions.restore'),
        message: t('common.messages.confirm.restore', { item: content.title }),
        confirmText: t('common.actions.restore'),
        variant: 'info'
    });

    if (!confirmed) return;

    try {
        await api.put(`/admin/cms/contents/${content.id}/restore`);
        await fetchContents();
        await fetchStats();
        toast.success.action(t('common.messages.success.restored') || 'Content restored');
    } catch (error) {
        console.error('Failed to restore content:', error);
        toast.error(error);
    }
};

const handleForceDelete = async (content) => {
    const confirmed = await confirm({
        title: t('common.actions.deletePermanently'),
        message: t('common.messages.confirm.deletePermanently', { item: content.title }),
        confirmText: t('common.actions.delete'),
        variant: 'destructive'
    });
    if (!confirmed) return;
    try {
        await api.delete(`/admin/cms/contents/${content.id}/force-delete`);
        await fetchContents();
        await fetchStats();
    } catch (error) {
        console.error('Failed to force delete content:', error);
        toast.error(error);
    }
};

const handleEdit = (content) => {
    router.push({ name: 'contents.edit', params: { id: content.id } });
};

const handlePreview = (content) => {
    const routeData = router.resolve({ name: 'posts.show', params: { slug: content.slug } });
    window.open(routeData.href, '_blank');
};

const handleDuplicate = async (content) => {
    try {
        await api.post(`/admin/cms/contents/${content.id}/duplicate`);
        await fetchContents();
        await fetchStats();
        toast.success.action(t('common.messages.success.duplicated') || 'Content duplicated');
    } catch (error) {
        console.error('Failed to duplicate content:', error);
        toast.error(error);
    }
};

const handleApprove = async (content) => {
    try {
        await api.patch(`/admin/cms/contents/${content.id}/approve`);
        await fetchContents();
        await fetchStats();
        toast.success.action(t('features.content.messages.approved') || 'Content approved');
    } catch (error) {
        console.error('Failed to approve content:', error);
        toast.error(error);
    }
};

const handleReject = async (content) => {
    const reason = await confirm({
        title: t('features.content.actions.reject'),
        message: t('features.content.messages.rejectReason'),
        input: true,
        inputPlaceholder: t('features.content.placeholders.rejectionReason'),
        confirmText: t('features.content.actions.reject'),
        variant: 'destructive'
    });
    if (reason === false) return;
    try {
        await api.patch(`/admin/cms/contents/${content.id}/reject`, { reason_for_rejection: reason });
        await fetchContents();
        await fetchStats();
        toast.success.action(t('features.content.messages.rejected') || 'Content rejected');
    } catch (error) {
        console.error('Failed to reject content:', error);
        toast.error(error);
    }
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString();
};

watch([search, statusFilter], () => {
    fetchContents();
});

onMounted(() => {
    if (route.query.q) {
        search.value = route.query.q;
    }
    fetchContents();
    fetchStats();
});
</script>

<template>
    <div class="container mx-auto p-6 space-y-8">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="space-y-1">
                <h1 class="text-2xl font-bold tracking-tight">{{ $t('features.content.list.title') }}</h1>
                <p class="text-muted-foreground">{{ $t('features.content.list.description') }}</p>
            </div>
            <div class="flex items-center gap-2">
                <Button variant="outline" v-if="authStore.hasPermission('manage content templates')" @click="router.push({ name: 'content-templates' })">
                    <LayoutTemplate class="w-4 h-4 mr-2" />
                    {{ $t('features.content.list.templates') }}
                </Button>
                <Button class="shadow-sm" v-if="authStore.hasPermission('create content')" @click="router.push({ name: 'contents.create' })">
                    <Plus class="w-4 h-4 mr-2" />
                    {{ $t('features.content.list.createNew') }}
                </Button>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-6 gap-4 mb-8">
            <!-- Total Contents -->
            <Card class="hover:shadow-md transition-shadow duration-300">
                <CardContent class="p-6">
                    <div class="flex items-center justify-between">
                        <div class="space-y-1">
                            <p class="text-xs font-semibold text-muted-foreground">{{ $t('features.dashboard.stats.totalContents') }}</p>
                            <p class="text-2xl font-bold text-foreground">{{ stats.total || 0 }}</p>
                        </div>
                        <div class="p-2.5 bg-primary/10 rounded-xl text-primary">
                            <FileText class="w-5 h-5" />
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Published -->
            <Card class="hover:shadow-md transition-shadow duration-300">
                <CardContent class="p-6">
                    <div class="flex items-center justify-between">
                        <div class="space-y-1">
                            <p class="text-xs font-semibold text-muted-foreground">{{ $t('features.content.status.published') }}</p>
                            <p class="text-2xl font-bold text-emerald-600 dark:text-emerald-400">{{ stats.published || 0 }}</p>
                        </div>
                        <div class="p-2.5 bg-emerald-500/10 dark:bg-emerald-500/20 rounded-xl text-emerald-600 dark:text-emerald-400">
                            <CheckCircle2 class="w-5 h-5" />
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Draft -->
            <Card class="hover:shadow-md transition-shadow duration-300">
                <CardContent class="p-6">
                    <div class="flex items-center justify-between">
                        <div class="space-y-1">
                            <p class="text-xs font-semibold text-muted-foreground">{{ $t('features.content.status.draft') }}</p>
                            <p class="text-2xl font-bold text-slate-600 dark:text-slate-400">{{ stats.draft || 0 }}</p>
                        </div>
                        <div class="p-2.5 bg-slate-500/10 dark:bg-slate-500/20 rounded-xl text-slate-600 dark:text-slate-400">
                            <FileEdit class="w-5 h-5" />
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Pending -->
            <Card class="hover:shadow-md transition-shadow duration-300">
                <CardContent class="p-6">
                    <div class="flex items-center justify-between">
                        <div class="space-y-1">
                            <p class="text-xs font-semibold text-muted-foreground">{{ $t('features.content.status.pending') }}</p>
                            <p class="text-2xl font-bold text-amber-600 dark:text-amber-400">{{ stats.pending || 0 }}</p>
                        </div>
                        <div class="p-2.5 bg-amber-500/10 dark:bg-amber-500/20 rounded-xl text-amber-600 dark:text-amber-400">
                            <Clock3 class="w-5 h-5" />
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Archived -->
            <Card class="hover:shadow-md transition-shadow duration-300">
                <CardContent class="p-6">
                    <div class="flex items-center justify-between">
                        <div class="space-y-1">
                            <p class="text-xs font-semibold text-muted-foreground">{{ $t('features.content.status.archived') }}</p>
                            <p class="text-2xl font-bold text-rose-600 dark:text-rose-400">{{ stats.archived || 0 }}</p>
                        </div>
                        <div class="p-2.5 bg-rose-500/10 dark:bg-rose-500/20 rounded-xl text-rose-600 dark:text-rose-400">
                            <Archive class="w-5 h-5" />
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Trashed -->
            <Card class="hover:shadow-md transition-shadow duration-300">
                <CardContent class="p-6">
                    <div class="flex items-center justify-between">
                        <div class="space-y-1">
                            <p class="text-xs font-semibold text-muted-foreground">{{ $t('features.content.status.trashed') }}</p>
                            <p class="text-2xl font-bold text-red-600 dark:text-red-400">{{ stats.trashed || 0 }}</p>
                        </div>
                        <div class="p-2.5 bg-red-500/10 dark:bg-red-500/20 rounded-xl text-red-600 dark:text-red-400">
                            <Trash2 class="w-5 h-5" />
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>

        <Card class="overflow-hidden">
            <!-- Filters -->
            <div class="px-6 py-4 border-b border-border">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div class="flex items-center gap-2" v-if="selectedContents.length > 0">
                        <span class="text-sm text-muted-foreground">{{ $t('features.content.list.selected', { count: selectedContents.length }) }}</span>
                        <div class="flex items-center gap-2 border-l border-border pl-2">
                            <Button variant="outline" size="sm" @click="handleBulkAction('approve')" v-if="authStore.hasPermission('approve content')">
                                <CheckCircle2 class="w-4 h-4 mr-2 text-emerald-500" />
                                {{ $t('features.content.actions.approve') }}
                            </Button>
                            <Button variant="outline" size="sm" @click="handleBulkAction('reject')" v-if="authStore.hasPermission('approve content')">
                                <XCircle class="w-4 h-4 mr-2 text-rose-500" />
                                {{ $t('features.content.actions.reject') }}
                            </Button>
                            <Button variant="outline" size="sm" class="text-destructive hover:text-destructive" @click="handleBulkAction('delete')" v-if="authStore.hasPermission('delete content')">
                                <Trash2 class="w-4 h-4 mr-2" />
                                {{ $t('common.actions.delete') }}
                            </Button>
                        </div>
                    </div>
                    <div class="relative w-full md:w-72" v-else>
                        <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-muted-foreground" />
                        <Input
                            v-model="search"
                            type="text"
                            :placeholder="$t('features.comments.filter.searchPlaceholder')"
                            class="pl-9"
                        />
                    </div>
                    <div class="flex items-center gap-2" v-if="selectedContents.length === 0">
                         <Select v-model="statusFilter">
                            <SelectTrigger class="w-[180px]">
                                <SelectValue placeholder="Filter by status" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">All Status</SelectItem>
                                <SelectItem value="published">Published</SelectItem>
                                <SelectItem value="draft">Draft</SelectItem>
                                <SelectItem value="pending">Pending</SelectItem>
                                <SelectItem value="archived">Archived</SelectItem>
                                <SelectItem value="trashed">Trashed</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                </div>
            </div>

            <!-- Table -->
            <div class="relative overflow-x-auto">
                <Table>
                    <TableHeader>
                        <TableRow class="hover:bg-muted/50 border-b border-border">
                            <TableHead class="w-12 px-6">
                                <Checkbox
                                    :checked="allSelected"
                                    @update:checked="toggleSelectAll"
                                />
                            </TableHead>
                            <TableHead class="px-6 py-4 text-xs font-bold text-muted-foreground">Title</TableHead>
                            <TableHead class="px-6 py-4 text-xs font-bold text-muted-foreground">Author</TableHead>
                            <TableHead class="px-6 py-4 text-xs font-bold text-muted-foreground">Status</TableHead>
                            <TableHead class="px-6 py-4 text-xs font-bold text-muted-foreground">Featured</TableHead>
                            <TableHead class="px-6 py-4 text-xs font-bold text-muted-foreground">Date</TableHead>
                            <TableHead class="px-6 py-4 text-center text-xs font-bold text-muted-foreground">Actions</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-for="content in contents" :key="content.id" class="group hover:bg-muted/50 border-b border-border">
                            <TableCell class="px-6">
                                <Checkbox
                                    :checked="selectedContents.includes(content.id)"
                                    @update:checked="(checked) => {
                                        if (checked) selectedContents.push(content.id)
                                        else selectedContents = selectedContents.filter(id => id !== content.id)
                                    }"
                                />
                            </TableCell>
                            <TableCell class="px-6 py-4">
                                <div class="flex flex-col gap-0.5">
                                    <div class="flex items-center gap-2">
                                        <span class="text-sm font-semibold text-foreground group-hover:text-primary transition-colors">{{ content.title }}</span>
                                        <Badge v-if="content.deleted_at" variant="destructive" class="h-4.5 text-[10px] px-1.5 uppercase font-bold tracking-wider">
                                            {{ t('features.content.status.trashed') }}
                                        </Badge>
                                    </div>
                                    <span class="text-xs text-muted-foreground/70 font-mono">{{ content.slug }}</span>
                                </div>
                            </TableCell>
                            <TableCell class="px-6 py-4">
                                <div class="flex items-center gap-2">
                                    <div class="w-6 h-6 rounded-full bg-primary/10 flex items-center justify-center text-[10px] font-bold text-primary">
                                        {{ getUserInitials(content.author?.name) }}
                                    </div>
                                    <span class="text-sm text-foreground/80">{{ content.author?.name }}</span>
                                </div>
                            </TableCell>
                            <TableCell class="px-6 py-4">
                                <Badge variant="outline" :class="getStatusBadgeClass(content.status)" class="capitalize border-none px-2 py-0.5">
                                    {{ content.status }}
                                </Badge>
                            </TableCell>
                            <TableCell class="px-6 py-4">
                                <Switch :checked="!!content.is_featured" @update:checked="toggleFeatured(content)" />
                            </TableCell>
                            <TableCell class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center gap-1.5 text-xs text-muted-foreground">
                                    <Calendar class="w-3.5 h-3.5" />
                                    {{ formatDate(content.created_at) }}
                                </div>
                            </TableCell>
                            <TableCell class="px-6 py-4 text-right">
                                <div class="flex justify-end items-center gap-1">
                                    <template v-if="content.deleted_at">
                                        <Button variant="ghost" size="icon" class="h-8 w-8 text-emerald-600" @click="handleRestore(content)" v-if="authStore.hasPermission('delete content')" :title="t('common.actions.restore')">
                                            <RotateCcw class="w-4 h-4" />
                                        </Button>
                                        <Button variant="ghost" size="icon" class="h-8 w-8 text-destructive" @click="handleForceDelete(content)" v-if="authStore.hasPermission('delete content')" :title="t('common.actions.deletePermanently')">
                                            <Trash2 class="w-4 h-4" />
                                        </Button>
                                    </template>
                                    <template v-else>
                                        <Button variant="ghost" size="icon" class="h-8 w-8" @click="handleEdit(content)" v-if="authStore.hasPermission('edit content')" :title="t('common.actions.edit')">
                                            <Edit2 class="w-4 h-4" />
                                        </Button>
                                        <Button variant="ghost" size="icon" class="h-8 w-8 text-destructive" @click="handleDelete(content)" v-if="authStore.hasPermission('delete content')" :title="t('common.actions.delete')">
                                            <Trash2 class="w-4 h-4" />
                                        </Button>
                                    </template>
                                </div>
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>
            
            <div class="px-6 py-4 border-t border-border">
                <Pagination
                    :total-items="pagination.total || 0"
                    :per-page="parseInt(perPage)"
                    :current-page="pagination.current_page || 1"
                    @update:page="fetchContents"
                    @update:per-page="(val) => { perPage = String(val); fetchContents(1); }"
                />
            </div>
        </Card>
    </div>
</template>
