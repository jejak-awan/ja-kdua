<template>
    <div>
        <!-- Header -->
        <div v-if="!isEmbedded" class="mb-6 flex justify-between items-center">
            <h1 class="text-2xl font-bold text-foreground">{{ $t('features.tags.title') }}</h1>
            <div class="flex space-x-3">
                <Button 
                    v-if="authStore.hasPermission('create tags')"
                    @click="openCreateModal"
                >
                    <Plus class="w-4 h-4 mr-2" />
                    {{ $t('features.tags.createNew') }}
                </Button>
            </div>
        </div>

        <!-- Statistics -->
        <div v-if="statistics && !isEmbedded" class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <Card class="bg-card">
                <CardContent class="p-6">
                    <div class="flex items-center">
                        <div class="bg-indigo-100 dark:bg-indigo-900/30 p-2 rounded-lg">
                            <Tag class="h-5 w-5 text-primary" />
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-muted-foreground">{{ $t('features.tags.stats.total') }}</p>
                            <p class="text-2xl font-semibold text-foreground">{{ statistics.total_tags || 0 }}</p>
                        </div>
                    </div>
                </CardContent>
            </Card>
            <Card class="bg-card">
                <CardContent class="p-6">
                    <div class="flex items-center">
                        <div class="bg-green-100 dark:bg-green-900/30 p-2 rounded-lg">
                            <BarChart3 class="h-5 w-5 text-success" />
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-muted-foreground">{{ $t('features.tags.stats.used') }}</p>
                            <p class="text-2xl font-semibold text-foreground">{{ statistics.used_tags || 0 }}</p>
                        </div>
                    </div>
                </CardContent>
            </Card>
            <Card class="bg-card">
                <CardContent class="p-6">
                    <div class="flex items-center">
                        <div class="bg-blue-100 dark:bg-blue-900/30 p-2 rounded-lg">
                            <MousePointer2 class="h-5 w-5 text-info" />
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-muted-foreground">{{ $t('features.tags.stats.usage') }}</p>
                            <p class="text-2xl font-semibold text-foreground">{{ statistics.total_usage || 0 }}</p>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>

        <!-- content -->
        <Card class="overflow-hidden">
            <!-- Filters & Bulk Actions -->
            <div class="px-6 py-4 border-b border-border/40">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div class="flex flex-1 items-center gap-2 max-w-sm flex-wrap md:flex-nowrap">
                        <div class="relative w-full">
                            <SearchIcon class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" />
                            <Input
                                v-model="search"
                                type="text"
                                :placeholder="$t('features.tags.search')"
                                class="pl-9"
                                @input="onSearchInput"
                            />
                        </div>
                        <Select v-model="filterUsage" @update:model-value="fetchTags(1)">
                            <SelectTrigger class="w-[180px]">
                                <SelectValue :placeholder="$t('features.tags.filters.usage')" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">{{ $t('features.tags.filters.all') }}</SelectItem>
                                <SelectItem value="used">{{ $t('features.tags.filters.used') }}</SelectItem>
                                <SelectItem value="unused">{{ $t('features.tags.filters.unused') }}</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>

                    <div class="flex items-center gap-2">
                        <div v-if="selectedIds.length > 0" class="flex items-center gap-3 p-1.5 px-3 rounded-lg bg-primary/5 border border-primary/10 animate-in fade-in slide-in-from-right-2 mr-2">
                            <span class="text-xs font-semibold text-primary whitespace-nowrap uppercase tracking-wider">{{ selectedIds.length }} {{ $t('common.labels.selected') }}</span>
                            <div class="h-4 w-px bg-primary/20"></div>
                            <Button variant="ghost" size="sm" class="h-7 px-2 text-destructive hover:bg-destructive/10" @click="bulkDelete">
                                <Trash2 class="w-4 h-4 mr-1.5" />
                                {{ $t('common.actions.delete') }}
                            </Button>
                        </div>

                        <!-- Add Create Button here when embedded -->
                        <Button 
                            v-if="isEmbedded && authStore.hasPermission('create tags')"
                            size="sm"
                            @click="openCreateModal"
                        >
                            <Plus class="w-4 h-4 mr-1" />
                            {{ $t('features.tags.createNew') }}
                        </Button>
                    </div>
                </div>
            </div>

            <CardContent class="p-0">
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead class="w-[50px]">
                                <Checkbox 
                                    v-if="authStore.hasPermission('delete tags')"
                                    :checked="selectedIds.length === tags.length && tags.length > 0"
                                    @update:checked="toggleAll"
                                />
                            </TableHead>
                            <TableHead class="text-xs text-muted-foreground/70">{{ $t('features.tags.table.name') }}</TableHead>
                            <TableHead class="hidden md:table-cell text-xs text-muted-foreground/70">{{ $t('features.tags.table.slug') }}</TableHead>
                            <TableHead class="hidden lg:table-cell text-xs text-muted-foreground/70">{{ $t('features.tags.table.description') }}</TableHead>
                            <TableHead class="text-right text-xs text-muted-foreground/70">{{ $t('features.tags.table.usage') }}</TableHead>
                            <TableHead class="w-[100px] text-right text-xs text-muted-foreground/70">{{ $t('features.tags.table.actions') }}</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-if="loading">
                            <TableCell colspan="6" class="h-24 text-center">
                                <Loader2 class="h-6 w-6 animate-spin mx-auto" />
                            </TableCell>
                        </TableRow>
                        
                        <TableRow v-else-if="tags.length === 0">
                            <TableCell colspan="6" class="h-32 text-center">
                                <div class="flex flex-col items-center justify-center space-y-2">
                                    <TagIcon class="h-8 w-8 text-muted-foreground/20" />
                                    <p class="text-sm text-muted-foreground">{{ $t('features.tags.empty') }}</p>
                                </div>
                            </TableCell>
                        </TableRow>

                        <TableRow v-else v-for="tag in tags" :key="tag.id" :class="cn('group', selectedIds.includes(tag.id) ? 'bg-muted/50' : '')">
                            <TableCell>
                                <Checkbox 
                                    v-if="authStore.hasPermission('delete tags')"
                                    :checked="selectedIds.includes(tag.id)"
                                    @update:checked="(val) => toggleSelection(tag.id, val)"
                                />
                            </TableCell>
                            <TableCell class="font-medium">
                                {{ tag.name }}
                                <div class="md:hidden text-xs text-muted-foreground mt-1">{{ tag.slug }}</div>
                            </TableCell>
                            <TableCell class="hidden md:table-cell text-muted-foreground">{{ tag.slug }}</TableCell>
                            <TableCell class="hidden lg:table-cell max-w-[300px] truncate" :title="tag.description">
                                {{ tag.description || '-' }}
                            </TableCell>
                            <TableCell class="text-right">
                                <Badge variant="secondary" class="font-mono">{{ tag.contents_count || 0 }}</Badge>
                            </TableCell>
                            <TableCell>
                                <div class="flex justify-end gap-1">
                                    <Button v-if="authStore.hasPermission('edit tags')" variant="ghost" size="icon" @click="openEditModal(tag)" class="h-8 w-8">
                                        <Edit class="w-4 h-4" />
                                    </Button>
                                    <Button v-if="authStore.hasPermission('delete tags')" variant="ghost" size="icon" class="h-8 w-8 text-destructive hover:text-destructive hover:bg-destructive/10" @click="deleteTag(tag)">
                                        <Trash2 class="w-4 h-4" />
                                    </Button>
                                </div>
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </CardContent>
            
            <!-- Pagination -->
            <Pagination
                v-if="pagination && pagination.total > 0"
                :current-page="pagination.current_page"
                :total-items="pagination.total"
                :per-page="Number(pagination.per_page)"
                @page-change="changePage"
                @update:per-page="changePerPage"
                class="border-none shadow-none"
            />
        </Card>

        <!-- Tag Modal -->
        <TagFormModal
            v-model:open="showModal"
            :tag="editingTag"
            @success="handleModalSuccess"
        />
    </div>
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { useRouter } from 'vue-router';
import { 
    Tag as TagIcon, 
    BarChart3, 
    MousePointer2, 
    Edit, 
    Trash2, 
    Plus, 
    Search as SearchIcon, 
    Filter,
    Loader2
} from 'lucide-vue-next';
import api from '@/services/api';
import { useConfirm } from '@/composables/useConfirm';
import { useToast } from '@/composables/useToast';
import { debounce } from '@/utils/debounce';
import { parseResponse } from '@/utils/responseParser';
import { cn } from '@/lib/utils';
import TagFormModal from './TagFormModal.vue';

// @ts-ignore
import Button from '@/components/ui/button.vue';
// @ts-ignore
import Input from '@/components/ui/input.vue';
// @ts-ignore
import Badge from '@/components/ui/badge.vue';
// @ts-ignore
import Checkbox from '@/components/ui/checkbox.vue';
// @ts-ignore
import Card from '@/components/ui/card.vue';
// @ts-ignore
import CardHeader from '@/components/ui/card-header.vue';
// @ts-ignore
import CardContent from '@/components/ui/card-content.vue';
// @ts-ignore
import Pagination from '@/components/ui/pagination.vue';
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

import { useAuthStore } from '@/stores/auth';
import type { Tag } from '@/types/cms';

const props = defineProps({
    isEmbedded: {
        type: Boolean,
        default: false
    }
});

const { t } = useI18n();
const { confirm } = useConfirm();
const toast = useToast();
const router = useRouter();
const authStore = useAuthStore();
const loading = ref(true);
const tags = ref<Tag[]>([]);
const statistics = ref<any>(null);
const search = ref('');
const filterUsage = ref('all');
const selectedIds = ref<number[]>([]);
const pagination = ref<any>({
    current_page: 1,
    per_page: 20,
    total: 0,
    last_page: 1,
    from: 0,
    to: 0
});

// Modal State
const showModal = ref(false);
const editingTag = ref<Tag | null>(null);

const onSearchInput = debounce(() => {
    fetchTags(1);
}, 500);

const fetchTags = async (page = 1) => {
    loading.value = true;
    try {
        const params: any = {
            page: page,
            per_page: pagination.value.per_page,
            search: search.value,
            usage: filterUsage.value !== 'all' ? filterUsage.value : undefined
        };

        const response = await api.get('/admin/ja/tags', { params });
        const { data, pagination: meta } = parseResponse(response);
        
        tags.value = data;
        if (meta) {
            pagination.value = {
                ...pagination.value,
                ...meta
            };
        }

        // Fetch statistics
        try {
            const statsResponse = await api.get('/admin/ja/tags/statistics');
            statistics.value = statsResponse.data.data || statsResponse.data;
        } catch (error) {
            // Fallback: simple stats from current page or rough estimate
            if (!statistics.value) {
                statistics.value = {
                    total_tags: pagination.value.total || tags.value.length,
                    used_tags: tags.value.filter(t => (t.contents_count || 0) > 0).length,
                    total_usage: tags.value.reduce((sum, t) => sum + (t.contents_count || 0), 0),
                };
            }
        }
    } catch (error) {
        console.error('Failed to fetch tags:', error);
    } finally {
        loading.value = false;
    }
};

const changePage = (page: number) => {
    if (page >= 1 && page <= (pagination.value.last_page || 1)) {
        fetchTags(page);
    }
};

const changePerPage = (value: string | number) => {
    pagination.value.per_page = typeof value === 'string' ? parseInt(value) : value;
    fetchTags(1);
};

const toggleSelection = (id: number, checked: boolean) => {
    if (checked) {
        selectedIds.value.push(id);
    } else {
        selectedIds.value = selectedIds.value.filter(i => i !== id);
    }
};

const toggleAll = (checked: boolean) => {
    if (checked) {
        selectedIds.value = tags.value.map(t => t.id);
    } else {
        selectedIds.value = [];
    }
};

const bulkDelete = async () => {
    if (selectedIds.value.length === 0) return;
    
    const confirmed = await confirm({
        title: t('features.tags.actions.bulkDelete'),
        message: t('common.messages.confirm.bulkDelete', { count: selectedIds.value.length }),
        variant: 'danger',
        confirmText: t('common.actions.delete'),
    });

    if (!confirmed) return;

    try {
        await api.post('/admin/ja/tags/bulk-delete', { ids: selectedIds.value });
        selectedIds.value = [];
        await fetchTags(pagination.value.current_page);
        toast.success.delete('Tags');
    } catch (error: any) {
        console.error('Bulk delete failed:', error);
        toast.error.action(error);
    }
};

// Modal Actions
const openCreateModal = () => {
    editingTag.value = null;
    showModal.value = true;
};

const openEditModal = (tag: Tag) => {
    editingTag.value = { ...tag };
    showModal.value = true;
};

const handleModalSuccess = () => {
    fetchTags(pagination.value.current_page);
};

// editTag replaced by openEditModal

const deleteTag = async (tag: Tag) => {
    const confirmed = await confirm({
        title: t('features.tags.actions.delete'),
        message: t('features.tags.messages.deleteConfirm', { name: tag.name }),
        variant: 'danger',
        confirmText: t('common.actions.delete'),
    });

    if (!confirmed) return;

    try {
        await api.delete(`/admin/ja/tags/${tag.id}`);
        await fetchTags();
        toast.success.delete('Tag');
    } catch (error: any) {
        console.error('Failed to delete tag:', error);
        toast.error.delete(error, 'Tag');
    }
};

onMounted(() => {
    fetchTags();
});
</script>
