<template>
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h1 class="text-3xl font-bold tracking-tight text-foreground">{{ $t('features.categories.title') }}</h1>
                <p class="text-muted-foreground mt-2">{{ $t('features.categories.description') }}</p>
            </div>
            <div class="flex space-x-3">
                <router-link :to="{ name: 'categories.create' }" v-if="authStore.hasPermission('create categories')">
                    <Button>
                        <Plus class="w-4 h-4 mr-2" />
                        {{ $t('features.categories.createNew') }}
                    </Button>
                </router-link>
            </div>
        </div>

        <!-- content -->
        <Card>
            <CardHeader class="pb-3 border-b border-border">
                <div class="flex flex-col sm:flex-row justify-between items-center gap-4">
                    <!-- Actions / Search / Filter -->
                    <div class="flex items-center gap-2 flex-1 flex-wrap">
                        <div class="relative w-full sm:w-72">
                            <Search class="absolute left-2.5 top-2.5 h-4 w-4 text-muted-foreground" />
                            <Input
                                v-model="search"
                                :placeholder="$t('features.categories.search')"
                                class="pl-9"
                            />
                        </div>
                        <!-- Status Filter -->
                        <Select v-model="statusFilter" @update:model-value="onFilterChange">
                            <SelectTrigger class="w-[140px]">
                                <Filter class="w-4 h-4 mr-2 text-muted-foreground" />
                                <SelectValue :placeholder="$t('common.labels.status')" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">{{ $t('common.labels.all') }}</SelectItem>
                                <SelectItem value="active">{{ $t('features.categories.status.active') }}</SelectItem>
                                <SelectItem value="inactive">{{ $t('features.categories.status.inactive') }}</SelectItem>
                            </SelectContent>
                        </Select>
                        <!-- Bulk Actions -->
                        <div v-if="selectedIds.length > 0" class="flex items-center gap-2 animate-in fade-in slide-in-from-left-2">
                             <div class="h-6 w-px bg-border mx-2"></div>
                             <span class="text-sm text-muted-foreground whitespace-nowrap">
                                {{ selectedIds.length }} {{ $t('common.labels.selected') }}
                             </span>
                             <Button variant="destructive" size="sm" @click="confirmBulkDelete">
                                <Trash2 class="w-4 h-4 mr-2" />
                                {{ $t('common.actions.delete') }}
                            </Button>
                        </div>
                    </div>
                </div>
            </CardHeader>
            <CardContent class="p-0">
                <div class="rounded-md border-0">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead class="w-[40px] text-center">
                                    <Checkbox 
                                        v-if="authStore.hasPermission('delete categories')"
                                        :checked="isAllSelected"
                                        @update:checked="toggleSelectAll"
                                    />
                                </TableHead>
                                <TableHead>{{ $t('features.categories.table.name') }}</TableHead>
                                <TableHead>{{ $t('features.categories.table.slug') }}</TableHead>
                                <TableHead>{{ $t('features.categories.table.status') }}</TableHead>
                                <TableHead class="text-center">{{ $t('features.categories.table.actions') }}</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-if="loading">
                                <TableCell colspan="5" class="h-24 text-center">
                                    <Loader2 class="h-6 w-6 animate-spin mx-auto" />
                                </TableCell>
                            </TableRow>
                            
                            <TableRow v-else-if="flatCategories.length === 0">
                                <TableCell colspan="5" class="h-24 text-center text-muted-foreground">
                                    {{ $t('features.categories.empty') }}
                                </TableCell>
                            </TableRow>

                            <TableRow 
                                v-for="category in flatCategories" 
                                :key="category.id"
                                class="group hover:bg-muted/50"
                            >
                                <TableCell class="text-center">
                                    <Checkbox 
                                        v-if="authStore.hasPermission('delete categories')"
                                        :checked="selectedIds.includes(category.id)" 
                                        @update:checked="(checked) => toggleSelect(checked, category.id)"
                                    />
                                </TableCell>
                                <TableCell>
                                    <div :style="{ paddingLeft: `${category._depth * 24}px` }" class="flex items-center">
                                        <Button 
                                            v-if="hasChildren(category)"
                                            variant="ghost"
                                            size="icon"
                                            class="h-6 w-6 mr-2 shrink-0"
                                            @click.stop="toggleExpand(category.id)"
                                        >
                                            <ChevronRight 
                                                class="w-4 h-4 transition-transform duration-200"
                                                :class="{ 'rotate-90': expandedIds.includes(category.id) }"
                                            />
                                        </Button>
                                        <span v-else class="w-6 mr-2 shrink-0"></span>
                                        <span class="font-medium">{{ category.name }}</span>
                                    </div>
                                </TableCell>
                                <TableCell class="text-muted-foreground font-mono text-xs">{{ category.slug }}</TableCell>
                                <TableCell>
                                    <Badge :variant="category.is_active ? 'success' : 'secondary'">
                                        {{ category.is_active ? $t('features.categories.status.active') : $t('features.categories.status.inactive') }}
                                    </Badge>
                                </TableCell>
                                <TableCell class="text-center">
                                    <div class="flex justify-center gap-1">
                                        <Button 
                                            v-if="authStore.hasPermission('edit categories')"
                                            variant="ghost" 
                                            size="icon" 
                                            class="h-8 w-8 text-muted-foreground hover:text-foreground" 
                                            @click="editCategory(category)"
                                        >
                                            <Edit2 class="w-4 h-4" />
                                        </Button>
                                        <Button 
                                            v-if="authStore.hasPermission('delete categories')"
                                            variant="ghost" 
                                            size="icon" 
                                            class="h-8 w-8 text-muted-foreground hover:text-destructive hover:bg-destructive/10" 
                                            @click="deleteCategory(category)"
                                        >
                                            <Trash2 class="w-4 h-4" />
                                        </Button>
                                    </div>
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </div>
            </CardContent>
            <Pagination
                v-if="pagination"
                :current-page="pagination.current_page"
                :total-items="pagination.total"
                :per-page="Number(pagination.per_page)"
                :show-page-numbers="true"
                @page-change="changePage"
                @update:per-page="changePerPage"
                class="border-none shadow-none mt-4"
            />
        </Card>
    </div>
</template>

<script setup>
import { ref, onMounted, computed, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import { useRouter } from 'vue-router';
import api from '../../../services/api';
import { 
    Plus, 
    Search, 
    Loader2,
    Trash2,
    Edit2,
    ChevronRight,
    Filter
} from 'lucide-vue-next';
import _ from 'lodash';

// Shadcn UI
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

import { useConfirm } from '../../../composables/useConfirm';
import { useToast } from '../../../composables/useToast';

const { t } = useI18n();
const router = useRouter();
const authStore = useAuthStore();
const toast = useToast();

const loading = ref(true);
const categories = ref([]); // Raw tree data
const search = ref('');
const statusFilter = ref('all');
const selectedIds = ref([]);
const expandedIds = ref([]); // Track expanded nodes
const perPage = ref(5);
const pagination = ref({
    current_page: 1,
    last_page: 1,
    per_page: 5,
    total: 0,
    from: 0,
    to: 0
});

const { confirm } = useConfirm();

// Toggle expand/collapse
const toggleExpand = (id) => {
    console.log('Toggle expand called for ID:', id, 'Current expandedIds:', expandedIds.value);
    const index = expandedIds.value.indexOf(id);
    if (index > -1) {
        expandedIds.value.splice(index, 1);
    } else {
        expandedIds.value.push(id);
    }
    expandedIds.value = [...expandedIds.value];
    console.log('After toggle, expandedIds:', expandedIds.value);
};

// Helper to check if category has children
const hasChildren = (category) => {
    const children = category.all_children || category.children;
    return children && Array.isArray(children) && children.length > 0;
};

// Helper to collect all parent IDs for auto-expansion
const getAllParentIds = (nodes) => {
    let ids = [];
    nodes.forEach(node => {
        const children = node.all_children || node.children;
        if (children && children.length > 0) {
            ids.push(node.id);
            ids = ids.concat(getAllParentIds(children));
        }
    });
    return ids;
};

// Flatten tree into array for Table display, calculating depth
const flattenTree = (nodes, depth = 0) => {
    if (!nodes) return [];
    let result = [];
    nodes.forEach(node => {
        const flatNode = { ...node, _depth: depth };
        result.push(flatNode);
        
        const children = node.all_children || node.children;
        const hasChildren = children && Array.isArray(children) && children.length > 0;
        
        if (hasChildren && (search.value || expandedIds.value.includes(node.id))) {
            result = result.concat(flattenTree(children, depth + 1));
        }
    });
    return result;
};

// Computed flat list for display
const flatCategories = computed(() => {
    let nodesToFlatten = categories.value;
    // Dependency on expandedIds.value is inside flattenTree
    return flattenTree(nodesToFlatten);
});

const fetchCategories = async (page = 1) => {
    loading.value = true;
    try {
        const params = {
            page: page,
            per_page: pagination.value.per_page,
            tree: true 
        };
        
        if (search.value) {
            params.tree = false;
            params.search = search.value;
        }

        // Add status filter
        if (statusFilter.value && statusFilter.value !== 'all') {
            params.is_active = statusFilter.value === 'active' ? 1 : 0;
        }

        const response = await api.get('/admin/cms/categories', { params });
        
        if (response.data) {
             const data = response.data;
             const meta = data.meta || data; 
             
             if (data.data) {
                 categories.value = data.data;
             } else {
                 categories.value = Array.isArray(data) ? data : [];
             }

             // Auto-expand all by default on first load or page change
             if (!search.value) {
                 expandedIds.value = getAllParentIds(categories.value);
             } else {
                 expandedIds.value = [];
             }

             pagination.value = {
                 current_page: meta.current_page || 1,
                 last_page: meta.last_page || 1,
                 per_page: meta.per_page || 5,
                 total: meta.total || categories.value.length || 0,
                 from: meta.from || 0,
                 to: meta.to || 0
             };
        } else {
            categories.value = [];
        }
        
        selectedIds.value = [];
    } catch (error) {
        console.error('Failed to fetch categories:', error);
    } finally {
        loading.value = false;
    }
};

const debouncedSearch = _.debounce(() => {
    fetchCategories(1);
}, 300);

// Watch search input
watch(search, () => {
    debouncedSearch();
});

// Handle filter change
const onFilterChange = () => {
    fetchCategories(1);
};

const changePage = (page) => {
    if (page >= 1 && page <= pagination.value.last_page) {
        fetchCategories(page);
    }
};

const changePerPage = (perPage) => {
    pagination.value.per_page = parseInt(perPage);
    fetchCategories(1);
};

const editCategory = (category) => {
    router.push({ name: 'categories.edit', params: { id: category.id } });
};

const deleteCategory = async (category) => {
    const confirmed = await confirm({
        title: t('features.categories.actions.delete'),
        message: t('features.categories.messages.deleteConfirm', { name: category.name }),
        variant: 'danger',
        confirmText: t('common.actions.delete'),
    });

    if (confirmed) {
        try {
            await api.delete(`/admin/cms/categories/${category.id}`);
            fetchCategories(pagination.value.current_page);
            toast.success.delete('Category');
        } catch (error) {
            console.error('Failed to delete category:', error);
            toast.error.delete(error, 'Category');
        }
    }
};

// Bulk Actions
const toggleSelect = (checked, id) => {
    if (checked) {
        if (!selectedIds.value.includes(id)) selectedIds.value.push(id);
    } else {
        selectedIds.value = selectedIds.value.filter(itemId => itemId !== id);
    }
};

const toggleSelectAll = (checked) => {
    if (checked) {
        // Select all VISIBLE items (flat)
        selectedIds.value = flatCategories.value.map(c => c.id);
    } else {
        selectedIds.value = [];
    }
};

const isAllSelected = computed(() => {
    return flatCategories.value.length > 0 && 
           flatCategories.value.every(c => selectedIds.value.includes(c.id));
});

const confirmBulkDelete = async () => {
   const confirmed = await confirm({
       title: t('common.actions.bulkDelete'),
       message: t('common.messages.confirm.bulkDelete', { count: selectedIds.value.length }) || `Are you sure you want to delete ${selectedIds.value.length} categories?`,
       variant: 'danger',
       confirmText: t('common.actions.delete'),
   });

   if (confirmed) {
        try {
            await api.post('/admin/cms/categories/bulk-destroy', { ids: selectedIds.value });
            const count = selectedIds.value.length;
            selectedIds.value = [];
            fetchCategories(pagination.value.current_page);
            toast.success.delete(`${count} Categories`);
        } catch (error) {
           console.error('Bulk delete failed:', error);
           toast.error.action(error);
        }
    }
}

onMounted(() => {
    fetchCategories();
});
</script>
