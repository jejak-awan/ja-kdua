<template>
    <div class="fixed inset-0 z-50 overflow-y-auto bg-black bg-opacity-50" @click.self="$emit('close')">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="bg-white rounded-lg shadow-xl max-w-md w-full">
                <!-- Header -->
                <div class="flex items-center justify-between p-6 border-b">
                    <h3 class="text-lg font-semibold">
                        Move Category
                    </h3>
                    <button
                        @click="$emit('close')"
                        class="text-gray-400 hover:text-gray-600"
                    >
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Content -->
                <form @submit.prevent="handleSubmit" class="p-6 space-y-4">
                    <div>
                        <p class="text-sm text-gray-600 mb-4">
                            Move <strong>{{ category.name }}</strong> to:
                        </p>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            New Parent Category
                        </label>
                        <select
                            v-model="selectedParentId"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                        >
                            <option :value="null">No Parent (Root Category)</option>
                            <option
                                v-for="cat in availableParents"
                                :key="cat.id"
                                :value="cat.id"
                            >
                                {{ getCategoryPath(cat) }}
                            </option>
                        </select>
                    </div>
                </form>

                <!-- Footer -->
                <div class="flex items-center justify-end space-x-3 p-6 border-t">
                    <button
                        @click="$emit('close')"
                        class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50"
                    >
                        Cancel
                    </button>
                    <button
                        @click="handleSubmit"
                        :disabled="saving"
                        class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 disabled:opacity-50"
                    >
                        {{ saving ? 'Moving...' : 'Move Category' }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import api from '../../services/api';

const props = defineProps({
    category: {
        type: Object,
        required: true,
    },
    categories: {
        type: Array,
        default: () => [],
    },
});

const emit = defineEmits(['close', 'moved']);

const saving = ref(false);
const selectedParentId = ref(null);

const availableParents = computed(() => {
    // Exclude self and descendants from parent options
    return props.categories.filter(cat => {
        if (cat.id === props.category.id) return false;
        // Check if cat is a descendant of current category
        let check = cat;
        while (check.parent_id) {
            if (check.parent_id === props.category.id) return false;
            check = props.categories.find(c => c.id === check.parent_id);
            if (!check) break;
        }
        return true;
    });
});

const getCategoryPath = (category) => {
    let path = category.name;
    let current = category;
    while (current.parent_id) {
        const parent = props.categories.find(c => c.id === current.parent_id);
        if (parent) {
            path = parent.name + ' > ' + path;
            current = parent;
        } else {
            break;
        }
    }
    return path;
};

const handleSubmit = async () => {
    saving.value = true;
    try {
        await api.post(`/admin/cms/categories/${props.category.id}/move`, {
            parent_id: selectedParentId.value,
        });
        emit('moved');
    } catch (error) {
        console.error('Failed to move category:', error);
        alert(error.response?.data?.message || 'Failed to move category');
    } finally {
        saving.value = false;
    }
};

onMounted(() => {
    selectedParentId.value = props.category.parent_id;
});
</script>

