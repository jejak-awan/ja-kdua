<script setup lang="ts">
import { ref, onMounted, watch, computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { useRoute, useRouter } from 'vue-router';
import { useHead } from '@vueuse/head';
import { 
    Plus, 
    Search, 
    CheckCircle2,
    XCircle,
    Clock3,
    Pencil,
    FileEdit,
    Archive,
    RotateCcw,
    Trash2,
    LayoutTemplate,
    Type,
    Layout
} from 'lucide-vue-next';
import { useAuthStore } from '@/stores/auth';
import { useCmsStore } from '@/stores/cms';
import { useConfirm } from '@/composables/useConfirm';
import { useToast } from '@/composables/useToast';
import api from '@/services/api';
import { parseResponse, ensureArray } from '@/utils/responseParser';
import { cn } from '@/lib/utils';
import type { Content } from '@/types/cms';

// Shadcn Components
// @ts-ignore
import Button from '@/components/ui/button.vue';
// @ts-ignore
import Input from '@/components/ui/input.vue';
// @ts-ignore
import Badge from '@/components/ui/badge.vue';
// @ts-ignore
import Card from '@/components/ui/card.vue';
// @ts-ignore
import CardContent from '@/components/ui/card-content.vue';
// @ts-ignore
import Checkbox from '@/components/ui/checkbox.vue';
// @ts-ignore
import Switch from '@/components/ui/switch.vue';
// @ts-ignore
import Pagination from '@/components/ui/pagination.vue';
// @ts-ignore
import Select from '@/components/ui/select.vue';
// @ts-ignore
import SelectContent from '@/components/ui/select-content.vue';
// @ts-ignore
import SelectItem from '@/components/ui/select-item.vue';
// @ts-ignore
import SelectTrigger from '@/components/ui/select-trigger.vue';
// @ts-ignore
import SelectValue from '@/components/ui/select-value.vue';
// @ts-ignore
import Table from '@/components/ui/table.vue';
// @ts-ignore
import TableBody from '@/components/ui/table-body.vue';
// @ts-ignore
import TableCell from '@/components/ui/table-cell.vue';
// @ts-ignore
import TableHead from '@/components/ui/table-head.vue';
// @ts-ignore
import TableHeader from '@/components/ui/table-header.vue';
// @ts-ignore
import TableRow from '@/components/ui/table-row.vue';

const { t } = useI18n();
const route = useRoute();
const router = useRouter();
const authStore = useAuthStore();
const cmsStore = useCmsStore();
const { confirm } = useConfirm();
const toast = useToast();

const props = defineProps({
    isEmbedded: {
        type: Boolean,
        default: false
    }
});

if (!props.isEmbedded) {
    useHead({
        title: computed(() => `${cmsStore.siteSettings?.site_name || t('app.name')} | ${t('features.content.list.title')}`)
    });
}

const loading = ref(true);
const contents = ref<Content[]>([]);
const pagination = ref<any>({});
const search = ref('');
const statusFilter = ref('all');
const perPage = ref('10');
const selectedContents = ref<number[]>([]);
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
        const params: any = {
            page,
            per_page: perPage.value,
            sort: 'created_at',
            order: 'desc',
        };

        if (search.value) params.search = search.value;
        if (statusFilter.value && statusFilter.value !== 'all') {
            params.status = statusFilter.value;
        }

        const response = await api.get('/admin/ja/contents', { params });
        
        const { data, pagination: meta } = parseResponse(response);
        
        contents.value = ensureArray(data);
        pagination.value = meta || {};
        
    } catch (error: any) {
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
        const response = await api.get('/admin/ja/contents/stats');
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

const toggleSelectAll = (checked: boolean) => {
    if (checked) {
        selectedContents.value = contents.value.map(c => c.id);
    } else {
        selectedContents.value = [];
    }
};

const toggleFeatured = async (content: Content) => {
    const previousState = content.is_featured;
    content.is_featured = !content.is_featured;

    try {
        await api.patch(`/admin/ja/contents/${content.id}/toggle-featured`);
        toast.success.action(t('common.messages.success.updated'));
    } catch (error: any) {
        content.is_featured = previousState;
        toast.error.action(error);
    }
};

const getStatusBadgeClass = (status: string) => {
    switch (status) {
        case 'published': return 'bg-success/10 text-success border-success/20';
        case 'draft': return 'bg-muted text-muted-foreground border-border/40';
        case 'pending': return 'bg-warning/10 text-warning border-warning/20';
        case 'archived': return 'bg-primary/10 text-primary border-primary/20';
        case 'trashed': return 'bg-destructive/10 text-destructive border-destructive/20';
        default: return 'bg-muted text-muted-foreground border-border/40';
    }
};

const getUserInitials = (name?: string) => {
    if (!name) return '??';
    return name.split(' ').map(n => n[0]).join('').toUpperCase().substring(0, 2);
};

const handleBulkAction = async (action: string) => {
    if (!action) return;
    
    const confirmConfig: any = {
        title: t('features.content.list.bulkActions'),
        message: t('common.messages.confirm.bulkAction', { action: action, count: selectedContents.value.length }),
    };
    
    if (action === 'delete') {
        confirmConfig.variant = 'danger';
        confirmConfig.confirmText = t('common.actions.delete');
    } else if (action === 'approve') {
        confirmConfig.variant = 'success';
        confirmConfig.confirmText = t('features.content.actions.approve');
    } else if (action === 'reject') {
        confirmConfig.variant = 'danger';
        confirmConfig.confirmText = t('features.content.actions.reject');
    } else if (action === 'restore') {
        confirmConfig.variant = 'default';
        confirmConfig.confirmText = t('common.actions.restore');
    } else if (action === 'force_delete') {
        confirmConfig.variant = 'danger';
        confirmConfig.confirmText = t('common.actions.deletePermanently');
    }

    const confirmed = await confirm(confirmConfig);

    if (!confirmed) {
        bulkAction.value = '';
        return;
    }

    try {
        await api.post('/admin/ja/contents/bulk-action', {
            action: action,
            content_ids: selectedContents.value,
        });
        selectedContents.value = []; // Clear selection
        await fetchContents();
        await fetchStats();
        toast.success.update();
        bulkAction.value = '';
    } catch (error: any) {
        console.error('Failed to perform bulk action:', error);
        toast.error.action(error);
    } finally {
        bulkAction.value = '';
    }
};

const handleEmptyTrash = async () => {
    const confirmed = await confirm({
        title: t('features.content.actions.emptyTrash') || 'Empty Trash',
        message: t('common.messages.confirm.emptyTrash') || 'Are you sure you want to permanently delete all items in the trash? This action cannot be undone.',
        confirmText: t('common.actions.deletePermanently'),
        variant: 'danger'
    });

    if (!confirmed) return;

    try {
        await api.delete('/admin/ja/contents/trash/empty');
        await fetchContents();
        await fetchStats();
        toast.success.action(t('common.messages.success.deleted') || 'Trash emptied successfully');
    } catch (error: any) {
        console.error('Failed to empty trash:', error);
        toast.error.action(error);
    }
};

const handleDelete = async (content: Content) => {
    const confirmed = await confirm({
        title: t('common.actions.delete'),
        message: t('common.messages.confirm.delete', { item: content.title }),
        confirmText: t('common.actions.delete'),
        variant: 'danger'
    });
    if (!confirmed) return;
    try {
        await api.delete(`/admin/ja/contents/${content.id}`);
        await fetchContents();
        await fetchStats();
    } catch (error: any) {
        console.error('Failed to delete content:', error);
        toast.error.delete(error, content.title);
    }
};

const handleRestore = async (content: Content) => {
    const confirmed = await confirm({
        title: t('common.actions.restore'),
        message: t('common.messages.confirm.restore', { item: content.title }),
        confirmText: t('common.actions.restore'),
        variant: 'info'
    });

    if (!confirmed) return;

    try {
        await api.put(`/admin/ja/contents/${content.id}/restore`);
        await fetchContents();
        await fetchStats();
        toast.success.action(t('common.messages.success.restored') || 'Content restored');
    } catch (error: any) {
        console.error('Failed to restore content:', error);
        toast.error.action(error);
    }
};

const handleForceDelete = async (content: Content) => {
    const confirmed = await confirm({
        title: t('common.actions.deletePermanently'),
        message: t('common.messages.confirm.deletePermanently', { item: content.title }),
        confirmText: t('common.actions.delete'),
        variant: 'danger'
    });
    if (!confirmed) return;
    try {
        await api.delete(`/admin/ja/contents/${content.id}/force-delete`);
        await fetchContents();
        await fetchStats();
    } catch (error: any) {
        console.error('Failed to force delete content:', error);
        toast.error.delete(error, content.title);
    }
};

const handleEdit = (content: Content) => {
    router.push({ name: 'contents.edit', params: { id: content.id } });
};

const handlePreview = (content: Content) => {
    const routeData = router.resolve({ name: 'posts.show', params: { slug: content.slug } });
    window.open(routeData.href, '_blank');
};

const handleDuplicate = async (content: Content) => {
    try {
        await api.post(`/admin/ja/contents/${content.id}/duplicate`);
        await fetchContents();
        await fetchStats();
        toast.success.action(t('common.messages.success.duplicated') || 'Content duplicated');
    } catch (error: any) {
        console.error('Failed to duplicate content:', error);
        toast.error.action(error);
    }
};

const handleApprove = async (content: Content) => {
    try {
        await api.patch(`/admin/ja/contents/${content.id}/approve`);
        await fetchContents();
        await fetchStats();
        toast.success.action(t('features.content.messages.approved') || 'Content approved');
    } catch (error: any) {
        console.error('Failed to approve content:', error);
        toast.error.action(error);
    }
};

const handleReject = async (content: Content) => {
    const reason = await confirm({
        title: t('features.content.actions.reject'),
        message: t('features.content.messages.rejectReason'),
        input: true,
        inputPlaceholder: t('features.content.placeholders.rejectionReason'),
        confirmText: t('features.content.actions.reject'),
        variant: 'danger'
    });
    if (reason === false) return;
    try {
        await api.patch(`/admin/ja/contents/${content.id}/reject`, { reason_for_rejection: reason });
        await fetchContents();
        await fetchStats();
        toast.success.action(t('features.content.messages.rejected') || 'Content rejected');
    } catch (error: any) {
        console.error('Failed to reject content:', error);
        toast.error.action(error);
    }
};

const formatDate = (date: string | undefined) => {
    if (!date) return '-';
    return new Date(date).toLocaleDateString();
};

watch([search, statusFilter], () => {
    fetchContents();
});

onMounted(() => {
    if (route.query.q) {
        search.value = route.query.q as string;
    }
    fetchContents();
    fetchStats();
});
</script>

<template>
    <div :class="isEmbedded ? 'space-y-6' : 'container mx-auto p-6 space-y-8'">
        <div v-if="!isEmbedded" class="mb-6">
            <!-- Header removed or simplified since it's redundant in Studio -->
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-6 gap-4 mb-8">
            <!-- Total Contents -->
            <Card 
                @click="statusFilter = 'all'"
                :class="cn(
                    'cursor-pointer transition-shadow duration-300 hover:shadow-md border',
                    statusFilter === 'all' ? 'border-primary' : 'border-border'
                )"
            >
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
            <Card 
                @click="statusFilter = 'published'"
                :class="cn('px-6 py-4 cursor-pointer hover:shadow-md',
                    statusFilter === 'published' ? 'border-success' : 'border-border')"
            >
                <CardContent class="p-6">
                    <div class="flex items-center justify-between">
                        <div class="space-y-1">
                            <p class="text-xs font-semibold text-muted-foreground">{{ $t('features.content.status.published') }}</p>
                            <p class="text-2xl font-bold text-success">{{ stats.published || 0 }}</p>
                        </div>
                        <div class="p-2.5 bg-success/10 rounded-xl text-success">
                            <CheckCircle2 class="w-5 h-5" />
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Draft -->
            <Card 
                @click="statusFilter = 'draft'"
                :class="cn('px-6 py-4 cursor-pointer hover:shadow-md',
                    statusFilter === 'draft' ? 'border-primary' : 'border-border')"
            >
                <CardContent class="p-6">
                    <div class="flex items-center justify-between">
                        <div class="space-y-1">
                            <p class="text-xs font-semibold text-muted-foreground">{{ $t('features.content.status.draft') }}</p>
                            <p class="text-2xl font-bold text-primary">{{ stats.draft || 0 }}</p>
                        </div>
                        <div class="p-2.5 bg-primary/10 rounded-xl text-primary">
                            <FileEdit class="w-5 h-5" />
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Pending -->
            <Card 
                @click="statusFilter = 'pending'"
                :class="cn('px-6 py-4 cursor-pointer hover:shadow-md',
                    statusFilter === 'pending' ? 'border-warning' : 'border-border')"
            >
                <CardContent class="p-6">
                    <div class="flex items-center justify-between">
                        <div class="space-y-1">
                            <p class="text-xs font-semibold text-muted-foreground">{{ $t('features.content.status.pending') }}</p>
                            <p class="text-2xl font-bold text-warning">{{ stats.pending || 0 }}</p>
                        </div>
                        <div class="p-2.5 bg-warning/10 rounded-xl text-warning">
                            <Clock3 class="w-5 h-5" />
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Archived -->
            <Card 
                @click="statusFilter = 'archived'"
                :class="cn('px-6 py-4 cursor-pointer hover:shadow-md',
                    statusFilter === 'archived' ? 'border-primary' : 'border-border')"
            >
                <CardContent class="p-6">
                    <div class="flex items-center justify-between">
                        <div class="space-y-1">
                            <p class="text-xs font-semibold text-muted-foreground">{{ $t('features.content.status.archived') }}</p>
                            <p class="text-2xl font-bold text-primary">{{ stats.archived || 0 }}</p>
                        </div>
                        <div class="p-2.5 bg-primary/10 rounded-xl text-primary">
                            <Archive class="w-5 h-5" />
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Trashed -->
            <Card 
                @click="statusFilter = 'trashed'"
                :class="cn('px-6 py-4 cursor-pointer hover:shadow-md',
                    statusFilter === 'trashed' ? 'border-destructive' : 'border-border')"
            >
                <CardContent class="p-6">
                    <div class="flex items-center justify-between">
                        <div class="space-y-1">
                            <p class="text-xs font-semibold text-muted-foreground">{{ $t('features.content.status.trashed') }}</p>
                            <p class="text-2xl font-bold text-destructive">{{ stats.trashed || 0 }}</p>
                        </div>
                        <div class="p-2.5 bg-destructive/10 rounded-xl text-destructive">
                            <Trash2 class="w-5 h-5" />
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>

        <Card class="overflow-hidden">
            <!-- Filters -->
            <!-- Filters -->
            <div class="px-6 py-4 border-b border-border">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <!-- Left: Search & Filter -->
                    <div class="flex items-center gap-2 flex-1 flex-wrap">
                        <div class="relative w-full md:w-72">
                            <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-muted-foreground" />
                            <Input
                                v-model="search"
                                type="text"
                                :placeholder="$t('common.actions.search') + '...'"
                                class="pl-9"
                            />
                        </div>
                        <Select v-model="statusFilter">
                            <SelectTrigger class="w-[180px]">
                                <SelectValue placeholder="Filter by status" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">{{ $t('common.labels.all') }} {{ $t('common.labels.status') }}</SelectItem>
                                <SelectItem value="published">{{ $t('features.content.status.published') }}</SelectItem>
                                <SelectItem value="draft">{{ $t('features.content.status.draft') }}</SelectItem>
                                <SelectItem value="pending">{{ $t('features.content.status.pending') }}</SelectItem>
                                <SelectItem value="archived">{{ $t('features.content.status.archived') }}</SelectItem>
                                <SelectItem value="trashed">{{ $t('features.content.status.trashed') }}</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>

                    <!-- Right: Actions -->
                    <div class="flex items-center gap-2">
                         <!-- Empty Trash -->
                         <Button 
                            v-if="statusFilter === 'trashed' && stats.trashed > 0 && selectedContents.length === 0 && authStore.hasPermission('delete content')" 
                            variant="destructive" 
                            size="sm" 
                            @click="handleEmptyTrash" 
                        >
                            <Trash2 class="w-4 h-4 mr-2" />
                            {{ t('features.content.actions.emptyTrash') || 'Empty Trash' }}
                        </Button>

                        <!-- Bulk Actions -->
                        <div v-if="selectedContents.length > 0" class="flex items-center gap-2 animate-in fade-in slide-in-from-right-2 mr-2">
                             <div class="flex items-center gap-3 p-1.5 px-3 rounded-lg bg-primary/5 border border-primary/10">
                                <span class="text-sm font-medium text-primary whitespace-nowrap">
                                    {{ selectedContents.length }} selected
                                </span>
                                <div class="h-4 w-px bg-primary/20"></div>
                                <Select
                                    v-model="bulkAction"
                                    @update:model-value="(val: string) => handleBulkAction(val)"
                                >
                                    <SelectTrigger class="w-[140px] h-8 border-primary/20">
                                        <SelectValue placeholder="Bulk Action" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <template v-if="statusFilter !== 'trashed'">
                                            <SelectItem value="approve" class="text-success focus:text-success" v-if="authStore.hasPermission('approve content')">
                                                {{ $t('features.content.actions.approve') }}
                                            </SelectItem>
                                            <SelectItem value="reject" class="text-destructive focus:text-destructive" v-if="authStore.hasPermission('approve content')">
                                                {{ $t('features.content.actions.reject') }}
                                            </SelectItem>
                                            <SelectItem value="delete" class="text-destructive focus:text-destructive" v-if="authStore.hasPermission('delete content')">
                                                {{ $t('common.actions.delete') }}
                                            </SelectItem>
                                        </template>
                                        <template v-else>
                                            <SelectItem value="restore" class="text-success focus:text-success" v-if="authStore.hasPermission('delete content')">
                                                {{ $t('common.actions.restore') }}
                                            </SelectItem>
                                            <SelectItem value="force_delete" class="text-destructive focus:text-destructive" v-if="authStore.hasPermission('delete content')">
                                                {{ $t('common.actions.deletePermanently') }}
                                            </SelectItem>
                                        </template>
                                    </SelectContent>
                                </Select>
                            </div>
                        </div>

                        <!-- Create Button -->
                        <Button v-if="isEmbedded && authStore.hasPermission('create content')" size="sm" @click="router.push({ name: 'contents.create' })">
                            <Plus class="w-4 h-4 mr-1" />
                            {{ $t('features.content.list.createNew') }}
                        </Button>
                    </div>
                </div>
            </div>

            <!-- Table -->
            <div class="relative overflow-x-auto">
                <Table>
                    <TableHeader>
                        <TableRow class="hover:bg-muted/30 border-b border-border/40">
                            <TableHead class="w-12 px-6">
                                <Checkbox
                                    :checked="allSelected"
                                    @update:checked="toggleSelectAll"
                                />
                            </TableHead>
                            <TableHead class="px-6 py-4 text-[10px] font-bold text-muted-foreground/70 tracking-wider">{{ t('common.labels.title') }}</TableHead>
                            <TableHead class="px-6 py-4 text-[10px] font-bold text-muted-foreground/70 tracking-wider">{{ t('common.labels.author') }}</TableHead>
                            <TableHead class="px-6 py-4 text-[10px] font-bold text-muted-foreground/70 tracking-wider">{{ t('common.labels.status') }}</TableHead>
                            <TableHead class="px-6 py-4 text-[10px] font-bold text-muted-foreground/70 tracking-wider">{{ t('features.content.form.featured') }}</TableHead>
                            <TableHead class="px-6 py-4 text-[10px] font-bold text-muted-foreground/70 tracking-wider">{{ t('common.labels.date') }}</TableHead>
                            <TableHead class="px-6 py-4 text-[10px] font-bold text-muted-foreground/70 tracking-wider">{{ t('common.labels.type') }}</TableHead>
                            <TableHead class="px-6 py-4 text-center text-[10px] font-bold text-muted-foreground/70 tracking-wider">{{ t('common.actions.title') }}</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-for="content in contents" :key="content.id" class="group hover:bg-muted/30 border-b border-border/40">
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
                                        <Badge v-if="content.deleted_at" variant="destructive" class="h-4.5 text-[9px] px-1.5 font-bold tracking-wider">
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
                                <Badge variant="outline" :class="getStatusBadgeClass(content.status || '')" class="capitalize border-none px-2 py-0.5">
                                    {{ $t(`features.content.status.${content.status}`) }}
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
                            <TableCell class="px-6 py-4">
                                <div class="flex items-center gap-1.5">
                                    <component :is="content.editor_type === 'builder' ? Layout : Type" class="w-3.5 h-3.5 text-muted-foreground" />
                                    <span class="text-xs capitalize text-muted-foreground">{{ content.editor_type === 'builder' ? 'Builder' : $t('features.content.form.useDefault').replace($t('features.content.form.useDefault').split(' ')[0], '').trim() }}</span>
                                </div>
                            </TableCell>
                            <TableCell class="px-6 py-4 text-right">
                                <div class="flex justify-end items-center gap-1">
                                    <template v-if="content.deleted_at">
                                        <Button variant="ghost" size="icon" class="h-8 w-8 text-success" @click="handleRestore(content)" v-if="authStore.hasPermission('delete content')" :title="t('common.actions.restore')">
                                            <RotateCcw class="w-4 h-4" />
                                        </Button>
                                        <Button variant="ghost" size="icon" class="h-8 w-8 text-destructive" @click="handleForceDelete(content)" v-if="authStore.hasPermission('delete content')" :title="t('common.actions.deletePermanently')">
                                            <Trash2 class="w-4 h-4" />
                                        </Button>
                                    </template>
                                    <template v-else>
                                        <Button variant="ghost" size="icon" class="h-8 w-8" @click="handleEdit(content)" v-if="authStore.hasPermission('edit content')" :title="t('common.actions.edit')">
                                            <Pencil class="w-4 h-4" />
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
                    @page-change="fetchContents"
                    @update:per-page="(val) => { perPage = String(val); fetchContents(1); }"
                />
            </div>
        </Card>
    </div>
</template>
