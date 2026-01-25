<template>
    <div>
        <Card>
            <CardHeader class="pb-10 border-b-0 space-y-4">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <!-- Left: Search / Filters -->
                    <div class="flex items-center gap-3 w-full md:w-auto flex-wrap">
                        <div class="relative w-full md:w-72">
                            <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-muted-foreground" />
                            <Input
                                v-model="search"
                                :placeholder="t('features.content_templates.search')"
                                class="pl-9"
                                @input="handleSearch"
                            />
                        </div>
                        <Select
                            v-model="typeFilter"
                            @update:model-value="fetchTemplates"
                        >
                            <SelectTrigger class="w-[140px]">
                                <SelectValue placeholder="All Types" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">All Types</SelectItem>
                                <SelectItem value="post">Post</SelectItem>
                                <SelectItem value="page">Page</SelectItem>
                                <SelectItem value="custom">Custom</SelectItem>
                            </SelectContent>
                        </Select>
                         <Select
                            v-model="trashedFilter"
                            @update:model-value="fetchTemplates"
                        >
                            <SelectTrigger class="w-[140px]">
                                <SelectValue :placeholder="t('common.labels.status')" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="without">{{ t('common.labels.activeOnly') }}</SelectItem>
                                <SelectItem value="with">{{ t('common.labels.includesTrashed') }}</SelectItem>
                                <SelectItem value="only">{{ t('common.labels.trashedOnly') }}</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>

                    <!-- Right: Actions -->
                    <div class="flex items-center gap-2">
                        <div v-if="selectedTemplates.length > 0" class="flex items-center gap-3 p-1.5 px-3 rounded-lg bg-primary/5 border border-primary/10 animate-in fade-in slide-in-from-top-1 mr-2">
                            <span class="text-sm font-medium text-primary">
                                {{ selectedTemplates.length }} selected
                            </span>
                            <div class="h-4 w-px bg-primary/20"></div>
                            <Select
                                v-model="bulkAction"
                                @update:model-value="handleBulkAction"
                            >
                                <SelectTrigger class="w-[140px] h-8 border-primary/20">
                                    <SelectValue placeholder="Bulk Action" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="delete" class="text-destructive focus:text-destructive">Delete Selected</SelectItem>
                                    <SelectItem value="restore" class="text-emerald-600 focus:text-emerald-600">Restore Selected</SelectItem>
                                    <SelectItem value="force_delete" class="text-destructive focus:text-destructive">Force Delete Selected</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>

                        <!-- Create Button -->
                        <Button v-if="isEmbedded && authStore.hasPermission('manage content templates')" as-child size="sm" class="shadow-sm">
                            <router-link :to="{ name: 'content-templates.create' }" class="flex items-center">
                                <Plus class="w-4 h-4 mr-1" />
                                {{ t('features.content_templates.create') }}
                            </router-link>
                        </Button>
                    </div>
                </div>
            </CardHeader>
            <CardContent class="p-0">
                <div v-if="loading && templates.length === 0" class="p-12 text-center">
                    <Loader2 class="w-8 h-8 animate-spin mx-auto text-muted-foreground mb-4" />
                    <p class="text-muted-foreground font-medium">{{ t('features.content_templates.loading') }}</p>
                </div>

                <div v-else-if="templates.length === 0" class="p-12 text-center">
                    <FileText class="w-12 h-12 mx-auto text-muted-foreground/20 mb-4" />
                    <p class="text-muted-foreground font-medium">{{ t('features.content_templates.empty') }}</p>
                </div>

                <div v-else class="relative overflow-x-auto">
                    <Table>
                        <TableHeader class="[&_tr]:border-b-0">
                            <TableRow class="bg-muted/50 hover:bg-muted/50 border-b-0 !border-b-0 [&>th]:border-b-0">
                                <TableHead class="w-12 px-6">
                                    <Checkbox
                                        :checked="allSelected"
                                        @update:checked="toggleSelectAll"
                                    />
                                </TableHead>
                                <TableHead class="font-semibold text-foreground">{{ t('features.content_templates.table.name') }}</TableHead>
                                <TableHead class="font-semibold text-foreground">{{ t('features.content_templates.table.type') }}</TableHead>
                                <TableHead class="font-semibold text-foreground">{{ t('features.content_templates.table.description') }}</TableHead>
                                <TableHead class="font-semibold text-foreground">{{ t('features.content_templates.table.updated') }}</TableHead>
                                <TableHead class="text-center font-semibold text-foreground">{{ t('features.content_templates.table.actions') }}</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="template in templates" :key="template.id" class="hover:bg-muted/50 group border-b-0 [&>td]:border-b-0 text-muted-foreground">
                                <TableCell class="px-6">
                                    <Checkbox
                                        :checked="selectedTemplates.includes(template.id)"
                                        @update:checked="(checked) => toggleSelection(template.id, checked)"
                                    />
                                </TableCell>
                                <TableCell>
                                    <div class="flex items-center gap-2 text-sm font-medium text-foreground">
                                        {{ template.name }}
                                        <Badge v-if="template.deleted_at" variant="destructive" class="h-4.5 text-[10px] px-1.5 uppercase font-bold tracking-wider">
                                            {{ t('common.labels.deleted') }}
                                        </Badge>
                                    </div>
                                </TableCell>
                                <TableCell>
                                    <Badge variant="secondary" class="capitalize">
                                        {{ template.type || 'post' }}
                                    </Badge>
                                </TableCell>
                                <TableCell>
                                    <div class="text-sm truncate max-w-xs" :title="template.description">
                                        {{ template.description || '-' }}
                                    </div>
                                </TableCell>
                                <TableCell class="text-sm">
                                    {{ formatDate(template.updated_at) }}
                                </TableCell>
                                <TableCell class="text-center">
                                    <div class="flex justify-center gap-1">
                                        <Button
                                            variant="ghost"
                                            size="icon"
                                            @click="createFromTemplate(template)"
                                            :title="t('features.content_templates.actions.createContent')"
                                            class="h-8 w-8 text-emerald-600 dark:text-emerald-400 hover:text-emerald-700 dark:hover:text-emerald-300 hover:bg-emerald-500/10"
                                        >
                                            <CopyPlus class="w-4 h-4" />
                                        </Button>
                                        <Button
                                            variant="ghost"
                                            size="icon"
                                            as-child
                                            class="h-8 w-8 text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300 hover:bg-indigo-500/10"
                                        >
                                            <router-link :to="{ name: 'content-templates.edit', params: { id: template.id } }">
                                                <Pencil class="w-4 h-4" />
                                            </router-link>
                                        </Button>
                                        <Button
                                            v-if="template.deleted_at"
                                            variant="ghost"
                                            size="icon"
                                            @click="handleRestore(template)"
                                            :title="t('common.actions.restore')"
                                            class="h-8 w-8 text-emerald-600 hover:text-emerald-700 hover:bg-emerald-500/10"
                                        >
                                            <RotateCcw class="w-4 h-4" />
                                        </Button>
                                        <Button
                                            variant="ghost"
                                            size="icon"
                                            @click="handleDelete(template)"
                                            :title="template.deleted_at ? t('common.actions.forceDelete') : t('features.content_templates.actions.delete')"
                                            class="h-8 w-8 text-destructive hover:text-destructive hover:bg-destructive/10"
                                        >
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
                    @page-change="fetchTemplates"
                    @update:per-page="(val) => { perPage = String(val); fetchTemplates(1); }"
                    class="border-none shadow-none mt-4 px-6 py-4"
                />
            </CardContent>
        </Card>
    </div>
</template>

<script setup>
import { ref, onMounted, computed, watch } from 'vue';
import { useRouter } from 'vue-router';
import { useI18n } from 'vue-i18n';
import api from '../../../../services/api';
import { useConfirm } from '../../../../composables/useConfirm';
import { useToast } from '../../../../composables/useToast';
import { parseResponse, ensureArray, parseSingleResponse } from '../../../../utils/responseParser';
import { debounce } from '@/utils/debounce';
import { useAuthStore } from '@/stores/auth';
import Card from '../../../../components/ui/card.vue';
import CardHeader from '../../../../components/ui/card-header.vue';
import CardContent from '../../../../components/ui/card-content.vue';
import Pagination from '../../../../components/ui/pagination.vue';
import Button from '../../../../components/ui/button.vue';
import Input from '../../../../components/ui/input.vue';
import Table from '../../../../components/ui/table.vue';
import TableHeader from '../../../../components/ui/table-header.vue';
import TableBody from '../../../../components/ui/table-body.vue';
import TableRow from '../../../../components/ui/table-row.vue';
import TableCell from '../../../../components/ui/table-cell.vue';
import TableHead from '../../../../components/ui/table-head.vue';
import Badge from '../../../../components/ui/badge.vue';
import Checkbox from '../../../../components/ui/checkbox.vue';
import Select from '../../../../components/ui/select.vue';
import SelectTrigger from '../../../../components/ui/select-trigger.vue';
import SelectValue from '../../../../components/ui/select-value.vue';
import SelectContent from '../../../../components/ui/select-content.vue';
import SelectItem from '../../../../components/ui/select-item.vue';
import {
    Plus,
    Search,
    FileText,
    Pencil,
    Trash2,
    CopyPlus,
    Loader2,
    RotateCcw,
    ChevronLeft
} from 'lucide-vue-next';

const { t } = useI18n();
const { confirm } = useConfirm();
const toast = useToast();
const router = useRouter();
const authStore = useAuthStore();

const props = defineProps({
    isEmbedded: {
        type: Boolean,
        default: false
    }
});
const templates = ref([]);
const loading = ref(false);
const search = ref('');
const typeFilter = ref('all');
const trashedFilter = ref('without');
const pagination = ref(null);
const perPage = ref('10');
const selectedTemplates = ref([]);
const bulkAction = ref('');

const allSelected = computed(() => {
    return templates.value.length > 0 && selectedTemplates.value.length === templates.value.length;
});

const handleSearch = debounce(() => {
    fetchTemplates(1);
}, 300);

const fetchTemplates = async (page = 1) => {
    loading.value = true;
    try {
        const params = {
            page,
            per_page: perPage.value,
            // If 'all', send specific classic types to filter on server
            type: typeFilter.value !== 'all' ? typeFilter.value : 'post,page,custom',
            search: search.value,
            trashed: trashedFilter.value !== 'without' ? trashedFilter.value : undefined
        };

        const response = await api.get('/admin/ja/content-templates', { params });
        const { data, pagination: pag } = parseResponse(response);
        
        templates.value = ensureArray(data);
        pagination.value = pag;
        selectedTemplates.value = []; // Reset selection on page change
    } catch (error) {
        console.error('Failed to fetch templates:', error);
        templates.value = [];
    } finally {
        loading.value = false;
    }
};

const createFromTemplate = async (template) => {
    try {
        const response = await api.post(`/admin/ja/content-templates/${template.id}/create-content`);
        const content = parseSingleResponse(response);
        if (content && content.id) {
            toast.success.createFromTemplate();
            router.push({ name: 'contents.edit', params: { id: content.id } });
        }
    } catch (error) {
        console.error('Failed to create content from template:', error);
        toast.error.templateCreateContent(error);
    }
};

const handleDelete = async (template) => {
    const isTrashed = !!template.deleted_at;
    const confirmed = await confirm({
        title: isTrashed ? t('common.actions.forceDelete') : t('features.content_templates.actions.delete'),
        message: isTrashed 
            ? `Are you sure you want to PERMANENTLY delete ${template.name}?`
            : t('features.content_templates.messages.deleteConfirm', { name: template.name }),
        variant: 'danger',
        confirmText: isTrashed ? t('common.actions.forceDelete') : t('common.actions.delete'),
    });

    if (!confirmed) return;

    try {
        if (isTrashed) {
            await api.delete(`/admin/ja/content-templates/${template.id}/force-delete`);
            toast.success.action(t('common.messages.success.deleted', { item: 'Template' }));
        } else {
            await api.delete(`/admin/ja/content-templates/${template.id}`);
            toast.success.delete('Template');
        }
        await fetchTemplates(pagination.value?.current_page || 1);
    } catch (error) {
        console.error('Failed to delete template:', error);
        toast.error.delete(error, 'Template');
    }
};

const handleRestore = async (template) => {
    const confirmed = await confirm({
        title: t('common.actions.restore'),
        message: `Restore ${template.name}?`,
        variant: 'info',
        confirmText: t('common.actions.restore'),
    });

    if (!confirmed) return;

    try {
        await api.post(`/admin/ja/content-templates/${template.id}/restore`);
        toast.success.restore('Template');
        await fetchTemplates(pagination.value?.current_page || 1);
    } catch (error) {
        console.error('Failed to restore template:', error);
        toast.error.fromResponse(error);
    }
};

const toggleSelectAll = (checked) => {
    if (checked) {
        selectedTemplates.value = templates.value.map(t => t.id);
    } else {
        selectedTemplates.value = [];
    }
};

const toggleSelection = (id, checked) => {
    if (checked) {
        selectedTemplates.value.push(id);
    } else {
        selectedTemplates.value = selectedTemplates.value.filter(tId => tId !== id);
    }
};

const handleBulkAction = async () => {
    if (!bulkAction.value || selectedTemplates.value.length === 0) return;

    const action = bulkAction.value;
    const count = selectedTemplates.value.length;

    if (action === 'delete' || action === 'force_delete') {
        const isForce = action === 'force_delete';
        const confirmed = await confirm({
            title: isForce ? t('common.actions.forceDelete') : t('features.content_templates.actions.bulkDelete'),
            message: isForce 
                ? `Are you sure you want to PERMANENTLY delete ${count} templates?`
                : `Are you sure you want to move ${count} templates to trash?`,
            variant: 'danger',
            confirmText: isForce ? t('common.actions.forceDelete') : t('common.actions.delete'),
        });

        if (!confirmed) {
            bulkAction.value = '';
            return;
        }

        try {
            await api.post('/admin/ja/content-templates/bulk-action', {
                action: action,
                ids: selectedTemplates.value
            });
            await fetchTemplates(pagination.value?.current_page || 1);
            bulkAction.value = '';
            toast.success.delete('Templates');
        } catch (error) {
            console.error('Bulk action failed:', error);
            toast.error.action(error);
        }
    } else if (action === 'restore') {
        try {
            await api.post('/admin/ja/content-templates/bulk-action', {
                action: 'restore',
                ids: selectedTemplates.value
            });
            await fetchTemplates(pagination.value?.current_page || 1);
            bulkAction.value = '';
            toast.success.restore('Templates');
        } catch (error) {
            console.error('Bulk action failed:', error);
            toast.error.action(error);
        }
    }
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString();
};

onMounted(() => {
    fetchTemplates();
});
</script>

