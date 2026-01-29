<template>
    <div class="space-y-6">
        <!-- Header -->
        <div v-if="!isEmbedded" class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h1 class="text-3xl font-bold tracking-tight text-foreground">{{ $t('features.categories.title') }}</h1>
                <p class="text-muted-foreground mt-2">{{ $t('features.categories.description') }}</p>
            </div>
            <div class="flex space-x-3">
                <Button 
                    v-if="authStore.hasPermission('create categories')"
                    @click="openCreateModal"
                >
                    <Plus class="w-4 h-4 mr-2" />
                    {{ $t('features.categories.createNew') }}
                </Button>
            </div>
        </div>

        <!-- content -->
        <Card class="overflow-hidden">
            <!-- Filters -->
            <div class="px-6 py-4 border-b border-border/40">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <!-- Left: Search / Filter -->
                    <div class="flex items-center gap-2 flex-1 flex-wrap">
                        <div class="relative w-full sm:w-72">
                            <Search class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" />
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
                    </div>

                    <!-- Right: Actions -->
                    <div class="flex items-center gap-2">
                        <!-- Bulk Actions -->
                        <div v-if="selectedIds.length > 0" class="flex items-center gap-3 p-1.5 px-3 rounded-lg bg-primary/5 border border-primary/10 animate-in fade-in slide-in-from-right-2 mr-2">
                             <span class="text-xs font-semibold text-primary whitespace-nowrap uppercase tracking-wider">
                                {{ selectedIds.length }} {{ $t('common.labels.selected') }}
                             </span>
                             <div class="h-4 w-px bg-primary/20"></div>
                             <Button variant="ghost" size="sm" class="h-7 px-2 text-destructive hover:bg-destructive/10" @click="confirmBulkDelete">
                                <Trash2 class="w-4 h-4 mr-1.5" />
                                {{ $t('common.actions.delete') }}
                            </Button>
                        </div>

                        <!-- Create Button -->
                        <Button 
                            v-if="isEmbedded && authStore.hasPermission('create categories')"
                            size="sm"
                            @click="openCreateModal"
                        >
                            <Plus class="w-4 h-4 mr-1" />
                            {{ $t('features.categories.createNew') }}
                        </Button>
                    </div>
                </div>
            </div>
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
                                <TableHead class="text-xs text-muted-foreground/70">{{ $t('features.categories.table.name') }}</TableHead>
                                <TableHead class="text-xs text-muted-foreground/70">{{ $t('features.categories.table.slug') }}</TableHead>
                                <TableHead class="text-xs text-muted-foreground/70">{{ $t('features.categories.table.status') }}</TableHead>
                                <TableHead class="text-center text-xs text-muted-foreground/70">{{ $t('features.categories.table.actions') }}</TableHead>
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
                                class="group"
                            >
                                <TableCell class="text-center">
                                    <Checkbox 
                                        v-if="authStore.hasPermission('delete categories')"
                                        :checked="selectedIds.includes(category.id)" 
                                        @update:checked="(checked: any) => toggleSelect(!!checked, category.id)"
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
                                    <Badge :variant="(category.is_active ? 'default' : 'secondary') as any">
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
                                            @click="openEditModal(category)"
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

        <!-- Category Modal -->
        <CategoryFormModal
            v-model:open="showModal"
            :category="editingCategory"
            :categories="categories"
            @success="handleModalSuccess"
        />
    </div>
</template>

<script setup lang="ts">
import { ref, onMounted, computed, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import { useRouter } from 'vue-router';
import api from '@/services/api';
import Plus from 'lucide-vue-next/dist/esm/icons/plus.js';
import Search from 'lucide-vue-next/dist/esm/icons/search.js';
import Loader2 from 'lucide-vue-next/dist/esm/icons/loader-circle.js';
import Trash2 from 'lucide-vue-next/dist/esm/icons/trash-2.js';
import Edit2 from 'lucide-vue-next/dist/esm/icons/pen-line.js';
import ChevronRight from 'lucide-vue-next/dist/esm/icons/chevron-right.js';
import Filter from 'lucide-vue-next/dist/esm/icons/list-filter.js';
import { debounce } from '@/utils/debounce';
import { cn } from '@/lib/utils';
import CategoryFormModal from './CategoryFormModal.vue';

// UI Components
import {
    Button,
    Input,
    Badge,
    Checkbox,
    Card,
    CardHeader,
    CardContent,
    Pagination,
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue
} from '@/components/ui';

// Stores & Composables
import { useAuthStore } from '@/stores/auth';
import { useConfirm } from '@/composables/useConfirm';
import { useToast } from '@/composables/useToast';
import type { Category } from '@/types/cms';
import { parseResponse, type PaginationData } from '@/utils/responseParser';

interface FlatCategory extends Category {
    _depth: number;
}

const props = defineProps<{
    isEmbedded?: boolean;
}>();

const { t } = useI18n();
const router = useRouter();
const authStore = useAuthStore();
const toast = useToast();

const loading = ref(true);
const categories = ref<Category[]>([]); // Raw tree data
const search = ref('');
const statusFilter = ref('all');
const selectedIds = ref<number[]>([]);
const expandedIds = ref<number[]>([]); // Track expanded nodes
const pagination = ref<PaginationData>({
    current_page: 1,
    last_page: 1,
    per_page: 5,
    total: 0,
    from: 1,
    to: 1
});

// Modal State
const showModal = ref(false);
const editingCategory = ref<Category | null>(null);

const { confirm } = useConfirm();

// Toggle expand/collapse
const toggleExpand = (id: number) => {
    const index = expandedIds.value.indexOf(id);
    if (index > -1) {
        expandedIds.value.splice(index, 1);
    } else {
        expandedIds.value.push(id);
    }
};

// Helper to check if category has children
const hasChildren = (category: Category): boolean => {
    const children = category.all_children || category.children;
    return !!(children && Array.isArray(children) && children.length > 0);
};

// Helper to collect all parent IDs for auto-expansion
const getAllParentIds = (nodes: Category[]): number[] => {
    let ids: number[] = [];
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
const flattenTree = (nodes: Category[], depth = 0): FlatCategory[] => {
    if (!nodes) return [];
    let result: FlatCategory[] = [];
    nodes.forEach(node => {
        const flatNode: FlatCategory = { ...node, _depth: depth };
        result.push(flatNode);
        
        const children = node.all_children || node.children;
        const hasKids = children && Array.isArray(children) && children.length > 0;
        
        if (hasKids && (search.value || expandedIds.value.includes(node.id))) {
            result = result.concat(flattenTree(children, depth + 1));
        }
    });
    return result;
};

// Computed flat list for display
const flatCategories = computed(() => {
    return flattenTree(categories.value);
});

const fetchCategories = async (page = 1) => {
    loading.value = true;
    try {
        const params: Record<string, any> = {
            page: page,
            per_page: pagination.value.per_page,
            tree: true 
        };
        
        if (search.value) {
            params.tree = false;
            params.search = search.value;
        }

        if (statusFilter.value && statusFilter.value !== 'all') {
            params.is_active = statusFilter.value === 'active' ? 1 : 0;
        }

        const response = await api.get('/admin/ja/categories', { params });
        const { data, pagination: paginationData } = parseResponse<Category>(response);
        
        categories.value = data || [];

        // Auto-expand all by default on first load or page change
        if (!search.value) {
            expandedIds.value = getAllParentIds(categories.value);
        } else {
            expandedIds.value = [];
        }

        if (paginationData) {
            pagination.value = paginationData;
        } else {
            pagination.value.total = categories.value.length;
            pagination.value.current_page = 1;
        }
        
        selectedIds.value = [];
    } catch (error: any) {
        console.error('Failed to fetch categories:', error);
    } finally {
        loading.value = false;
    }
};

const debouncedSearch = debounce(() => {
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

const changePage = (page: number) => {
    if (page >= 1 && page <= pagination.value.last_page) {
        fetchCategories(page);
    }
};

const changePerPage = (perPage: string | number) => {
    pagination.value.per_page = typeof perPage === 'string' ? parseInt(perPage) : perPage;
    fetchCategories(1);
};

// Modal Actions
const openCreateModal = () => {
    editingCategory.value = null;
    showModal.value = true;
};

const openEditModal = (category: Category) => {
    // Clone to specific object to avoid binding issues
    editingCategory.value = { ...category };
    showModal.value = true;
};

const handleModalSuccess = () => {
    fetchCategories(pagination.value.current_page);
};

// const editCategory REMOVED - replaced by openEditModal

const deleteCategory = async (category: Category) => {
    const confirmed = await confirm({
        title: t('features.categories.actions.delete'),
        message: t('features.categories.messages.deleteConfirm', { name: category.name }),
        variant: 'danger',
        confirmText: t('common.actions.delete'),
    });

    if (confirmed) {
        try {
            await api.delete(`/admin/ja/categories/${category.id}`);
            fetchCategories(pagination.value.current_page);
            toast.success.delete('Category');
        } catch (error: any) {
            console.error('Failed to delete category:', error);
            toast.error.delete(error, 'Category');
        }
    }
};

// Bulk Actions
const toggleSelect = (checked: boolean, id: number) => {
    if (checked) {
        if (!selectedIds.value.includes(id)) selectedIds.value.push(id);
    } else {
        selectedIds.value = selectedIds.value.filter(itemId => itemId !== id);
    }
};

const toggleSelectAll = (checked: boolean) => {
    if (checked) {
        // Select all VISIBLE items (flat)
        selectedIds.value = flatCategories.value.map((c: any) => c.id);
    } else {
        selectedIds.value = [];
    }
};

const isAllSelected = computed(() => {
    return flatCategories.value.length > 0 && 
           flatCategories.value.every((c: any) => selectedIds.value.includes(c.id));
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
            await api.post('/admin/ja/categories/bulk-destroy', { ids: selectedIds.value });
            const count = selectedIds.value.length;
            selectedIds.value = [];
            fetchCategories(pagination.value.current_page);
            toast.success.delete(`${count} Categories`);
        } catch (error: any) {
           console.error('Bulk delete failed:', error);
           toast.error.action(error);
        }
    }
}

onMounted(() => {
    fetchCategories();
});
</script>
