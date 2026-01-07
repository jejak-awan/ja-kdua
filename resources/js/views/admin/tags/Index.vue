<template>
    <div>
        <!-- Header -->
        <div class="mb-6 flex justify-between items-center">
            <h1 class="text-2xl font-bold text-foreground">{{ $t('features.tags.title') }}</h1>
            <router-link
                :to="{ name: 'tags.create' }"
                v-if="authStore.hasPermission('create tags')"
            >
                <Button>
                    <Plus class="w-4 h-4 mr-2" />
                    {{ $t('features.tags.createNew') }}
                </Button>
            </router-link>
        </div>

        <!-- Statistics -->
        <div v-if="statistics" class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <Card class="bg-card">
                <CardContent class="p-6">
                    <div class="flex items-center">
                        <div class="bg-indigo-100 dark:bg-indigo-900/30 p-2 rounded-lg">
                            <Tag class="h-5 w-5 text-indigo-600 dark:text-indigo-400" />
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
                            <BarChart3 class="h-5 w-5 text-green-600 dark:text-green-400" />
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
                            <MousePointer2 class="h-5 w-5 text-blue-600 dark:text-blue-400" />
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-muted-foreground">{{ $t('features.tags.stats.usage') }}</p>
                            <p class="text-2xl font-semibold text-foreground">{{ statistics.total_usage || 0 }}</p>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>

        <!-- Filters & Bulk Actions -->
        <Card class="mb-6">
            <CardContent class="p-4">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div class="flex flex-1 items-center gap-2 max-w-sm">
                        <div class="relative w-full">
                            <SearchIcon class="absolute left-2.5 top-2.5 h-4 w-4 text-muted-foreground" />
                            <Input
                                v-model="search"
                                type="text"
                                :placeholder="$t('features.tags.search')"
                                class="pl-8"
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
                        <div v-if="selectedIds.length > 0" class="flex items-center gap-2 mr-2">
                            <span class="text-sm text-muted-foreground">{{ selectedIds.length }} {{ $t('common.labels.selected') }}</span>
                            <Button variant="destructive" size="sm" @click="bulkDelete">
                                <Trash2 class="w-4 h-4 mr-2" />
                                {{ $t('common.actions.delete') }}
                            </Button>
                        </div>
                    </div>
                </div>
            </CardContent>
        </Card>

        <!-- Tags List -->
        <div v-if="loading" class="bg-card border border-border rounded-lg p-12 text-center">
            <p class="text-muted-foreground">{{ $t('features.tags.loading') }}</p>
        </div>

        <div v-else-if="tags.length === 0" class="bg-card border border-border rounded-lg p-12 text-center">
            <svg class="mx-auto h-12 w-12 text-muted-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
            </svg>
            <p class="mt-4 text-muted-foreground">{{ $t('features.tags.empty') }}</p>
        </div>

        <Card v-else>
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
                            <TableHead>{{ $t('features.tags.table.name') }}</TableHead>
                            <TableHead class="hidden md:table-cell">{{ $t('features.tags.table.slug') }}</TableHead>
                            <TableHead class="hidden lg:table-cell">{{ $t('features.tags.table.description') }}</TableHead>
                            <TableHead class="text-right">{{ $t('features.tags.table.usage') }}</TableHead>
                            <TableHead class="w-[100px] text-right">{{ $t('features.tags.table.actions') }}</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-for="tag in tags" :key="tag.id" :class="{ 'bg-muted/50': selectedIds.includes(tag.id) }">
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
                                    <Button v-if="authStore.hasPermission('edit tags')" variant="ghost" size="icon" @click="editTag(tag)" class="h-8 w-8">
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
    </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { useRouter } from 'vue-router';
import { 
    Tag, 
    BarChart3, 
    MousePointer2, 
    Edit, 
    Trash2, 
    Plus, 
    Search as SearchIcon, 
    Filter,
    Loader2
} from 'lucide-vue-next';
import api from '../../../services/api';
import { useConfirm } from '../../../composables/useConfirm';
import { useToast } from '../../../composables/useToast';
import _ from 'lodash';
import { parseResponse } from '../../../utils/responseParser';

import Button from '@/components/ui/button.vue';
import Input from '@/components/ui/input.vue';
import Badge from '@/components/ui/badge.vue';
import Checkbox from '@/components/ui/checkbox.vue';
import Card from '@/components/ui/card.vue';
import CardHeader from '@/components/ui/card-header.vue';
import CardContent from '@/components/ui/card-content.vue';
import Pagination from '@/components/ui/pagination.vue';
import Table from '@/components/ui/table.vue';
import TableBody from '@/components/ui/table-body.vue';
import TableCell from '@/components/ui/table-cell.vue';
import TableHead from '@/components/ui/table-head.vue';
import TableHeader from '@/components/ui/table-header.vue';
import TableRow from '@/components/ui/table-row.vue';
import Select from '@/components/ui/select.vue';
import SelectContent from '@/components/ui/select-content.vue';
import SelectItem from '@/components/ui/select-item.vue';
import SelectTrigger from '@/components/ui/select-trigger.vue';
import SelectValue from '@/components/ui/select-value.vue';

import { useAuthStore } from '../../../stores/auth';

const { t } = useI18n();
const { confirm } = useConfirm();
const toast = useToast();
const router = useRouter();
const authStore = useAuthStore();
const loading = ref(true);
const tags = ref([]);
const statistics = ref(null);
const search = ref('');
const filterUsage = ref('all');
const selectedIds = ref([]);
const pagination = ref({
    current_page: 1,
    per_page: 20,
    total: 0,
    last_page: 1,
    from: 0,
    to: 0
});

const onSearchInput = _.debounce(() => {
    fetchTags(1);
}, 500);

const fetchTags = async (page = 1) => {
    loading.value = true;
    try {
        const params = {
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

const changePage = (page) => {
    if (page >= 1 && page <= (pagination.value.last_page || 1)) {
        fetchTags(page);
    }
};

const changePerPage = (value) => {
    pagination.value.per_page = parseInt(value);
    fetchTags(1);
};

const toggleSelection = (id, checked) => {
    if (checked) {
        selectedIds.value.push(id);
    } else {
        selectedIds.value = selectedIds.value.filter(i => i !== id);
    }
};

const toggleAll = (checked) => {
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
    } catch (error) {
        console.error('Bulk delete failed:', error);
        toast.error.action(error);
    }
};

const editTag = (tag) => {
    router.push({ name: 'tags.edit', params: { id: tag.id } });
};

const deleteTag = async (tag) => {
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
    } catch (error) {
        console.error('Failed to delete tag:', error);
        toast.error.delete(error, 'Tag');
    }
};

onMounted(() => {
    fetchTags();
});
</script>
