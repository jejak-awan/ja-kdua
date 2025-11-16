<template>
    <div>
        <!-- Header -->
        <div class="mb-6 flex justify-between items-center">
            <h1 class="text-2xl font-bold text-gray-900">Categories</h1>
            <button
                @click="showCreateModal = true"
                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700"
            >
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                New Category
            </button>
        </div>

        <!-- Filters -->
        <div class="bg-white shadow rounded-lg p-4 mb-4">
            <div class="flex items-center space-x-4">
                <input
                    v-model="search"
                    type="text"
                    placeholder="Search categories..."
                    class="flex-1 px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                >
                <select
                    v-model="viewMode"
                    class="px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                >
                    <option value="tree">Tree View</option>
                    <option value="list">List View</option>
                </select>
            </div>
        </div>

        <!-- Categories List -->
        <div v-if="loading" class="bg-white shadow rounded-lg p-12 text-center">
            <p class="text-gray-500">Loading categories...</p>
        </div>

        <div v-else-if="filteredCategories.length === 0" class="bg-white shadow rounded-lg p-12 text-center">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
            </svg>
            <p class="mt-4 text-gray-500">No categories found</p>
        </div>

        <!-- Tree View -->
        <div v-else-if="viewMode === 'tree'" class="bg-white shadow rounded-lg overflow-hidden">
            <div class="divide-y divide-gray-200">
                <CategoryTreeItem
                    v-for="category in rootCategories"
                    :key="category.id"
                    :category="category"
                    :all-categories="allCategories"
                    @edit="editCategory"
                    @delete="deleteCategory"
                    @move="moveCategory"
                />
            </div>
        </div>

        <!-- List View -->
        <div v-else class="bg-white shadow rounded-lg overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Name
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Slug
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Parent
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Contents
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Status
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <tr v-for="category in filteredCategories" :key="category.id" class="hover:bg-gray-50">
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
                                    <div class="text-sm font-medium text-gray-900">{{ category.name }}</div>
                                    <div v-if="category.description" class="text-sm text-gray-500 truncate max-w-xs">
                                        {{ category.description }}
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">{{ category.slug }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">{{ category.parent?.name || '-' }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">{{ category.contents_count || 0 }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span
                                class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                                :class="category.is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'"
                            >
                                {{ category.is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <div class="flex justify-end space-x-2">
                                <button
                                    @click="showMoveModal(category)"
                                    class="text-blue-600 hover:text-blue-900"
                                    title="Move"
                                >
                                    Move
                                </button>
                                <button
                                    @click="editCategory(category)"
                                    class="text-indigo-600 hover:text-indigo-900"
                                >
                                    Edit
                                </button>
                                <button
                                    @click="deleteCategory(category)"
                                    class="text-red-600 hover:text-red-900"
                                >
                                    Delete
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Create/Edit Modal -->
        <CategoryModal
            v-if="showCreateModal || showEditModal"
            @close="closeModal"
            @saved="handleCategorySaved"
            :category="editingCategory"
        />

        <!-- Move Modal -->
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
import api from '../../../services/api';
import CategoryTreeItem from '../../../components/categories/CategoryTreeItem.vue';
import CategoryModal from '../../../components/categories/CategoryModal.vue';
import MoveCategoryModal from '../../../components/categories/MoveCategoryModal.vue';
import { parseResponse, ensureArray } from '../../../utils/responseParser';

const loading = ref(false);
const categories = ref([]);
const treeCategories = ref([]);
const search = ref('');
const viewMode = ref('tree');
const showCreateModal = ref(false);
const showEditModal = ref(false);
const editingCategory = ref(null);
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
    editingCategory.value = category;
    showEditModal.value = true;
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

const moveCategory = async (category, newParentId, newSortOrder) => {
    try {
        await api.post(`/admin/cms/categories/${category.id}/move`, {
            parent_id: newParentId,
            sort_order: newSortOrder,
        });
        await fetchCategories();
    } catch (error) {
        console.error('Failed to move category:', error);
        alert('Failed to move category');
    }
};

const closeModal = () => {
    showCreateModal.value = false;
    showEditModal.value = false;
    editingCategory.value = null;
};

const handleCategorySaved = () => {
    fetchCategories();
    closeModal();
};

const showMoveModal = (category) => {
    movingCategory.value = category;
};

const closeMoveModal = () => {
    movingCategory.value = null;
};

const handleCategoryMoved = () => {
    fetchCategories();
    closeMoveModal();
};

onMounted(() => {
    fetchCategories();
});
</script>
