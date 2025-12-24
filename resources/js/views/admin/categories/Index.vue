<template>
    <div>
        <!-- Header -->
        <div class="mb-6 flex justify-between items-center">
            <h1 class="text-2xl font-bold text-foreground">{{ $t('features.categories.title') }}</h1>
            <router-link
                :to="{ name: 'categories.create' }"
                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-primary-foreground bg-primary hover:bg-primary/80"
            >
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                {{ $t('features.categories.createNew') }}
            </router-link>
        </div>

        <!-- Filters -->
        <div class="bg-card border border-border rounded-lg p-4 mb-4">
            <div class="flex items-center space-x-4">
                <input
                    v-model="search"
                    type="text"
                    :placeholder="$t('features.categories.search')"
                    class="flex-1 px-4 py-2 border border-input bg-card text-foreground rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                >
                <select
                    v-model="viewMode"
                    class="px-4 py-2 border border-input bg-card text-foreground rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                >
                    <option value="tree">{{ $t('features.categories.viewMode.tree') }}</option>
                    <option value="list">{{ $t('features.categories.viewMode.list') }}</option>
                </select>
            </div>
        </div>

        <!-- Categories List -->
        <div v-if="loading" class="bg-card border border-border rounded-lg p-12 text-center">
            <p class="text-muted-foreground">{{ $t('features.categories.loading') }}</p>
        </div>

        <div v-else-if="filteredCategories.length === 0" class="bg-card border border-border rounded-lg p-12 text-center">
            <svg class="mx-auto h-12 w-12 text-muted-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
            </svg>
            <p class="mt-4 text-muted-foreground">{{ $t('features.categories.empty') }}</p>
        </div>

        <!-- Tree View -->
        <div v-else-if="viewMode === 'tree'" class="bg-card border border-border rounded-lg overflow-hidden">
            <div class="divide-y divide-border">
                <CategoryTreeItem
                    v-for="category in rootCategories"
                    :key="category.id"
                    :category="category"
                    :all-categories="allCategories"
                    @edit="editCategory"
                    @delete="deleteCategory"
                    @move="showMoveModal"
                />
            </div>
        </div>

        <!-- List View -->
        <div v-else class="bg-card border border-border rounded-lg overflow-hidden">
            <table class="min-w-full divide-y divide-border">
                <thead class="bg-muted">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground tracking-wider">
                            {{ $t('features.categories.table.name') }}
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground tracking-wider">
                            {{ $t('features.categories.table.slug') }}
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground tracking-wider">
                            {{ $t('features.categories.table.parent') }}
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground tracking-wider">
                            {{ $t('features.categories.table.contents') }}
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground tracking-wider">
                            {{ $t('features.categories.table.status') }}
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-muted-foreground tracking-wider">
                            {{ $t('features.categories.table.actions') }}
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-card divide-y divide-border">
                    <tr v-for="category in filteredCategories" :key="category.id" class="hover:bg-muted">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div
                                    v-if="category.image"
                                    class="flex-shrink-0 h-10 w-10 mr-3"
                                >
                                    <img
                                        :src="category.image"
                                        :alt="category.name"
                                        class="h-10 w-10 rounded-full object-cover"
                                    >
                                </div>
                                <div>
                                    <div class="text-sm font-medium text-foreground">{{ category.name }}</div>
                                    <div v-if="category.description" class="text-sm text-muted-foreground truncate max-w-xs">
                                        {{ category.description }}
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-foreground">{{ category.slug }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-foreground">{{ category.parent?.name || '-' }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-foreground">{{ category.contents_count || 0 }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span
                                class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                                :class="category.is_active ? 'bg-green-500/20 text-green-400' : 'bg-secondary text-secondary-foreground'"
                            >
                                {{ category.is_active ? $t('features.categories.status.active') : $t('features.categories.status.inactive') }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <div class="flex justify-end space-x-2">
                                <button
                                    @click="showMoveModal(category)"
                                    class="text-blue-600 hover:text-blue-900"
                                    :title="$t('features.categories.actions.move')"
                                >
                                    {{ $t('features.categories.actions.move') }}
                                </button>
                                <button
                                    @click="editCategory(category)"
                                    class="text-indigo-600 hover:text-indigo-900"
                                >
                                    {{ $t('features.categories.actions.edit') }}
                                </button>
                                <button
                                    @click="deleteCategory(category)"
                                    class="text-red-600 hover:text-red-900"
                                >
                                    {{ $t('features.categories.actions.delete') }}
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Move Modal (Kept as is) -->
        <MoveCategoryModal
            v-if="movingCategory"
            @close="closeMoveModal"
            @moved="handleCategoryMoved"
            :category="movingCategory"
            :categories="allCategories"
        />
    </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { useRouter } from 'vue-router';
import api from '../../../services/api';
import CategoryTreeItem from '../../../components/categories/CategoryTreeItem.vue';
import MoveCategoryModal from '../../../components/categories/MoveCategoryModal.vue';
import { parseResponse, ensureArray } from '../../../utils/responseParser';

const { t } = useI18n();
const router = useRouter();
const loading = ref(false);
const categories = ref([]);
const treeCategories = ref([]);
const search = ref('');
const viewMode = ref('tree');
const movingCategory = ref(null);

const allCategories = computed(() => categories.value);

const rootCategories = computed(() => {
    // For tree view, use tree structure
    if (viewMode.value === 'tree') {
        if (!Array.isArray(treeCategories.value)) {
            return [];
        }
        return treeCategories.value.filter(cat => cat && !cat.parent_id);
    }
    // For list view, use flat list
    if (!Array.isArray(categories.value)) {
        return [];
    }
    return categories.value.filter(cat => cat && !cat.parent_id);
});

const filteredCategories = computed(() => {
    if (!Array.isArray(categories.value)) {
        return [];
    }
    if (!search.value) return categories.value;
    
    const searchLower = search.value.toLowerCase();
    return categories.value.filter(cat => {
        if (!cat) return false;
        return cat?.name?.toLowerCase().includes(searchLower) ||
        cat?.slug?.toLowerCase().includes(searchLower) ||
        (cat?.description && cat.description.toLowerCase().includes(searchLower));
    });
});

const fetchCategories = async () => {
    loading.value = true;
    try {
        // Fetch tree structure
        const treeResponse = await api.get('/admin/cms/categories?tree=true');
        const { data: treeData } = parseResponse(treeResponse);
        const treeArray = ensureArray(treeData);
        
        // Store tree data for tree view
        treeCategories.value = treeArray;
        
        // Flatten tree structure for list view
        const flattenTree = (items) => {
            if (!Array.isArray(items)) {
                return [];
            }
            let result = [];
            items.forEach(item => {
                if (!item) return;
                const flatItem = { ...item };
                // Remove children for flat list
                delete flatItem.children;
                result.push(flatItem);
                if (item.children && Array.isArray(item.children) && item.children.length > 0) {
                    result = result.concat(flattenTree(item.children));
                }
            });
            return result;
        };
        
        categories.value = flattenTree(treeArray);
    } catch (error) {
        console.error('Failed to fetch categories:', error);
        categories.value = [];
        treeCategories.value = [];
    } finally {
        loading.value = false;
    }
};

const editCategory = (category) => {
    router.push({ name: 'categories.edit', params: { id: category.id } });
};

const deleteCategory = async (category) => {
    if (!category || !category.name) {
        return;
    }
    if (!confirm(`Are you sure you want to delete "${category.name}"?`)) {
        return;
    }

    try {
        await api.delete(`/admin/cms/categories/${category.id}`);
        await fetchCategories();
    } catch (error) {
        console.error('Failed to delete category:', error);
        const message = error.response?.data?.message || 'Failed to delete category';
        alert(message);
    }
};

const showMoveModal = (category) => {
    movingCategory.value = category;
};

const closeMoveModal = () => {
    movingCategory.value = null;
};

const handleCategoryMoved = () => {
    closeMoveModal();
    fetchCategories();
};

onMounted(() => {
    fetchCategories();
});
</script>
