<template>
    <div>
        <div class="mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold tracking-tight text-foreground">{{ $t('features.content.list.title') }}</h1>
                <p class="text-muted-foreground">{{ $t('features.content.list.description') }}</p>
            </div>
            <div class="flex items-center gap-2">
                <Button variant="outline" asChild v-if="authStore.hasPermission('manage content templates')">
                    <router-link :to="{ name: 'content-templates' }" class="flex items-center gap-2">
                        <LayoutTemplate class="w-4 h-4" />
                        {{ $t('features.content.list.templates') }}
                    </router-link>
                </Button>
                <Button asChild class="shadow-sm" v-if="authStore.hasPermission('create content')">
                    <router-link :to="{ name: 'contents.create' }" class="flex items-center gap-2">
                        <Plus class="w-4 h-4" />
                        {{ $t('features.content.list.createNew') }}
                    </router-link>
                </Button>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <Card class="hover:shadow-md transition-all duration-300">
                <CardContent class="p-6">
                    <div class="flex items-center justify-between">
                        <div class="space-y-1">
                            <p class="text-sm font-medium text-muted-foreground">{{ $t('features.dashboard.stats.totalContents') }}</p>
                            <p class="text-2xl font-bold">{{ stats.total }}</p>
                        </div>
                        <div class="p-3 bg-primary/10 rounded-xl text-primary">
                            <FileText class="w-5 h-5" />
                        </div>
                    </div>
                </CardContent>
            </Card>
            <Card class="hover:shadow-md transition-all duration-300">
                <CardContent class="p-6">
                    <div class="flex items-center justify-between">
                        <div class="space-y-1">
                            <p class="text-sm font-medium text-muted-foreground">{{ $t('features.content.status.published') }}</p>
                            <p class="text-2xl font-bold">{{ stats.published }}</p>
                        </div>
                        <div class="p-3 bg-emerald-500/10 rounded-xl text-emerald-500">
                            <CheckCircle2 class="w-5 h-5" />
                        </div>
                    </div>
                </CardContent>
            </Card>
            <Card class="hover:shadow-md transition-all duration-300">
                <CardContent class="p-6">
                    <div class="flex items-center justify-between">
                        <div class="space-y-1">
                            <p class="text-sm font-medium text-muted-foreground">{{ $t('features.content.status.pending') }}</p>
                            <p class="text-2xl font-bold">{{ stats.pending || 0 }}</p>
                        </div>
                        <div class="p-3 bg-amber-500/10 rounded-xl text-amber-500">
                            <Clock3 class="w-5 h-5" />
                        </div>
                    </div>
                </CardContent>
            </Card>
            <Card class="hover:shadow-md transition-all duration-300">
                <CardContent class="p-6">
                    <div class="flex items-center justify-between">
                        <div class="space-y-1">
                            <p class="text-sm font-medium text-muted-foreground">{{ $t('features.content.status.draft') }}</p>
                            <p class="text-2xl font-bold">{{ stats.draft }}</p>
                        </div>
                        <div class="p-3 bg-slate-500/10 rounded-xl text-slate-500">
                            <FileText class="w-5 h-5" />
                        </div>
                    </div>
                </CardContent>
            </Card>
            <Card class="hover:shadow-md transition-all duration-300">
                <CardContent class="p-6">
                    <div class="flex items-center justify-between">
                        <div class="space-y-1">
                            <p class="text-sm font-medium text-muted-foreground">{{ $t('features.content.status.archived') }}</p>
                            <p class="text-2xl font-bold">{{ stats.archived }}</p>
                        </div>
                        <div class="p-3 bg-muted rounded-xl text-muted-foreground">
                            <Archive class="w-5 h-5" />
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>

        <Card class="overflow-hidden">
            <div class="px-6 py-4 border-b border-border">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <div class="relative w-full md:w-72">
                            <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-muted-foreground" />
                            <Input
                                v-model="search"
                                type="text"
                                :placeholder="$t('features.comments.filter.searchPlaceholder')"
                                class="pl-9"
                            />
                        </div>
                        <Select
                            v-model="statusFilter"
                            @update:model-value="fetchContents"
                        >
                            <SelectTrigger class="w-[160px]">
                                <SelectValue :placeholder="$t('features.comments.filter.allStatus')" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">{{ $t('features.comments.filter.allStatus') }}</SelectItem>
                                <SelectItem value="published">{{ $t('features.content.status.published') }}</SelectItem>
                                <SelectItem value="pending">{{ $t('features.content.status.pending') }}</SelectItem>
                                <SelectItem value="draft">{{ $t('features.content.status.draft') }}</SelectItem>
                                <SelectItem value="archived">{{ $t('features.content.status.archived') }}</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                    
                    <div v-if="selectedContents.length > 0" class="flex items-center gap-3 p-1.5 px-3 rounded-lg bg-primary/5 border border-primary/10 transition-all animate-in fade-in slide-in-from-top-1">
                        <span class="text-sm font-medium text-primary">
                            {{ $t('features.content.list.selected', { count: selectedContents.length }) }}
                        </span>
                        <div class="h-4 w-px bg-primary/20"></div>
                        <Select
                            v-model="bulkAction"
                            @update:model-value="handleBulkAction"
                        >
                            <SelectTrigger class="w-[160px] h-8 border-primary/20">
                                <SelectValue :placeholder="$t('features.content.list.bulkActions')" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="publish" v-if="authStore.hasPermission('publish content')">{{ $t('features.content.actions.publishNow') }}</SelectItem>
                                <SelectItem value="approve" v-if="authStore.hasPermission('approve content')">{{ $t('features.content.actions.approve') }}</SelectItem>
                                <SelectItem value="reject" v-if="authStore.hasPermission('approve content')">{{ $t('features.content.actions.reject') }}</SelectItem>
                                <SelectItem value="draft">{{ $t('features.content.actions.saveDraft') }}</SelectItem>
                                <SelectItem value="archive">{{ $t('features.content.status.archived') }}</SelectItem>
                                <SelectItem value="delete" class="text-destructive focus:text-destructive" v-if="authStore.hasPermission('delete content')">{{ $t('features.languages.actions.delete') }}</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                </div>
            </div>

            <div v-if="loading && contents.length === 0" class="flex flex-col items-center justify-center py-24 text-muted-foreground space-y-4">
                <Loader2 class="w-10 h-10 animate-spin opacity-20" />
                <p class="text-sm font-medium animate-pulse">{{ $t('common.messages.loading.default') }}</p>
            </div>

            <div v-else-if="contents.length === 0" class="flex flex-col items-center justify-center py-24 text-muted-foreground space-y-4">
                <div class="p-4 rounded-full bg-muted/30">
                    <FileX2 class="w-10 h-10 opacity-20" />
                </div>
                <p class="text-sm font-medium">{{ $t('features.content.list.empty') }}</p>
            </div>

            <div v-else class="relative overflow-x-auto">
                <Table>
                    <TableHeader>
                        <TableRow class="hover:bg-muted/50 border-b border-border">
                            <TableHead class="w-12 px-6">
                                <Checkbox
                                    :checked="allSelected"
                                    @update:checked="toggleSelectAll"
                                />
                            </TableHead>
                            <TableHead class="px-6 py-4 text-xs font-bold text-muted-foreground">
                                {{ $t('features.content.form.title') }}
                            </TableHead>
                            <TableHead class="px-6 py-4 text-xs font-bold text-muted-foreground">
                                {{ $t('features.comments.detail.author') }}
                            </TableHead>
                            <TableHead class="px-6 py-4 text-xs font-bold text-muted-foreground">
                                {{ $t('common.labels.status') }}
                            </TableHead>
                            <TableHead class="px-6 py-4 text-xs font-bold text-muted-foreground">
                                {{ $t('features.content.form.featured') }}
                            </TableHead>
                            <TableHead class="px-6 py-4 text-xs font-bold text-muted-foreground">
                                {{ $t('features.content.form.publishDate') }}
                            </TableHead>
                            <TableHead class="px-6 py-4 text-center text-xs font-bold text-muted-foreground">
                                {{ $t('features.languages.list.headers.actions') }}
                            </TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-for="content in contents" :key="content.id" class="group hover:bg-muted/50 transition-colors border-b border-border">
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
                                    <span class="text-sm font-semibold text-foreground group-hover:text-primary transition-colors">{{ content.title }}</span>
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
                                <Badge
                                    variant="outline"
                                    :class="getStatusBadgeClass(content.status)"
                                    class="capitalize border-none px-2 py-0.5"
                                >
                                    {{ content.status }}
                                </Badge>
                            </TableCell>
                            <TableCell class="px-6 py-4">
                                <Switch
                                    :checked="!!content.is_featured"
                                    @update:checked="toggleFeatured(content)"
                                />
                            </TableCell>
                            <TableCell class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center gap-1.5 text-xs text-muted-foreground">
                                    <Calendar class="w-3.5 h-3.5" />
                                    {{ formatDate(content.created_at) }}
                                </div>
                            </TableCell>
                            <TableCell class="px-6 py-4 text-right">
                                <div class="flex justify-end items-center gap-1">
                                    <Button v-if="content.status === 'pending' && authStore.hasPermission('approve content')" variant="ghost" size="icon" class="h-8 w-8 text-emerald-500 hover:text-emerald-600 hover:bg-emerald-500/10" @click="handleApprove(content)" :title="$t('features.content.actions.approve')">
                                        <CheckCircle2 class="w-4 h-4" />
                                    </Button>
                                    <Button v-if="content.status === 'pending' && authStore.hasPermission('approve content')" variant="ghost" size="icon" class="h-8 w-8 text-amber-500 hover:text-amber-600 hover:bg-amber-500/10" @click="handleReject(content)" :title="$t('features.content.actions.reject')">
                                        <XCircle class="w-4 h-4" />
                                    </Button>
                                    <Button variant="ghost" size="icon" class="h-8 w-8 text-blue-500 hover:text-blue-600 hover:bg-blue-500/10" @click="handlePreview(content)" :title="$t('features.content.form.preview')">
                                        <Eye class="w-4 h-4" />
                                    </Button>
                                    <Button variant="ghost" size="icon" class="h-8 w-8 text-muted-foreground hover:text-foreground hover:bg-muted" @click="handleDuplicate(content)" :title="$t('features.content.actions.duplicate')" v-if="authStore.hasPermission('create content')">
                                        <Copy class="w-4 h-4" />
                                    </Button>
                                    <Button variant="ghost" size="icon" class="h-8 w-8 text-purple-500 hover:text-purple-600 hover:bg-purple-500/10" asChild :title="$t('features.content.list.revisions')" v-if="authStore.hasPermission('edit content')">
                                        <router-link :to="{ name: 'contents.revisions', params: { id: content.id } }">
                                            <History class="w-4 h-4" />
                                        </router-link>
                                    </Button>
                                    <Button variant="ghost" size="icon" class="h-8 w-8 text-indigo-500 hover:text-indigo-600 hover:bg-indigo-500/10" asChild :title="$t('common.actions.edit')" v-if="authStore.hasPermission('edit content') && (authStore.hasPermission('manage content') || content.author_id === authStore.user?.id)">
                                        <router-link :to="{ name: 'contents.edit', params: { id: content.id } }">
                                            <Edit2 class="w-4 h-4" />
                                        </router-link>
                                    </Button>
                                    <Button variant="ghost" size="icon" class="h-8 w-8 text-destructive hover:text-destructive hover:bg-destructive/10" @click="handleDelete(content)" :title="$t('features.languages.actions.delete')" v-if="authStore.hasPermission('delete content') && (authStore.hasPermission('manage content') || content.author_id === authStore.user?.id)">
                                        <Trash2 class="w-4 h-4" />
                                    </Button>
                                </div>
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>

            <!-- Pagination -->
            <Pagination
                v-if="pagination && pagination.total > 0"
                :current-page="pagination.current_page"
                :total-items="pagination.total"
                :per-page="Number(perPage)"
                @page-change="fetchContents"
                @update:per-page="(val) => { perPage = String(val); fetchContents(1); }"
            />
        </Card>
    </div>
</template>

<script setup>
import { ref, onMounted, watch, computed } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import { useI18n } from 'vue-i18n';
import api from '../../../services/api';
import { parseResponse, parseSingleResponse } from '../../../utils/responseParser';
import Card from '@/components/ui/card.vue';
import CardContent from '@/components/ui/card-content.vue';
import Button from '@/components/ui/button.vue';
import Input from '@/components/ui/input.vue';
import Badge from '@/components/ui/badge.vue';
import Checkbox from '@/components/ui/checkbox.vue';
import Switch from '@/components/ui/switch.vue';
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
import Pagination from '@/components/ui/pagination.vue';
import { 
    Plus, 
    LayoutTemplate, 
    Search, 
    Loader2, 
    FileX2, 
    Eye, 
    Copy, 
    History, 
    Edit2, 
    Trash2,
    Calendar,
    FileText,
    CheckCircle2,
    Clock,
    Archive,
    Clock3,
    XCircle
} from 'lucide-vue-next';
import { useAuthStore } from '../../../stores/auth';
import { useConfirm } from '../../../composables/useConfirm';
import { useToast } from '../../../composables/useToast';

const { t } = useI18n();
const router = useRouter();
const route = useRoute();
const toast = useToast();
const contents = ref([]);
const loading = ref(false);
const search = ref('');
const statusFilter = ref('');
const selectedContents = ref([]);
const bulkAction = ref('');
const pagination = ref(null);
const perPage = ref('10');
const stats = ref({
    total: 0,
    published: 0,
    pending: 0,
    draft: 0,
    archived: 0
});
const authStore = useAuthStore();

const allSelected = computed(() => {
    return contents.value.length > 0 && selectedContents.value.length === contents.value.length;
});

const fetchStats = async () => {
    try {
        // Fetch stats if user can manage or create content
        if (!authStore.hasPermission('manage content') && !authStore.hasPermission('create content') && !authStore.hasPermission('edit content')) return;

        // Use dedicated content stats endpoint
        const response = await api.get('/admin/cms/contents/stats');
        const data = parseSingleResponse(response);
        
        if (data) {
            stats.value = {
                total: data.total || 0,
                published: data.published || 0,
                pending: data.pending || 0,
                draft: data.draft || 0,
                archived: data.archived || 0
            };
        }
    } catch (error) {
        console.error('Failed to fetch stats:', error);
    }
};

const fetchContents = async (page = 1) => {
    loading.value = true;
    try {
        const params = { 
            page,
            per_page: perPage.value 
        };
        
        if (statusFilter.value && statusFilter.value !== 'all') {
            params.status = statusFilter.value;
        }

        if (search.value) {
            params.search = search.value;
        }

        const response = await api.get('/admin/cms/contents', { params });
        const { data: parsedData, pagination: parsedPagination } = parseResponse(response);
        
        contents.value = parsedData || [];
        pagination.value = parsedPagination;
        selectedContents.value = [];
    } catch (error) {
        console.error('Failed to fetch contents:', error);
    } finally {
        loading.value = false;
    }
};

const getStatusBadgeClass = (status) => {
    const s = status?.toLowerCase();
    if (s === 'published') return 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400';
    if (s === 'pending') return 'bg-amber-500/10 text-amber-600 dark:text-amber-400';
    if (s === 'draft') return 'bg-slate-500/10 text-slate-600 dark:text-slate-400';
    if (s === 'archived') return 'bg-muted text-muted-foreground';
    return 'bg-secondary text-secondary-foreground';
};

const getUserInitials = (name) => {
    if (!name) return '?';
    return name.split(' ').map(n => n[0]).slice(0, 2).join('').toUpperCase();
};

const toggleSelectAll = () => {
    if (allSelected.value) {
        selectedContents.value = [];
    } else {
        selectedContents.value = contents.value.map(c => c.id);
    }
};

const toggleFeatured = async (content) => {
    const originalState = content.is_featured;
    // Optimistic update
    content.is_featured = !originalState;
    
    try {
        await api.post(`/admin/cms/contents/${content.id}/toggle-featured`);
        toast.success.update();
    } catch (error) {
        console.error('Failed to toggle featured status:', error);
        // Revert on failure
        content.is_featured = originalState;
        toast.error.update(error);
    }
};

const { confirm } = useConfirm();

const handleApprove = async (content) => {
    const confirmed = await confirm({
        title: t('features.content.actions.approve'),
        message: t('common.messages.confirm.approve', { item: content.title }),
        confirmText: t('features.content.actions.approve'),
        variant: 'success'
    });
    
    if (!confirmed) return;
    
    try {
        await api.put(`/admin/cms/contents/${content.id}/approve`);
        await fetchContents();
        await fetchStats();
        toast.success.approve();
    } catch (error) {
        console.error('Failed to approve content:', error);
        toast.error.approve(error);
    }
};

const handleReject = async (content) => {
    const confirmed = await confirm({
        title: t('features.content.actions.reject'),
        message: t('common.messages.confirm.reject', { item: content.title }),
        confirmText: t('features.content.actions.reject'),
        variant: 'destructive'
    });

    if (!confirmed) return;

    try {
        await api.put(`/admin/cms/contents/${content.id}/reject`);
        await fetchContents();
        await fetchStats();
        toast.success.reject();
    } catch (error) {
        console.error('Failed to reject content:', error);
        toast.error.reject(error);
    }
};

const handleDuplicate = async (content) => {
    const confirmed = await confirm({
        title: t('features.content.actions.duplicate'),
        message: t('common.messages.confirm.duplicate', { item: content.title }),
        confirmText: t('features.content.actions.duplicate'),
    });

    if (!confirmed) return;

    try {
        const response = await api.post(`/admin/cms/contents/${content.id}/duplicate`);
        const duplicatedContent = response.data.data || response.data;
        toast.success.duplicate();
        router.push({ name: 'contents.edit', params: { id: duplicatedContent.id } });
    } catch (error) {
        console.error('Failed to duplicate content:', error);
        toast.error.duplicate(error);
    }
};

const handlePreview = async (content) => {
    try {
        const response = await api.get(`/admin/cms/contents/${content.id}/preview`);
        // Handle data wrapped in "data" key from BaseApiController
        const data = response.data?.data || response.data;
        const previewUrl = data?.url || data?.preview_url;
        
        if (previewUrl) {
            window.open(previewUrl, '_blank');
        } else {
            // Fallback: open content URL if preview URL not available
            window.open(`/${content.slug}`, '_blank');
        }
    } catch (error) {
        console.error('Failed to get preview URL:', error);
        // Fallback: open content URL
        window.open(`/${content.slug}`, '_blank');
    }
};

const handleBulkAction = async () => {
    if (!bulkAction.value || selectedContents.value.length === 0) {
        bulkAction.value = '';
        return;
    }

    const action = bulkAction.value;
    const count = selectedContents.value.length;
    
    let confirmConfig = {
        title: t('features.content.list.bulkActions'),
        message: t('common.messages.confirm.bulkAction', { action: action, count: count }),
        confirmText: t('common.actions.confirm'),
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
            ids: selectedContents.value,
        });
        await fetchContents();
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
    } catch (error) {
        console.error('Failed to delete content:', error);
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
    // Check for search query param from Global Search
    if (route.query.q) {
        search.value = route.query.q;
    }
    fetchContents();
    fetchStats();
});
</script>

